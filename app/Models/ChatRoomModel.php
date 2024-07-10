<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\ChatRoom;
use CodeIgniter\I18n\Time;
use App\Models\RoomUserModel;

class ChatRoomModel extends Model
{
    protected $table      = 'chat_rooms';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name',
        'description',
        'image',
        'created_by'
    ];

    protected $useSoftDeletes = true;
    
    protected $returnType = ChatRoom::class;

    protected $useTimestamps = true;
    
    /**
     * Adds chat room in database along new row in room users table
     *
     * @return int|false returns the new chat room id if success or false otherwise
     */
    public function addChatRoom(ChatRoom $chatRoom, ?array $users = null): bool|int {
        $this->db->transStart();

        if (! ($newRoomId = $this->insert($chatRoom, true))) {
            $this->db->transRollback();
            return false;
        }

        if ($users !== null) {
             (new RoomUserModel())->addUsers($newRoomId, $users);
        }

        $this->db->transComplete();

        return $newRoomId;
    }

    /**
     * Gets all user chat rooms
     *
     * @return array|object|false returns all chat rooms for user
     */
    public function getUserChatRooms(int|string $userId)
    {
        $subquery = $this->db->table('chat_messages cm1')
                             ->select('cm1.id, cm1.room_id, cm1.message, cm1.author, cm1.created_at')
                             ->join('(SELECT room_id, MAX(created_at) as max_created_at
                                      FROM chat_messages
                                      GROUP BY room_id) cm2', 'cm1.room_id = cm2.room_id AND cm1.created_at = cm2.max_created_at', 'inner')
                             ->getCompiledSelect();
    
        $this->select('chat_rooms.id, chat_rooms.name, chat_rooms.description, chat_rooms.image, cm.message, COALESCE(cm.created_at, chat_rooms.created_at) as created_at, cm.author, rf.file_type')
             ->join('room_users as ru', 'ru.room_id = chat_rooms.id', 'left')
             ->join("($subquery) as cm", 'cm.room_id = chat_rooms.id', 'left')
             ->join('room_files as rf', 'rf.message_id = cm.id', 'left')
             ->where('chat_rooms.created_by', $userId)
             ->orWhere('ru.user_id', $userId)
             ->groupBy('chat_rooms.id')
             ->orderBy('cm.created_at DESC');
    
        return $this->findAll();
    }
    
    /**
     * Gets room with messages and active users.
     *
     * @return array|object returns all chat rooms for user
     */
    public function getRoom(int|string $roomId)
    {
        $room = $this->select('chat_rooms.id, chat_rooms.name, chat_rooms.description')->find($roomId);
        $roomUsers = (new RoomUserModel())->getRoomUsers($roomId);

        $roomData = [];

        if ($room) {
            $roomData['room'] = [
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
            ];
        }

        if ($roomUsers) {
            $roomUsers = array_filter($roomUsers, function($user) {
                return $user->username === auth()->user()->username ? false : true;
            });
            
            $roomData['roomUsers'] = array_values($roomUsers);
        }

        return $roomData;
    }
    
    public function getMessages(int|string $roomId, int $limit = 20, int $offset = 0) {
        $chatMessageQuery = $this->db->table('chat_messages');
        $chatMessageQuery->select('u.username, chat_messages.id, chat_messages.message, chat_messages.created_at, rf.file_name, rf.file_type, rf.file_original_name');
        $chatMessageQuery->join('users as u', 'u.id = chat_messages.user_id');
        $chatMessageQuery->join('room_files as rf', 'rf.message_id = chat_messages.id', 'left');
        $chatMessageQuery->where('chat_messages.room_id', $roomId);
        $chatMessageQuery->orderBy('created_at', 'DESC');
        $chatMessageQuery->limit($limit, $offset);
        $chatMessages = $chatMessageQuery->get()->getResult();
        
        $roomMessages = [];
        
        if ($chatMessages !== []) {
            foreach($chatMessages as $message) {
                $messageTime = new Time($message->created_at);
                $formattedTime = $messageTime->getTimestamp();

                $roomMessages[$message->id] = [
                    'user' => $message->username,
                    'message' => $message->message,
                    'time' => $formattedTime,
                ];
                
                if ($message->file_name) {
                    $roomMessages[$message->id]['file'] = [
                        'name' => $message->file_name,
                        'original_name' => $message->file_original_name,
                        'type' => $message->file_type,
                    ];
                }
            }
        }

        return $roomMessages;
    }
}