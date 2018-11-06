<?php

use yii\db\Migration;

/**
 * Handles the creation of table `contact_lists`.
 * Has foreign keys to the tables:
 *
 * - `users`
 */
class m181024_080252_create_contact_lists_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('contact_lists', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string(25)->notNull(),
            'created_on' => $this->dateTime(),
            'modified_on' => $this->dateTime(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            'idx-contact_lists-user_id',
            'contact_lists',
            'user_id'
        );

        // add foreign key for table `users`
        $this->addForeignKey(
            'fk-contact_lists-user_id',
            'contact_lists',
            'user_id',
            'users',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `users`
        $this->dropForeignKey(
            'fk-contact_lists-user_id',
            'contact_lists'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-contact_lists-user_id',
            'contact_lists'
        );

        $this->dropTable('contact_lists');
    }
}
