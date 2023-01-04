<?php
/**
 * @copyright Copyright (c) 2017 Working Concept
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\cloudflare\console\controllers;

use putyourlightson\cloudflare\Cloudflare;
use yii\console\Controller;
use yii\console\ExitCode;

class PurgeController extends Controller
{
    /**
     * Attempt to purge specific URLs.
     * https://www.yiiframework.com/doc/guide/2.0/en/tutorial-console#arguments
     *
     * @param string[] $urls
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
     */
    public function actionPurgeAll(): int
    {
        $this->stdout('Purging Cloudflare zone...' . PHP_EOL);

        $response = Cloudflare::getInstance()->api->purgeZoneCache();

        return $this->_handleResult($response);
    }

    /**
     * Handle Cloudflare’s API response for console output.
     */
    private function _handleResult(?object $response): int
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
