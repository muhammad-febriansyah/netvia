<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserRepository
{
    /**
     * Base query for the user DataTable, eager loading roles.
     *
     * @return Builder<User>
     */
    public function dataTableQuery(): Builder
    {
        return User::query()->with('roles:id,name')->select(['id', 'name', 'email', 'is_active', 'created_at']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
