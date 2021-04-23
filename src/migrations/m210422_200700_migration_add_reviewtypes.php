<?php

namespace mortscode\reviews\migrations;

use Craft;
use craft\db\Migration;

/**
 * m210422_200700_migration_add_reviewtypes migration.
 */
class m210422_200700_migration_add_reviewtypes extends Migration
{
    /**
     * @inheritdoc
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%reviews_reviewsrecord}}', 'reviewType')) {
            $this->addColumn('{{%reviews_reviewsrecord}}', 'reviewType', $this->string()->notNull()->defaultValue(''));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210422_200700_migration_add_reviewtypes cannot be reverted.\n";
        return false;
    }
}
