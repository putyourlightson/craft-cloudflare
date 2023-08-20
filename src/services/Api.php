<?php
/**
 * @copyright Copyright (c) 2017 Working Concept
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\cloudflare\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use putyourlightson\cloudflare\helpers\ConfigHelper;
use putyourlightson\cloudflare\helpers\UrlHelper;
use putyourlightson\cloudflare\models\Settings;

/**
 * @property-read string $apiBaseUrl
 * @property-read array $connectionErrors
 * @property-read Client|null $client
 * @property-read null|array $zones
 */
class Api extends Component
{
    /**
     * Base Cloudflare API URL.
     */
    public const API_BASE_URL = 'https://api.cloudflare.com/client/v4/';

    /**
     * @var ?Client
     */
    private ?Client $_client = null;

    /**
     * @var string[]
     */
    private array $_connectionErrors = [];

    /**
     * Get a configured Guzzle client if we have an API key and email.
     * Otherwise, returns null.
     */
    public function getClient(): ?Client
    {
        if ($this->_client === null && ConfigHelper::isConfigured()) {
            $this->_client = Craft::createGuzzleClient([
                'base_uri' => self::API_BASE_URL,
                'headers' => $this->_getClientHeaders(),
                'verify' => false,
                'debug' => false,
            ]);
        }

        return $this->_client;
    }

