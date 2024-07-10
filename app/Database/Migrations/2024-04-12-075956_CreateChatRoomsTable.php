<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatRoomsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'description'   => ['type' => 'TEXT'],
            'image'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at'    => ['type' => 'datetime', 'null' => true],
            'updated_at'    => ['type' => 'datetime', 'null' => true],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'NO ACTION');
        $this->forge->createTable('chat_rooms');
    }

    public function down()
    {
        $this->forge->dropTable('chat_rooms', true);
    }
}
