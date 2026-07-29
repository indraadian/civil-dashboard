<?php

namespace App\Events;

use App\Models\CivilExport;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExportFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly CivilExport $export,
        public readonly string $reason,
    ) {}
}
