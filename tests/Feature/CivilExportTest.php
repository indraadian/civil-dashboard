<?php

use App\Jobs\GenerateCivilExportJob;
use App\Models\CivilExport;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Storage::disk('local')->put('exports/civils.xlsx', 'dummy content');
    Queue::fake();
});

// ── Export Flow ────────────────────────────────────────────────────────────────

test('admin dapat memulai proses export', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);

    $response = $this->actingAs($admin)
        ->post(route('civils.export'));

    $response->assertRedirect();
    $response->assertSessionHas('info');
});

test('export memastikan job di-dispatch ke queue', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);

    $this->actingAs($admin)->post(route('civils.export'));

    Queue::assertPushed(GenerateCivilExportJob::class);
});

test('export membuat record di database dengan status pending', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);

    $this->actingAs($admin)->post(route('civils.export'));

    $this->assertDatabaseHas('exports', [
        'created_by' => $admin->id,
        'status'     => 'pending',
    ]);
});

test('non-admin tidak bisa mengakses export', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->post(route('civils.export'));

    $response->assertForbidden();
    Queue::assertNothingPushed();
});

// ── Progress Endpoint ──────────────────────────────────────────────────────────

test('endpoint progress export mengembalikan JSON yang benar', function () {
    $admin  = User::factory()->create(['role' => 'admin']);
    $export = CivilExport::factory()->create([
        'created_by'     => $admin->id,
        'status'         => 'processing',
        'progress'       => 50,
        'total_rows'     => 5000,
        'processed_rows' => 2500,
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('civils.export.progress', $export));

    $response->assertOk()
        ->assertJsonStructure(['id', 'status', 'progress', 'total_rows', 'processed_rows', 'download_url'])
        ->assertJsonFragment([
            'status'   => 'processing',
            'progress' => 50,
        ]);
});

// ── Download Endpoint ──────────────────────────────────────────────────────────

test('user bisa download file export miliknya yang sudah selesai', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);

    Storage::disk('local')->put('exports/2026/07/civils_test.xlsx', 'fake content');

    $export = CivilExport::factory()->create([
        'created_by'   => $admin->id,
        'status'       => 'completed',
        'progress'     => 100,
        'stored_path'  => 'exports/2026/07/civils_test.xlsx',
        'download_url' => route('civils.export.download', 1),
        'expires_at'   => now()->addHours(24),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('civils.export.download', $export));

    $response->assertOk();
});

test('download gagal jika file sudah kadaluarsa', function () {
    $admin  = User::factory()->create(['role' => 'admin']);
    $export = CivilExport::factory()->create([
        'created_by'   => $admin->id,
        'status'       => 'completed',
        'stored_path'  => 'exports/civils.xlsx',
        'download_url' => '/download/1',
        'expires_at'   => now()->subHours(1), // sudah kadaluarsa
    ]);

    $this->actingAs($admin)
        ->get(route('civils.export.download', $export))
        ->assertForbidden();
});

test('user tidak bisa download file export milik user lain', function () {
    $owner = User::factory()->create(['role' => 'admin']);
    $other = User::factory()->create(['role' => 'user']);

    $export = CivilExport::factory()->create([
        'created_by' => $owner->id,
        'status'     => 'completed',
        'expires_at' => now()->addHours(24),
    ]);

    $this->actingAs($other)
        ->get(route('civils.export.download', $export))
        ->assertForbidden();
});
