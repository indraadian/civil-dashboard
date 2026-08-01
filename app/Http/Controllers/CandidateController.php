<?php

namespace App\Http\Controllers;

use App\DataTables\Definitions\CandidateDataTable;
use App\Http\Requests\StoreCandidateRequest;
use App\Http\Requests\UpdateCandidateRequest;
use App\Http\Traits\HasDataTable;
use App\Models\Candidate;
use App\Models\CivilExport;
use App\Models\CivilImport;
use App\Services\CandidateExportService;
use App\Services\CandidateImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CandidateController extends Controller
{
    use HasDataTable;

    public function index(): View
    {
        $this->authorize('viewAny', Candidate::class);

        $config = $this->dataTableConfig(new CandidateDataTable());

        return view('pages.settings.candidates', compact('config'));
    }

    public function data(Request $request): JsonResponse
    {
        return $this->dataTableResponse($request, new CandidateDataTable());
    }

    public function store(StoreCandidateRequest $request): RedirectResponse
    {
        $this->authorize('create', Candidate::class);

        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');
        $data['created_by'] = auth()->id();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('candidates', 'public');
        }

        Candidate::create($data);

        return redirect()->route('settings.candidates')
            ->with('success', 'Candidate berhasil ditambahkan.');
    }

    public function edit(Candidate $candidate): JsonResponse
    {
        $this->authorize('view', $candidate);

        return response()->json($candidate);
    }

    public function update(UpdateCandidateRequest $request, Candidate $candidate): RedirectResponse
    {
        $this->authorize('update', $candidate);

        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');
        $data['updated_by'] = auth()->id();

        if ($request->hasFile('photo')) {
            if ($candidate->photo) {
                Storage::disk('public')->delete($candidate->photo);
            }
            $data['photo'] = $request->file('photo')->store('candidates', 'public');
        }

        $candidate->update($data);

        return redirect()->route('settings.candidates')
            ->with('success', 'Data candidate berhasil diperbarui.');
    }

    public function destroy(Candidate $candidate): JsonResponse
    {
        $this->authorize('delete', $candidate);

        if ($candidate->photo) {
            Storage::disk('public')->delete($candidate->photo);
        }

        $candidate->delete();

        return response()->json(['message' => 'Candidate berhasil dihapus']);
    }

    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $candidates = Candidate::whereIn('id', $request->ids)->get();
        foreach ($candidates as $candidate) {
            $this->authorize('delete', $candidate);
            if ($candidate->photo) {
                Storage::disk('public')->delete($candidate->photo);
            }
            $candidate->delete();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Data Candidate terpilih berhasil dihapus',
        ]);
    }

    /**
     * Export Candidate data.
     */
    public function export(Request $request, CandidateExportService $exportService): RedirectResponse
    {
        $this->authorize('viewAny', Candidate::class);

        $export = $exportService->initiate(
            userId: $request->user()->id,
            filters: $request->all(),
            format: $request->input('format', 'xlsx')
        );

        return back()->with(
            'info',
            "File export Candidate sedang dibuat di background. ID Export: #{$export->id}."
        );
    }

    public function exportProgress(CivilExport $export): JsonResponse
    {
        return response()->json([
            'id'             => $export->id,
            'status'         => $export->status,
            'progress'       => $export->progress,
            'download_url'   => $export->download_url,
            'error_message'  => $export->error_message,
        ]);
    }

    /**
     * Import Candidate data from CSV/Excel file.
     */
    public function import(Request $request, CandidateImportService $importService): RedirectResponse
    {
        $this->authorize('create', Candidate::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ]);

        $import = $importService->initiate($request);

        return back()->with(
            'info',
            "File import Candidate sedang diproses di background. ID Import: #{$import->id}."
        );
    }

    public function importProgress(CivilImport $import): JsonResponse
    {
        return response()->json([
            'id'             => $import->id,
            'status'         => $import->status,
            'progress'       => $import->progress,
            'total_rows'     => $import->total_rows,
            'processed_rows' => $import->processed_rows,
            'failed_rows'    => $import->failed_rows,
            'error_message'  => $import->error_message,
        ]);
    }
}
