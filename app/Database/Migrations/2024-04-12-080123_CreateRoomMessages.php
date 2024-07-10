<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoomMessages extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'room_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'author'        => ['type' => 'VARCHAR', 'constraint' => 120],
            'message'       => ['type' => 'TEXT'],
            'created_at'    => ['type' => 'datetime', 'null' => true],
            'updated_at'    => ['type' => 'datetime', 'null' => true],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('room_id', 'chat_rooms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'NO ACTION');
        $this->forge->createTable('chat_messages');
    }

    public function down()
    {
        $this->forge->dropTable('chat_messages');
    }
}
