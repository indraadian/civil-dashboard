<?php

use App\Models\Candidate;
use App\Models\QuickCount;
use App\Models\QuickCountDetail;
use App\Models\Tps;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    (new RolePermissionSeeder())->run();
});

it('includes candidate vote columns in quick count datatable response', function () {
    $user = User::factory()->create([
        'role' => 'super_admin',
        'email' => 'qcadmin@example.com',
    ]);
    $user->assignRole('Super Admin');

    $tps = Tps::create(['name' => 'TPS 01 TEST']);

    $c1 = Candidate::create([
        'number' => 1,
        'name' => 'Candidate Alpha',
        'is_active' => true,
    ]);
    $c2 = Candidate::create([
        'number' => 2,
        'name' => 'Candidate Beta',
        'is_active' => true,
    ]);

    $qc = QuickCount::create([
        'tps_id' => $tps->id,
        'officer_name' => 'John Doe',
        'officer_phone' => '08123456789',
        'input_at' => now(),
        'total_voters' => 150,
        'invalid_votes' => 10,
        'created_by' => $user->id,
    ]);

    QuickCountDetail::create([
        'quick_count_id' => $qc->id,
        'candidate_id' => $c1->id,
        'vote_count' => 80,
    ]);

    QuickCountDetail::create([
        'quick_count_id' => $qc->id,
        'candidate_id' => $c2->id,
        'vote_count' => 60,
    ]);

    $response = $this->actingAs($user, 'web')
        ->getJson('/quick-counts/data');

    $response->assertStatus(200);
    $data = $response->json('data');

    expect($data)->not->toBeEmpty();
    expect($data[0]['candidate_vote_' . $c1->id])->toBe('80');
    expect($data[0]['candidate_vote_' . $c2->id])->toBe('60');
});
