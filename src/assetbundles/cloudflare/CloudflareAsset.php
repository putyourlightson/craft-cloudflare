<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2017 Working Concept
 */

namespace workingconcept\cloudflare\assetbundles\Cloudflare;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Working Concept
 * @package   Cloudflare
 * @since     1.0.0
 */
class CloudflareAsset extends AssetBundle
{

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath
            = "@workingconcept/cloudflare/assetbundles/cloudflare/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/cp.js',
        ];

        $this->css = [
            'css/cp.css',
        ];

        parent::init();
    }
}
