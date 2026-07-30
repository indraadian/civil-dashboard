@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Master Data RT" />

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-500/15 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        {{-- DataTable Component --}}
        <x-datatable
            :config="$config"
            data-url="{{ route('settings.rts.data') }}"
            base-url="/settings/rts"
            title="Master Data RT"
        >
        </x-datatable>
    </div>

    {{-- Modal Tambah RT --}}
    <x-ui.modal x-data="{ open: false }" x-on:open-rt-modal.window="open = true" :isOpen="false" class="max-w-[500px]">
        <div class="relative w-full max-w-[500px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
            <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Tambah Master RT</h4>

            <form action="{{ route('settings.rts.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pilih RW <span class="text-red-500">*</span></label>
                    <select name="rw_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="" disabled selected>-- Pilih RW --</option>
                        @foreach ($rws as $rw)
                            <option value="{{ $rw->id }}">RW {{ $rw->code }} ({{ $rw->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kode RT (misal: 001, 002) <span class="text-red-500">*</span></label>
                    <input type="text" name="code" required placeholder="001"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama RT (opsional)</label>
                    <input type="text" name="name" placeholder="e.g. RT 001 Mawar"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active_rt_add" value="1" checked class="rounded border-gray-300 text-brand-500" />
                    <label for="is_active_rt_add" class="text-sm font-medium text-gray-700 dark:text-gray-400">Status Aktif</label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Batal</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">Simpan</button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    {{-- Modal Edit RT --}}
    <x-ui.modal x-data="{ open: false, formData: {} }" x-on:open-edit-rt-modal.window="open = true; formData = $event.detail.data" :isOpen="false" class="max-w-[500px]">
        <div class="relative w-full max-w-[500px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
            <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Ubah Master RT</h4>

            <form :action="'/settings/rts/' + formData.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pilih RW</label>
                    <select name="rw_id" x-model="formData.rw_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @foreach ($rws as $rw)
                            <option value="{{ $rw->id }}">RW {{ $rw->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kode RT</label>
                    <input type="text" name="code" x-model="formData.code" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama RT</label>
                    <input type="text" name="name" x-model="formData.name"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active_rt_edit" value="1" :checked="formData.is_active" class="rounded border-gray-300 text-brand-500" />
                    <label for="is_active_rt_edit" class="text-sm font-medium text-gray-700 dark:text-gray-400">Status Aktif</label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Batal</button>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">Simpan</button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    <script>
        function editRt(id) {
            fetch(`/settings/rts/${id}/edit`)
                .then(res => res.json())
                .then(rt => {
                    window.dispatchEvent(new CustomEvent('open-edit-rt-modal', { detail: { data: rt } }));
                });
        }

        function deleteRt(id, button) {
            if (!confirm('Yakin ingin menghapus data RT ini?')) return;
            fetch(`/settings/rts/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => window.location.reload());
        }
    </script>
@endsection
