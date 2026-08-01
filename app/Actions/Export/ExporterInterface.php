<?php

namespace App\Actions\Export;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface ExporterInterface
{
    /**
     * @return array<int, string>
     */
    public function getHeadings(): array;

    public function buildQuery(array $filters = []): Builder;

    /**
     * @return array<int, mixed>
     */
    public function mapRow(Model $model): array;
}
