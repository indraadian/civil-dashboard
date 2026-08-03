<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Civil;
use App\Models\QuickCount;
use App\Models\QuickCountDetail;
use App\Models\Tps;
use Illuminate\Http\JsonResponse;
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

        // Quick Count & Candidate Metrics
        $candidates = Candidate::where('is_active', true)->orderBy('number')->get();
        $totalTpsCount = Tps::count();
        $inputtedTpsIds = QuickCount::distinct('tps_id')->pluck('tps_id');
        $tpsSudahInput = $inputtedTpsIds->count();
        $tpsBelumInput = max(0, $totalTpsCount - $tpsSudahInput);

        $totalSuaraSah = (int) QuickCountDetail::whereHas('quickCount')->whereHas('candidate')->sum('vote_count');
        $totalSuaraTidakSah = (int) QuickCount::sum('invalid_votes');
        $totalPemilih = (int) QuickCount::sum('total_voters');
        $progressPercentage = $totalTpsCount > 0 ? round(($tpsSudahInput / $totalTpsCount) * 100, 1) : 0;

        $candidateVotesMap = QuickCountDetail::whereHas('quickCount')
            ->whereHas('candidate')
            ->select('candidate_id', DB::raw('SUM(vote_count) as total_votes'))
            ->groupBy('candidate_id')
            ->pluck('total_votes', 'candidate_id')
            ->toArray();

        $recentQuickCounts = QuickCount::with(['tps', 'details.candidate'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('pages.dashboard.dashboard', [
            'totalWarga' => $totalWarga,
            'totalToday' => $todayCount,
            'Militan' => $statusCounts['Militan'] ?? 0,
            'Ngambang' => $statusCounts['Ngambang'] ?? 0,
            'Lawan' => $statusCounts['Lawan'] ?? 0,

            // Quick Count Data
            'candidates' => $candidates,
            'candidateVotesMap' => $candidateVotesMap,
            'totalTpsCount' => $totalTpsCount,
            'tpsSudahInput' => $tpsSudahInput,
            'tpsBelumInput' => $tpsBelumInput,
            'totalSuaraSah' => $totalSuaraSah,
            'totalSuaraTidakSah' => $totalSuaraTidakSah,
            'totalPemilih' => $totalPemilih,
            'progressPercentage' => $progressPercentage,
            'recentQuickCounts' => $recentQuickCounts,
        ]);
    }

    /**
     * Return JSON statistics for realtime dashboard polling.
     */
    public function stats(): JsonResponse
    {
        $totalWarga = Civil::count();
        $todayCount = Civil::whereDate('created_at', today())->count();

        $statusCounts = Civil::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalTpsCount = Tps::count();
        $inputtedTpsIds = QuickCount::distinct('tps_id')->pluck('tps_id');
        $tpsSudahInput = $inputtedTpsIds->count();
        $tpsBelumInput = max(0, $totalTpsCount - $tpsSudahInput);
        $totalSuaraSah = (int) QuickCountDetail::whereHas('quickCount')->whereHas('candidate')->sum('vote_count');
        $totalSuaraTidakSah = (int) QuickCount::sum('invalid_votes');
        $totalPemilih = (int) QuickCount::sum('total_voters');
        $progressPercentage = $totalTpsCount > 0 ? round(($tpsSudahInput / $totalTpsCount) * 100, 1) : 0;

        $candidateVotesMap = QuickCountDetail::whereHas('quickCount')
            ->whereHas('candidate')
            ->select('candidate_id', DB::raw('SUM(vote_count) as total_votes'))
            ->groupBy('candidate_id')
            ->pluck('total_votes', 'candidate_id')
            ->toArray();

        $candidates = Candidate::where('is_active', true)->orderBy('number')->get();
        $formattedCandidateVotes = [];
        foreach ($candidates as $candidate) {
            $formattedCandidateVotes[$candidate->id] = number_format($candidateVotesMap[$candidate->id] ?? 0, 0, ',', '.');
        }

        $recentQuickCounts = QuickCount::with(['tps', 'details.candidate'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($qc) {
                return [
                    'id' => $qc->id,
                    'tps_name' => $qc->tps->name ?? '-',
                    'officer_name' => $qc->officer_name,
                    'officer_phone' => $qc->officer_phone,
                    'valid_votes' => number_format($qc->details->sum('vote_count'), 0, ',', '.'),
                    'invalid_votes' => number_format($qc->invalid_votes, 0, ',', '.'),
                    'total_voters' => number_format($qc->total_voters, 0, ',', '.'),
                    'updated_at' => $qc->updated_at->format('H:i:s'),
                ];
            });

        return response()->json([
            'totalWarga' => number_format($totalWarga),
            'totalToday' => number_format($todayCount),
            'Militan' => number_format($statusCounts['Militan'] ?? 0),
            'Ngambang' => number_format($statusCounts['Ngambang'] ?? 0),
            'Lawan' => number_format($statusCounts['Lawan'] ?? 0),

            'totalTpsCount' => number_format($totalTpsCount),
            'tpsSudahInput' => number_format($tpsSudahInput),
            'tpsBelumInput' => number_format($tpsBelumInput),
            'totalSuaraSah' => number_format($totalSuaraSah, 0, ',', '.'),
            'totalSuaraTidakSah' => number_format($totalSuaraTidakSah, 0, ',', '.'),
            'totalPemilih' => number_format($totalPemilih, 0, ',', '.'),
            'progressPercentage' => $progressPercentage,
            'candidateVotes' => $formattedCandidateVotes,
            'recentQuickCounts' => $recentQuickCounts,
        ]);
    }
}
