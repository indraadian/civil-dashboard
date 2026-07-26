<?php

use App\Models\Civil;

it('allows mass assigning kk', function () {
    $civil = new Civil([
        'nik' => '3201010101010101',
        'name' => 'Test User',
        'kk' => '320101010101010101',
        'hamlet' => 'Dusun 1',
        'location_type' => 'village',
        'rt' => '001',
        'rw' => '002',
        'address' => 'Test Address',
        'date_of_birth' => '1990-01-01',
        'gender' => 'L',
        'status' => 'Militan',
    ]);

    expect($civil->kk)->toBe('320101010101010101');
});
