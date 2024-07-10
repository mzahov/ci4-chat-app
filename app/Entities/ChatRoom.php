<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ChatRoom extends Entity
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
        'name' => 'string',
        'description' => 'string',
        'image' => '?string',
        'created_by' => 'integer'
    ];
}