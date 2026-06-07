<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Civil;
use Faker\Factory as Faker;

class UpdateCivilBirthDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Mengambil semua data yang date_of_birth-nya masih kosong/null
        $civils = Civil::whereNull('date_of_birth')->get();

        foreach ($civils as $civil) {
            $civil->update([
                'date_of_birth' => $faker->date('Y-m-d', '2005-01-01') // Tanggal lahir random
            ]);
        }
    }
}
