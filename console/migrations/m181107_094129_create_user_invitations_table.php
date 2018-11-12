<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_invitations`.
 * Has foreign keys to the tables:
 *
 * - `users`
 * - `users`
 */
class m181107_094129_create_user_invitations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_invitations', [
            'id' => $this->primaryKey(),
            'invited_by_id' => $this->integer()->notNull(),
            'invited_id' => $this->integer()->notNull(),
            'token' => $this->string(250)->notNull()->unique(),
            'expiry' => $this->dateTime(),
            'status' => $this->string(30)->notNull(),
            'created_on' => $this->dateTime(),
            'modified_on' => $this->dateTime(),
        ]);

        // creates index for column `invited_by_id`
        $this->createIndex(
            'idx-user_invitations-invited_by_id',
            'user_invitations',
            'invited_by_id'
        );

        // add foreign key for table `users`
        $this->addForeignKey(
            'fk-user_invitations-invited_by_id',
            'user_invitations',
            'invited_by_id',
            'users',
            'id',
            'CASCADE'
        );

        // creates index for column `invited_id`
        $this->createIndex(
            'idx-user_invitations-invited_id',
            'user_invitations',
            'invited_id'
        );

        // add foreign key for table `users`
        $this->addForeignKey(
            'fk-user_invitations-invited_id',
            'user_invitations',
            'invited_id',
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
            'fk-user_invitations-invited_by_id',
            'user_invitations'
        );

        // drops index for column `invited_by_id`
        $this->dropIndex(
            'idx-user_invitations-invited_by_id',
            'user_invitations'
        );

        // drops foreign key for table `users`
        $this->dropForeignKey(
            'fk-user_invitations-invited_id',
            'user_invitations'
        );

        // drops index for column `invited_id`
        $this->dropIndex(
            'idx-user_invitations-invited_id',
            'user_invitations'
        );

        $this->dropTable('user_invitations');
    }
}
