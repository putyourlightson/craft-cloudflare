<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

/**
 * Cloudflare config.php
 *
 * This file exists only as a template for the Cloudflare settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'cloudflare.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    '*' => [
        // Type of API authentication to use.
        //'authType' => 'key',

        // Account-level API key.
        //'apiKey' => '1234567890',

        // Primary account email address. Required with $apiKey.
        //'email' => 'email@domain.com',

        // App token. (Alternative to $apiKey + $email.)
        //'apiToken' => '1234567890',

        // This site’s related Cloudflare Zone ID.
        //'zone' => '1234567890',

        // List of element type classes that should be purged automatically.
        //purgeElements = [
        //    'craft\elements\Asset',
        //],
    ],
];
