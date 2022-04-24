<?php
/**
 * Cloudflare plugin for Craft CMS 3.x
 *
 * Purge Cloudflare caches from Craft.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2017 Working Concept
 */

namespace workingconcept\cloudflare\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Table as CraftTable;
use workingconcept\cloudflare\db\Table;

/**
 * @author    Working Concept
 * @package   Cloudflare
 * @since     1.0.0
 */
class Install extends Migration
{
    /**
     * @var string The database driver to use
     */
    public $driver;

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;

        if ($this->createTables()) {
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    /**
     * @return bool
     */
    protected function createTables(): bool
    {
        $tablesCreated = false;

        if (! Craft::$app->db->tableExists(Table::RULES)) {
            $tablesCreated = true;
            $this->createTable(
                Table::RULES,
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'siteId' => $this->integer()->notNull(),
                    'trigger' => $this->string(255)->notNull(),
                    'urlsToClear' => $this->string(255)->notNull(),
                    'refresh' => $this->boolean()->defaultValue(false),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function addForeignKeys(): void
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName(),
            Table::RULES,
            'siteId',
            CraftTable::SITES,
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * @return void
     */
    protected function removeTables(): void
    {
        $this->dropTableIfExists(Table::RULES);
    }
}
