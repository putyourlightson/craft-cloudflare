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

use workingconcept\cloudflare\services\CloudflareService;
use workingconcept\cloudflare\services\RulesService;
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
 * @property  CloudflareService $cloudflare
 * @property  RulesService      $rules
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
     * @var bool
     */
    public $hasCpSettings = true;

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
            'cloudflare' => CloudflareService::class,
            'rules'      => RulesService::class
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
            $this->cloudflare->isConfigured() &&
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
        if ($zoneInfo = $this->cloudflare->getZoneById($settings->zone))
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
                'isCraft31' => version_compare(Craft::$app->getVersion(), '3.1', '>='),
            ]
        );
    }


    // Private Methods
    // =========================================================================

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

        $isClearableEntry = $this->getSettings()->purgeEntryUrls &&
            is_a($element, \craft\elements\Entry::class);

        $isClearableAsset = $this->getSettings()->purgeAssetUrls &&
            is_a($element, \craft\elements\Asset::class);

        if (! $isNew && ($isClearableEntry || $isClearableAsset))
        {
            $elementUrl = $element->getUrl();

            /**
             * Try making relative URLs absolute.
             */
            if (strpos($elementUrl, '//') === false)
            {
                $elementUrl = UrlHelper::siteUrl($elementUrl);
            }

            $this->cloudflare->purgeUrls([
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