    /**
     * Returns true if provided credentials can successfully make API calls.
     */
    public function verifyConnection(): bool
    {
        if ($this->getClient() === null) {
            return false;
        }

        $authType = ConfigHelper::getParsedSetting('authType');
        $testUri = $authType === Settings::AUTH_TYPE_KEY ?
            'zones?per_page=1' : 'user/tokens/verify';

        try {
            $response = $this->getClient()->get($testUri);
            $responseContents = Json::decode(
                $response->getBody()->getContents(),
                false
            );

            // should be 200 response containing `"success": true`
            $success = $response->getStatusCode() === 200
                && $responseContents->success;

            if ($success) {
                return true;
            }

            if (isset($responseContents->errors)) {
                $this->_connectionErrors = $responseContents->errors;

                Craft::info(sprintf(
                    'Connection test failed: %s',
                    Json::encode($responseContents->errors)
                ), 'cloudflare');
            } else {
                Craft::info('Connection test failed.', 'cloudflare');
            }
        } catch (RequestException $exception) {
            $reason = $this->_getExceptionReason($exception);

            if (($data = Json::decode($reason, false)) && isset($data->errors)) {
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
     * Returns an array of connection errors.
     *
     * @return string[]
     */
    public function getConnectionErrors(): array
    {
        return $this->_connectionErrors;
    }

    /**
     * Returns `true` if we’re able to list available zones.
     */
    public function canListZones(): bool
    {
        if (!$this->getClient()) {
            return false;
        }

        if ($response = $this->_getPagedZones(0, 2)) {
            return count($response->result) > 0;
        }

        return false;
    }

    /**
     * Get a list of zones (domains) available for the provided Cloudflare account.
     * https://developers.cloudflare.com/api/operations/zones-get
     *
     * @return array|null zones from `response.result` (combined if there was pagination)
     */
    public function getZones(): ?array
    {
        if (!$this->getClient()) {
            return null;
        }

        $responseItems = [];

        $currentPage = 0;
        $totalPages = 1;
        $perPage = 50;

        while ($currentPage < $totalPages) {
            $currentPage++;

            if ($response = $this->_getPagedZones($currentPage, $perPage)) {
                if (count($response->result) > 0) {
                    $totalRecords = $response->result_info->total_count;
                    $totalPages = ceil($totalRecords / $perPage);

                    foreach ($response->result as $item) {
                        $responseItems[] = $item;
                    }
                }
            }
        }

        return $responseItems;
    }

    /**
     * Get details for a zone.
     * https://developers.cloudflare.com/api/operations/zones-0-get
     */
    public function getZoneById(string $zoneId): ?object
    {
        if (!$this->getClient()) {
            return null;
        }

        try {
            $response = $this->getClient()->get(sprintf(
                'zones/%s',
                $zoneId
            ));

            if ($response->getStatusCode() !== 200) {
                Craft::info(sprintf(
                    'Request failed: %s',
                    $response->getBody()
                ), 'cloudflare');

                return null;
            }
        } catch (RequestException $exception) {
            Craft::info(sprintf(
                'Zone request failed: %s',
                $this->_getExceptionReason($exception)
            ),
                'cloudflare'
            );

            return null;
        }

        Craft::info(
            sprintf('Retrieved zone #%s', $zoneId),
            'cloudflare'
        );

        return Json::decode($response->getBody(), false)
            ->result;
    }

    /**
     * Purge the entire zone cache.
     * https://developers.cloudflare.com/api/operations/zone-purge
     */
    public function purgeZoneCache(): ?object
    {
        if (!$this->getClient()) {
            return null;
        }

        try {
            $response = $this->getClient()->delete(sprintf(
                'zones/%s/purge_cache',
                ConfigHelper::getParsedSetting('zone')
            ),
                ['body' => Json::encode(['purge_everything' => true])]
            );

            $responseBody = Json::decode($response->getBody(), false);

            if ($response->getStatusCode() !== 200 || $responseBody->success === false) {
                Craft::info(sprintf(
                    'Zone purge request failed: %s',
                    Json::encode($responseBody)
                ), 'cloudflare');

                return (object)[
                    'success' => false,
                    'message' => $response->getBody()->getContents(),
                    'result' => [],
                ];
            }

            Craft::info(
                sprintf('Purged entire zone cache (%s)', $responseBody->result->id),
                'cloudflare'
            );

            return $responseBody;
        } catch (ClientException|RequestException $exception) {
            return $this->_handleApiException($exception, 'zone purge');
        }
    }

    /**
     * Clear specific URLs in Cloudflare’s cache.
     * https://developers.cloudflare.com/api/operations/zone-purge
     *
     * @param string[] $urls array of absolute URLs
     */
    public function purgeUrls(array $urls = []): mixed
    {
        if (!$this->getClient()) {
            return null;
        }

        $urls = UrlHelper::prepUrls($urls);

        // don’t do anything if URLs are missing
        if (count($urls) === 0) {
            return $this->_failureResponse(
                'Cannot purge; no valid URLs.'
            );
        }

        try {
            $response = $this->getClient()->delete(sprintf(
                'zones/%s/purge_cache',
                ConfigHelper::getParsedSetting('zone')
            ),
                ['body' => Json::encode(['files' => $urls])]
            );

            $responseBody = Json::decode($response->getBody(), false);

            if ($response->getStatusCode() !== 200) {
                Craft::info(sprintf(
                    'Request failed: %s',
                    Json::encode($responseBody)
                ), 'cloudflare');

                return (object)[
                    'success' => false,
                    'message' => $response->getBody()->getContents(),
                    'result' => [],
                ];
            }

            $urlString = implode(',', $urls);

            Craft::info(sprintf(
                'Purged URLs (%d): %s',
                $responseBody->result->id,
                $urlString
            ), 'cloudflare');

            return $responseBody;
        } catch (ClientException|RequestException $exception) {
            return $this->_handleApiException($exception, 'URL purge', $urls);
        }
    }

    /**
     * Get Cloudflare’s base API URL.
     */
    public function getApiBaseUrl(): string
    {
        return self::API_BASE_URL;
    }

    /**
     * Quietly handle an exception from the Cloudflare API.
     *
     * @param mixed $exception (ClientException or RequestException)
     * @param string $action human-friendly description of the attempted action
     * @param string[] $urls related URLs (if relevant)
     *
     * @return object with populated `result` property array
     */
    private function _handleApiException(mixed $exception, string $action, array $urls = []): object
    {
        if ($responseBody = Json::decode($exception->getResponse()->getBody(), false)) {
            $message = $action . " failed.\n";

            if ($urls) {
                $message .= '- urls: ' . implode(',', $urls) . "\n";
            }

            foreach ($responseBody->errors as $error) {
                $message .= "- error code " . $error->code . ": " . $error->message . "\n";
            }

            Craft::info($message, 'cloudflare');

            return (object)[
                'success' => false,
                'errors' => $responseBody->errors ?? [],
                'result' => [],
            ];
        }

        // return a more generic failure if we don’t have better details
        return $this->_failureResponse(sprintf(
            'Request failed: %s',
            $this->_getExceptionReason($exception)
        ));
    }

    /**
     * Fetch zones via API, which returns paginated results.
     */
    private function _getPagedZones(int $page = 1, int $perPage = 50): ?object
    {
        if (!$this->getClient()) {
            return null;
        }

        Craft::info(sprintf(
            'Getting zones (page %d).',
            $page
        ), 'cloudflare');

        try {
            $response = $this->getClient()->get(sprintf(
                'zones?per_page=%d',
                $perPage
            ));

            if ($response->getStatusCode() !== 200) {
                return $this->_failureResponse(sprintf(
                    'Request failed: %s',
                    $response->getBody()
                ));
            }

            Craft::info('Retrieved zones.', 'cloudflare');

            return Json::decode($response->getBody(), false);
        } catch (RequestException $exception) {
            return $this->_failureResponse(sprintf(
                'Request failed: %s',
                $this->_getExceptionReason($exception)
            ));
        }
    }

    /**
     * Returns request headers for the relevant authorization type.
     *
     * @return array<string, string>
     */
    private function _getClientHeaders(): array
    {
        $authType = ConfigHelper::getParsedSetting('authType');

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($authType === Settings::AUTH_TYPE_KEY) {
            $headers['X-Auth-Key'] = ConfigHelper::getParsedSetting('apiKey');
            $headers['X-Auth-Email'] = ConfigHelper::getParsedSetting('email');
        } elseif ($authType === Settings::AUTH_TYPE_TOKEN) {
            $headers['Authorization'] = sprintf(
                'Bearer %s',
                ConfigHelper::getParsedSetting('apiToken')
            );
        }

        return $headers;
    }

    /**
     * Log message and return standard failure response.
     */
    private function _failureResponse(string $message): object
    {
        Craft::error($message, 'cloudflare');

        return (object)[
            'success' => false,
            'message' => $message,
            'result' => [],
        ];
    }

    /**
     * Returns a string for the request exception that can be used for logging.
     */
    private function _getExceptionReason(RequestException $exception): string
    {
        if ($exception->hasResponse()) {
            return $exception->getResponse()->getBody()->getContents();
        }

        return $exception->getRequest()->getUri();
    }
}
