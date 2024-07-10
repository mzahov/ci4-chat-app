<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ChatMessage extends Entity
{
    /**
     * @var array<int, string>
     * @phpstan-var list<string>
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'room_id' => 'integer',
        'message' => 'string',
        'author' => 'string'
    ];
}