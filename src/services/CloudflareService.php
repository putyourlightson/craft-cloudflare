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

use Craft;
use craft\base\Component;
use GuzzleHttp\Client;
use stdClass;

/**
 * @author    Working Concept
 * @package   Cloudflare
 * @since     1.0.0
 */
class CloudflareService extends Component
{

    /**
     * @var \workingconcept\cloudflare\models\Settings
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
     * @var boolean
     */
    protected $isConfigured;

    /**
    * @var stdClass
    */
    protected $responseItems;

    /**
     * Initializes the service.
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        // populate the settings
        $this->settings = Cloudflare::$plugin->getSettings();

        // set the Cloudflare API base URL
        $this->apiBaseUrl = 'https://api.cloudflare.com/client/v4/';

        $apiKey = $this->settings->apiKey;
        $email  = $this->settings->email;

        if (Craft::$app->request->getIsSiteRequest()) 
        {
            // Check for parameters if relevant, like when we're first testing the credentials from the Settings page.
            $apiKey = ! empty(Craft::$app->request->getParam('apiKey')) ? Craft::$app->request->getParam('apiKey') : $this->settings->apiKey;
            $email  = ! empty(Craft::$app->request->getParam('email')) ? Craft::$app->request->getParam('email') : $this->settings->email;
        }

        $this->isConfigured = ! empty($apiKey) && ! empty($email);

        if ($this->isConfigured)
        {
            $this->client = new \GuzzleHttp\Client([
                'base_uri' => $this->apiBaseUrl,
                'headers' => [
                    'X-Auth-Key'   => $apiKey,
                    'X-Auth-Email' => $email,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json'
                ],
                'verify' => false,
                'debug' => false
            ]);
        }
    }


    /**
     * Get a list of zones (domains) available for the provided Cloudflare account.
     * https://api.cloudflare.com/#zone-list-zones
     *
     * @return array zones from response.result (combined if there was pagination)
     */

