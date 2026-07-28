<?php

namespace App\Http\Controllers;

use App\Models\Civil;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard dengan statistik warga.
     */
    public function index(): View
    {
        $totalWarga = Civil::count();
        $todayCount = Civil::whereDate('created_at', today())->count();

        $statusCounts = Civil::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('pages.dashboard.dashboard', [
            'totalWarga' => $totalWarga,
            'totalToday' => $todayCount,
            'Militan' => $statusCounts['Militan'] ?? 0,
            'Ngambang' => $statusCounts['Ngambang'] ?? 0,
            'Lawan' => $statusCounts['Lawan'] ?? 0,
        ]);
    }
}
