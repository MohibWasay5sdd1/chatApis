<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_contacts`.
 * Has foreign keys to the tables:
 *
 * - `contact_lists`
 * - `users`
 */
class m181024_093649_create_user_contacts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_contacts', [
            'id' => $this->primaryKey(),
            'contact_list_id' => $this->integer()->notNull(),
            'contact_id' => $this->integer()->notNull(),
            'image' => $this->string(250),
            'status' => $this->string(25)->notNull(),
            'created_on' => $this->dateTime(),
            'modified_on' => $this->dateTime(),
        ]);

        // creates index for column `contact_list_id`
        $this->createIndex(
            'idx-user_contacts-contact_list_id',
            'user_contacts',
            'contact_list_id'
        );

        // add foreign key for table `contact_lists`
        $this->addForeignKey(
            'fk-user_contacts-contact_list_id',
            'user_contacts',
            'contact_list_id',
            'contact_lists',
            'id',
            'CASCADE'
        );

        // creates index for column `contact_id`
        $this->createIndex(
            'idx-user_contacts-contact_id',
            'user_contacts',
            'contact_id'
        );

        // add foreign key for table `users`
        $this->addForeignKey(
            'fk-user_contacts-contact_id',
            'user_contacts',
            'contact_id',
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
        // drops foreign key for table `contact_lists`
        $this->dropForeignKey(
            'fk-user_contacts-contact_list_id',
            'user_contacts'
        );

        // drops index for column `contact_list_id`
        $this->dropIndex(
            'idx-user_contacts-contact_list_id',
            'user_contacts'
        );

        // drops foreign key for table `users`
        $this->dropForeignKey(
            'fk-user_contacts-contact_id',
            'user_contacts'
        );

        // drops index for column `contact_id`
        $this->dropIndex(
            'idx-user_contacts-contact_id',
            'user_contacts'
        );

        $this->dropTable('user_contacts');
    }
}
