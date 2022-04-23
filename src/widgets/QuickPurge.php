<?php
/**
 * Cloudflare plugin for Craft CMS 4.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2017 Working Concept
 */

namespace workingconcept\cloudflare\widgets;

use Craft;
use craft\base\Widget;
use workingconcept\cloudflare\assetbundles\CloudflareAsset;
use workingconcept\cloudflare\Cloudflare;
use workingconcept\cloudflare\helpers\ConfigHelper;

/**
 * Cloudflare Widget
 *
 * @author    Working Concept
 * @package   Cloudflare
 * @since     1.0.0
 *
 * @property-read string|false $bodyHtml
 * @property-read string       $title
 */
class QuickPurge extends Widget
{
    /**
     * Disallow multiple widget instances.
     *
     * @return bool
     */
    protected static function allowMultipleInstances(): bool
    {
        return false;
    }

    /**
     * Returns the translated widget display name.
     *
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('cloudflare', 'Cloudflare Purge');
    }

    /**
     * Returns the widget’s icon path.
     *
     * @return string
     */
    public static function icon(): string
    {
        return Craft::getAlias("@workingconcept/cloudflare/assetbundles/dist/img/quickpurge-icon.svg");
    }

    /**
     * Sets the maximum column span to 1.
     *
     * @return int
     */
    public static function maxColspan(): int
    {
        return 1;
    }

    /**
     * Returns the translated widget title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return Craft::t('cloudflare', 'Cloudflare Purge');
    }

    /**
     * Returns the widget body HTML.
     *
     * @return ?string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getBodyHtml(): ?string
    {
        Craft::$app->getView()->registerAssetBundle(CloudflareAsset::class);

        return Craft::$app->getView()->renderTemplate(
            'cloudflare/widget',
            [
                'settings' => Cloudflare::getInstance()->getSettings(),
                'isConfigured' => ConfigHelper::isConfigured(),
            ]
        );
    }
}
