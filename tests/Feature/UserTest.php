<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('admin');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('customer');
});

it('lists users via the datatable', function () {
    $this->actingAs($this->superAdmin)
        ->getJson(route('user.data'))
        ->assertOk()
        ->assertJsonPath('recordsTotal', 2);
});

it('creates a user with a role and hashed password', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('user.store'), [
            'name' => 'Staff Baru',
            'email' => 'staff@netvia.id',
            'password' => 'rahasia123',
            'role' => 'admin',
            'is_active' => '1',
        ])
        ->assertRedirect(route('user.index'));

    $user = User::where('email', 'staff@netvia.id')->first();
    expect($user)->not->toBeNull()
        ->and($user->hasRole('admin'))->toBeTrue()
        ->and(Hash::check('rahasia123', $user->password))->toBeTrue();
});

it('validates user creation', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('user.store'), ['email' => $this->admin->email])
        ->assertSessionHasErrors([
            'name' => 'Nama wajib diisi.',
            'email' => 'Email sudah digunakan.',
            'password' => 'Kata sandi wajib diisi.',
            'role' => 'Peran wajib dipilih.',
        ]);
});

it('updates a user and keeps the password when blank', function () {
    $user = User::factory()->create(['password' => Hash::make('original1')]);
    $user->assignRole('admin');

    $this->actingAs($this->superAdmin)
        ->put(route('user.update', $user), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'password' => '',
            'role' => 'admin',
            'is_active' => '1',
        ])
        ->assertRedirect(route('user.index'));

    $user->refresh();
    expect($user->name)->toBe('Updated Name')
        ->and($user->hasRole('admin'))->toBeTrue()
        ->and(Hash::check('original1', $user->password))->toBeTrue();
});

it('soft deletes a user', function () {
    $target = User::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson(route('user.destroy', $target))
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($target->fresh()->trashed())->toBeTrue();
});

it('cannot delete its own account', function () {
    $this->actingAs($this->superAdmin)
        ->deleteJson(route('user.destroy', $this->superAdmin))
        ->assertStatus(422)
        ->assertJsonPath('success', false);

    expect($this->superAdmin->fresh()->trashed())->toBeFalse();
});

it('toggles user active status', function () {
    $target = User::factory()->create(['is_active' => true]);

    $this->actingAs($this->superAdmin)
        ->patchJson(route('user.toggle', $target))
        ->assertOk();

    expect($target->fresh()->is_active)->toBeFalse();
});

it('cannot deactivate its own account', function () {
    $this->actingAs($this->superAdmin)
        ->patchJson(route('user.toggle', $this->superAdmin))
        ->assertStatus(422);
});

it('resets a user password', function () {
    $target = User::factory()->create();

    $this->actingAs($this->superAdmin)
        ->postJson(route('user.resetPassword', $target), ['password' => 'newpass123'])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(Hash::check('newpass123', $target->fresh()->password))->toBeTrue();
});

it('forbids non-super-admin from managing users', function () {
    $this->actingAs($this->admin)->getJson(route('user.data'))->assertForbidden();
    $this->actingAs($this->admin)
        ->post(route('user.store'), ['name' => 'X', 'email' => 'x@x.id', 'password' => 'pass1234', 'role' => 'admin'])
        ->assertForbidden();
});
