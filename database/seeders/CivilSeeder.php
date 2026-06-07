<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CivilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 100; $i++) {
            \App\Models\Civil::create([
                'nik' => '3201' . str_pad($i, 12, '0', STR_PAD_LEFT),
                'name' => 'Warga ' . $i,
                'hamlet' => 'Dusun ' . ['Wargakoo', 'Sukamaju', 'Makmur'][rand(0, 2)],
                'location_type' => ['village', 'housing'][rand(0, 1)],
                'rt' => str_pad(rand(1, 20), 3, '0', STR_PAD_LEFT),
                'rw' => str_pad(rand(1, 10), 3, '0', STR_PAD_LEFT),
                'address' => 'Jl. Contoh No. ' . $i,
            ]);
        }
    }
}
