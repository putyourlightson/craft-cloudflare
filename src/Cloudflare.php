<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2017 Working Concept
 */

namespace workingconcept\cloudflare;

use workingconcept\cloudflare\helpers\ConfigHelper;
use workingconcept\cloudflare\services\Api;
use workingconcept\cloudflare\services\Rules;
use workingconcept\cloudflare\variables\CloudflareVariable;
use workingconcept\cloudflare\models\Settings;
use workingconcept\cloudflare\widgets\QuickPurge as QuickPurgeWidget;

use Craft;
use craft\console\Application as ConsoleApplication;
use craft\base\Plugin;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\services\Dashboard;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\ElementEvent;
use craft\services\Elements;
use craft\helpers\UrlHelper;
use yii\base\Event;

/**
 * Class Cloudflare
 *
 * @author    Working Concept
 * @package   Cloudflare
 * @since     1.0.0
 *
 * @property  Api   $api
 * @property  Rules $rules
 */
class Cloudflare extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Cloudflare
     */
    public static $plugin;

    /**
     * @var array
     */
    public static $supportedElementTypes = [
        'craft\elements\Asset',
        'craft\elements\Category',
        'craft\elements\Entry',
        'craft\elements\Tag',
        'craft\commerce\elements\Variant',
        'craft\commerce\elements\Product',
    ];

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var string
     */
    public $t9nCategory = 'cloudflare';


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'api'   => Api::class,
            'rules' => Rules::class
        ]);

        // register the widget
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            static function (RegisterComponentTypesEvent $event) {
                $event->types[] = QuickPurgeWidget::class;
            }
        );

        // register the variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            static function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('cloudflare', CloudflareVariable::class);
            }
        );

        if (Craft::$app->getRequest()->getIsCpRequest())
        {
            // register the actions
            Event::on(
                UrlManager::class,
                UrlManager::EVENT_REGISTER_CP_URL_RULES,
                static function(RegisterUrlRulesEvent $event) {
                    $event->rules['cloudflare/rules'] = [
                        'template' => 'cloudflare/rules'
                    ];
                }
            );
        }

        if (
            ConfigHelper::isConfigured() &&
            ($this->getSettings()->purgeEntryUrls || $this->getSettings()->purgeAssetUrls)
        )
        {
            Event::on(
                Elements::class,
                Elements::EVENT_AFTER_SAVE_ELEMENT,
                function(ElementEvent $event) {
                    $this->_handleElementChange(
                        $event->isNew,
                        $event->element
                    );
                }
            );

            Event::on(
                Elements::class,
                Elements::EVENT_AFTER_DELETE_ELEMENT,
                function(ElementEvent $event) {
                    $this->_handleElementChange(
                        $event->isNew,
                        $event->element
                    );
                }
            );
        }

        if (Craft::$app instanceof ConsoleApplication)
        {
            $this->controllerNamespace = 'workingconcept\cloudflare\console\controllers';
        }

        Craft::info(
            Craft::t(
                'cloudflare',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            'cloudflare'
        );
    }

    /**
     * Store the selected Cloudflare Zone's base URL for later comparison.
     *
     * @return bool
     */
    public function beforeSaveSettings(): bool
    {
        $settings = $this->getSettings();

        // save the human-friendly zone name if we have one
        if ($zoneInfo = $this->api->getZoneById(
            ConfigHelper::getParsedSetting('zone')
        ))
        {
            $settings->zoneName = $zoneInfo->name;
        }

        // don't save stale key credentials
        if ($settings->authType === Settings::AUTH_TYPE_TOKEN)
        {
            $settings->apiKey = null;
            $settings->email = null;
        }

        // don't save stale token
        if ($settings->authType === Settings::AUTH_TYPE_KEY)
        {
            $settings->apiToken = null;
        }

        return true;
    }


    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'cloudflare/settings',
            [
                'settings'  => $this->getSettings(),
                'isConfigured' => ConfigHelper::isConfigured(),
                'isCraft31' => ConfigHelper::isCraft31(),
                'elementTypes' => $this->_getElementTypeOptions()
            ]
        );
    }


    // Private Methods
    // =========================================================================

    /**
     * Returns element types that should be available options for
     * automatic purging.
     *
     * @return array|string[]
     */
    private function _getElementTypeOptions(): array
    {
        $options = [];
        $service = Craft::$app->getElements();
        $elementTypes = $service->getAllElementTypes();

        foreach ($elementTypes as $elementType)
        {
            // only make the option available if we support it
            if ($this->_isSupportedElementType($elementType))
            {
                $options[$elementType] = $elementType::pluralDisplayName();
            }
        }

        return $options;
    }

    /**
     * Returns `true` is the given element type is one we support,
     * mostly to be sure there’s a chance its element will have a URL.
     *
     * @param string $elementType
     *
     * @return bool
     */
    private function _isSupportedElementType($elementType): bool
    {
        $elementType = ConfigHelper::normalizeClassName($elementType);
        
        return in_array($elementType, self::$supportedElementTypes, true);
    }

    /**
     * Returns `true` if the provided element type is both supported and
     * enabled for purging in the plugin’s settings.
     *
     * @param $elementType
     *
     * @return bool
     */
    private function _shouldPurgeElementType($elementType): bool
    {
        if ( ! $this->_isSupportedElementType($elementType))
        {
            return false;
        }

        $elementType = ConfigHelper::normalizeClassName($elementType);
        $purgeElements = $this->getSettings()->purgeElements;

        if (empty($purgeElements) || ! is_array($purgeElements))
        {
            return false;
        }

        return in_array($elementType, $purgeElements, true);
    }

    /**
     * @param bool $isNew
     * @param \craft\base\ElementInterface|null $element
     *
     * @throws \yii\base\Exception
     */
    private function _handleElementChange(bool $isNew, $element)
    {
        /**
         * Bail if we don't have an Element or an Element URL to work with.
         */
        if ($element === null || $element->getUrl() === null)
        {
            return;
        }

        $className = get_class($element);

        if (! $isNew && $this->_shouldPurgeElementType($className))
        {
            $elementUrl = $element->getUrl();

            /**
             * Try making relative URLs absolute.
             */
            if (strpos($elementUrl, '//') === false)
            {
                $elementUrl = UrlHelper::siteUrl($elementUrl);
            }

            $this->api->purgeUrls([
                $elementUrl
            ]);
        }

        /**
         * Honor any explicit rules that match this URL, regardless
         * of whatever Element it is.
         */
        $this->rules->purgeCachesForUrl(
            $element->getUrl()
        );
    }

}
