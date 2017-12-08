<?php

namespace mattstein\cloudflare\resources\cloudflarepurgewidget;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * CloudflarePurgeWidgetAsset class
 *
 * @package mattstein\cloudflare
 */
class CloudflarePurgeWidgetAsset extends AssetBundle {
    public function init() {
        $this->sourcePath = '@mattstein/cloudflare/resources/cloudflarepurgewidget/dist';
        $this->depends    = [ CpAsset::class ];
        $this->js         = [ 'js/widget.js' ];
        $this->css        = [ 'css/widget.css' ];

        parent::init();
    }
}
