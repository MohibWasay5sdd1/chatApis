<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_invitations`.
 * Has foreign keys to the tables:
 *
 * - `users`
 */
class m181023_101542_create_user_invitations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_invitations', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'email' => $this->string(50)->notNull(),
            'token' => $this->string(250)->notNull()->unique(),
            'expiry' => $this->dateTime(),
            'status' => $this->string(30)->notNull(),
            'created_on' => $this->dateTime(),
            'modified_on' => $this->dateTime(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            'idx-user_invitations-user_id',
            'user_invitations',
            'user_id'
        );

        // add foreign key for table `users`
        $this->addForeignKey(
            'fk-user_invitations-user_id',
            'user_invitations',
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
            'fk-user_invitations-user_id',
            'user_invitations'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-user_invitations-user_id',
            'user_invitations'
        );

        $this->dropTable('user_invitations');
    }
}
