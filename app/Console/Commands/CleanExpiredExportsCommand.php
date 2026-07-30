<?php

namespace App\Console\Commands;

use App\Models\CivilExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanExpiredExportsCommand extends Command
{
    /**
     * Nama dan tanda tangan perintah konsol.
     *
     * @var string
     */
    protected $signature = 'exports:clean';

    /**
     * Deskripsi perintah konsol.
     *
     * @var string
     */
    protected $description = 'Hapus file fisik dan data record export yang telah kadaluarsa (>24 jam)';

    /**
     * Jalankan perintah konsol.
     */
    public function handle(): int
    {
        $expiredExports = CivilExport::where('created_at', '<=', now()->subHours(24))
            ->orWhere(function ($q) {
                $q->whereNotNull('expires_at')->where('expires_at', '<=', now());
            })
            ->get();

        $count = 0;
        foreach ($expiredExports as $export) {
            if ($export->stored_path && Storage::disk('local')->exists($export->stored_path)) {
                Storage::disk('local')->delete($export->stored_path);
            }
            $export->delete();
            $count++;
        }

        $this->info("Berhasil menghapus {$count} file export kadaluarsa (>24 jam).");
        Log::info("CleanExpiredExports: Berhasil menghapus {$count} file export kadaluarsa.");

        return Command::SUCCESS;
    }
}
