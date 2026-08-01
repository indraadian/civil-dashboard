<?php

namespace App\Http\Controllers;

use App\DataTables\Definitions\RtDataTable;
use App\Http\Traits\HasDataTable;
use App\Models\Rt;
use App\Models\Rw;
use App\Services\RtExportService;
use App\Services\RtImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RtController extends Controller
{
    use HasDataTable;

    public function __construct(
        private readonly RtExportService $exportService,
        private readonly RtImportService $importService,
    ) {}

    public function index(Request $request): View
    {
        $config = $this->dataTableConfig(new RtDataTable());
        $rws = Rw::orderBy('code', 'asc')->get();

        return view('pages.settings.rts', compact('rws', 'config'));
    }

    public function data(Request $request): JsonResponse
    {
        return $this->dataTableResponse($request, new RtDataTable());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rw_id' => ['required', 'exists:rws,id'],
            'code' => ['required', 'string', 'max:10'],
            'name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $exists = Rt::where('rw_id', $validated['rw_id'])->where('code', trim($validated['code']))->exists();
        if ($exists) {
            return back()->withErrors(['code' => 'Kode RT tersebut sudah ada pada RW ini.'])->withInput();
        }

        Rt::create([
            'rw_id' => $validated['rw_id'],
            'code' => trim($validated['code']),
            'name' => $validated['name'] ?? ('RT ' . trim($validated['code'])),
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        return redirect()->route('settings.rts')->with('success', 'Master RT berhasil ditambahkan.');
    }

    public function edit(Rt $rt): JsonResponse
    {
        return response()->json($rt->load('rw'));
    }

    public function update(Request $request, Rt $rt): RedirectResponse
    {
        $validated = $request->validate([
            'rw_id' => ['required', 'exists:rws,id'],
            'code' => ['required', 'string', 'max:10'],
            'name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $exists = Rt::where('rw_id', $validated['rw_id'])
            ->where('code', trim($validated['code']))
            ->where('id', '!=', $rt->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'Kode RT tersebut sudah ada pada RW ini.'])->withInput();
        }

        $rt->update([
            'rw_id' => $validated['rw_id'],
            'code' => trim($validated['code']),
            'name' => $validated['name'] ?? ('RT ' . trim($validated['code'])),
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : $rt->is_active,
        ]);

        return redirect()->route('settings.rts')->with('success', 'Master RT berhasil diperbarui.');
    }

    public function destroy(Rt $rt): JsonResponse
    {
        $rt->delete();

        return response()->json(['message' => 'Master RT berhasil dihapus.']);
    }

    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        Rt::whereIn('id', $request->ids)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data RT terpilih berhasil dihapus.',
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
}
