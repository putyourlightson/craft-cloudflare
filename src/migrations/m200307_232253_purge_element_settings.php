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
        $this->_updateSettings();
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
    private function _updateSettings()
    {
        $purgeElements = [];
        $settings = Cloudflare::$plugin->getSettings();

        if ( ! empty($settings->purgeElements))
        {
            return;
        }

        if (isset($settings->purgeEntryUrls) && $settings->purgeEntryUrls)
        {
            $purgeElements[] = 'craft\elements\Entry';
        }

        if (isset($settings->purgeAssetUrls) && $settings->purgeAssetUrls)
        {
            $purgeElements[] = 'craft\elements\Asset';
        }

        if (count($purgeElements))
        {
            $settings->purgeElements = $purgeElements;
        }

        Craft::$app->getPlugins()->savePluginSettings(
            Cloudflare::$plugin,
            $purgeElements
        );
    }
}
