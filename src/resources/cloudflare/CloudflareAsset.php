<?php

namespace mattstein\cloudflare\resources\cloudflare;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * CloudflareAsset class
 *
 * @package mattstein\cloudflare
 */
class CloudflarePurgeWidgetAsset extends AssetBundle {
    public function init() {
        $this->sourcePath = '@mattstein/cloudflare/resources/cloudflare/dist';
        $this->depends    = [ CpAsset::class ];
        $this->js         = [ 'js/settings.js' ];
        $this->css        = [ 'css/settings.css' ];

        parent::init();
    }
}
