<?php

namespace Craft;

class CloudflareController extends BaseController
{

	public function actionClearZone()
	{
		$response = craft()->cloudflare->purgeAll();
		CloudflarePlugin::log(print_r($response, true), LogLevel::Info);

		if ($response->status === 'success')
		{
			craft()->userSession->setNotice(Craft::t('Cloudflare zone cache cleared.'));
		}
		else
		{
			craft()->userSession->setNotice(Craft::t('Cloudflare zone cache clear failed.'));
		}

		// TODO: redirect to someplace more sensible
		craft()->request->redirect('/admin');
	}

	public function actionGetZones()
	{
		$this->returnJson(craft()->cloudflare->getZones());
	}

	public function actionPurgeAll()
	{
		$response = craft()->cloudflare->purgeZoneCache();

		if ($response->success)
		{
			craft()->userSession->setNotice(Craft::t('Cloudflare cache purged.'));
		}
		else
		{
			craft()->userSession->setNotice(Craft::t('Purge failed:') . $response->result->errors[0]->message);
		}

		craft()->request->redirect('/admin/settings/plugins/cloudflare');
	}

}
