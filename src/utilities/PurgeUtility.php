<?php
/**
 * @copyright Copyright (c) Working Concept
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\cloudflare\utilities;

use Craft;
use craft\base\Utility;
use putyourlightson\cloudflare\Cloudflare;

class PurgeUtility extends Utility
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('cloudflare', 'Cloudflare');
    }

    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'cloudflare-purge';
    }

    /**
     * @inheritdoc
     */
    public static function icon(): ?string
    {
        return Craft::getAlias('@putyourlightson/cloudflare/resources/images/cloud.svg');
    }

    /**
     * @inheritdoc
     */
    public static function contentHtml(): string
    {
        return Craft::$app->getView()->renderTemplate(
            'cloudflare/utility',
            [
                'settings' => Cloudflare::$plugin->getSettings(),
            ]
        );
    }
}
