<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoomUserTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'room_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('room_id', 'chat_rooms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'NO ACTION');
        $this->forge->createTable('room_user');
    }

    public function down()
    {
        $this->forge->dropTable('room_user');
    }
}
