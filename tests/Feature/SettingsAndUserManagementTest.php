<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

it('allows admin to access settings page', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    $response = $this->actingAs($admin, 'web')->get('/settings');

    $response->assertStatus(200);
});

it('allows admin to create a new user from settings', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin2@example.com',
    ]);

    $response = $this->actingAs($admin, 'web')->post('/settings/users', [
        'name' => 'User Baru',
        'email' => 'baru@example.com',
        'password' => 'password123',
        'role' => 'user',
    ]);

    $response->assertRedirect('/settings/users');
    $this->assertDatabaseHas('users', ['email' => 'baru@example.com']);
});

it('renders an ajax delete handler for users in the settings page', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin3@example.com',
    ]);

    $response = $this->actingAs($admin, 'web')->get('/settings/users');

    $response->assertStatus(200);
    $response->assertSee('function deleteUser(', false);
    $response->assertSee('X-Requested-With', false);
});

it('returns a partial table for ajax user searches', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin4@example.com',
    ]);

    User::factory()->create([
        'name' => 'Alice Search',
        'email' => 'alice-search@example.com',
        'role' => 'user',
    ]);

    $response = $this->actingAs($admin, 'web')
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get('/settings/users?search=alice');

    $response->assertStatus(200);
    $response->assertDontSee('Manajemen User');
    $response->assertSee('Alice Search');
});

it('shows migration failure details in the UI', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin3@example.com',
    ]);

    Artisan::shouldReceive('call')
        ->once()
        ->andReturn(1);

    Artisan::shouldReceive('output')
        ->once()
        ->andReturn("SQLSTATE[42S01]: Base table or view already exists\nTable 'civils' already exists");

    $response = $this->actingAs($admin, 'web')->from('/settings/general')->post('/settings/migrate');

    $response->assertRedirect('/settings/general');
    $response->assertSessionHas('error', "SQLSTATE[42S01]: Base table or view already exists\nTable 'civils' already exists");
});
