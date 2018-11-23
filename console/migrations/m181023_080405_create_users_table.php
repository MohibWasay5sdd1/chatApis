<?php

use yii\db\Migration;

/**
 * Handles the creation of table `users`.
 * Has foreign keys to the tables:
 *
 * - `roles`
 */
class m181023_080405_create_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('users', [
            'id' => $this->primaryKey(),
            'user_name' => $this->string(25)->notNull()->unique(),
            'first_name' => $this->string(50),
            'last_name' => $this->string(50),
            'full_name' => $this->string(100),
            'user_email' => $this->string(100)->notNull()->unique(),
            'user_password' => $this->string(100)->notNull(),
            'phone_number' => $this->text(),
            'dob' => $this->date(),
            'device_token' => $this->string(250)->unique(),
            'status' => $this->string(25)->notNull(),
            'reset_token' => $this->string(250),
            'reset_expiry' => $this->dateTime(),
            'profile_pic_url' => $this->string(300),
            'last_login' => $this->dateTime(),
            'role_id' => $this->integer()->notNull(),
            'created_on' => $this->dateTime(),
            'modified_on' => $this->dateTime(),
        ]);

        // creates index for column `role_id`
        $this->createIndex(
            'idx-users-role_id',
            'users',
            'role_id'
        );

        // add foreign key for table `roles`
        $this->addForeignKey(
            'fk-users-role_id',
            'users',
            'role_id',
            'roles',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `roles`
        $this->dropForeignKey(
            'fk-users-role_id',
            'users'
        );

        // drops index for column `role_id`
        $this->dropIndex(
            'idx-users-role_id',
            'users'
        );

        $this->dropTable('users');
    }
}
