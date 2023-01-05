<?php
/**
 * @copyright Copyright (c) 2017 Working Concept
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\cloudflare;

use Craft;
use craft\base\ElementInterface;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\ElementEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\ElementHelper;
use craft\helpers\Queue;
use craft\helpers\UrlHelper;
use craft\services\Dashboard;
use craft\services\Elements;
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use putyourlightson\cloudflare\helpers\ConfigHelper;
use putyourlightson\cloudflare\models\Settings;
use putyourlightson\cloudflare\queue\jobs\PurgeCloudflareCache;
use putyourlightson\cloudflare\services\Api;
use putyourlightson\cloudflare\services\Rules;
use putyourlightson\cloudflare\utilities\PurgeUtility;
use putyourlightson\cloudflare\variables\CloudflareVariable;
use putyourlightson\cloudflare\widgets\QuickPurge;
use yii\base\Event;

/**
 * @property-read Api $api
 * @property-read Rules $rules
 * @property-read Settings $settings
 *
 * @method Settings getSettings()
 */
class Cloudflare extends Plugin
{
    /**
     * @var Cloudflare
     */
    public static Cloudflare $plugin;

    /**
     * @inerhitdoc
     */
    public static function config(): array
    {
        return [
            'components' => [
                'api' => ['class' => Api::class],
                'rules' => ['class' => Rules::class],
            ],
        ];
    }

    /**
     * @var string[]
     */
    public static array $supportedElementTypes = [
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
    public bool $hasCpSettings = true;

    /**
     * @var bool
     */
    public bool $hasCpSection = false;

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.1';

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        $this->_registerVariables();

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->_registerCpUrlRules();
            $this->_registerUtility();
            $this->_registerWidgets();
        }

        if (ConfigHelper::isConfigured() && !empty($this->getSettings()->purgeElements)) {
            $this->_registerElementChangeEvents();
        }
    }

    /**
     * Store the selected Cloudflare Zone's base URL for later comparison.
     */
    public function beforeSaveSettings(): bool
    {
        $settings = $this->getSettings();
        $zoneInfo = $this->api->getZoneById(ConfigHelper::getParsedSetting('zone'));

        // Save the human-friendly zone name if we have one
        if ($zoneInfo) {
            $settings->zoneName = $zoneInfo->name;
        }

        // Don’t save stale key credentials
        if ($settings->authType === Settings::AUTH_TYPE_TOKEN) {
            $settings->apiKey = null;
            $settings->email = null;
        }

        // Don’t save stale token
        if ($settings->authType === Settings::AUTH_TYPE_KEY) {
            $settings->apiToken = null;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate('cloudflare/settings', [
            'settings' => $this->getSettings(),
            'isConfigured' => ConfigHelper::isConfigured(),
            'elementTypes' => $this->_getElementTypeOptions(),
        ]);
    }

    private function _registerVariables(): void
    {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            static function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('cloudflare', CloudflareVariable::class);
            }
        );
    }

    private function _registerCpUrlRules(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            static function(RegisterUrlRulesEvent $event) {
                $event->rules['cloudflare/rules'] = [
                    'template' => 'cloudflare/rules',
                ];
            }
        );
    }

    private function _registerUtility(): void
    {
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            static function(RegisterComponentTypesEvent $event) {
                $event->types[] = PurgeUtility::class;
            }
        );
    }

    private function _registerWidgets(): void
    {
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            static function(RegisterComponentTypesEvent $event) {
                $event->types[] = QuickPurge::class;
            }
        );
    }

    private function _registerElementChangeEvents(): void
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

    /**
     * Returns element types that should be available options for
     * automatic purging.
     *
     * @return string[]
     */
    private function _getElementTypeOptions(): array
    {
        $options = [];
        $service = Craft::$app->getElements();
        $elementTypes = $service->getAllElementTypes();

        foreach ($elementTypes as $elementType) {
            // only make the option available if we support it
            if ($this->_isSupportedElementType($elementType)) {
                /** @var ElementInterface|string $elementType */
                $options[$elementType] = $elementType::pluralDisplayName();
            }
        }

        return $options;
    }

    /**
     * Returns `true` is the given element type is one we support,
     * mostly to be sure there’s a chance its element will have a URL.
     */
    private function _isSupportedElementType(string $elementType): bool
    {
        $elementType = ConfigHelper::normalizeClassName($elementType);

        return in_array($elementType, self::$supportedElementTypes, true);
    }

    /**
     * Returns `true` if the provided element type is both supported and
     * enabled for purging in the plugin’s settings.
     */
    private function _shouldPurgeElementType(string $elementType): bool
    {
        if (!$this->_isSupportedElementType($elementType)) {
            return false;
        }

        $elementType = ConfigHelper::normalizeClassName($elementType);

        $settings = $this->getSettings();

        if (empty($settings->purgeElements)) {
            return false;
        }

        return in_array($elementType, $settings->purgeElements, true);
    }

    private function _handleElementChange(bool $isNew, ?ElementInterface $element): void
    {
        // Bail if we don’t have an Element or an Element URL to work with
        if ($element === null || $element->getUrl() === null) {
            return;
        }

        // Bail if this is not published
        if (ElementHelper::isDraftOrRevision($element)) {
            return;
        }

        $className = get_class($element);

        if (!$isNew && $this->_shouldPurgeElementType($className)) {
            $elementUrl = $element->getUrl();

            // Try making relative URLs absolute.
            if (!str_contains($elementUrl, '//')) {
                $elementUrl = UrlHelper::siteUrl($elementUrl);
            }

            Queue::push(new PurgeCloudflareCache(['urls' => [$elementUrl]]));
        }

        // Honour any explicit rules that match this URL, regardless of whatever Element it is.
        $this->rules->purgeCachesForUrl(
            $element->getUrl()
        );
    }
}
