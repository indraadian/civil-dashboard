<?php

namespace App\Events;

use App\Models\CivilImport;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly CivilImport $import,
        public readonly string $reason,
    ) {}
}
