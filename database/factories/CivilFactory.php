<?php

namespace Database\Factories;

use App\Models\Civil;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Civil>
 */
class CivilFactory extends Factory
{
    protected $model = Civil::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender        = fake()->randomElement(['Laki-laki', 'Perempuan']);
        $locationType  = fake()->randomElement(['village', 'housing']);

        return [
            'nik'           => fake()->unique()->numerify('################'), // 16 digit
            'kk'            => fake()->numerify('################'),
            'name'          => fake()->name(),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-17 years')->format('Y-m-d'),
            'gender'        => $gender,
            'rt'            => fake()->numerify('0##'),
            'rw'            => fake()->numerify('0##'),
            'hamlet'        => fake()->randomElement(['Kampung Baru', 'Kampung Lama', null]),
            'address'       => fake()->address(),
            'location_type' => $locationType,
            'status'        => fake()->randomElement(['tetap', 'pendatang']),
        ];
    }
}
