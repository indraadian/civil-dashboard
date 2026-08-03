@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Quick Count TPS" />

    <div class="space-y-6">

        @if (session('success'))
            <div class="flex items-center gap-3 rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-500/15 dark:text-green-400">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm3.707 7.707l-4.5 4.5a1 1 0 01-1.414 0l-2-2a1 1 0 111.414-1.414L8.5 11.586l3.793-3.793a1 1 0 111.414 1.414z" fill="currentColor" />
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

        {{-- Dynamic Candidate & Summary Cards Widget --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">

            {{-- Dynamic Cards for Active Candidates --}}
            @foreach ($candidates as $candidate)
                <div class="rounded-2xl border border-brand-200 bg-brand-50/30 p-5 shadow-xs dark:border-brand-900/40 dark:bg-brand-950/20">
                    <div class="flex items-center gap-3">
                        @if ($candidate->photo_url)
                            <img src="{{ $candidate->photo_url }}" class="h-11 w-11 rounded-xl object-cover border border-brand-300" />
                        @else
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-100 text-brand-700 font-bold dark:bg-brand-900/40 dark:text-brand-300">
                                #{{ $candidate->number }}
                            </div>
                        @endif
                        <div>
                            <p class="text-xs font-semibold text-brand-700 dark:text-brand-300">Calon {{ $candidate->number }}</p>
                            <h4 class="text-xl font-extrabold text-gray-900 dark:text-white">
                                {{ number_format($candidateVotesMap[$candidate->id] ?? 0, 0, ',', '.') }}
                            </h4>
                            <p class="text-[10px] text-gray-500 truncate max-w-[110px]">{{ $candidate->name }}</p>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Total Suara Sah --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Suara Sah</p>
                        <h4 class="text-xl font-bold text-green-600 dark:text-green-400">{{ number_format($totalSuaraSah, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>

            {{-- Suara Tidak Sah --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Suara Tidak Sah</p>
                        <h4 class="text-xl font-bold text-red-600 dark:text-red-400">{{ number_format($totalSuaraTidakSah, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>

            {{-- Total Pengguna Hak Pilih --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pengguna Hak Pilih</p>
                        <h4 class="text-xl font-bold text-gray-800 dark:text-white">{{ number_format($totalPemilih, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>

            {{-- Progress Quick Count (%) --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Progress TPS ({{ $tpsSudahInput }}/{{ $totalTpsCount }})</p>
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
        :isOpen="false" class="max-w-[680px]">
        <div class="no-scrollbar relative w-full max-w-[680px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-8">
            <div class="pr-10 mb-5">
                <h4 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Input Quick Count</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Masukkan hasil perhitungan perolehan suara per calon dan bukti C1</p>
            </div>

            <form action="{{ route('quick-counts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- TPS Select --}}
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            TPS <span class="text-red-500">*</span>
                        </label>
                        <select name="tps_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">-- Pilih TPS --</option>
                            @foreach ($tpsList as $tps)
                                <option value="{{ $tps->id }}">{{ $tps->name }} (DPT: {{ number_format($tps->total_voters) }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nama Petugas --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nama Petugas <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="officer_name" required placeholder="Nama lengkap petugas TPS" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    {{-- Nomor HP Petugas --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nomor HP Petugas <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="officer_phone" required placeholder="08xxxxxxxxxx" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>

                {{-- Perolehan Suara Per Calon (Dynamic Inputs) --}}
                <div class="rounded-2xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                    <h5 class="text-sm font-bold text-gray-800 dark:text-white mb-3">Perolehan Suara Calon</h5>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach ($candidates as $candidate)
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    Calon {{ $candidate->number }}: {{ $candidate->name }} <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="votes[{{ $candidate->id }}]" min="0" value="0" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Suara Tidak Sah --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Jumlah Suara Tidak Sah <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="invalid_votes" min="0" value="0" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    {{-- Total Pengguna Hak Pilih --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Total Pengguna Hak Pilih <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="total_voters" min="0" required placeholder="Total Suara Sah + Tidak Sah" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>

                {{-- C1 Photo Upload --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Upload Foto C1
                    </label>
                    <input type="file" name="c1_photo" accept="image/*"
                        @change="const file = $event.target.files[0]; if (file) { previewUrl = URL.createObjectURL(file); }"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300" />
                    <div x-show="previewUrl" class="mt-3">
                        <p class="text-xs text-gray-500 mb-1">Preview Foto C1:</p>
                        <img :src="previewUrl" class="h-32 w-auto max-w-full rounded-lg border border-gray-200 object-cover dark:border-gray-700" />
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Batal
                    </button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Simpan Quick Count
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    {{-- Edit Quick Count Modal --}}
    <x-ui.modal x-data="{
            open: false,
            formData: { id: null, tps_id: '', officer_name: '', officer_phone: '', invalid_votes: 0, total_voters: 0, votes: {}, c1_photo_url: null },
            previewUrl: null
        }" @open-edit-quick-count-modal.window="
            const target = $event.detail?.data || $event.detail;
            const id = target ? (target.id || target) : null;
            if (id) {
                formData.id = id;
                open = true;
                fetch(`/quick-counts/${id}/edit`)
                    .then(res => res.json())
                    .then(data => {
                        formData = data;
                        previewUrl = data.c1_photo_url;
                    });
            }
        " :isOpen="false" class="max-w-[680px]">
        <div class="no-scrollbar relative w-full max-w-[680px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-8">
            <div class="pr-10 mb-5">
                <h4 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Edit Quick Count</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui perolehan suara TPS</p>
            </div>

            <form :action="'/quick-counts/' + formData.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            TPS <span class="text-red-500">*</span>
                        </label>
                        <select name="tps_id" x-model="formData.tps_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">-- Pilih TPS --</option>
                            @foreach (\App\Models\Tps::orderBy('name')->get() as $tps)
                                <option value="{{ $tps->id }}">{{ $tps->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nama Petugas <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="officer_name" x-model="formData.officer_name" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nomor HP Petugas <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="officer_phone" x-model="formData.officer_phone" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>

                {{-- Dynamic Candidate Votes --}}
                <div class="rounded-2xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                    <h5 class="text-sm font-bold text-gray-800 dark:text-white mb-3">Perolehan Suara Calon</h5>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach ($candidates as $candidate)
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    Calon {{ $candidate->number }}: {{ $candidate->name }} <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="votes[{{ $candidate->id }}]" :value="formData.votes ? (formData.votes[{{ $candidate->id }}] || 0) : 0" min="0" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Jumlah Suara Tidak Sah <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="invalid_votes" x-model="formData.invalid_votes" min="0" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Total Pengguna Hak Pilih <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="total_voters" x-model="formData.total_voters" min="0" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Foto Form C1 (Opsional, jika ingin mengganti)
                    </label>
                    <input type="file" name="c1_photo" accept="image/*"
                        @change="const file = $event.target.files[0]; if (file) { previewUrl = URL.createObjectURL(file); }"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300" />
                    <div x-show="previewUrl" class="mt-3 flex items-end gap-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Foto C1 Preview:</p>
                            <img :src="previewUrl" class="h-32 w-auto max-w-full rounded-lg border border-gray-200 object-cover dark:border-gray-700" />
                        </div>
                        @canany(['quick-count.export', 'quick-count.view'])
                            <template x-if="formData.c1_photo_url">
                                <a :href="formData.c1_photo_url" download target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Unduh Foto C1
                                </a>
                            </template>
                        @endcanany
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Batal
                    </button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Perbarui Quick Count
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    {{-- Export Modal Component --}}
    <x-ui.export-modal :action="route('quick-counts.export')" module="quick_count" title="Ekspor Hasil Quick Count TPS"
        description="Pilih format file untuk mengunduh perolehan suara Quick Count TPS.">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Filter TPS</label>
            <select name="tps_id"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">Semua TPS</option>
                @foreach (\App\Models\Tps::orderBy('name')->get() as $tps)
                    <option value="{{ $tps->id }}">{{ $tps->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Filter Nama Petugas</label>
            <input type="text" name="officer_name" placeholder="Nama petugas TPS (opsional)"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
        </div>
    </x-ui.export-modal>

    {{-- Import Modal Component --}}
    <x-ui.import-modal module="quick_count" :action="route('quick-counts.import')" title="Impor Hasil Quick Count TPS"
        description="Unggah file CSV / Excel untuk mengimpor atau memperbarui data perolehan suara per TPS."
        :validationRules="[
            '<strong>Kode TPS / Nama TPS</strong>: Wajib, kode atau nama TPS (contoh: `TPS-001`).',
            '<strong>Nama Petugas</strong>: Nama lengkap petugas TPS.',
            '<strong>Nomor HP</strong>: Nomor HP petugas.',
            '<strong>Suara Per Calon</strong>: Perolehan suara masing-masing calon.',
            '<strong>Suara Tidak Sah</strong>: Angka jumlah suara tidak sah.',
            '<strong>Total Pengguna Hak Pilih</strong>: Total pengguna hak pilih.',
        ]" />
@endsection