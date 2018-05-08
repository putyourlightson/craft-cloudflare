<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2017 Working Concept
 */

namespace workingconcept\cloudflare\assetbundles\quickpurgewidget;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Working Concept
 * @package   Cloudflare
 * @since     1.0.0
 */
class QuickPurgeWidgetAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@workingconcept/cloudflare/assetbundles/quickpurgewidget/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/quickpurge.js',
        ];

        $this->css = [
            'css/quickpurge.css',
        ];

        parent::init();
    }
}
