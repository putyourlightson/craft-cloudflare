<?php
/**
 * @copyright Copyright (c) 2017 Working Concept
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\cloudflare\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class CloudflareAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = "@putyourlightson/cloudflare/resources";

        $this->depends = [ CpAsset::class ];
        $this->js = [ 'js/cp.js' ];
        $this->css = [ 'css/cp.css' ];

        parent::init();
    }
}
