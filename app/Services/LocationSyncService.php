<?php

namespace App\Services;

use App\Models\Civil;
use App\Models\Rt;
use App\Models\Rw;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LocationSyncService
{
    /**
     * Jalankan proses patch master RW & RT dari data Civil yang ada.
     *
     * @return array{new_rws: int, new_rts: int, skipped: int}
     */
    public function syncFromCivils(): array
    {
        $newRws = 0;
        $newRts = 0;
        $skipped = 0;

        DB::transaction(function () use (&$newRws, &$newRts, &$skipped) {
            // 1. Ekstraksi RW unik dari tabel civils
            $uniqueRws = Civil::query()
                ->whereNotNull('rw')
                ->where('rw', '!=', '')
                ->distinct()
                ->pluck('rw');

            foreach ($uniqueRws as $rawRw) {
                $rwCode = trim((string) $rawRw);
                if ($rwCode === '') {
                    continue;
                }

                $rw = Rw::where('code', $rwCode)->first();
                if (!$rw) {
                    $rw = Rw::create([
                        'code' => $rwCode,
                        'name' => 'RW ' . $rwCode,
                        'is_active' => true,
                    ]);
                    $newRws++;
                } else {
                    $skipped++;
                }
            }

            // 2. Ekstraksi pasangan (RW, RT) unik dari tabel civils
            $uniquePairs = Civil::query()
                ->whereNotNull('rw')
                ->where('rw', '!=', '')
                ->whereNotNull('rt')
                ->where('rt', '!=', '')
                ->select('rw', 'rt')
                ->distinct()
                ->get();

            foreach ($uniquePairs as $pair) {
                $rwCode = trim((string) $pair->rw);
                $rtCode = trim((string) $pair->rt);

                if ($rwCode === '' || $rtCode === '') {
                    continue;
                }

                $rw = Rw::where('code', $rwCode)->first();
                if (!$rw) {
                    $rw = Rw::create([
                        'code' => $rwCode,
                        'name' => 'RW ' . $rwCode,
                        'is_active' => true,
                    ]);
                    $newRws++;
                }

                $rt = Rt::where('rw_id', $rw->id)->where('code', $rtCode)->first();
                if (!$rt) {
                    Rt::create([
                        'rw_id' => $rw->id,
                        'code' => $rtCode,
                        'name' => 'RT ' . $rtCode,
                        'is_active' => true,
                    ]);
                    $newRts++;
                } else {
                    $skipped++;
                }
            }
        });

        Log::info('LocationSyncService completed.', [
            'new_rws' => $newRws,
            'new_rts' => $newRts,
            'skipped' => $skipped,
        ]);

        return [
            'new_rws' => $newRws,
            'new_rts' => $newRts,
            'skipped' => $skipped,
        ];
    }
}
