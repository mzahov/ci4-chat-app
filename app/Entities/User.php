<?php

namespace App\Entities;

use CodeIgniter\Shield\Entities\Login;
use CodeIgniter\Shield\Entities\User as ShieldUser;
use CodeIgniter\Shield\Models\LoginModel;

class User extends ShieldUser
{
    public function __construct(?array $data = null)
    {
        $casts = [
            'fullname'          => 'string',
        ];

        $this->casts = [...$this->casts, ...$casts];

        parent::__construct($data);
    }

    /**
     * Returns the user's last login record.
     */
    public function lastLogin(): ?Login
    {
        return model(LoginModel::class)->lastLogin($this);
    }

    /**
     * Returns the user's last login records.
     */
    public function logins(int $limit = 10)
    {
        return model(LoginModel::class)
            ->where('user_id', $this->id)
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->find();
    }
}
