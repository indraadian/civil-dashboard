@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Master Data RW" />

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-500/15 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="flex flex-col gap-1 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-500/15 dark:text-red-400">
                <div class="font-semibold flex items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z" fill="currentColor" />
                    </svg>
                    Gagal menyimpan data RW:
                </div>
                <ul class="list-disc pl-7 mt-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- DataTable Component --}}
        <x-datatable
            :config="$config"
            data-url="{{ route('settings.rws.data') }}"
            base-url="/settings/rws"
            title="Master Data RW"
        >
        </x-datatable>
    </div>

    {{-- Modal Tambah RW --}}
    <x-ui.modal x-data="{ open: false }" x-on:open-rw-modal.window="open = true" :isOpen="false" class="max-w-[500px]">
        <div class="relative w-full max-w-[500px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
            <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Tambah Master RW</h4>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Masukkan kode dan nama RW baru.</p>

            <form action="{{ route('settings.rws.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kode RW (misal: 001, 002)</label>
                    <input type="text" name="code" required placeholder="001"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama RW (opsional)</label>
                    <input type="text" name="name" placeholder="e.g. RW 001 Mekar"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active_add" value="1" checked class="rounded border-gray-300 text-brand-500" />
                    <label for="is_active_add" class="text-sm font-medium text-gray-700 dark:text-gray-400">Status Aktif</label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Batal</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">Simpan</button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    {{-- Modal Edit RW --}}
    <x-ui.modal x-data="{ open: false, formData: {} }" x-on:open-edit-rw-modal.window="open = true; formData = $event.detail.data" :isOpen="false" class="max-w-[500px]">
        <div class="relative w-full max-w-[500px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
            <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Ubah Master RW</h4>

            <form :action="'/settings/rws/' + formData.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kode RW</label>
                    <input type="text" name="code" x-model="formData.code" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama RW</label>
                    <input type="text" name="name" x-model="formData.name"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active_edit" value="1" :checked="formData.is_active" class="rounded border-gray-300 text-brand-500" />
                    <label for="is_active_edit" class="text-sm font-medium text-gray-700 dark:text-gray-400">Status Aktif</label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Batal</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">Simpan</button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    <script>
        function editRw(id) {
            fetch(`/settings/rws/${id}/edit`)
                .then(res => res.json())
                .then(rw => {
                    window.dispatchEvent(new CustomEvent('open-edit-rw-modal', { detail: { data: rw } }));
                });
        }

        function deleteRw(id, button) {
            if (!confirm('Yakin ingin menghapus data RW ini beserta seluruh RT di dalamnya?')) return;
            fetch(`/settings/rws/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => window.location.reload());
        }
    </script>
    {{-- Export Modal --}}
    <x-ui.export-modal
        :action="route('settings.rws.export')"
        title="Ekspor Master RW"
        description="Pilih format file untuk mengunduh data Master RW."
    />

    {{-- Import Modal --}}
    <x-ui.import-modal
        module="rw"
        :action="route('settings.rws.import')"
        title="Impor Master RW"
        description="Unggah file CSV / Excel untuk mengimpor atau memperbarui data Master RW."
        :validationRules="[
            '<strong>Kode RW</strong>: Wajib, format 3 digit (contoh: `001`, `002`).',
            '<strong>Nama RW</strong>: Nama lengkap RW (opsional, contoh: `RW 001 Sukamaju`).',
            '<strong>Status</strong>: `Aktif` atau `Non-Aktif` (default: Aktif).',
        ]"
    />
@endsection
