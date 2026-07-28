<?php

namespace App\Http\Controllers;

use App\DataTables\Definitions\CivilDataTable;
use App\Exports\CivilsExport;
use App\Http\Requests\StoreCivilRequest;
use App\Http\Requests\UpdateCivilRequest;
use App\Http\Traits\HasDataTable;
use App\Imports\CivilsImport;
use App\Models\Civil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CivilController extends Controller
{
    use HasDataTable;

    /**
     * Tampilkan halaman daftar warga.
     */
    public function index(): View
    {
        $config = $this->dataTableConfig(new CivilDataTable());

        return view('pages.civil.list', compact('config'));
    }

    /**
     * Ambil data warga (JSON) untuk DataTable.
     */
    public function data(Request $request): JsonResponse
    {
        return $this->dataTableResponse($request, new CivilDataTable());
    }

    /**
     * Simpan data warga baru.
     */
    public function store(StoreCivilRequest $request): RedirectResponse
    {
        Civil::create($request->validated());

        return redirect()->route('civils')
            ->with('success', 'Data warga baru berhasil didaftarkan!');
    }

    /**
     * Ambil data warga untuk form edit (JSON response).
     */
    public function edit(Civil $civil): JsonResponse
    {
        return response()->json($civil);
    }

    /**
     * Update data warga.
     */
    public function update(UpdateCivilRequest $request, Civil $civil): RedirectResponse
    {
        $civil->update($request->validated());

        return redirect()->route('civils')
            ->with('success', 'Data warga berhasil diperbarui!');
    }

    /**
     * Hapus satu data warga.
     */
    public function destroy(Civil $civil): JsonResponse
    {
        $civil->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    /**
     * Hapus beberapa data warga sekaligus.
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        Civil::whereIn('id', $request->ids)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data terpilih berhasil dihapus',
        ]);
    }

    /**
     * Export data warga ke file Excel.
     */
    public function export(): BinaryFileResponse
    {
        return Excel::download(new CivilsExport, 'civils.xlsx');
    }

    /**
     * Import data warga dari file Excel/CSV.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        try {
            set_time_limit(0);
            Excel::import(new CivilsImport, $request->file('file'));

            return back()->with('success', 'Data berhasil diimpor!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
