<?php

namespace App\Http\Controllers;

use App\DataTables\Definitions\RwDataTable;
use App\Http\Traits\HasDataTable;
use App\Models\Rw;
use App\Services\RwExportService;
use App\Services\RwImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RwController extends Controller
{
    use HasDataTable;

    public function __construct(
        private readonly RwExportService $exportService,
        private readonly RwImportService $importService,
    ) {}

    public function index(Request $request): View
    {
        $config = $this->dataTableConfig(new RwDataTable());

        return view('pages.settings.rws', compact('config'));
    }

    public function data(Request $request): JsonResponse
    {
        return $this->dataTableResponse($request, new RwDataTable());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:rws,code'],
            'name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Rw::create([
            'code' => trim($validated['code']),
            'name' => $validated['name'] ?? ('RW ' . trim($validated['code'])),
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        return redirect()->route('settings.rws')->with('success', 'Master RW berhasil ditambahkan.');
    }

    public function edit(Rw $rw): JsonResponse
    {
        return response()->json($rw);
    }

    public function update(Request $request, Rw $rw): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:rws,code,' . $rw->id],
            'name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $rw->update([
            'code' => trim($validated['code']),
            'name' => $validated['name'] ?? ('RW ' . trim($validated['code'])),
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : $rw->is_active,
        ]);

        return redirect()->route('settings.rws')->with('success', 'Master RW berhasil diperbarui.');
    }

    public function destroy(Rw $rw): JsonResponse
    {
        $rw->delete();

        return response()->json(['message' => 'Master RW berhasil dihapus.']);
    }

    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        Rw::whereIn('id', $request->ids)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data RW terpilih berhasil dihapus.',
        ]);
    }

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

    public function getRts(Rw $rw): JsonResponse
    {
        $rts = $rw->rts()->where('is_active', true)->orderBy('code', 'asc')->get();

        return response()->json($rts);
    }
}
