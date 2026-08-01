<?php

namespace App\Http\Controllers;

use App\DataTables\Definitions\QuickCountDataTable;
use App\Http\Requests\StoreQuickCountRequest;
use App\Http\Requests\UpdateQuickCountRequest;
use App\Http\Traits\HasDataTable;
use App\Models\Candidate;
use App\Models\QuickCount;
use App\Models\QuickCountDetail;
use App\Models\Tps;
use App\Services\QuickCountExportService;
use App\Services\QuickCountImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class QuickCountController extends Controller
{
    use HasDataTable;

    public function __construct(
        private readonly QuickCountExportService $exportService,
        private readonly QuickCountImportService $importService,
    ) {}

    public function index(): View
    {
        $config = $this->dataTableConfig(new QuickCountDataTable());

        $candidates = Candidate::where('is_active', true)->orderBy('number')->get();

        $totalTpsCount = Tps::count();
        $inputtedTpsIds = QuickCount::pluck('tps_id');
        $tpsSudahInput = $inputtedTpsIds->count();
        $tpsBelumInput = max(0, $totalTpsCount - $tpsSudahInput);

        $totalSuaraSah = (int) QuickCountDetail::sum('vote_count');
        $totalSuaraTidakSah = (int) QuickCount::sum('invalid_votes');
        $totalPemilih = (int) QuickCount::sum('total_voters');
        $progressPercentage = $totalTpsCount > 0 ? round(($tpsSudahInput / $totalTpsCount) * 100, 1) : 0;

        // Grouped votes per candidate
        $candidateVotesMap = QuickCountDetail::select('candidate_id', DB::raw('SUM(vote_count) as total_votes'))
            ->groupBy('candidate_id')
            ->pluck('total_votes', 'candidate_id')
            ->toArray();

        $tpsList = Tps::whereNotIn('id', $inputtedTpsIds)->orderBy('name')->get();

        return view('pages.quick-count.index', compact(
            'config',
            'candidates',
            'totalTpsCount',
            'tpsSudahInput',
            'tpsBelumInput',
            'totalSuaraSah',
            'totalSuaraTidakSah',
            'totalPemilih',
            'progressPercentage',
            'candidateVotesMap',
            'tpsList'
        ));
    }

    public function data(Request $request): JsonResponse
    {
        return $this->dataTableResponse($request, new QuickCountDataTable());
    }

    public function store(StoreQuickCountRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();

            $photoPath = null;
            if ($request->hasFile('c1_photo')) {
                $photoPath = $request->file('c1_photo')->store('c1-photos', 'public');
            }

            $quickCount = QuickCount::create([
                'tps_id' => $data['tps_id'],
                'officer_name' => $data['officer_name'],
                'officer_phone' => $data['officer_phone'],
                'input_at' => now(),
                'invalid_votes' => $data['invalid_votes'],
                'total_voters' => $data['total_voters'],
                'c1_photo' => $photoPath,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['votes'] as $candidateId => $voteCount) {
                QuickCountDetail::create([
                    'quick_count_id' => $quickCount->id,
                    'candidate_id' => $candidateId,
                    'vote_count' => (int) $voteCount,
                ]);
            }
        });

        return redirect()->route('quick-counts.index')
            ->with('success', 'Data Quick Count berhasil disimpan.');
    }

    public function edit(QuickCount $quickCount): JsonResponse
    {
        $quickCount->load(['tps', 'details']);
        $votesMap = $quickCount->details->pluck('vote_count', 'candidate_id')->toArray();

        $response = $quickCount->toArray();
        $response['votes'] = $votesMap;

        return response()->json($response);
    }

    public function update(UpdateQuickCountRequest $request, QuickCount $quickCount): RedirectResponse
    {
        DB::transaction(function () use ($request, $quickCount) {
            $data = $request->validated();

            if ($request->hasFile('c1_photo')) {
                if ($quickCount->c1_photo) {
                    Storage::disk('public')->delete($quickCount->c1_photo);
                }
                $data['c1_photo'] = $request->file('c1_photo')->store('c1-photos', 'public');
            }

            $quickCount->update([
                'tps_id' => $data['tps_id'],
                'officer_name' => $data['officer_name'],
                'officer_phone' => $data['officer_phone'],
                'invalid_votes' => $data['invalid_votes'],
                'total_voters' => $data['total_voters'],
                'c1_photo' => $data['c1_photo'] ?? $quickCount->c1_photo,
                'updated_by' => auth()->id(),
            ]);

            foreach ($data['votes'] as $candidateId => $voteCount) {
                QuickCountDetail::updateOrCreate(
                    [
                        'quick_count_id' => $quickCount->id,
                        'candidate_id' => $candidateId,
                    ],
                    [
                        'vote_count' => (int) $voteCount,
                    ]
                );
            }
        });

        return redirect()->route('quick-counts.index')
            ->with('success', 'Data Quick Count berhasil diperbarui.');
    }

    public function destroy(QuickCount $quickCount): RedirectResponse
    {
        if ($quickCount->c1_photo) {
            Storage::disk('public')->delete($quickCount->c1_photo);
        }

        $quickCount->delete();

        return redirect()->route('quick-counts.index')
            ->with('success', 'Data Quick Count berhasil dihapus.');
    }
}
