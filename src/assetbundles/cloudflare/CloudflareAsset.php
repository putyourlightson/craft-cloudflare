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

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;

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
		$this->sourcePath = "@workingconcept/cloudflare/assetbundles/cloudflare/dist";

		$this->depends = [
			CpAsset::class,
		];

		$this->js = [
			'js/settings.js',
		];

		$this->css = [
			'css/settings.css',
		];

		parent::init();
	}
}
