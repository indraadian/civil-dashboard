<?php

namespace App\Http\Controllers;

use App\Models\Civil;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Semua Warga
        $totalWarga = Civil::count();

        // 2. Total Ditambahkan Hari Ini
        $todayCount = Civil::whereDate('created_at', today())->count();

        // 3. Total per Status (Militan, Ngambang, Lawan)
        // Hasilnya: array ['Militan' => 10, 'Ngambang' => 5, 'Lawan' => 2]
        $statusCounts = Civil::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $data = [
            'totalWarga' => $totalWarga,
            'totalToday' => $todayCount,
            'Militan' => $statusCounts['Militan'] ?? 0,
            'Ngambang' => $statusCounts['Ngambang'] ?? 0,
            'Lawan' => $statusCounts['Lawan'] ?? 0,
        ];

        return view('pages.dashboard.dashboard', $data);
    }
}
