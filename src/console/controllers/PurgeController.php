<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2019 Working Concept
 */

namespace workingconcept\cloudflare\console\controllers;

use GuzzleHttp\Exception\GuzzleException;
use workingconcept\cloudflare\Cloudflare;
use yii\console\Controller;
use yii\console\ExitCode;

class PurgeController extends Controller
{
    /**
     * Attempt to purge specific URLs.
     * https://www.yiiframework.com/doc/guide/2.0/en/tutorial-console#arguments
     *
     * @param array $urls
     * @return int
     * @throws GuzzleException
     */
    public function actionPurgeUrls(array $urls): int
    {
        $urlCount = count($urls);
        $urlWord = $urlCount === 1 ? 'URL' : 'URLs';

        $this->stdout(
            sprintf('Purging %d %s...', $urlCount, $urlWord) . PHP_EOL
        );

        $response = Cloudflare::getInstance()->api->purgeUrls($urls);

        return $this->_handleResult($response);
    }

    /**
     * Attempt to purge entire zone cache.
     *
     * @return int
     * @throws GuzzleException
     */
    public function actionPurgeAll(): int
    {
        $this->stdout('Purging Cloudflare zone...' . PHP_EOL);

        $response = Cloudflare::getInstance()->api->purgeZoneCache();

        return $this->_handleResult($response);
    }

    /**
     * Handle Cloudflare’s API response for console output.
     *
     * @param ?object $response
     * @return int
     */
    private function _handleResult($response): int
    {
        if (empty($response)) {
            $this->stdout('✗ Cloudflare plugin not configured' . PHP_EOL);
            return ExitCode::CONFIG;
        }

        if (isset($response->success)) {
            if ($response->success) {
                $this->stdout('✓ success' . PHP_EOL);
                return ExitCode::OK;
            }

            $this->stdout('✗ purge failed' . PHP_EOL);

            if (isset($response->errors)) {
                foreach ($response->errors as $error) {
                    $this->stdout(
                        sprintf('- %s: %s', $error->code, $error->message) . PHP_EOL
                    );
                }
            }

            return ExitCode::UNAVAILABLE;
        }

        $this->stdout('✗ purge failed' . PHP_EOL);
        return ExitCode::UNAVAILABLE;
    }
}
