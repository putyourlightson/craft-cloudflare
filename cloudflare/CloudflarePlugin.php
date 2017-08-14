<?php

namespace Craft;

class CloudflarePlugin extends BasePlugin
{
	public function getName()
	{
		return Craft::t('Cloudflare');
	}

	public function getVersion()
	{
		return '0.1.0';
	}

	public function getDescription()
	{
		return 'Automatically purge Cloudflare URLs.';
	}

	public function getDeveloper()
	{
		return 'Working Concept';
	}

	public function getDeveloperUrl()
	{
		return 'https://workingconcept.com';
	}

	public function getReleaseFeedUrl()
	{
		return 'https://raw.githubusercontent.com/workingconcept/cloudflare-craft-plugin/master/releases.json';
	}

	public function getDocumentationUrl()
	{
	    return 'https://github.com/workingconcept/cloudflare-craft-plugin/blob/master/readme.md';
	}

	public function hasCpSection()
	{
		return false;
	}

	public function init()
	{
		craft()->on('assets.onSaveAsset', function (Event $event) {
			if ($event->params['isNewAsset'] === false)
			{
				$asset = $event->params['asset'];
				craft()->cloudflare->purgeUrls(array($asset->url));
			}
		});

		craft()->on('assets.onDeleteAsset', function (Event $event) {
			$asset = $event->params['asset'];
			craft()->cloudflare->purgeUrls(array($asset->url));
		});

		craft()->on('entries.onSaveEntry', function (Event $event) {
			$entry = $event->params['entry'];
			craft()->cloudflare->purgeUrls(array($entry->url));
		});

		craft()->on('entries.onDeleteEntry', function (Event $event) {
			$entry = $event->params['entry'];
			craft()->cloudflare->purgeUrls(array($entry->url));
		});

		// TODO: clear on publish, update, or delete entry
		// TODO: figure out how to clear indexes, not just detail URLs
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('cloudflare/_settings', array(
			'settings' => craft()->cloudflare->settings
		));
	}

	protected function defineSettings()
	{
		return array(
			'apiKey'         => array(AttributeType::String, 'required' => true, 'label' => 'Cloudflare API Key'),
			'email'          => array(AttributeType::String, 'required' => true, 'label' => 'Cloudflare Account Email'),
			'zone'           => array(AttributeType::String, 'required' => true, 'label' => 'Cloudflare Zone'),
			'userServiceKey' => array(AttributeType::String, 'required' => false, 'label' => 'Cloudflare User Service Key'),
		);
	}

}
