@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Master Candidate" />

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
                    Gagal menyimpan Candidate:
                </div>
                <ul class="list-disc pl-7 mt-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Configuration-Driven DataTable Component --}}
        <x-datatable :config="$config" data-url="{{ route('settings.candidates.data') }}" base-url="/settings/candidates"
            title="Master Data Calon (Candidate)"
            description="Kelola daftar pasangan calon pilkades, foto, dan status aktif untuk Quick Count" />

    </div>

    {{-- Create / Edit Candidate Modal using reusable x-ui.modal --}}
    <x-ui.modal x-data="{
                    open: false,
                    isEdit: false,
                    formId: null,
                    number: 1,
                    name: '',
                    is_active: true,
                    photoPreview: null
                }" @open-candidate-modal.window="
                    isEdit = false;
                    formId = null;
                    number = 1;
                    name = '';
                    is_active = true;
                    photoPreview = null;
                    open = true;
                " @open-edit-candidate-modal.window="
                    const targetData = $event.detail?.data || $event.detail;
                    if (targetData && typeof targetData === 'object' && targetData.id) {
                        isEdit = true;
                        formId = targetData.id;
                        number = targetData.number;
                        name = targetData.name;
                        is_active = !!targetData.is_active;
                        photoPreview = targetData.photo_url || null;
                        open = true;
                    } else if (targetData) {
                        const id = typeof targetData === 'object' ? targetData.id : targetData;
                        fetch(`/settings/candidates/${id}/edit`)
                            .then(res => res.json())
                            .then(data => {
                                isEdit = true;
                                formId = data.id;
                                number = data.number;
                                name = data.name;
                                is_active = !!data.is_active;
                                photoPreview = data.photo_url || null;
                                open = true;
                            });
                    }
                " :isOpen="false" class="max-w-[550px]">
        <div
            class="no-scrollbar relative w-full max-w-[550px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-8">
            <div class="pr-10 mb-5">
                <h4 class="text-2xl font-semibold text-gray-800 dark:text-white/90"
                    x-text="isEdit ? 'Edit Data Candidate' : 'Tambah Candidate Baru'"></h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Isi nomor urut, nama pasangan calon, foto, serta
                    status aktif</p>
            </div>

            <form :action="isEdit ? `/settings/candidates/${formId}` : '{{ route('settings.candidates.store') }}'"
                method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Nomor Urut Calon <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="number" x-model="number" min="1" required placeholder="e.g. 1"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Nama Pasangan Calon <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" x-model="name" required
                        placeholder="Contoh: H. Suherman & Bambang Irawan"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Foto Pasangan Calon
                    </label>
                    <input type="file" name="photo" accept="image/*"
                        @change="const file = $event.target.files[0]; if (file) { photoPreview = URL.createObjectURL(file); }"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300" />

                    <template x-if="photoPreview">
                        <div class="mt-3 flex items-center gap-3">
                            <img :src="photoPreview"
                                class="h-16 w-16 rounded-xl object-cover border border-gray-200 shadow-xs dark:border-gray-700" />
                            <span class="text-xs text-gray-500">Preview Foto Paslon</span>
                        </div>
                    </template>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="candidate_is_active" value="1" x-model="is_active"
                        class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                    <label for="candidate_is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">Status
                        Aktif (Tampil di Quick Count)</label>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="open = false"
                        class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        <span x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Candidate'"></span>
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    {{-- Export Modal Component --}}
    <x-ui.export-modal :action="route('settings.candidates.export')" module="candidate" title="Ekspor Master Candidate"
        description="Pilih format file untuk mengunduh daftar pasangan calon pilkades.">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Filter Status</label>
            <select name="is_active"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">Semua Candidate</option>
                <option value="1">Aktif</option>
                <option value="0">Nonaktif</option>
            </select>
        </div>
    </x-ui.export-modal>

    {{-- Import Modal Component --}}
    <x-ui.import-modal module="candidate" :action="route('settings.candidates.import')" title="Impor Master Candidate"
        description="Unggah file CSV / Excel untuk mengimpor data master pasangan calon." :validationRules="[
            '<strong>Nomor Urut / No Urut</strong>: Wajib, angka nomor urut calon (contoh: `1`).',
            '<strong>Nama Pasangan Calon / Nama Calon</strong>: Wajib, nama lengkap pasangan calon.',
            '<strong>Status Aktif</strong>: `1` / `Aktif` / `Ya` atau `0` / `Nonaktif`.',
        ]" />
@endsection