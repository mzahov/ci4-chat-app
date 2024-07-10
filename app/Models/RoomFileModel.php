<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\RoomFile;

class RoomFileModel extends Model
{
    protected $table            = 'room_files';

    protected $primaryKey = 'id';

    protected $allowedFields = [
        'room_id',
        'message_id',
        'file_original_name',
        'file_name',
        'file_type',
        'file_size'
    ];
    
    protected $returnType = RoomFile::class;

    protected $useTimestamps = true;
}
