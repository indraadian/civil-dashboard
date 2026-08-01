<?php

namespace App\Http\Controllers;

use App\Models\Civil;
use App\Models\QuickCount;
use App\Models\Tps;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard dengan statistik warga dan Quick Count.
     */
    public function index(): View
    {
        $totalWarga = Civil::count();
        $todayCount = Civil::whereDate('created_at', today())->count();

        $statusCounts = Civil::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Quick Count Metrics
        $totalTpsCount = Tps::count();
        $inputtedTpsIds = QuickCount::distinct('tps_id')->pluck('tps_id');
        $tpsSudahInput = $inputtedTpsIds->count();
        $tpsBelumInput = max(0, $totalTpsCount - $tpsSudahInput);
        $totalSuara = (int) QuickCount::sum('vote_count');
        $totalPemilih = (int) QuickCount::sum('total_voters');
        $progressPercentage = $totalTpsCount > 0 ? round(($tpsSudahInput / $totalTpsCount) * 100, 1) : 0;

        return view('pages.dashboard.dashboard', [
            'totalWarga' => $totalWarga,
            'totalToday' => $todayCount,
            'Militan' => $statusCounts['Militan'] ?? 0,
            'Ngambang' => $statusCounts['Ngambang'] ?? 0,
            'Lawan' => $statusCounts['Lawan'] ?? 0,

            // Quick Count Data
            'totalTpsCount' => $totalTpsCount,
            'tpsSudahInput' => $tpsSudahInput,
            'tpsBelumInput' => $tpsBelumInput,
            'totalSuara' => $totalSuara,
            'totalPemilih' => $totalPemilih,
            'progressPercentage' => $progressPercentage,
        ]);
    }

    /**
     * Return JSON statistics for realtime dashboard polling.
     */
    public function stats(): \Illuminate\Http\JsonResponse
    {
        $totalWarga = Civil::count();
        $todayCount = Civil::whereDate('created_at', today())->count();

        $statusCounts = Civil::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Quick Count Metrics
        $totalTpsCount = Tps::count();
        $inputtedTpsIds = QuickCount::distinct('tps_id')->pluck('tps_id');
        $tpsSudahInput = $inputtedTpsIds->count();
        $tpsBelumInput = max(0, $totalTpsCount - $tpsSudahInput);
        $totalSuara = (int) QuickCount::sum('vote_count');
        $totalPemilih = (int) QuickCount::sum('total_voters');
        $progressPercentage = $totalTpsCount > 0 ? round(($tpsSudahInput / $totalTpsCount) * 100, 1) : 0;

        return response()->json([
            'totalWarga' => number_format($totalWarga),
            'totalToday' => number_format($todayCount),
            'Militan' => number_format($statusCounts['Militan'] ?? 0),
            'Ngambang' => number_format($statusCounts['Ngambang'] ?? 0),
            'Lawan' => number_format($statusCounts['Lawan'] ?? 0),

            // Quick Count Data
            'totalTpsCount' => number_format($totalTpsCount),
            'tpsSudahInput' => number_format($tpsSudahInput),
            'tpsBelumInput' => number_format($tpsBelumInput),
            'totalSuara' => number_format($totalSuara),
            'totalPemilih' => number_format($totalPemilih),
            'progressPercentage' => $progressPercentage,
        ]);
    }
}
