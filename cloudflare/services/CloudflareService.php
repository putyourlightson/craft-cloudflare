<?php

namespace Craft;

class CloudflareService extends BaseApplicationComponent
{
    public $settings = array();

    protected $apiBaseUrl;
    protected $client;


    /**
     * Constructor
     */

    public function init()
    {
        parent::init();

        $this->settings   = craft()->plugins->getPlugin('cloudflare')->getSettings();
        $this->apiBaseUrl = 'https://api.cloudflare.com/client/v4/';
        $this->client     = new \Guzzle\Http\Client($this->apiBaseUrl);
    }


    /**
     * Get a list of zones (domains) available for the provided Cloudflare account.
     * https://api.cloudflare.com/#zone-list-zones
     *
     * @return array Cloudflare's response
     */

    public function getZones()
    {
        $apiKey = ! empty(craft()->request->getParam('apiKey')) ? craft()->request->getParam('apiKey') : $this->settings->apiKey;
        $email = ! empty(craft()->request->getParam('email')) ? craft()->request->getParam('email') : $this->settings->email;

        if (empty($apiKey) || empty($email))
        {
            // don't bother if we don't have credentials
            return;
        }

        try
        {
            $request = $this->client->get('zones', array(), array(
                'headers' => array(
                    'X-Auth-Key'   => $apiKey,
                    'X-Auth-Email' => $email,
                ),
                'verify' => false,
                'debug'  => false
            ));

            $response = $request->send();

            if ( ! $response->isSuccessful())
            {
                CloudflarePlugin::log('Request failed: ' . $response->getBody(), LogLevel::Warning);
                return;
            }
            else
            {
                CloudflarePlugin::log('Retrieved zones.', LogLevel::Info);
            }

            return json_decode($response->getBody(true));
        }
        catch(\Exception $e)
        {
            CloudflarePlugin::log('Request failed: ' . $e, LogLevel::Error);
            return;
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
            $request = $this->client->delete('zones/' . $this->settings->zone . '/purge_cache',
                array(
                    'X-Auth-Key'   => $this->settings->apiKey,
                    'X-Auth-Email' => $this->settings->email,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json'
                )
            );

            $request->setBody(json_encode(
                array('purge_everything' => true)
            ));

            $response = $request->send();
            $responseBody = json_decode($response->getBody(true));

            if ( ! $response->isSuccessful())
            {
                CloudflarePlugin::log('Request failed: ' . json_encode($responseBody), LogLevel::Warning);
                return;
            }
            else
            {
                CloudflarePlugin::log('Purged entire zone cache ('.$responseBody->result->id.').', LogLevel::Info);
            }

            return $responseBody;
        }
        catch(\Exception $e)
        {
            CloudflarePlugin::log('Request failed: ' . $e, LogLevel::Error);
            return;
        }
    }


    /**
     * Clear specific URLs in Cloudflare's cache.
     * https://api.cloudflare.com/#zone-purge-individual-files-by-url-and-cache-tags
     *
     * @param  array  $urls  array of urls
     *
     * @return mixed  API response object or null
     */

    public function purgeUrls($urls = array(), $tags = array())
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
            return;
        }

        // TODO: make sure attempts match zone

        try
        {
            $request = $this->client->delete('zones/' . $this->settings->zone . '/purge_cache',
                array(
                    'X-Auth-Key'   => $this->settings->apiKey,
                    'X-Auth-Email' => $this->settings->email,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json'
                )
            );

            $request->setBody(json_encode(
                array('files' => $urls)
            ));

            $response = $request->send();
            $responseBody = json_decode($response->getBody(true));

            if ( ! $response->isSuccessful())
            {
                CloudflarePlugin::log('Request failed: ' . json_encode($responseBody), LogLevel::Warning);
                return;
            }
            else
            {
                CloudflarePlugin::log('Purged URLs ('.$responseBody->result->id.').', LogLevel::Info);
            }

            return $responseBody;
        }
        catch(\Exception $e)
        {
            if ($responseBody = json_decode($e->getResponse()->getBody(true)))
            {
                $message = "URL purge failed.\n";
                $message .= "- urls: " . implode($urls, ',') . "\n";

                foreach ($responseBody->errors as $error)
                {
                    $message .= "- error code {$error->code}: " . $error->message . "\n";
                }

                CloudflarePlugin::log($message, LogLevel::Error);
            }
            else
            {
                CloudflarePlugin::log('Request failed: ' . $e, LogLevel::Error);
            }

            return;
        }
    }

    public function getApiBaseUrl()
    {
        return $this->apiBaseUrl;
    }

}
