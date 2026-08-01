@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Master TPS" />

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
                    Gagal menyimpan data TPS:
                </div>
                <ul class="list-disc pl-7 mt-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Configuration-Driven DataTable --}}
        <x-datatable
            :config="$config"
            data-url="{{ route('settings.tps.data') }}"
            base-url="/settings/tps"
            title="Master Tempat Pemungutan Suara (TPS)"
            description="Kelola daftar TPS dan alokasi total pemilih (DPT)"
        />

    </div>

    {{-- Add TPS Modal --}}
    <x-ui.modal x-data="{ open: false }" @open-tps-modal.window="open = true" :isOpen="false" class="max-w-[550px]">
        <div class="no-scrollbar relative w-full max-w-[550px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-10">
            <div class="pr-10 mb-5">
                <h4 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Tambah TPS Baru</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Isi data lokasi TPS dan jumlah DPT pemilih</p>
            </div>

            <form action="{{ route('settings.tps.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Nama TPS <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" placeholder="e.g. TPS 01 - RW 01 Sukamaju" required
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kode TPS (Optional)</label>
                        <input type="text" name="code" placeholder="e.g. TPS-001"
                               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Total DPT / Pemilih <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="total_voters" min="0" placeholder="e.g. 350" required
                               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Alamat / Lokasi TPS</label>
                    <textarea name="location" rows="2" placeholder="e.g. Balai Warga RW 01..."
                              class="w-full rounded-lg border border-gray-300 bg-transparent p-3 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Batal
                    </button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Simpan TPS
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    {{-- Edit TPS Modal --}}
    <x-ui.modal x-data="{
        open: false,
        formData: { id: null, name: '', code: '', location: '', total_voters: 0 }
    }"
    @open-edit-tps-modal.window="
        open = true;
        formData = $event.detail.data;
    "
    :isOpen="false" class="max-w-[550px]">
        <div class="no-scrollbar relative w-full max-w-[550px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-10">
            <div class="pr-10 mb-5">
                <h4 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Edit Data TPS</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui rincian TPS dan total DPT</p>
            </div>

            <form :action="'/settings/tps/' + formData.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Nama TPS <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" x-model="formData.name" required
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kode TPS</label>
                        <input type="text" name="code" x-model="formData.code"
                               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Total DPT / Pemilih <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="total_voters" x-model="formData.total_voters" min="0" required
                               class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Alamat / Lokasi TPS</label>
                    <textarea name="location" x-model="formData.location" rows="2"
                              class="w-full rounded-lg border border-gray-300 bg-transparent p-3 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Batal
                    </button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Perbarui TPS
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    {{-- Export Modal --}}
    <x-ui.export-modal
        :action="route('settings.tps.export')"
        title="Ekspor Master TPS"
        description="Pilih format file untuk mengunduh data Master TPS."
    >
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Filter Nama / Kode TPS</label>
            <input type="text" name="search" placeholder="Contoh: TPS 01 (opsional)"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
        </div>
    </x-ui.export-modal>

    {{-- Import Modal --}}
    <x-ui.import-modal
        module="tps"
        :action="route('settings.tps.import')"
        title="Impor Master TPS"
        description="Unggah file CSV / Excel untuk mengimpor atau memperbarui data Master TPS dan total DPT."
        :validationRules="[
            '<strong>Kode TPS</strong>: Kode unik TPS (contoh: `TPS-001`).',
            '<strong>Nama TPS</strong>: Nama lengkap TPS (contoh: `TPS 01 - RW 01`).',
            '<strong>Lokasi</strong>: Lokasi atau alamat TPS (opsional).',
            '<strong>Total DPT / Pemilih</strong>: Angka jumlah pemilih di TPS.',
        ]"
    />
@endsection
