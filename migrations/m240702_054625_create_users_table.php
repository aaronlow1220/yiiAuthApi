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
            "id" => $this->primaryKey(),
            "uuid" =>  $this->string(255)->notNull(),
            "username" => $this->string(255),
            "email"=> $this->string(255)->notNull(),
            "password"=> $this->string(255)->notNull(),
            "status"=> $this->smallInteger(1)->notNull()->defaultValue(0),
            "access_token"=> $this->string(255),
            "created_at"=> $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            "updated_at"=> $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
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