    public function getZones()
    {
        if ( ! $this->isConfigured)
        {
            return;
        }

        $this->responseItems = [];

        $currentPage = 0;
        $totalPages  = 1; // temporary
        $perPage     = 50;

        while ($currentPage < $totalPages)
        {
            $currentPage++;

            if ($response = $this->getPagedZones($currentPage, $perPage))
            {
                if (count($response->result) > 0)
                {
                    $totalRecords = $response->result_info->total_count;
                    $totalPages   = ceil($totalRecords / $perPage);

                    $this->responseItems = array_merge($this->responseItems, $response->result);
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
     * Fetch zones via API, which returns paginated results.
     *
     * @param integer $page
     * @param integer $perPage
     * @return void
     */

    private function getPagedZones($page = 1, $perPage = 50)
    {
        Craft::trace('Getting zones (page ' . $page . ').', __METHOD__);

        try 
        {
            $response = $this->client->get('zones?per_page=' . $perPage);

            if ( ! $response->getStatusCode() == 200)
            {
                Craft::trace('Request failed: ' . $response->getBody(), 'cloudflare');
                return (object) [ 'result' => [] ];
            }
            else
            {
                Craft::trace('Retrieved zones.', 'cloudflare');
            }

            return json_decode($response->getBody(true));
        }
        catch (RequestException $exception)
        {
            // if there is a response, we'll use it's body, otherwise we default to the request URI
            $reason = ( $exception->hasResponse()
                ? $exception->getResponse()->getBody()
                : $exception->getRequest()->getUri()
            );

            Craft::trace('Request failed: ' . $reason, 'cloudflare');

            return (object) [ 'result' => [] ];
        }
    }


    /**
     * Purge the entire zone cache.
     * https://api.cloudflare.com/#zone-purge-all-files
     *
     * @return array Cloudflare's response
     */

    public function purgeZoneCache()
    {
        try
        {
            $response = $this->client->delete(
                sprintf('zones/%s/purge_cache', $this->settings->zone),
                [
                    'body' => json_encode(['purge_everything' => true])
                ]
            );

            $responseBody = json_decode($response->getBody(true));

            if ( ! $response->getStatusCode() == 200)
            {
                Craft::trace('Request failed: ' . json_encode($responseBody), 'cloudflare');
                return (object) [ 'result' => [] ];
            }
            else
            {
                Craft::trace(sprintf('Purged entire zone cache (%s)', $responseBody->result->id), 'cloudflare');
            }

            return $responseBody;
        }
        catch(RequestException $exception)
        {
            // if there is a response, we'll use it's body, otherwise we default to the request URI
            $reason = ( $exception->hasResponse()
                ? $exception->getResponse()->getBody()
                : $exception->getRequest()->getUri()
            );

            Craft::trace('Request failed: ' . $reason, 'cloudflare');
            return (object) [ 'result' => [] ];
        }
    }


    /**
     * Clear specific URLs in Cloudflare's cache.
     * https://api.cloudflare.com/#zone-purge-individual-files-by-url-and-cache-tags
     *
     * @param  array  $urls  array of absolute URLs
     *
     * @return mixed  API response object or null
     */

    public function purgeUrls(array $urls = [], array $tags = []): stdClass
    {
        // trim whitespace from each URL
        $urls = array_map('trim', $urls);

        // remove any invalid URLs
        for ($i=0; $i < count($urls); $i++)
        {
            if (filter_var($urls[$i], FILTER_VALIDATE_URL) === false)
            {
                unset($urls[$i]);
            }
        }

        // don't do anything if URLs are missing
        if (count($urls) === 0)
        {
            Craft::trace('No valid URLs provided for purge.', 'cloudflare');
            return (object) [ 'result' => [] ];
        }

        // TODO: make sure attempts match zone

        try
        {
            $response = $this->client->delete(sprintf('zones/%s/purge_cache', $this->settings->zone), [
                'body' => json_encode(['files' => $urls])
            ]);

            $responseBody = json_decode($response->getBody(true));

            if ( ! $response->getStatusCode() == 200)
            {
                Craft::trace('Request failed: ' . json_encode($responseBody), 'cloudflare');
                return (object) [ 'result' => [] ];
            }
            else
            {
                Craft::trace('Purged URLs ('.$responseBody->result->id.').' . "\n\t" . implode("\n\t", $urls), 'cloudflare');
            }

            return $responseBody;
        }
        catch(\GuzzleHttp\Exception\ClientException $exception)
        {
            return $this->handleApiException($urls, $exception);
        }
        catch(RequestException $exception)
        {
            return $this->handleApiException($urls, $exception);
        }
    }


    /**
     * Get Cloudflare's base API URL.
     *
     * @return string
     */

    public function getApiBaseUrl(): string
    {
        return $this->apiBaseUrl;
    }


    /**
     * Get `true` if Cloudlfare API credentials have been saved.
     *
     * @return boolean
     */

    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }


    /**
     * Quietly handle an exception from the Cloudflare API.
     *
     * @param array     $urls
     * @param Exception $exception
     * 
     * @return array    [ 'result' => [] ]
     */

    private function handleApiException($urls, $exception)
    {
        if ($responseBody = json_decode($exception->getResponse()->getBody(true)))
        {
            $message = "URL purge failed.\n";
            $message .= "- urls: " . implode($urls, ',') . "\n";

            foreach ($responseBody->errors as $error)
            {
                $message .= "- error code {$error->code}: " . $error->message . "\n";
            }

            Craft::trace($message, 'cloudflare');

            return (object) [ 'result' => [] ];
        }
        else
        {
            $reason = ( $exception->hasResponse()
                ? $exception->getResponse()->getBody()
                : $exception->getRequest()->getUri()
            );

            Craft::trace('Request failed: ' . $reason, 'cloudflare');

            return (object) [ 'result' => [] ];
        }
    }

}
