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

use Craft;
use craft\web\Controller;
use GuzzleHttp\Exception\GuzzleException;
use workingconcept\cloudflare\Cloudflare;
use yii\web\BadRequestHttpException;
use yii\web\Response;

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

        $wasSuccessful = Cloudflare::getInstance()->api->verifyConnection();
        $return = [
            'success' => $wasSuccessful,
        ];

        if (!$wasSuccessful) {
            $return['errors'] = Cloudflare::getInstance()->api->getConnectionErrors();
        }

        return $this->asJson($return);
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
     * @return mixed
     * @throws craft\errors\MissingComponentException without a valid session.
     * @throws BadRequestHttpException|GuzzleException
     */
    public function actionPurgeUrls()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $session = Craft::$app->getSession();

        $urls = $request->getBodyParam('urls');

        if (empty($urls)) {
            $session->setError(Craft::t(
                'cloudflare',
                'Failed to purge empty or invalid URLs.'
            ));

            return $this->asErrorJson(
                'Failed to purge empty or invalid URLs.'
            );
        }

        // split lines into array items
        $urls = explode("\n", $urls);
        $response = Cloudflare::getInstance()->api->purgeUrls($urls);

        return $this->asJson($response);
    }

    /**
     * Purges entire Cloudflare zone cache.
     *
     * @return mixed
     * @throws BadRequestHttpException|GuzzleException
     */
    public function actionPurgeAll()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $response = Cloudflare::getInstance()->api->purgeZoneCache();

        return $this->asJson($response);
    }
}
