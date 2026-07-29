<?php

namespace Database\Factories;

use App\Models\CivilExport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CivilExport>
 */
class CivilExportFactory extends Factory
{
    protected $model = CivilExport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filename'       => 'civils_' . now()->format('Ymd_His') . '.xlsx',
            'stored_path'    => null,
            'status'         => 'pending',
            'progress'       => 0,
            'total_rows'     => 0,
            'processed_rows' => 0,
            'download_url'   => null,
            'expires_at'     => null,
            'started_at'     => null,
            'finished_at'    => null,
            'created_by'     => User::factory(),
        ];
    }

    /**
     * State: export sedang diproses.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attrs) => [
            'status'     => 'processing',
            'total_rows' => 5000,
            'started_at' => now(),
        ]);
    }

    /**
     * State: export sudah selesai dan bisa didownload.
     */
    public function completed(): static
    {
        $filename = 'civils_test.xlsx';

        return $this->state(fn (array $attrs) => [
            'status'         => 'completed',
            'progress'       => 100,
            'total_rows'     => 5000,
            'processed_rows' => 5000,
            'stored_path'    => 'exports/2026/07/' . $filename,
            'download_url'   => '/exports/1/download',
            'expires_at'     => now()->addHours(24),
            'started_at'     => now()->subMinutes(3),
            'finished_at'    => now(),
        ]);
    }

    /**
     * State: export gagal.
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
