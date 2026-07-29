<?php

use App\Jobs\ProcessCivilImportJob;
use App\Models\CivilImport;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();
});

// ── Import Flow ────────────────────────────────────────────────────────────────

test('admin dapat mengupload file import', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $file  = UploadedFile::fake()->create('civils.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $response = $this->actingAs($admin)
        ->post(route('civils.import'), ['file' => $file]);

    $response->assertRedirect();
    $response->assertSessionHas('info');
});

test('import memastikan job di-dispatch ke queue', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $file  = UploadedFile::fake()->create('civils.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $this->actingAs($admin)
        ->post(route('civils.import'), ['file' => $file]);

    Queue::assertPushed(ProcessCivilImportJob::class);
});

test('import membuat record di database dengan status pending', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $file  = UploadedFile::fake()->create('civils.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $this->actingAs($admin)
        ->post(route('civils.import'), ['file' => $file]);

    $this->assertDatabaseHas('imports', [
        'created_by' => $admin->id,
        'status'     => 'pending',
    ]);
});

test('import menolak file dengan format tidak valid', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $file  = UploadedFile::fake()->create('civils.pdf', 100, 'application/pdf');

    $response = $this->actingAs($admin)
        ->post(route('civils.import'), ['file' => $file]);

    $response->assertSessionHasErrors('file');
    Queue::assertNothingPushed();
});

test('import menolak file melebihi ukuran 10MB', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $file  = UploadedFile::fake()->create('civils.xlsx', 11000); // 11 MB

    $response = $this->actingAs($admin)
        ->post(route('civils.import'), ['file' => $file]);

    $response->assertSessionHasErrors('file');
    Queue::assertNothingPushed();
});

test('non-admin tidak bisa mengakses import', function () {
    $user = User::factory()->create(['role' => 'user']);
    $file = UploadedFile::fake()->create('civils.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $response = $this->actingAs($user)
        ->post(route('civils.import'), ['file' => $file]);

    $response->assertForbidden();
    Queue::assertNothingPushed();
});

// ── Progress Endpoint ──────────────────────────────────────────────────────────

test('endpoint progress import mengembalikan JSON yang benar', function () {
    $admin  = User::factory()->create(['role' => 'admin']);
    $import = CivilImport::factory()->create([
        'created_by'     => $admin->id,
        'status'         => 'processing',
        'progress'       => 65,
        'total_rows'     => 10000,
        'processed_rows' => 6500,
    ]);

    $response = $this->actingAs($admin)
        ->getJson(route('civils.import.progress', $import));

    $response->assertOk()
        ->assertJsonStructure(['id', 'status', 'progress', 'total_rows', 'processed_rows', 'failed_rows'])
        ->assertJsonFragment([
            'status'   => 'processing',
            'progress' => 65,
        ]);
});

test('user hanya bisa melihat progress import miliknya sendiri', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $other = User::factory()->create(['role' => 'user']);

    $import = CivilImport::factory()->create(['created_by' => $owner->id]);

    $this->actingAs($other)
        ->getJson(route('civils.import.progress', $import))
        ->assertForbidden();
});
