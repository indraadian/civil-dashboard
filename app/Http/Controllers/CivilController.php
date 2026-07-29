<?php

namespace App\Http\Controllers;

use App\DataTables\Definitions\CivilDataTable;
use App\Http\Requests\ExportCivilRequest;
use App\Http\Requests\ImportCivilRequest;
use App\Http\Requests\StoreCivilRequest;
use App\Http\Requests\UpdateCivilRequest;
use App\Http\Traits\HasDataTable;
use App\Models\Civil;
use App\Models\CivilExport;
use App\Models\CivilImport;
use App\Services\CivilExportService;
use App\Services\CivilImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CivilController extends Controller
{
    use HasDataTable;

    public function __construct(
        private readonly CivilImportService $importService,
        private readonly CivilExportService $exportService,
    ) {}

    // ── CRUD ──────────────────────────────────────────────────────────────────

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
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        Civil::whereIn('id', $request->ids)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data terpilih berhasil dihapus',
        ]);
    }

    // ── IMPORT ────────────────────────────────────────────────────────────────

    /**
     * Mulai proses import data warga secara asynchronous.
     *
     * Flow: validate file → simpan ke storage → buat record → dispatch job
     * HTTP request selesai dalam hitungan milidetik.
     */
    public function import(ImportCivilRequest $request): RedirectResponse
    {
        $import = $this->importService->initiate($request);

        return back()->with(
            'info',
            "File sedang diproses di background. ID Import: #{$import->id}. " .
            'Anda akan diberitahu melalui email ketika selesai.'
        );
    }

    /**
     * Kembalikan status dan progress import (untuk polling frontend).
     *
     * GET /imports/{import}
     */
    public function importProgress(CivilImport $import): JsonResponse
    {
        $this->authorize('view', $import);

        return response()->json([
            'id'             => $import->id,
            'status'         => $import->status,
            'progress'       => $import->progress,
            'total_rows'     => $import->total_rows,
            'processed_rows' => $import->processed_rows,
            'failed_rows'    => $import->failed_rows,
            'error_message'  => $import->error_message,
            'started_at'     => $import->started_at?->toIso8601String(),
            'finished_at'    => $import->finished_at?->toIso8601String(),
        ]);
    }

    // ── EXPORT ────────────────────────────────────────────────────────────────

    /**
     * Mulai proses export data warga secara asynchronous.
     *
     * Flow: validate request → buat record → dispatch job
     * HTTP request selesai dalam hitungan milidetik.
     */
    public function export(ExportCivilRequest $request): RedirectResponse
    {
        $export = $this->exportService->initiate(
            userId: $request->user()->id,
            filters: $request->filters(),
            format:  $request->exportFormat(),
        );

        return back()->with(
            'info',
            "File sedang dibuat di background. ID Export: #{$export->id}. " .
            'Anda akan diberitahu melalui email ketika file siap diunduh.'
        );
    }

    /**
     * Kembalikan status dan progress export (untuk polling frontend).
     *
     * GET /exports/{export}
     */
    public function exportProgress(CivilExport $export): JsonResponse
    {
        $this->authorize('view', $export);

        return response()->json([
            'id'             => $export->id,
            'status'         => $export->status,
            'progress'       => $export->progress,
            'total_rows'     => $export->total_rows,
            'processed_rows' => $export->processed_rows,
            'download_url'   => $export->isDownloadable() ? $export->download_url : null,
            'expires_at'     => $export->expires_at?->toIso8601String(),
            'started_at'     => $export->started_at?->toIso8601String(),
            'finished_at'    => $export->finished_at?->toIso8601String(),
        ]);
    }

    /**
     * Download file export yang sudah selesai.
     *
     * GET /exports/{export}/download
     */
    public function exportDownload(CivilExport $export): StreamedResponse
    {
        $this->authorize('download', $export);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        return $disk->download(
            path:    $export->stored_path,
            name:    $export->filename,
            headers: ['Content-Type' => 'application/octet-stream'],
        );
    }
}
