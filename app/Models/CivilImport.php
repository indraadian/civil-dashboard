<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CivilImport extends Model
{
    use HasFactory;

    protected $table = 'imports';

    protected $fillable = [
        'filename',
        'stored_path',
        'status',
        'progress',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'error_message',
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
            'failed_rows'    => 'integer',
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

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Hitung dan update persentase progress.
     */
    public function updateProgress(int $processedRows, int $failedRows = 0): void
    {
        $progress = $this->total_rows > 0
            ? (int) round(($processedRows / $this->total_rows) * 100)
            : 0;

        $this->update([
            'processed_rows' => $processedRows,
            'failed_rows'    => $failedRows,
            'progress'       => $progress,
        ]);
    }

    /**
     * Tandai import sebagai sedang diproses.
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status'     => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Tandai import sebagai selesai.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status'      => 'completed',
            'progress'    => 100,
            'finished_at' => now(),
        ]);
    }

    /**
     * Tandai import sebagai gagal.
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
