<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Civil;
use App\Http\Resources\CivilResource;
use App\Exports\CivilsExport;
use App\Imports\CivilsImport;
use Maatwebsite\Excel\Facades\Excel;

class CivilController extends Controller
{
    public function index()
    {
        return view('pages.civil.list');
    }

    public function data(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');

        $civils = Civil::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            })
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);

        return CivilResource::collection($civils);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input Form
        $validated = $request->validate([
            'nik'           => 'required|numeric|digits:16|unique:civils,nik', // NIK harus 16 digit & unik
            'name'          => 'required|string|max:255',
            'hamlet'        => 'nullable|string|max:255',
            'location_type' => 'required|in:village,housing',
            'rt'            => 'required|string|max:5',
            'rw'            => 'required|string|max:5',
            'address'       => 'required|string',
            'date_of_birth' => 'required|date',
            'gender'        => 'required|in:L,P',
            'status'        => 'required|in:Militan,Ngambang,Lawan'
        ], [
            // Custom pesan error bahasa Indonesia biar user ramah bacanya
            'nik.unique'   => 'NIK sudah terdaftar dalam sistem!',
            'nik.digits'   => 'NIK harus tepat berisikan 16 digit angka.',
            'nik.numeric'  => 'NIK hanya boleh berupa angka.',
            'required'     => 'Kolom :attribute wajib diisi!'
        ]);

        // 2. Simpan Data ke Database menggunakan Eloquent
        Civil::create($validated);

        // 3. Redirect kembali ke halaman list dengan membawa pesan sukses
        return redirect()->route('civils')->with('success', 'Data warga baru berhasil didaftarkan!');
    }

    public function edit(int $id)
    {
        $civil = Civil::findOrFail($id);
        // Karena kita pakai modal berbasis API/AJAX biar gak reload halaman, kita return JSON
        return response()->json($civil);
    }

    public function update(Request $request, int $id)
    {
        $civil = Civil::findOrFail($id);

        $validated = $request->validate([
            'nik'           => 'required|numeric|digits:16|unique:civils,nik,' . $id, // Abaikan NIK milik dia sendiri saat validasi unik
            'name'          => 'required|string|max:255',
            'hamlet'        => 'nullable|string|max:255',
            'location_type' => 'required|in:village,housing',
            'rt'            => 'required|string|max:5',
            'rw'            => 'required|string|max:5',
            'address'       => 'required|string',
            'date_of_birth' => 'required|date',
            'gender'        => 'required|in:L,P',
            'status'        => 'required|in:Militan,Ngambang,Lawan'
        ]);

        $civil->update($validated);

        return redirect()->route('civils')->with('success', 'Data warga berhasil diperbarui!');
    }

    public function destroy(int $id)
    {
        $civil = Civil::findOrFail($id);
        $civil->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function destroyBulk(Request $request)
    {
        // Validasi bahwa 'ids' harus ada dan merupakan array
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        Civil::whereIn('id', $request->ids, 'and', false)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data terpilih berhasil dihapus'
        ]);
    }

    public function export()
    {
        return Excel::download(new CivilsExport, 'civils.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        // 2. Mengambil file dari request
        $file = $request->file('file');

        // 3. Menjalankan proses import
        try {
            set_time_limit(0);
            Excel::import(new CivilsImport, $file);
            return back()->with('success', 'Data berhasil diimpor!');
        } catch (\Exception $e) {
            dd("gagal", $e);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
