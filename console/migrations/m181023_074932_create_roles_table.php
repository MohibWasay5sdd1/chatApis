<?php

use yii\db\Migration;

/**
 * Handles the creation of table `roles`.
 */
class m181023_074932_create_roles_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('roles', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->notNull()->unique(),
            'created_on' => $this->dateTime(),
            'modified_on' => $this->dateTime(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('roles');
    }
}
