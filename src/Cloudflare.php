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
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
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
 * @property  CloudflareService $cloudflareService
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
     * @var bool
     */
    public $hasCpSection = false;

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
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = QuickPurgeWidget::class;
            }
        );

        // register the variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('cloudflare', CloudflareVariable::class);
            }
        );

        // register the actions
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['cloudflare/rules'] = ['template' => 'cloudflare/rules'];
            }
        );

        if (Cloudflare::$plugin->settings->purgeEntryUrls || Cloudflare::$plugin->settings->purgeAssetUrls)
        {
            Event::on(
                Elements::class,
                Elements::EVENT_AFTER_SAVE_ELEMENT,
                function(ElementEvent $event) {

                    $isClearableEntry = Cloudflare::$plugin->settings->purgeEntryUrls && is_a($event->element, 'craft\elements\Entry');
                    $isClearableAsset = Cloudflare::$plugin->settings->purgeAssetUrls && is_a($event->element, 'craft\elements\Asset');

                    if (
                        ! $event->isNew && ! empty($event->element->url) // not new, has URL
                    )
                    {
                        if ($isClearableEntry || $isClearableAsset)
                        {
                            Cloudflare::$plugin->cloudflareService->purgeUrls([
                                UrlHelper::siteUrl($event->element->url)
                            ]);
                        }

                        // honor any explicit rules that match this URL
                        Cloudflare::$plugin->rulesService->purgeCachesForUrl($event->element->url);
                    }
                }
            );

            Event::on(
                Elements::class,
                Elements::EVENT_AFTER_DELETE_ELEMENT,
                function(ElementEvent $event) {

                    $isClearableEntry = Cloudflare::$plugin->settings->purgeEntryUrls && is_a($event->element, 'craft\elements\Entry');
                    $isClearableAsset = Cloudflare::$plugin->settings->purgeAssetUrls && is_a($event->element, 'craft\elements\Asset');

                    if (
                        ! $event->isNew && ! empty($event->element->url) // not new, has URL
                    )
                    {
                        if ($isClearableEntry || $isClearableAsset)
                        {
                            Cloudflare::$plugin->cloudflareService->purgeUrls([
                                UrlHelper::siteUrl($event->element->url)
                            ]);
                        }

                        // honor any explicit rules that match this URL
                        Cloudflare::$plugin->rulesService->purgeCachesForUrl($event->element->url);
                    }
                }
            );
        }

        Craft::info(
            Craft::t(
                'cloudflare',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
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
                'settings' => $this->getSettings()
            ]
        );
    }

}
