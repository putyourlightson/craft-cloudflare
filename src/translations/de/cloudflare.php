<?php
/**
 * Cloudflare plugin for Craft CMS 4.x
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
    'Cloudflare Purge' => 'Cloudflare',
    'Cloudflare API Key' => 'Cloudflare-API-Schlüssel',
    'Add your API key, which you\'ll find in the _My Profile_ section of Cloudflare\'s interface.' => 'Fügen Sie Ihren API-Schlüssel hinzu, den Sie auf der Cloudflare-Webseite unter [My Profile](https://www.cloudflare.com/a/profile/) finden.',
    'Cloudflare Account Email' => 'Cloudflare-Account E-Mail-Adresse',
    'Specify which account email should be used for API requests.' => 'Geben Sie die E-Mail-Adresse des Accounts an, der für die API-Zugriffe verwendet werden soll.',
    'Verify Credentials' => 'Zugangsdaten validieren',
    'Cloudflare Zone' => 'Cloudflare-Zone',
    'Specify which Cloudflare Zone is utilized by this site.' => 'Geben Sie die Cloudflare-Zone an, der diese Webseite zugeordnet ist.',
    'Add complete URLs, each on its own line, that you\'d like to clear from Cloudflare\'s cache.' => 'Fügen Sie vollständige URLs hinzu, jede auf einer eigenen Zeile, die aus dem Cloudflare-Cache gelöscht werden sollen.',
    'Purge URLs' => 'URLs entfernen',
    'Purge Cloudflare Cache' => 'Cloudflare-Cache leeren',
    'Purge Individual Files' => 'Einzelne Dateien entfernen',
    'Full URLs, one per line' => 'Vollständige URLs, eine pro Zeile',
    'Purge Everything' => 'Alles entfernen',
    'Please <a href="{settingsUrl}">configure the Cloudflare plugin</a> first.' => 'Bitte <a href="{settingsUrl}">konfigurieren Sie das Cloudflare plugin</a> zuerst.',

    // JavaScript strings
    'Could not verify API credentials.' => 'Konnte die API-Zugangsdaten nicht verifizieren.',
    'Please enter an API key and email address first.' => 'Bitte API-Schlüssel und E-Mail-Adresse eingeben.',
    'Could not purge URLs.' => 'Entfernung der URLs fehlgeschlagen.',
    'Successfully purged URLs.' => 'URLs erfolgreich entfernt.',
];
