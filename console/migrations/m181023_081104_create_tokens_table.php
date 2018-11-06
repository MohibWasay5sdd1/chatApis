<?php

use yii\db\Migration;

/**
 * Handles the creation of table `tokens`.
 * Has foreign keys to the tables:
 *
 * - `users`
 */
class m181023_081104_create_tokens_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('tokens', [
            'id' => $this->primaryKey(),
            'token' => $this->string(250)->notNull()->unique(),
            'expiry' => $this->dateTime()notNull(),
            'user_id' => $this->integer()->notNull(),
            'created_on' => $this->dateTime(),
            'modified_on' => $this->dateTime(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            'idx-tokens-user_id',
            'tokens',
            'user_id'
        );

        // add foreign key for table `users`
        $this->addForeignKey(
            'fk-tokens-user_id',
            'tokens',
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
            'fk-tokens-user_id',
            'tokens'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-tokens-user_id',
            'tokens'
        );

        $this->dropTable('tokens');
    }
}
