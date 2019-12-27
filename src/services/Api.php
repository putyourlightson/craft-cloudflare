<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2017 Working Concept
 */

namespace workingconcept\cloudflare\services;

use workingconcept\cloudflare\Cloudflare;
use workingconcept\cloudflare\helpers\ConfigHelper;
use workingconcept\cloudflare\models\Settings;
use workingconcept\cloudflare\helpers\UrlHelper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

use Craft;
use craft\base\Component;

/**
 * @author    Working Concept
 * @package   Cloudflare
 * @since     1.0.0
 */
class Api extends Component
{
    // Constants
    // =========================================================================

    const API_BASE_URL = 'https://api.cloudflare.com/client/v4/';


    // Public Properties
    // =========================================================================

    /**
     * @var array
     */
    protected $responseItems;


    // Private Properties
    // =========================================================================

    /**
     * @var \GuzzleHttp\Client
     */
    private $_client;

    /**
     * @var array
     */
    private $_connectionErrors = [];


    // Public Methods
    // =========================================================================

    /**
     * Get a configured Guzzle client if we have an API key and email. Otherwise
     * returns null.
     *
     * @return Client|null
     */
    public function getClient()
    {
        if ($this->_client === null && ConfigHelper::isConfigured())
        {
            $this->_client = Craft::createGuzzleClient([
                'base_uri' => self::API_BASE_URL,
                'headers' => $this->_getClientHeaders(),
                'verify' => false,
                'debug' => false
            ]);
        }

        return $this->_client;
    }

    /**
     * Returns true if provided credentials can successfully make API calls.
     *
     * @return bool
     */
    public function verifyConnection(): bool
    {
        if ($this->getClient() === null)
        {
            return false;
        }

        $authType = ConfigHelper::getParsedSetting('authType');
        $testUri = $authType === Settings::AUTH_TYPE_KEY ?
            'zones?per_page=1' : 'user/tokens/verify';

        try
        {
            $response = $this->getClient()->get($testUri);
            $responseContents = json_decode(
                $response->getBody()->getContents(),
                false
            );

            // should be 200 response containing `"success": true`
            $success = $response->getStatusCode() === 200
                && $responseContents->success;

            if ($success)
            {
                return true;
            }

            if (isset($responseContents->errors))
            {
                $this->_connectionErrors = $responseContents->errors;

                Craft::info(sprintf(
                    'Connection test failed: %s',
                    json_encode($responseContents->errors)
                ), 'cloudflare');
            }
            else
            {
                Craft::info('Connection test failed.', 'cloudflare');
            }
        }
        catch (RequestException $exception)
        {
            $reason = $this->_getExceptionReason($exception);

            if (($data = json_decode($reason, false)) && isset($data->errors))
            {
                $this->_connectionErrors = $data->errors;
            }

            Craft::info(sprintf(
                'Connection test failed with exception: %s',
                $reason
            ), 'cloudflare');
        }

        return false;
    }

    /**
     * @return array
     */
    public function getConnectionErrors(): array
    {
        return $this->_connectionErrors;
    }

    /**
     * Get a list of zones (domains) available for the provided Cloudflare account.
     * https://api.cloudflare.com/#zone-list-zones
     *
     * @return array|null zones from response.result (combined if there was pagination)
     */
    public function getZones()
    {
        if ( ! $this->getClient())
        {
            return null;
        }

        $this->responseItems = [];

        $currentPage = 0;
        $totalPages  = 1; // temporary
        $perPage     = 50;

        while ($currentPage < $totalPages)
        {
            $currentPage++;

            if ($response = $this->_getPagedZones($currentPage, $perPage))
            {
                if (count($response->result) > 0)
                {
                    $totalRecords = $response->result_info->total_count;
                    $totalPages = ceil($totalRecords / $perPage);

                    foreach ($response->result as $item)
                    {
                        $this->responseItems[] = $item;
                    }
                }
                else
                {
                    return [];
                }
            }
        }

        return $this->responseItems;
    }

    /**
     * Get details for a zone.
     * https://api.cloudflare.com/#zone-zone-details
     *
     * @param string $zoneId
     * @return object|null
     */
    public function getZoneById($zoneId)
    {
        if (! $this->getClient())
        {
            return null;
        }

        try
        {
            $response = $this->getClient()->get(sprintf(
                'zones/%s',
                $zoneId
            ));

            if ( ! $response->getStatusCode() == 200)
            {
                Craft::info(sprintf(
                    'Request failed: %s',
                    $response->getBody()
                ), 'cloudflare');

                return null;
            }
        }
        catch(RequestException $exception)
        {
            Craft::info(sprintf(
                    'Zone request failed: %s',
                    $this->_getExceptionReason($exception)
                ),
                'cloudflare'
            );

            return null;
        }

        Craft::info(sprintf(
                'Retrieved zone #%s',
                $zoneId
            ),
            'cloudflare'
        );

        return json_decode($response->getBody(), false)
            ->result;
    }

    /**
     * Purge the entire zone cache.
     * https://api.cloudflare.com/#zone-purge-all-files
     *
     * @return object|null Cloudflare's response
     */
    public function purgeZoneCache()
    {
        if ( ! $this->getClient())
        {
            return null;
        }

        try
        {
            $response = $this->getClient()->delete(sprintf(
                'zones/%s/purge_cache',
                Cloudflare::$plugin->getSettings()->zone
            ),
                [
                    'body' => json_encode(['purge_everything' => true])
                ]
            );

            $responseBody = json_decode($response->getBody(), false);

            if ( ! $response->getStatusCode() == 200)
            {
                Craft::info(sprintf(
                    'Request failed: %s',
                    json_encode($responseBody)
                ), 'cloudflare');

                return (object) [ 'result' => [] ];
            }

            Craft::info(sprintf(
                'Purged entire zone cache (%s)',
                $responseBody->result->id
            ), 'cloudflare');

            return $responseBody;
        }
        catch(RequestException $exception)
        {
            Craft::info(sprintf(
                    'Request failed: %s',
                    $this->_getExceptionReason($exception)
                ),
                'cloudflare'
            );

            return (object) [ 'result' => [] ];
        }
    }

