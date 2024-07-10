<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected function initialize(): void
    {
        parent::initialize();

        $this->allowedFields = [
            ...$this->allowedFields,
            'fullname',
        ];
    }

    public function getUsers(string|int|null $userId) {
        return $this->select('id, username')->where('id !=', $userId)->findAll();
    } 
}
