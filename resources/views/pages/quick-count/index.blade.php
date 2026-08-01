@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Quick Count TPS" />

    <div class="space-y-6">

        @if (session('success'))
            <div
                class="flex items-center gap-3 rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-500/15 dark:text-green-400">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M10 2a8 8 0 100 16 8 8 0 000-16zm3.707 7.707l-4.5 4.5a1 1 0 01-1.414 0l-2-2a1 1 0 111.414-1.414L8.5 11.586l3.793-3.793a1 1 0 111.414 1.414z"
                        fill="currentColor" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="flex flex-col gap-1 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-500/15 dark:text-red-400">
                <div class="font-semibold flex items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z" fill="currentColor" />
                    </svg>
                    Gagal menyimpan data Quick Count:
                </div>
                <ul class="list-disc pl-7 mt-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Uniform Summary Cards Widget --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">

            {{-- Total TPS --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total TPS</p>
                        <h4 class="text-xl font-bold text-gray-800 dark:text-white">{{ number_format($totalTpsCount) }} TPS
                        </h4>
                    </div>
                </div>
            </div>

            {{-- TPS Sudah Input --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">TPS Sudah Input</p>
                        <h4 class="text-xl font-bold text-green-600 dark:text-green-400">{{ number_format($tpsSudahInput) }}
                            TPS</h4>
                    </div>
                </div>
            </div>

            {{-- TPS Belum Input --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">TPS Belum Input</p>
                        <h4 class="text-xl font-bold text-orange-600 dark:text-orange-400">
                            {{ number_format($tpsBelumInput) }} TPS</h4>
                    </div>
                </div>
            </div>

            {{-- Total Suara Masuk --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Suara Masuk</p>
                        <h4 class="text-xl font-bold text-gray-800 dark:text-white">{{ number_format($totalSuara) }}</h4>
                    </div>
                </div>
            </div>

            {{-- Total Pemilih TPS --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pemilih TPS</p>
                        <h4 class="text-xl font-bold text-gray-800 dark:text-white">{{ number_format($totalPemilih) }}</h4>
                    </div>
                </div>
            </div>

            {{-- Progress Quick Count (%) --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Progress Quick Count (%)</p>
                        <h4 class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ $progressPercentage }}%</h4>
                    </div>
                </div>
            </div>

        </div>

        {{-- Configuration-Driven DataTable --}}
        <x-datatable :config="$config" data-url="{{ route('quick-counts.data') }}" base-url="/quick-counts"
            title="Hasil Quick Count Per TPS" description="Monitoring internal perolehan suara pemilihan kades per TPS" />

    </div>

    {{-- Add Quick Count Modal --}}
    <x-ui.modal x-data="{ open: false, previewUrl: null }" @open-quick-count-modal.window="open = true; previewUrl = null"
        :isOpen="false" class="max-w-[650px]">
        <div
            class="no-scrollbar relative w-full max-w-[650px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-10">
            <div class="pr-10 mb-5">
                <h4 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Input Quick Count</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Masukkan hasil perhitungan suara TPS dan unggah
                    foto C1</p>
            </div>

            <form action="{{ route('quick-counts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                {{-- Info Callout Banner for TPS --}}
                <div
                    class="flex items-center justify-between gap-2 rounded-xl bg-blue-50/80 p-3.5 text-xs text-blue-700 dark:bg-blue-500/10 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800/40">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Jika TPS belum ada, silakan input TPS terlebih dahulu.</span>
                    </div>
                    <a href="{{ route('settings.tps') }}" target="_blank"
                        class="shrink-0 font-semibold text-blue-600 hover:text-blue-800 underline dark:text-blue-400 dark:hover:text-blue-200">
                        Input TPS &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- TPS Select --}}
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            TPS <span class="text-red-500">*</span>
                        </label>
                        <select name="tps_id" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">-- Pilih TPS --</option>
                            @foreach ($tpsList as $tps)
                                <option value="{{ $tps->id }}">{{ $tps->name }} (Total DPT:
                                    {{ number_format($tps->total_voters) }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Vote Count --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Perolehan Suara <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="vote_count" min="0" placeholder="e.g. 150" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    {{-- Total Voters --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Total Pemilih di TPS <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="total_voters" min="0" placeholder="e.g. 250" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>

                {{-- C1 Photo Upload Component --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Foto Form C1 <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 flex items-center gap-4">
                        <input type="file" name="c1_photo" accept="image/*" required
                            @change="const file = $event.target.files[0]; if (file) { previewUrl = URL.createObjectURL(file); }"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300" />
                    </div>
                    <div x-show="previewUrl" class="mt-3">
                        <p class="text-xs text-gray-500 mb-1">Preview Foto:</p>
                        <img :src="previewUrl"
                            class="h-32 w-auto max-w-full rounded-lg border border-gray-200 object-cover dark:border-gray-700" />
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Catatan
                        (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="Catatan tambahan di TPS..."
                        class="w-full rounded-lg border border-gray-300 bg-transparent p-3 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="open = false"
                        class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Simpan Quick Count
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    {{-- Edit Quick Count Modal --}}
    <x-ui.modal x-data="{
            open: false,
            formData: { id: null, tps_id: '', candidate_id: '', vote_count: 0, total_voters: 0, notes: '', c1_photo_url: null },
            previewUrl: null
        }" @open-edit-quick-count-modal.window="
            open = true;
            formData = $event.detail.data;
            previewUrl = $event.detail.data ? $event.detail.data.c1_photo_url : null;
        " :isOpen="false" class="max-w-[650px]">
        <div
            class="no-scrollbar relative w-full max-w-[650px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-10">
            <div class="pr-10 mb-5">
                <h4 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Edit Quick Count</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui data perhitungan suara TPS</p>
            </div>

            <form :action="'/quick-counts/' + formData.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            TPS <span class="text-red-500">*</span>
                        </label>
                        <select name="tps_id" x-model="formData.tps_id" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">-- Pilih TPS --</option>
                            @foreach (\App\Models\Tps::orderBy('name')->get() as $tps)
                                <option value="{{ $tps->id }}">{{ $tps->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Perolehan Suara <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="vote_count" x-model="formData.vote_count" min="0" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Total Pemilih di TPS <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="total_voters" x-model="formData.total_voters" min="0" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Foto Form C1 (Biarkan kosong jika tidak ingin mengubah)
                    </label>
                    <div class="mt-1 flex items-center gap-4">
                        <input type="file" name="c1_photo" accept="image/*"
                            @change="const file = $event.target.files[0]; if (file) { previewUrl = URL.createObjectURL(file); }"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300" />
                    </div>
                    <div x-show="previewUrl" class="mt-3">
                        <p class="text-xs text-gray-500 mb-1">Foto C1 Saat Ini / Preview Baru:</p>
                        <img :src="previewUrl"
                            class="h-32 w-auto max-w-full rounded-lg border border-gray-200 object-cover dark:border-gray-700" />
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Catatan</label>
                    <textarea name="notes" x-model="formData.notes" rows="2"
                        class="w-full rounded-lg border border-gray-300 bg-transparent p-3 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="open = false"
                        class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Perbarui Quick Count
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    {{-- Export Modal Component --}}
    <x-ui.export-modal :action="route('quick-counts.export')" module="quick_count" title="Ekspor Hasil Quick Count TPS"
        description="Pilih format file untuk mengunduh perolehan suara Quick Count TPS." />

    {{-- Import Modal Component --}}
    <x-ui.import-modal module="quick_count" :action="route('quick-counts.import')" title="Impor Hasil Quick Count TPS"
        description="Unggah file CSV / Excel untuk mengimpor atau memperbarui data perolehan suara per TPS."
        :validationRules="[
            '<strong>Kode TPS / Nama TPS</strong>: Wajib, kode atau nama TPS (contoh: `TPS-001`).',
            '<strong>Perolehan Suara</strong>: Angka jumlah suara terkumpul.',
            '<strong>Total Pemilih</strong>: Angka total DPT / pemilih di TPS.',
            '<strong>Catatan</strong>: Catatan tambahan di TPS (opsional).',
        ]" />
@endsection