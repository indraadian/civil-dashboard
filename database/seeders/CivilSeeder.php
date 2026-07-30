<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Civil;
use Carbon\Carbon;

class CivilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = ['Jakarta', 'Bandung', 'Bogor', 'Surabaya', 'Semarang', 'Yogyakarta', 'Depok', 'Bekasi'];
        $statuses = ['Militan', 'Ngambang', 'Lawan'];
        $genders = ['L', 'P'];
        $locationTypes = ['village', 'housing'];
        $hamlets = ['Wargakoo', 'Sukamaju', 'Makmur'];

        for ($i = 1; $i <= 100; $i++) {
            $gender = $genders[rand(0, 1)];
            $dob = Carbon::now()->subYears(rand(18, 65))->subDays(rand(1, 365))->format('Y-m-d');

            Civil::create([
                'kk' => '3201' . str_pad((int) ceil($i / 3), 12, '0', STR_PAD_LEFT),
                'nik' => '3201' . str_pad($i, 12, '0', STR_PAD_LEFT),
                'name' => 'Warga ' . $i,
                'place_of_birth' => $cities[rand(0, count($cities) - 1)],
                'date_of_birth' => $dob,
                'gender' => $gender,
                'hamlet' => 'Dusun ' . $hamlets[rand(0, count($hamlets) - 1)],
                'location_type' => $locationTypes[rand(0, 1)],
                'rt' => (string) rand(1, 20),
                'rw' => (string) rand(1, 10),
                'address' => 'Jl. Contoh No. ' . $i,
                'status' => $statuses[rand(0, 2)],
            ]);
        }
    }
}
