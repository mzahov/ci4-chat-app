<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\ChatMessage;

class ChatMessageModel extends Model
{
    protected $table      = 'chat_messages';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id',
        'room_id',
        'message',
        'author',
    ];

    protected $useSoftDeletes = true;
    
    protected $returnType = ChatMessage::class;

    protected $useTimestamps = true;
}