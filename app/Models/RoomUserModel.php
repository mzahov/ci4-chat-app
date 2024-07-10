<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\RoomUser;

class RoomUserModel extends Model
{
    protected $table      = 'room_users';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id',
        'room_id'
    ];

    protected $returnType = RoomUser::class;

    /**
     * Adds users in chat room
    */
    public function addUsers(int $roomId, array|int|null $users) {
        $data = [];

        if (is_array($users) && $users !== null) {
            foreach($users as $user) {
                $data[] = [
                    'user_id' => $user,
                    'room_id' => $roomId
                ];
            }
        }
        
        // Include a room creator
        $data[] = [
            'user_id' => user_id(),
            'room_id' => $roomId
        ];

        $this->insertBatch($data);
    }

    public function getRoomUsers(int|string $roomId, int $limit = null) {
        $this->select('u.username');
        $this->join('users as u', 'u.id = room_users.user_id');
        $this->where('room_id', $roomId);
        $this->orderBy('u.last_active DESC');

        if ($limit) {
            $this->limit($limit);
        }
    
        return $this->get()->getResult();
    }
}