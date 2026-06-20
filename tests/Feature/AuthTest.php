<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

it('shows the login page to guests', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('Masuk');
});

it('redirects guests at root to login', function () {
    $this->get('/')->assertRedirect(route('login'));
});

it('redirects guests from the dashboard to login', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('logs in an active user with valid credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret123'),
        'is_active' => true,
    ]);

    $this->post(route('login.attempt'), [
        'email' => $user->email,
        'password' => 'secret123',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret123'),
    ]);

    $this->post(route('login.attempt'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('blocks inactive users from logging in', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret123'),
        'is_active' => false,
    ]);

    $this->post(route('login.attempt'), [
        'email' => $user->email,
        'password' => 'secret123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('validates required login fields', function () {
    $this->post(route('login.attempt'), [])
        ->assertSessionHasErrors(['email', 'password']);
});

it('throttles after too many failed attempts', function () {
    $user = User::factory()->create(['password' => Hash::make('secret123')]);

    foreach (range(1, 5) as $i) {
        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'wrong',
        ]);
    }

    $response = $this->post(route('login.attempt'), [
        'email' => $user->email,
        'password' => 'secret123',
    ]);

    $response->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Terlalu banyak percobaan');

    RateLimiter::clear(strtolower($user->email).'|127.0.0.1');
});

it('logs out an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('logs out a user deactivated mid-session via the active middleware', function () {
    $user = User::factory()->create(['is_active' => false]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
