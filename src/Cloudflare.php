<?php

namespace mattstein\cloudflare;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Dashboard;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use mattstein\cloudflare\models\Settings;
use mattstein\cloudflare\services\CloudflareService;
use mattstein\cloudflare\services\RulesService;
use mattstein\cloudflare\variables\CloudflareVariable;
use mattstein\cloudflare\widgets\QuickPurgeWidget;
use yii\base\Event;

/**
 * Provides a Cloudflare plugin to craft
 *
 * @property \mattstein\cloudflare\services\CloudflareService cloudflare
 * @property \mattstein\cloudflare\services\RulesService      rules
 * @package mattstein\cloudflare
 */
class Cloudflare extends Plugin {

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

    /**
     * Creates and returns the model used to store the plugin settings.
     *
     * @return \mattstein\cloudflare\Models\Settings
     */
    protected function createSettingsModel(): Settings {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();

        // load the services
        $this->setComponents( [
                                  'cloudflare' => CloudflareService::class,
                                  'rules'      => RulesService::class
                              ] );

        // register the widget
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function( RegisterComponentTypesEvent $event ) {
                $event->types[] = QuickPurgeWidget::class;
            }
        );

        // register the variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function( Event $event ) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set( 'cloudflare', CloudflareVariable::class );
            }
        );

        // register the actions
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function( RegisterUrlRulesEvent $event ) {
                $event->rules[ 'POST ' . $this->getSettings()->fetchZonesActionUri ] = 'cloudflare/fetch-zones';
                $event->rules[ 'POST ' . $this->getSettings()->purgeUrlsActionUri ]  = 'cloudflare/purge-urls';
                $event->rules[ 'POST ' . $this->getSettings()->purgeAllActionUri ]   = 'cloudflare/purge-all';
            }
        );

        Craft::info( Craft::t(
            'cloudflare',
            '{name} plugin loaded',
            [ 'name' => $this->name ]
        ), __METHOD__ );
    }

    /**
     * Retrieves the plugin settings HTML
     *
     * @return null|string
     * @throws \RuntimeException
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    protected function settingsHtml() {
        return Craft::$app->getView()->renderTemplate( 'cloudflare/settings', [
            'settings' => $this->getSettings()
        ] );
    }
}
