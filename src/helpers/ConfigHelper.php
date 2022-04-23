<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2019 Working Concept Inc.
 */

namespace workingconcept\cloudflare\helpers;

use Craft;
use craft\console\Application as ConsoleApplication;
use craft\helpers\App;
use workingconcept\cloudflare\Cloudflare;
use workingconcept\cloudflare\models\Settings;

class ConfigHelper
{
    /**
     * Returns true if we’ve got the settings to make REST API calls.
     *
     * @return bool
     */
    public static function isConfigured(): bool
    {
        $authType = self::getParsedSetting('authType');

        $hasKey = self::getParsedSetting('apiKey') !== null
            && self::getParsedSetting('email') !== null;

        $hasToken = self::getParsedSetting('apiToken') !== null;

        return ($authType === Settings::AUTH_TYPE_KEY && $hasKey)
            || ($authType === Settings::AUTH_TYPE_TOKEN && $hasToken);
    }

    /**
     * Returns settings needed to connect to the REST API. Checks request
     * parameters if we’re in the control panel checking unsaved settings.
     *
     * Also parses environment variables.
     *
     * @param string $key `apiKey`, `email`, `apiToken`, or `zone`
     *
     * @return string|null
     */
    public static function getParsedSetting(string $key): ?string
    {
        $request = Craft::$app->getRequest();
        $isConsole = Craft::$app instanceof ConsoleApplication;

        /**
         * Check post params if we’re in the control panel, where we use AJAX
         * for initially checking new parameters.
         */
        $usePost = !$isConsole &&
            $request->getIsAjax() &&
            !empty($request->getParam($key)) &&
            is_string($request->getParam($key));

        $settingValue = $usePost ? $request->getParam($key) :
            Cloudflare::getInstance()->getSettings()->{$key} ?? null;

        if ($settingValue) {
            /** @scrutinizer ignore-call */
            return App::parseEnv($settingValue);
        }

        return $settingValue;
    }

    /**
     * Strips leading slash from namespaced class and returns it.
     *
     * @param string $class
     *
     * @return string
     */
    public static function normalizeClassName(string $class): string
    {
        // remove any leading slash
        return ltrim($class, '\\');
    }
}
