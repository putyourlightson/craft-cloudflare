<?php
/**
 * @copyright Copyright (c) 2017 Working Concept
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\cloudflare\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\cloudflare\Cloudflare;
use yii\web\Response;

class DefaultController extends Controller
{
    /**
     * Checks whether the supplied credentials can connect to the Cloudflare account.
     */
    public function actionVerifyConnection(): Response
    {
        $this->requireAcceptsJson();
        $apiService = Cloudflare::$plugin->api;

        if (!$apiService->verifyConnection()) {
            return $this->asFailure(
                'Failed to verify connection.',
                [ 'errors' => $apiService->getConnectionErrors() ]
            );
        }

        return $this->asSuccess();
    }

    /**
     * Returns all available zones on the configured account.
     */
    public function actionFetchZones(): Response
    {
        return $this->asJson(Cloudflare::$plugin->api->getZones());
    }

    /**
     * Purges URLs passed in the `urls` parameter, whose value should be a string
     * with each URL on its own line.
     */
    public function actionPurgeUrls(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $urls = Craft::$app->getRequest()->getBodyParam('urls');

        if (empty($urls)) {
            return $this->asFailure(Craft::t(
                'cloudflare',
                'Failed to purge empty or invalid URLs.'
            ));
        }

        // split lines into array items
        $urls = explode("\n", $urls);

        return $this->asJson(
            Cloudflare::$plugin->api->purgeUrls($urls)
        );
    }

    /**
     * Purges entire Cloudflare zone cache.
     */
    public function actionPurgeAll(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        return $this->asJson(
            Cloudflare::$plugin->api->purgeZoneCache()
        );
    }
}
