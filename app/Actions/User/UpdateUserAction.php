<?php

namespace App\Actions\User;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

class UpdateUserAction
{
    public function __construct(private UserRepository $users) {}

    /**
     * @param  array{name: string, email: string, password?: ?string, is_active: bool, role: string}  $data
     */
    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $attributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'is_active' => $data['is_active'],
            ];

            if (! empty($data['password'])) {
                $attributes['password'] = $data['password'];
            }

            $this->users->update($user, $attributes);
            $user->syncRoles([$data['role']]);

            return $user;
        });
    }
}
