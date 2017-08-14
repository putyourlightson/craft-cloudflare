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
        $apiKey = ! empty($this->settings->apiKey) ? $this->settings->apiKey : craft()->request->getParam('apiKey');
        $email = ! empty($this->settings->email) ? $this->settings->email : craft()->request->getParam('email');

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
        if (count($urls) === 0)
        {
            return;
        }

        // TODO: make sure we have *valid* URLs, too

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
            CloudflarePlugin::log('Request failed: ' . $e, LogLevel::Error);
            return;
        }
    }

    public function getApiBaseUrl()
    {
        return $this->apiBaseUrl;
    }

}
