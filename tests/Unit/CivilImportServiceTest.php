<?php

use App\Http\Requests\ImportCivilRequest;
use App\Jobs\ProcessCivilImportJob;
use App\Models\CivilImport;
use App\Models\User;
use App\Services\CivilImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();
});

test('CivilImportService::initiate menyimpan file ke storage', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $file  = UploadedFile::fake()->create('civils.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $request = new ImportCivilRequest();
    $request->setUserResolver(fn () => $admin);
    $request->files->set('file', $file);

    $service = new CivilImportService();
    $import  = $service->initiate($request);

    Storage::disk('local')->assertExists($import->stored_path);
});

test('CivilImportService::initiate membuat record import dengan status pending', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $file  = UploadedFile::fake()->create('civils.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $request = new ImportCivilRequest();
    $request->setUserResolver(fn () => $admin);
    $request->files->set('file', $file);

    $service = new CivilImportService();
    $import  = $service->initiate($request);

    expect($import)->toBeInstanceOf(CivilImport::class);
    expect($import->status)->toBe('pending');
    expect($import->created_by)->toBe($admin->id);
});

test('CivilImportService::initiate mendispatch ProcessCivilImportJob', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $file  = UploadedFile::fake()->create('civils.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $request = new ImportCivilRequest();
    $request->setUserResolver(fn () => $admin);
    $request->files->set('file', $file);

    $service = new CivilImportService();
    $service->initiate($request);

    Queue::assertPushed(ProcessCivilImportJob::class);
});

test('CivilImportService::cancel mengubah status menjadi cancelled', function () {
    $admin  = User::factory()->create(['role' => 'admin']);
    $import = CivilImport::factory()->create([
        'created_by' => $admin->id,
        'status'     => 'pending',
    ]);

    $service = new CivilImportService();
    $service->cancel($import);

    expect($import->fresh()->status)->toBe('cancelled');
});

test('CivilImportService::cancel tidak mengubah status jika sudah processing', function () {
    $admin  = User::factory()->create(['role' => 'admin']);
    $import = CivilImport::factory()->create([
        'created_by' => $admin->id,
        'status'     => 'processing',
    ]);

    $service = new CivilImportService();
    $service->cancel($import);

    expect($import->fresh()->status)->toBe('processing'); // tidak berubah
});
