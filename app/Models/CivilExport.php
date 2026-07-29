<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CivilExport extends Model
{
    use HasFactory;

    protected $table = 'exports';

    protected $fillable = [
        'filename',
        'stored_path',
        'status',
        'progress',
        'total_rows',
        'processed_rows',
        'download_url',
        'expires_at',
        'started_at',
        'finished_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'progress'       => 'integer',
            'total_rows'     => 'integer',
            'processed_rows' => 'integer',
            'expires_at'     => 'datetime',
            'started_at'     => 'datetime',
            'finished_at'    => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isDownloadable(): bool
    {
        return $this->isCompleted() && ! $this->isExpired() && $this->download_url !== null;
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Update persentase progress saat export berlangsung.
     */
    public function updateProgress(int $processedRows): void
    {
        $progress = $this->total_rows > 0
            ? (int) round(($processedRows / $this->total_rows) * 100)
            : 0;

        $this->update([
            'processed_rows' => $processedRows,
            'progress'       => $progress,
        ]);
    }

    /**
     * Tandai export sebagai sedang diproses.
     */
    public function markAsProcessing(int $totalRows): void
    {
        $this->update([
            'status'     => 'processing',
            'total_rows' => $totalRows,
            'started_at' => now(),
        ]);
    }

    /**
     * Tandai export sebagai selesai dengan URL download.
     */
    public function markAsCompleted(string $storedPath, string $downloadUrl): void
    {
        $this->update([
            'status'       => 'completed',
            'progress'     => 100,
            'stored_path'  => $storedPath,
            'download_url' => $downloadUrl,
            'expires_at'   => now()->addHours(24),
            'finished_at'  => now(),
        ]);
    }

    /**
     * Tandai export sebagai gagal.
     */
    public function markAsFailed(string $message): void
    {
        $this->update([
            'status'        => 'failed',
            'error_message' => $message,
            'finished_at'   => now(),
        ]);
    }
}
