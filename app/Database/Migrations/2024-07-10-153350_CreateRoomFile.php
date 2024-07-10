<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoomFile extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'room_id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'message_id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'file_original_name'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_name'             => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_type'             => ['type' => 'VARCHAR', 'constraint' => 30],
            'file_size'             => ['type' => 'FLOAT'],
            'created_at'            => ['type' => 'datetime'],
            'updated_at'            => ['type' => 'datetime'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('room_id', 'chat_rooms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('message_id', 'chat_messages', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('room_files');
    }

    public function down()
    {
        $this->forge->dropTable('room_files');
    }
}
