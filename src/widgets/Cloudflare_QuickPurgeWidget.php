<?php
namespace Craft;

class Cloudflare_QuickPurgeWidget extends BaseWidget
{

	public $multipleInstances = false;

	public function getName()
	{
		return Craft::t('Cloudflare Purge');
	}

	public function getIconPath()
	{
		return CRAFT_PLUGINS_PATH . 'cloudflare/resources/icon-mask.svg';
	}

	public function getBodyHtml()
	{
		$settings = craft()->cloudflare->settings;

		return craft()->templates->render('cloudflare/_widgets/quickpurge/body', [
			'settings' => $settings
		]);
	}
}