    /**
     * Clear specific URLs in Cloudflare's cache.
     * https://api.cloudflare.com/#zone-purge-individual-files-by-url-and-cache-tags
     *
     * @param  array  $urls  array of absolute URLs
     *
     * @return mixed|null  API response data or null
     */
    public function purgeUrls(array $urls = [])
    {
        if ( ! $this->getClient())
        {
            return null;
        }

        $urls = UrlHelper::prepUrls($urls);

        // don't do anything if URLs are missing
        if (count($urls) === 0)
        {
            Craft::info(
                'No valid URLs provided for purge.',
                'cloudflare'
            );

            return (object) [
                'result' => []
            ];
        }

        try
        {
            $response = $this->getClient()->delete(sprintf(
                    'zones/%s/purge_cache',
                    Cloudflare::$plugin->getSettings()->zone
                ),
                [ 'body' => json_encode(['files' => $urls]) ]
            );

            $responseBody = json_decode($response->getBody(), false);

            if ( ! $response->getStatusCode() == 200)
            {
                Craft::info(sprintf(
                    'Request failed: %s',
                    json_encode($responseBody)
                ), 'cloudflare');

                return (object) [
                    'result' => []
                ];
            }

            $urlString = implode(',', $urls);

            Craft::info(sprintf(
                'Purged URLs (%d): %s',
                $responseBody->result->id,
                $urlString
            ), 'cloudflare');

            return $responseBody;
        }
        catch(ClientException $exception)
        {
            return $this->_handleApiException($urls, $exception);
        }
        catch(RequestException $exception)
        {
            return $this->_handleApiException($urls, $exception);
        }
    }

    /**
     * Get Cloudflare's base API URL.
     *
     * @return string
     */
    public function getApiBaseUrl(): string
    {
        return self::API_BASE_URL;
    }


    // Private Methods
    // =========================================================================

    /**
     * Fetch zones via API, which returns paginated results.
     *
     * @param int $page
     * @param int $perPage
     *
     * @return \stdClass|null
     */
    private function _getPagedZones($page = 1, $perPage = 50)
    {
        if ( ! $this->getClient())
        {
            return null;
        }

        Craft::info(sprintf(
            'Getting zones (page %d).',
            $page
        ), 'cloudflare');

        try
        {
            $response = $this->getClient()->get(sprintf(
                'zones?per_page=%d',
                $perPage
            ));

            if ( ! $response->getStatusCode() == 200)
            {
                Craft::info(sprintf(
                    'Request failed: %s',
                    $response->getBody()
                ), 'cloudflare');

                return (object) [
                    'result' => []
                ];
            }

            Craft::info('Retrieved zones.', 'cloudflare');

            return json_decode($response->getBody(), false);
        }
        catch (RequestException $exception)
        {
            Craft::info(sprintf(
                'Request failed: %s',
                $this->_getExceptionReason($exception)
            ), 'cloudflare');

            return (object) [
                'result' => []
            ];
        }
    }

    /**
     * Returns request headers for the relevant authorization type.
     *
     * @return array
     */
    private function _getClientHeaders(): array
    {
        $authType = ConfigHelper::getParsedSetting('authType');

        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json'
        ];

        if ($authType === Settings::AUTH_TYPE_KEY)
        {
            $headers['X-Auth-Key'] = ConfigHelper::getParsedSetting('apiKey');
            $headers['X-Auth-Email'] = ConfigHelper::getParsedSetting('email');
        }
        elseif ($authType === Settings::AUTH_TYPE_TOKEN)
        {
            $headers['Authorization'] = sprintf(
                'Bearer %s',
                ConfigHelper::getParsedSetting('apiToken')
            );
        }

        return $headers;
    }

    /**
     * Returns a string for the request exception that can be used for logging.
     *
     * @param  \GuzzleHttp\Exception\RequestException  $exception
     *
     * @return string
     */
    private function _getExceptionReason(RequestException $exception): string
    {
        if ($exception->hasResponse())
        {
            return $exception->getResponse()->getBody()->getContents();
        }

        return $exception->getRequest()->getUri();
    }

    /**
     * Quietly handle an exception from the Cloudflare API.
     *
     * @param array  $urls
     * @param mixed  $exception (ClientException or RequestException)
     *
     * @return \stdClass with populated `result` property array
     */
    private function _handleApiException($urls, $exception): \stdClass
    {
        if ($responseBody = json_decode($exception->getResponse()->getBody(), false))
        {
            $message = "URL purge failed.\n";
            $message .= '- urls: ' . implode(',', $urls) . "\n";

            foreach ($responseBody->errors as $error)
            {
                $message .= "- error code {$error->code}: " . $error->message . "\n";
            }

            Craft::info($message, 'cloudflare');

            return (object) [ 'result' => [] ];
        }

        Craft::info(sprintf(
            'Request failed: %s',
            $this->_getExceptionReason($exception)
        ), 'cloudflare');

        return (object) [ 'result' => [] ];
    }

}
