<?php

namespace App\Actions\User;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

class StoreUserAction
{
    public function __construct(private UserRepository $users) {}

    /**
     * @param  array{name: string, email: string, password: string, is_active: bool, role: string}  $data
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = $this->users->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_active' => $data['is_active'],
            ]);

            $user->syncRoles([$data['role']]);

            return $user;
        });
    }
}
