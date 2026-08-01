<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    (new RolePermissionSeeder())->run();
});

function createAdminUser(array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'role' => 'super_admin',
        'email' => 'admin@example.com',
    ], $attributes));
    $user->assignRole('Super Admin');
    return $user;
}

it('allows admin to access settings page', function () {
    $admin = createAdminUser();

    $response = $this->actingAs($admin, 'web')->get('/settings');

    $response->assertStatus(200);
});

it('allows admin to create a new user from settings', function () {
    $admin = createAdminUser(['email' => 'admin2@example.com']);

    $response = $this->actingAs($admin, 'web')->post('/settings/users', [
        'name' => 'User Baru',
        'email' => 'baru@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ]);

    $response->assertRedirect('/settings/users');
    $this->assertDatabaseHas('users', ['email' => 'baru@example.com']);
});

it('allows admin to access users settings page', function () {
    $admin = createAdminUser(['email' => 'admin3@example.com']);

    $response = $this->actingAs($admin, 'web')->get('/settings/users');

    $response->assertStatus(200);
});

it('returns json data for datatable user searches', function () {
    $admin = createAdminUser(['email' => 'admin4@example.com']);

    User::factory()->create([
        'name' => 'Alice Search',
        'email' => 'alice-search@example.com',
        'role' => 'user',
    ]);

    $response = $this->actingAs($admin, 'web')
        ->getJson('/settings/users/data?search=alice');

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'Alice Search']);
});

it('shows migration failure details in the UI', function () {
    $admin = createAdminUser(['email' => 'admin5@example.com']);

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
