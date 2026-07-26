<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
