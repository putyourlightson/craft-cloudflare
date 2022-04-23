<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2017 Working Concept
 */

/**
 * @author    Working Concept
 * @package   Cloudflare
 * @since     1.0.0
 */

return [
    'Add Rule'                                                                                     => 'Add Rule',
    'Add complete URLs, each on its own line, that you’d like to purge from Cloudflare’s cache.'   => 'Add complete URLs, each on its own line, that you’d like to purge from Cloudflare’s cache.',
    'Add your API key, which you’ll find in the _My Profile_ section of Cloudflare’s interface.'   => 'Add your API key, which you’ll find in the _My Profile_ section of Cloudflare’s interface.',
    'Add your API token. It must include at least `cache_purge:edit` and `zone:read` permissions.' => 'Add your API token. It must include at least `cache_purge:edit` and `zone:read` permissions.',
    'Authentication Type'                                                                          => 'Authentication Type',
    'Automatically Purge Elements'                                                                 => 'Automatically Purge Elements',
    'Cache Clearing Rules'                                                                         => 'Cache Clearing Rules',
    'Choose whether you’ll be using an account-level key or scope-limited token.'                  => 'Choose whether you’ll be using an account-level key or scope-limited token.',
    'Cloudflare API Key'                                                                           => 'Cloudflare API Key',
    'Cloudflare API Token'                                                                         => 'Cloudflare API Token',
    'Cloudflare Account Email'                                                                     => 'Cloudflare Account Email',
    'Cloudflare Purge'                                                                             => 'Cloudflare Purge',
    'Cloudflare Zone ID'                                                                           => 'Cloudflare Zone ID',
    'Cloudflare Zone'                                                                              => 'Cloudflare Zone',
    'Cloudflare rules saved.'                                                                      => 'Cloudflare rules saved.',
    'Could not purge URLs.'                                                                        => 'Could not purge URLs.',
    'Could not purge zone.'                                                                        => 'Could not purge zone.',
    'Could not verify API credentials.'                                                            => 'Could not verify API credentials.',
    'Failed to purge empty or invalid URLs.'                                                       => 'Failed to purge empty or invalid URLs.',
    'Full URLs, one per line'                                                                      => 'Full URLs, one per line',
    'Please <a href="{settingsUrl}">configure the Cloudflare plugin</a> first.'                    => 'Please <a href="{settingsUrl}">configure the Cloudflare plugin</a> first.',
    'Please enter required API credentials.'                                                       => 'Please enter required API credentials.',
    'Purge Cloudflare Cache'                                                                       => 'Purge Cloudflare Cache',
    'Purge Everything'                                                                             => 'Purge Everything',
    'Purge Individual URLs'                                                                        => 'Purge Individual URLs',
    'Purge URLs'                                                                                   => 'Purge URLs',
    'Purging Cloudflare URLs'                                                                      => 'Purging Cloudflare URLs',
    'Save Rules'                                                                                   => 'Save Rules',
    'Select elements whose URLs should automatically be purged when they’re updated.'              => 'Select elements whose URLs should automatically be purged when they’re updated.',
    'Specify which Cloudflare Zone is utilized by this site.'                                      => 'Specify which Cloudflare Zone is utilized by this site.',
    'Specify which account email should be used for API requests.'                                 => 'Specify which account email should be used for API requests.',
    'Successfully purged URLs.'                                                                    => 'Successfully purged URLs.',
    'Successfully purged zone.'                                                                    => 'Successfully purged zone.',
    'The Cloudflare Zone ID utilized by this site.'                                                => 'The Cloudflare Zone ID utilized by this site.',
    'Unable to list zones for selection. You must specify a Zone ID.'                              => 'Unable to list zones for selection. You must specify a Zone ID.',
    'Verify Credentials'                                                                           => 'Verify Credentials',
    'Zone ID hardcoded in config file.'                                                            => 'Zone ID hardcoded in config file.',

    // JavaScript strings
    'Could not verify API credentials'                                                             => 'Could not verify API credentials',
    'Please enter an API key and email address first.'                                             => 'Please enter an API key and email address first.',
    'Could not purge URLs'                                                                         => 'Could not purge URLs',
    'Successfully purged URLs'                                                                     => 'Successfully purged URLs',
];
