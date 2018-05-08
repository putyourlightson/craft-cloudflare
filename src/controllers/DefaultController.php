<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2017 Working Concept
 */

namespace workingconcept\cloudflare\controllers;

use workingconcept\cloudflare\Cloudflare;

use Craft;
use craft\web\Controller;
use craft\helpers\UrlHelper;

/**
 * @author    Working Concept
 * @package   Cloudflare
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    public function actionFetchZones()
    {
        return $this->asJson(Cloudflare::$plugin->cloudflareService->getZones());
    }

    /**
     * Have Cloudflare purge URL caches passed via `urls` GET/POST parameter, a string with each item on its own line.
     *
     * @return mixed
     */
    public function actionPurgeUrls()
    {
        $request = Craft::$app->getRequest();
        $urls = $request->getBodyParam('urls');
        $referrer = $request->getReferrer();

        if (empty($referrer))
        {
            $referrer = UrlHelper::getCpUrl('settings/plugins/cloudflare');
        }

        if (empty($urls))
        {
            Craft::$app->session->setError(Craft::t('cloudflare', 'Failed to purge empty or invalid URLs.'));
            return Craft::$app->controller->redirect($referrer);
        }

        // split lines into array items
        $urls = explode("\n", $urls);

        $response = Cloudflare::$plugin->cloudflareService->purgeUrls($urls);

        if ($request->isAjax)
        {
            return $this->asJson($response);
        }
        else
        {
            if (isset($response->success) && $response->success)
            {
                Craft::$app->session->setNotice(Craft::t('cloudflare', 'URL(s) purged.'));
            }
            else
            {
                Craft::$app->session->setError(Craft::t('cloudflare', 'Failed to purge URL(s).'));
            }

            return Craft::$app->controller->redirect($referrer);
        }
    }

    /**
     * Purge entire Cloudflare zone cache.
     * @return mixed
     */
    public function actionPurgeAll()
    {
        $response = Cloudflare::$plugin->cloudflareService->purgeZoneCache();

        if (isset($response->success) && $response->success)
        {
            Craft::$app->session->setNotice(Craft::t('cloudflare', 'Cloudflare cache purged.'));
        }
        else
        {
            Craft::$app->session->setError(Craft::t('cloudflare', 'Failed to purge Cloudflare cache.'));
        }

        $referrer = Craft::$app->request->getReferrer();

        if (empty($referrer))
        {
            $referrer = UrlHelper::cpUrl('settings/plugins/cloudflare');
        }

        return Craft::$app->controller->redirect($referrer);
    }

    /**
     * Save our Craft-URL-specific purge rules.
     * @return mixed
     */
    public function actionSaveRules()
    {
        Cloudflare::$plugin->rulesService->saveRules();
        Craft::$app->session->setNotice(Craft::t('cloudflare', 'Cloudflare rules saved.'));

        return Craft::$app->controller->redirect(UrlHelper::cpUrl('cloudflare/rules'));
    }
}
