<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2019 Working Concept Inc.
 */

namespace workingconcept\cloudflare\helpers;

use craft\console\Application as ConsoleApplication;
use workingconcept\cloudflare\Cloudflare;
use Craft;
use workingconcept\cloudflare\models\Settings;

class ConfigHelper
{
    /**
     * Returns true if we've got the settings to make REST API calls.
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
     * parameters if we're in the control panel checking unsaved settings.
     *
     * Also parses environment variables.
     *
     * @param string $key `apiKey`, `email`, or `apiToken`
     *
     * @return string|null
     */
    public static function getParsedSetting($key)
    {
        $request = Craft::$app->getRequest();
        $isConsole = Craft::$app instanceof ConsoleApplication;

        /**
         * Check post params if we're in the control panel, where we use AJAX
         * for initially checking new parameters.
         */
        $usePost = ! $isConsole &&
            $request->getIsAjax() &&
            ! empty($request->getParam($key)) &&
            is_string($request->getParam($key));

        $settingValue = $usePost ? $request->getParam($key) :
            Cloudflare::$plugin->getSettings()->{$key} ?? null;

        if (self::isCraft31() && $settingValue)
        {
            /** @scrutinizer ignore-call */
            return Craft::parseEnv($settingValue);
        }

        return $settingValue;
    }

    /**
     * Returns `true` if Craft version is 3.1 or greater.
     *
     * @return bool
     */
    public static function isCraft31(): bool
    {
        return version_compare(Craft::$app->getVersion(), '3.1', '>=');
    }
}
