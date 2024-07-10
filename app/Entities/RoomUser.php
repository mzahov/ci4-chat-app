<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class RoomUser extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'room_id' => 'integer',
        'user_id' => 'integer'
    ];
}