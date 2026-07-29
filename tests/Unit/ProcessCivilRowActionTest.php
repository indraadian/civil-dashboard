<?php

use App\Actions\Import\ProcessCivilRowAction;
use App\Models\Civil;

// ── executeBatch ───────────────────────────────────────────────────────────────

test('executeBatch memproses baris valid dan melakukan upsert', function () {
    $action = new ProcessCivilRowAction();

    $rows = [
        [
            'nik'           => '3201012345678901',
            'kk'            => '3201012345678900',
            'name'          => 'Budi Santoso',
            'tanggal_lahir' => '15-06-1990',
            'jenis_kelamin' => 'Laki-laki',
            'rt'            => '001',
            'rw'            => '002',
            'dusun'         => 'Kampung Baru',
            'alamat'        => 'Jl. Merdeka No. 1',
            'tipe_lokasi'   => 'kampung',
            'status'        => 'tetap',
        ],
    ];

    $count = $action->executeBatch($rows);

    expect($count)->toBe(1);
    $this->assertDatabaseHas('civils', ['nik' => '3201012345678901', 'name' => 'Budi Santoso']);
});

test('executeBatch melewati baris dengan NIK kosong', function () {
    $action = new ProcessCivilRowAction();

    $rows = [
        ['nik' => '', 'name' => 'Kosong'],
        ['nik' => null, 'name' => 'Null'],
    ];

    $count = $action->executeBatch($rows);

    expect($count)->toBe(0);
    $this->assertDatabaseCount('civils', 0);
});

test('execute membersihkan karakter non-angka dari NIK', function () {
    $action = new ProcessCivilRowAction();

    $row = [
        'nik'           => '3201 0123 4567 8901', // spasi
        'name'          => 'Test',
        'tanggal_lahir' => null,
        'jenis_kelamin' => null,
        'rt'            => '001',
        'rw'            => '002',
        'dusun'         => null,
        'alamat'        => 'Test Address',
        'tipe_lokasi'   => null,
        'status'        => null,
    ];

    $result = $action->execute($row);

    expect($result)->toBeTrue();
    $this->assertDatabaseHas('civils', ['nik' => '3201012345678901']);
});

test('execute tidak melempar exception jika tanggal lahir tidak valid', function () {
    $action = new ProcessCivilRowAction();

    $row = [
        'nik'           => '3201012345678902',
        'name'          => 'Test',
        'tanggal_lahir' => 'bukan-tanggal',
        'jenis_kelamin' => null,
        'rt'            => '001',
        'rw'            => '002',
        'dusun'         => null,
        'alamat'        => 'Test',
        'tipe_lokasi'   => null,
        'status'        => null,
    ];

    expect(fn () => $action->execute($row))->not->toThrow(\Exception::class);

    $this->assertDatabaseHas('civils', ['nik' => '3201012345678902', 'date_of_birth' => null]);
});

test('execute memetakan tipe_lokasi kampung ke village', function () {
    $action = new ProcessCivilRowAction();

    $row = [
        'nik'           => '3201012345678903',
        'name'          => 'Test',
        'tanggal_lahir' => null,
        'jenis_kelamin' => null,
        'rt'            => '001',
        'rw'            => '002',
        'dusun'         => null,
        'alamat'        => 'Test',
        'tipe_lokasi'   => 'kampung',
        'status'        => null,
    ];

    $action->execute($row);

    $this->assertDatabaseHas('civils', ['nik' => '3201012345678903', 'location_type' => 'village']);
});

test('execute memetakan tipe_lokasi selain kampung ke housing', function () {
    $action = new ProcessCivilRowAction();

    $row = [
        'nik'           => '3201012345678904',
        'name'          => 'Test',
        'tanggal_lahir' => null,
        'jenis_kelamin' => null,
        'rt'            => '001',
        'rw'            => '002',
        'dusun'         => null,
        'alamat'        => 'Test',
        'tipe_lokasi'   => 'perumahan',
        'status'        => null,
    ];

    $action->execute($row);

    $this->assertDatabaseHas('civils', ['nik' => '3201012345678904', 'location_type' => 'housing']);
});

test('executeBatch melakukan upsert jika NIK sudah ada', function () {
    $action = new ProcessCivilRowAction();

    Civil::factory()->create(['nik' => '3201012345678905', 'name' => 'Nama Lama']);

    $rows = [[
        'nik'           => '3201012345678905',
        'name'          => 'Nama Baru',
        'tanggal_lahir' => null,
        'jenis_kelamin' => null,
        'rt'            => '001',
        'rw'            => '002',
        'dusun'         => null,
        'alamat'        => 'Test',
        'tipe_lokasi'   => 'kampung',
        'status'        => null,
    ]];

    $action->executeBatch($rows);

    $this->assertDatabaseHas('civils', ['nik' => '3201012345678905', 'name' => 'Nama Baru']);
    $this->assertDatabaseCount('civils', 1); // tidak duplikat
});
