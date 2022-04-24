<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2020 Working Concept
 */

namespace workingconcept\cloudflare\utilities;

use Craft;
use craft\base\Utility;
use workingconcept\cloudflare\Cloudflare;

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
    public static function iconPath()
    {
        return Craft::getAlias("@workingconcept/cloudflare/assetbundles/dist/img/cloud.svg");
    }

    /**
     * @inheritdoc
     */
    public static function contentHtml(): string
    {
        return Craft::$app->getView()->renderTemplate(
            'cloudflare/utility',
            [
                'settings' => Cloudflare::getInstance()->getSettings(),
            ]
        );
    }
}
