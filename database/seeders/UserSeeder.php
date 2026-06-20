<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@netvia.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('password'), 'is_active' => true],
        );
        $superAdmin->syncRoles('super_admin');

        $admin = User::firstOrCreate(
            ['email' => 'admin@netvia.test'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'is_active' => true],
        );
        $admin->syncRoles('admin');

        $finance = User::firstOrCreate(
            ['email' => 'finance@netvia.test'],
            ['name' => 'Finance', 'password' => Hash::make('password'), 'is_active' => true],
        );
        $finance->syncRoles('finance');
    }
}
