<?php

namespace App\Http\Controllers;

use App\DataTables\Definitions\QuickCountDataTable;
use App\Http\Requests\StoreQuickCountRequest;
use App\Http\Requests\UpdateQuickCountRequest;
use App\Http\Traits\HasDataTable;
use App\Models\QuickCount;
use App\Models\Tps;
use App\Services\QuickCountExportService;
use App\Services\QuickCountImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class QuickCountController extends Controller
{
    use HasDataTable;

    public function __construct(
        private readonly QuickCountExportService $exportService,
        private readonly QuickCountImportService $importService,
    ) {}

    /**
     * Display the Quick Count monitoring & data grid page.
     */
    public function index(): View
    {
        $config = $this->dataTableConfig(new QuickCountDataTable());

        $totalTpsCount = Tps::count();
        $inputtedTpsIds = QuickCount::pluck('tps_id');
        $tpsSudahInput = $inputtedTpsIds->count();
        $tpsBelumInput = max(0, $totalTpsCount - $tpsSudahInput);

        $totalSuara = (int) QuickCount::sum('vote_count');
        $totalPemilih = (int) QuickCount::sum('total_voters');
        $progressPercentage = $totalTpsCount > 0 ? round(($tpsSudahInput / $totalTpsCount) * 100, 1) : 0;

        $tpsList = Tps::whereNotIn('id', $inputtedTpsIds)->orderBy('name')->get();

        return view('pages.quick-count.index', compact(
            'config',
            'totalTpsCount',
            'tpsSudahInput',
            'tpsBelumInput',
            'totalSuara',
            'totalPemilih',
            'progressPercentage',
            'tpsList'
        ));
    }

    /**
     * Return JSON dataset for Quick Count DataTable.
     */
    public function data(Request $request): JsonResponse
    {
        return $this->dataTableResponse($request, new QuickCountDataTable());
    }

    /**
     * Store a new Quick Count result record.
     */
    public function store(StoreQuickCountRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('c1_photo')) {
            $validated['c1_photo'] = $request->file('c1_photo')->store('quick_counts', 'public');
        }

        $validated['created_by'] = auth()->id();

        QuickCount::create($validated);

        return redirect()->route('quick-counts.index')
            ->with('success', 'Data Quick Count TPS berhasil disimpan!');
    }

    /**
     * Return Quick Count data for edit modal (JSON).
     */
    public function edit(QuickCount $quickCount): JsonResponse
    {
        return response()->json($quickCount->load(['tps', 'creator']));
    }

    /**
     * Update an existing Quick Count result record.
     */
    public function update(UpdateQuickCountRequest $request, QuickCount $quickCount): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('c1_photo')) {
            if ($quickCount->c1_photo) {
                Storage::disk('public')->delete($quickCount->c1_photo);
            }
            $validated['c1_photo'] = $request->file('c1_photo')->store('quick_counts', 'public');
        }

        $quickCount->update($validated);

        return redirect()->route('quick-counts.index')
            ->with('success', 'Data Quick Count TPS berhasil diperbarui!');
    }

    /**
     * Delete a single Quick Count record.
     */
    public function destroy(QuickCount $quickCount): JsonResponse
    {
        if ($quickCount->c1_photo) {
            Storage::disk('public')->delete($quickCount->c1_photo);
        }

        $quickCount->delete();

        return response()->json(['message' => 'Data Quick Count TPS berhasil dihapus']);
    }

    /**
     * Delete multiple Quick Count records in bulk.
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        QuickCount::whereIn('id', $request->ids)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data terpilih berhasil dihapus',
        ]);
    }

    /**
     * Export Quick Count results.
     */
    public function export(Request $request): RedirectResponse
    {
        $export = $this->exportService->initiate(
            userId: $request->user()->id,
            filters: $request->all(),
            format: $request->input('format', 'xlsx')
        );

        return back()->with(
            'info',
            "File sedang dibuat di background. ID Export: #{$export->id}. Anda akan diberitahu ketika file siap diunduh."
        );
    }

    /**
     * Import Quick Count results from CSV/Excel file.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ]);

        $import = $this->importService->initiate($request);

        return back()->with(
            'info',
            "File sedang diproses di background. ID Import: #{$import->id}. Anda akan diberitahu ketika selesai."
        );
    }
}
