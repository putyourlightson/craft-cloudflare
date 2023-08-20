<?php
/**
 * @copyright Copyright (c) 2017 Working Concept
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\cloudflare\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Table as CraftTable;
use putyourlightson\cloudflare\db\Table;

class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
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
        $this->removeTables();

        return true;
    }

    protected function createTables(): bool
    {
        $tablesCreated = false;

        if (!Craft::$app->db->tableExists(Table::RULES)) {
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

    protected function removeTables(): void
    {
        $this->dropTableIfExists(Table::RULES);
    }
}
