<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class RoomFile extends Entity
{
    /**
     * @var array<int, string>
     * @phpstan-var list<string>
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'room_id' => 'integer',
        'message_id' => 'integer',
        'file_original_name' => 'string',
        'file_name' => 'string',
        'file_type' => 'string',
        'file_size' => 'float'
    ];
}