<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_notifications`.
 * Has foreign keys to the tables:
 *
 * - `users`
 */
class m181113_114216_create_user_notifications_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_notifications', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'body' => $this->string(250)->notNull(),
            'subject' => $this->string(100),
            'image_link' => $this->string(250),
            'is_read' => $this->string(25),
            'created_on' => $this->dateTime(),
            'modified_on' => $this->dateTime(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            'idx-user_notifications-user_id',
            'user_notifications',
            'user_id'
        );

        // add foreign key for table `users`
        $this->addForeignKey(
            'fk-user_notifications-user_id',
            'user_notifications',
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
            'fk-user_notifications-user_id',
            'user_notifications'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-user_notifications-user_id',
            'user_notifications'
        );

        $this->dropTable('user_notifications');
    }
}
