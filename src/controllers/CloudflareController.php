<?php

namespace Craft;

class CloudflareController extends BaseController
{

	public function actionGetZones()
	{
		$this->returnJson(craft()->cloudflare->getZones());
	}

	public function actionPurgeUrls()
	{
		$urls = craft()->request->getPost('urls');

		if (empty($urls))
		{
			craft()->userSession->setError(Craft::t('Failed to purge empty or invalid URLs.'));
		}

		// split lines into array items
		$urls = explode("\n", $urls);

		$response = craft()->cloudflare->purgeUrls($urls);

		if (craft()->request->isAjaxRequest())
		{
			$this->returnJson($response);
		}
		else
		{
			if (isset($response->success) && $response->success)
			{
				craft()->userSession->setNotice(Craft::t('URL(s) purged.'));
			}
			else
			{
				craft()->userSession->setError(Craft::t('Failed to purge URL(s).'));
			}

			$referrer = craft()->request->getUrlReferrer();

			if (empty($referrer))
			{
				$referrer = UrlHelper::getCpUrl('settings/plugins/cloudflare');
			}

			craft()->request->redirect($referrer);
		}
	}

	public function actionPurgeAll()
	{
		$response = craft()->cloudflare->purgeZoneCache();

		if (isset($response->success) && $response->success)
		{
			craft()->userSession->setNotice(Craft::t('Cloudflare cache purged.'));
		}
		else
		{
			craft()->userSession->setError(Craft::t('Failed to purge Cloudflare cache.'));
		}

		$referrer = craft()->request->getUrlReferrer();

		if (empty($referrer))
		{
			$referrer = UrlHelper::getCpUrl('settings/plugins/cloudflare');
		}

		craft()->request->redirect($referrer);
	}

	public function actionSaveRules()
	{
		craft()->cloudflare_rules->saveRules();
		craft()->userSession->setNotice(Craft::t('Cloudflare rules saved.'));
		craft()->request->redirect(UrlHelper::getCpUrl('cloudflare/rules'));
	}

}
