<?php
/**
 * @copyright Copyright (c) 2017 Working Concept
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\cloudflare\widgets;

use Craft;
use craft\base\Widget;
use putyourlightson\cloudflare\assets\CloudflareAsset;
use putyourlightson\cloudflare\Cloudflare;
use putyourlightson\cloudflare\helpers\ConfigHelper;

/**
 * @property-read string|false $bodyHtml
 * @property-read string       $title
 */
class QuickPurge extends Widget
{
    /**
     * @inerhitdoc
     */
    protected static function allowMultipleInstances(): bool
    {
        return false;
    }

    /**
     * @inerhitdoc
     */
    public static function displayName(): string
    {
        return Craft::t('cloudflare', 'Cloudflare Purge');
    }

    /**
     * @inerhitdoc
     */
    public static function icon(): string
    {
        return Craft::getAlias("@putyourlightson/cloudflare/resources/images/quickpurge-icon.svg");
    }

    /**
     * @inerhitdoc
     */
    public static function maxColspan(): int
    {
        return 1;
    }

    /**
     * @inerhitdoc
     */
    public function getTitle(): string
    {
        return Craft::t('cloudflare', 'Cloudflare Purge');
    }

    /**
     * @inerhitdoc
     */
    public function getBodyHtml(): ?string
    {
        Craft::$app->getView()->registerAssetBundle(CloudflareAsset::class);

        return Craft::$app->getView()->renderTemplate(
            'cloudflare/widget',
            [
                'settings' => Cloudflare::$plugin->getSettings(),
                'isConfigured' => ConfigHelper::isConfigured(),
            ]
        );
    }
}
