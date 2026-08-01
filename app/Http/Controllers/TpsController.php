<?php

namespace App\Http\Controllers;

use App\DataTables\Definitions\TpsDataTable;
use App\Http\Requests\StoreTpsRequest;
use App\Http\Requests\UpdateTpsRequest;
use App\Http\Traits\HasDataTable;
use App\Models\Tps;
use App\Services\TpsExportService;
use App\Services\TpsImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TpsController extends Controller
{
    use HasDataTable;

    public function __construct(
        private readonly TpsExportService $exportService,
        private readonly TpsImportService $importService,
    ) {}

    /**
     * Display TPS management page.
     */
    public function index(): View
    {
        $config = $this->dataTableConfig(new TpsDataTable());

        return view('pages.settings.tps', compact('config'));
    }

    /**
     * Return JSON dataset for TPS DataTable.
     */
    public function data(Request $request): JsonResponse
    {
        return $this->dataTableResponse($request, new TpsDataTable());
    }

    /**
     * Store a new TPS record.
     */
    public function store(StoreTpsRequest $request): RedirectResponse
    {
        Tps::create($request->validated());

        return redirect()->route('settings.tps')
            ->with('success', 'Data TPS berhasil ditambahkan!');
    }

    /**
     * Return TPS data for edit modal (JSON).
     */
    public function edit(Tps $tp): JsonResponse
    {
        $this->authorize('view', $tp);

        return response()->json($tp);
    }

    /**
     * Update an existing TPS record.
     */
    public function update(UpdateTpsRequest $request, Tps $tp): RedirectResponse
    {
        $this->authorize('update', $tp);

        $tp->update($request->validated());

        return redirect()->route('settings.tps')
            ->with('success', 'Data TPS berhasil diperbarui!');
    }

    /**
     * Delete a single TPS record.
     */
    public function destroy(Tps $tp): JsonResponse
    {
        $this->authorize('delete', $tp);

        $tp->delete();

        return response()->json(['message' => 'Data TPS berhasil dihapus']);
    }

    /**
     * Delete multiple TPS records in bulk.
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        Tps::whereIn('id', $request->ids)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data TPS terpilih berhasil dihapus',
        ]);
    }

    /**
     * Export Master TPS data.
     */
    public function export(Request $request): RedirectResponse
    {
        $this->authorize('create', Tps::class);

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
     * Import Master TPS data from CSV/Excel file.
     */
    public function import(Request $request): RedirectResponse
    {
        $this->authorize('create', Tps::class);

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
