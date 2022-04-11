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
use yii\web\Response;
use yii\web\BadRequestHttpException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * @author    Working Concept
 * @package   Cloudflare
 * @since     1.0.0
 */
class DefaultController extends Controller
{
    /**
     * Checks whether the supplied credentials can connect to the Cloudflare account.
     *
     * @return Response
     * @throws BadRequestHttpException|GuzzleException
     */
    public function actionVerifyConnection(): Response
    {
        $this->requireAcceptsJson();
        $apiService = Cloudflare::getInstance()->api;

        if ( ! $apiService->verifyConnection()) {
            return $this->asFailure(
                'Failed to verify connection.',
                [ 'errors' => $apiService->getConnectionErrors() ]
            );
        }

        return $this->asSuccess();
    }

    /**
     * Returns all available zones on the configured account.
     *
     * @return Response
     * @throws GuzzleException
     */
    public function actionFetchZones(): Response
    {
        return $this->asJson(Cloudflare::getInstance()->api->getZones());
    }

    /**
     * Purges URLs passed in the `urls` parameter, whose value should be a string
     * with each URL on its own line.
     *
     * @return Response
     * @throws BadRequestHttpException|GuzzleException
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
            Cloudflare::getInstance()->api->purgeUrls($urls)
        );
    }

    /**
     * Purges entire Cloudflare zone cache.
     *
     * @return Response
     * @throws BadRequestHttpException|GuzzleException
     */
    public function actionPurgeAll(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        return $this->asJson(
            Cloudflare::getInstance()->api->purgeZoneCache()
        );
    }
}
