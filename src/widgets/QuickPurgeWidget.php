<?php

namespace mattstein\cloudflare\widgets;

use Craft;
use craft\base\Widget;
use mattstein\cloudflare\Cloudflare;

/**
 * Provides a quick purge widget
 *
 * @package mattstein\cloudflare
 */
class QuickPurgeWidget extends Widget {

    /**
     * Disallow multiple widget instances.
     *
     * @return bool
     */
    protected static function allowMultipleInstances(): bool {
        return false;
    }

    /**
     * Returns the translated widget display name.
     *
     * @return string
     */
    public static function displayName(): string {
        return Craft::t( 'cloudflare', 'Cloudflare Purge' );
    }

    /**
     * Returns the widget body HTML.

     *
     * @return false|string
     * @throws \RuntimeException
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getBodyHtml(): string {
        $settings = Cloudflare::getInstance()->getSettings();

        return Craft::$app->getView()->renderTemplate( 'cloudflare/widget', [
            'settings' => $settings
        ] );
    }

    /**
     * Returns the translated widget title.
     *
     * @return string
     */
    public function getTitle(): string {
        return Craft::t( 'cloudflare', 'Cloudflare Purge' );
    }

    /**
     * Sets the maximum column span to 1.
     *
     * @return int
     */
    public static function maxColspan(): int {
        return 1;
    }

    /**
     * Returns the widget's icon path.
     *
     * @return string
     */
    public function getIconPath(): string {
        /** @noinspection SpellCheckingInspection */
        return '@mattstein/cloudflare/resources/cloudflarepurgewidget/dist/img/CloudflarePurge-icon.svg';
    }

}
