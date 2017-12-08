<?php

namespace mattstein\cloudflare\services;

use Craft;
use craft\base\Component;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use mattstein\cloudflare\Cloudflare;
use stdClass;

/**
 * Provides a Cloudflare API service
 *
 * @package mattstein\cloudflare
 */
class CloudflareService extends Component {

    /**
     * @var \mattstein\cloudflare\models\Settings
     */
    public $settings;

    /**
     * @var string
     */
    protected $apiBaseUrl;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Initializes the service.
     *
     * @return void
     */
    public function init() {
        parent::init();

        // populate the settings
        $this->settings = Cloudflare::getInstance()->getSettings();

        // set the Cloudflare API base URL
        $this->apiBaseUrl = 'https://api.cloudflare.com/client/v4/';

        // create a HTTP client instance
        $this->client = new Client( [ 'base_uri' => $this->apiBaseUrl ] );
    }

    /**
     * Get a list of zones (domains) available for the provided CloudFlare account.
     * https://api.cloudflare.com/#zone-list-zones
     *
     * @return stdClass response from CloudFlare
     */
    public function getZones(): stdClass {
        Craft::trace( 'Getting zones', __METHOD__ );
        $apiKey = ( ! empty( Craft::$app->request->getParam( 'apiKey' ) )
            ? Craft::$app->request->getParam( 'apiKey' )
            : $this->settings->apiKey
        );
        $email  = ( ! empty( Craft::$app->request->getParam( 'email' ) )
            ? Craft::$app->request->getParam( 'email' )
            : $this->settings->email
        );

        try {
            $response = $this->client->get( 'zones', [
                'headers' => [
                    'X-Auth-Key'   => $apiKey,
                    'X-Auth-Email' => $email,
                ],
                'verify'  => false,
                'debug'   => false
            ] );
        }
        catch ( RequestException $exception ) {

            // if there is a response, we'll use it's body, otherwise we default to the request URI
            $reason = ( $exception->hasResponse()
                ? $exception->getResponse()->getBody()
                : $exception->getRequest()->getUri()
            );

            Craft::trace( 'Request failed: ' . $reason, 'cloudflare' );

            return (object) [ 'result' => [] ];
        }

        Craft::trace( 'Retrieved zones for account ' . $email, __METHOD__ );

        return json_decode( $response->getBody() );
    }

    /**
     * Purge the entire zone cache.
     * https://api.cloudflare.com/#zone-purge-all-files
     *
     * @return array CloudFlare's response
     */
    public function purgeZoneCache() {
        try {
            $response = $this->client->delete(
                sprintf( 'zones/%s/purge_cache', $this->settings->zone ),
                [
                    'headers' => [
                        'X-Auth-Key'   => $this->settings->apiKey,
                        'X-Auth-Email' => $this->settings->email,
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json'
                    ],
                    'query'   => [
                        'purge_everything' => true
                    ]
                ]
            );
        }
        catch ( RequestException $exception ) {

            // if there is a response, we'll use it's body, otherwise we default to the request URI
            $reason = ( $exception->hasResponse()
                ? $exception->getResponse()->getBody()
                : $exception->getRequest()->getUri()
            );
            Craft::trace( 'Request failed: ' . $reason, __METHOD__ );

            return [];
        }

        $responseBody = json_decode( $response->getBody() );

        Craft::trace( sprintf( 'Purged entire zone cache (%s)', $responseBody->result->id ), __METHOD__ );

        return $responseBody;
    }

    /**
     * Clear specific URLs in Cloudflare's cache.
     * https://api.cloudflare.com/#zone-purge-individual-files-by-url-and-cache-tags
     *
     * @param  array $urls array of urls
     *
     * @return mixed  API response object or null
     */
    public function purgeUrls( array $urls = [], array $tags = [] ): stdClass {

        // trim whitespace from each URL
        $urls = array_map( 'trim', $urls );

        // remove any invalid URLs
        for ( $i = 0; $i < count( $urls ); $i ++ ) {
            if ( filter_var( $urls[ $i ], FILTER_VALIDATE_URL ) === false ) {
                unset( $urls[ $i ] );
            }
        }

        // don't do anything if URLs are missing
        if ( count( $urls ) === 0 ) {
            return [];
        }

        try {
            $response = $this->client->delete(
                sprintf( 'zones/%s/purge_cache', $this->settings->zone ),
                [
                    'headers' => [
                        'X-Auth-Key'   => $this->settings->apiKey,
                        'X-Auth-Email' => $this->settings->email,
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json'
                    ],
                    'query'   => [
                        'files' => $urls
                    ]
                ]
            );
        }
        catch ( RequestException $exception ) {

            // if there is a response, we'll use it's body, otherwise we default to the request URI
            $reason = ( $exception->hasResponse()
                ? $exception->getResponse()->getBody()
                : $exception->getRequest()->getUri()
            );
            Craft::trace( 'Request failed: ' . $reason, __METHOD__ );

            return new stdClass();
        }

        $responseBody = json_decode( $response->getBody() );

        Craft::trace( sprintf( 'Purged URLs (%s)', $responseBody->result->id ), __METHOD__ );

        return $responseBody;
    }

    /**
     * Retrieves the CloudFlare API base URI
     *
     * @return string
     */
    public function getApiBaseUrl(): string {
        return $this->apiBaseUrl;
    }
}
