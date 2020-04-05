<?php

namespace workingconcept\cloudflare\migrations;

use Craft;
use workingconcept\cloudflare\Cloudflare;
use craft\db\Migration;

/**
 * m200307_232253_purge_element_settings migration.
 */
class m200307_232253_purge_element_settings extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->_resaveSettings();
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200307_232253_purge_element_settings cannot be reverted.\n";
        return false;
    }

    /**
     * Moves previous `purgeEntryUrls` and `purgeAssetUrls` settings to
     * new `purgeElements` array.
     */
    private function _resaveSettings()
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $settings = $projectConfig->get('plugins.cloudflare.settings');
        $schemaVersion = $projectConfig->get('plugins.cloudflare.schemaVersion');

        if (empty($settings) || version_compare($schemaVersion, '1.0.1', '>='))
        {
            echo 'No settings to update.';
            return;
        }

        $purgeElements = [];

        if ($settings['purgeEntryUrls'])
        {
            $purgeElements[] = 'craft\elements\Entry';
        }

        if ($settings['purgeAssetUrls'])
        {
            $purgeElements[] = 'craft\elements\Asset';
        }

        $settings['purgeElements'] = $purgeElements;

        unset($settings['purgeEntryUrls'], $settings['purgeAssetUrls']);

        $projectConfig->set(
            'plugins.cloudflare.settings',
            $settings,
            'Migrated previous plugin settings.'
        );
    }
}
