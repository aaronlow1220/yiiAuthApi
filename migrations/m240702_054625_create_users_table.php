<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation of table `users`.
 */
class m240702_054625_create_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('users', [
            "id" => $this->bigPrimaryKey()->unsigned()->notNull()->comment('編號'),
            "uuid" => $this->string(255)->notNull(),
            "username" => $this->string(255),
            "email" => $this->string(255)->notNull(),
            "password" => $this->string(255)->notNull(),
            "status" => $this->smallInteger(1)->notNull()->defaultValue(0),
            "auth_key" => $this->string(255),
            "access_token" => $this->string(255),
            "created_at" => $this->integer(10)->unsigned()->notNull()->comment('unixtime'),
            "updated_at" => $this->integer(10)->unsigned()->notNull()->comment('unixtime'),
            "created_by" => $this->bigInteger(20)->unsigned()->notNull()->comment('ref:users.id'),
            "updated_by" => $this->bigInteger(20)->unsigned()->notNull()->comment('ref:users.id'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('users');
    }
}
