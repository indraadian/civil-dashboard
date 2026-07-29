<?php

namespace Database\Factories;

use App\Models\CivilImport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CivilImport>
 */
class CivilImportFactory extends Factory
{
    protected $model = CivilImport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filename'       => 'civils_' . fake()->numerify('######') . '.xlsx',
            'stored_path'    => 'imports/2026/07/test_' . fake()->numerify('######') . '.xlsx',
            'status'         => 'pending',
            'progress'       => 0,
            'total_rows'     => 0,
            'processed_rows' => 0,
            'failed_rows'    => 0,
            'error_message'  => null,
            'started_at'     => null,
            'finished_at'    => null,
            'created_by'     => User::factory(),
        ];
    }

    /**
     * State: import sedang diproses.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attrs) => [
            'status'     => 'processing',
            'total_rows' => 1000,
            'started_at' => now(),
        ]);
    }

    /**
     * State: import sudah selesai.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attrs) => [
            'status'         => 'completed',
            'progress'       => 100,
            'total_rows'     => 1000,
            'processed_rows' => 1000,
            'started_at'     => now()->subMinutes(5),
            'finished_at'    => now(),
        ]);
    }

    /**
     * State: import gagal.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attrs) => [
            'status'        => 'failed',
            'error_message' => fake()->sentence(),
            'finished_at'   => now(),
        ]);
    }
}
