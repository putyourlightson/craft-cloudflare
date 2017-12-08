<?php

namespace Craft;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use mattstein\cloudflare\Cloudflare;

/**
 * CloudflareController class
 *
 * @package Craft
 */
class CloudflareController extends Controller {

    /**
     * @return void
     */
    public function actionFetchZones() {
        $this->asJson( Cloudflare::getInstance()->cloudflare->getZones() );
    }

    /**
     * @return void
     */
    public function actionPurgeUrls() {
        $urls = Craft::$app->request->getBodyParam( 'urls' );

        if ( empty( $urls ) ) {
            Craft::$app->session->setError( Craft::t( 'cloudflare', 'Failed to purge empty or invalid URLs.' ) );
        }

        // split lines into array items
        $urls = explode( "\n", $urls );

        $response = Cloudflare::getInstance()->cloudflare->purgeUrls( $urls );

        if ( Craft::$app->request->getIsAjax() ) {
            $this->asJson( $response );
        } else {
            if ( isset( $response->success ) && $response->success ) {
                Craft::$app->session->setNotice( Craft::t( 'cloudflare', 'URL(s) purged.' ) );
            } else {
                Craft::$app->session->setError( Craft::t( 'cloudflare', 'Failed to purge URL(s).' ) );
            }

            $referrer = Craft::$app->request->getReferrer();

            if ( empty( $referrer ) ) {
                $referrer = UrlHelper::cpUrl( 'settings/plugins/cloudflare' );
            }

            Craft::$app->response->redirect( $referrer );
        }
    }

    /**
     * @return void
     */
    public function actionPurgeAll() {
        $response = Cloudflare::getInstance()->cloudflare->purgeZoneCache();

        if ( isset( $response->success ) && $response->success ) {
            Craft::$app->session->setNotice( Craft::t( 'cloudflare', 'Cloudflare cache purged.' ) );
        } else {
            Craft::$app->session->setError( Craft::t( 'cloudflare', 'Failed to purge Cloudflare cache.' ) );
        }

        $referrer = Craft::$app->request->getReferrer();

        if ( empty( $referrer ) ) {
            $referrer = UrlHelper::cpUrl( 'settings/plugins/cloudflare' );
        }

        Craft::$app->response->redirect( $referrer );
    }

    /**
     * @return void
     */
    public function actionSaveRules() {
        Cloudflare::getInstance()->rules->saveRules();
        Craft::$app->session->setNotice( Craft::t( 'cloudflare', 'Cloudflare rules saved.' ) );
        Craft::$app->response->redirect( UrlHelper::cpUrl( 'cloudflare/rules' ) );
    }

}
