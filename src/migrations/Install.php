<?php
/**
 * Hop DocuSign plugin for Craft CMS 3.x
 *
 * Integrates DocuSign functionalities into your forms.
 *
 * @link      https://www.hopstudios.com
 * @copyright Copyright (c) 2020 Hop Studios
 */

namespace hopstudios\hopdocusign\migrations;

use hopstudios\hopdocusign\HopDocusign;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * @author    Hop Studios
 * @package   HopDocusign
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

   /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%hopdocusign_templates}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%hopdocusign_templates}}',
                [
                    'id' => $this->primaryKey(),
                    'form_handle' => $this->string(255)->notNull()->defaultValue(''),
                    'email_handle' => $this->string(255)->notNull()->defaultValue(''),
                    'recipient_name' => $this->string(255)->notNull()->defaultValue(''),
                    'email_subject' => $this->string(255)->notNull()->defaultValue(''),
                    'template_role' => $this->string(255)->notNull()->defaultValue(''),
                    'template_id' => $this->string(255)->notNull()->defaultValue(''),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->integer()->notNull(),
                ]
            );
        }

        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%hopdocusign_signing_urls}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%hopdocusign_signing_urls}}',
                [
                    'id' => $this->primaryKey(),
                    'token' => $this->string(255)->notNull()->defaultValue(''),
                    'signing_url' => $this->text()->notNull()->defaultValue(''),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->integer()->notNull(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(
            $this->db->getIndexName(
                '{{%hopdocusign_templates}}',
                'form_handle',
                true
            ),
            '{{%hopdocusign_templates}}',
            'form_handle',
            true
        );
        $this->createIndex(
            $this->db->getIndexName(
                '{{%hopdocusign_signing_urls}}',
                'token',
                true
            ),
            '{{%hopdocusign_signing_urls}}',
            'token',
            true
        );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
    }

    /**
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists('{{%hopdocusign_templates}}');
        $this->dropTableIfExists('{{%hopdocusign_signing_urls}}');
    }
}
