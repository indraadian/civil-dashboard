@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Manajemen Role" />

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
                    Gagal menyimpan Role:
                </div>
                <ul class="list-disc pl-7 mt-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Configuration-Driven DataTable --}}
        <x-datatable :config="$config" data-url="{{ route('settings.roles.data') }}" base-url="/settings/roles"
            title="Daftar Role Akses" description="Kelola daftar role pengguna serta pembagian permission per modul" />

    </div>

    {{-- Create / Edit Role Modal --}}
    <div x-data="{
        open: false,
        isEdit: false,
        formId: null,
        name: '',
        permissions: [],
        toggleModule(modulePerms) {
            const keys = Object.keys(modulePerms);
            const allChecked = keys.every(k => this.permissions.includes(k));
            if (allChecked) {
                this.permissions = this.permissions.filter(p => !keys.includes(p));
            } else {
                keys.forEach(k => {
                    if (!this.permissions.includes(k)) this.permissions.push(k);
                });
            }
        },
        isModuleChecked(modulePerms) {
            const keys = Object.keys(modulePerms);
            return keys.length > 0 && keys.every(k => this.permissions.includes(k));
        }
    }"
        @open-role-modal.window="
            open = true;
            isEdit = false;
            formId = null;
            name = '';
            permissions = [];
        "
        @open-edit-role-modal.window="
            open = true;
            isEdit = true;
            formId = $event.detail.data.id;
            name = '';
            permissions = [];
            fetch('/settings/roles/' + formId + '/edit')
                .then(res => res.json())
                .then(data => {
                    name = data.name;
                    permissions = data.permissions || [];
                });
        "
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div x-show="open" x-transition.opacity @click="open = false"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"></div>

            <div x-show="open" x-transition
                class="no-scrollbar relative w-full max-w-3xl transform overflow-hidden rounded-3xl bg-white p-6 text-left shadow-xl transition-all dark:bg-gray-900 lg:p-8">
                
                <div class="pr-10 mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                    <h4 class="text-2xl font-semibold text-gray-800 dark:text-white/90" x-text="isEdit ? 'Edit Role: ' + name : 'Tambah Role Baru'"></h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tentukan nama role dan pilih hak akses (permission) yang diberikan.</p>
                </div>

                <form :action="isEdit ? '/settings/roles/' + formId : '{{ route('settings.roles.store') }}'" method="POST" class="space-y-6">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nama Role <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" x-model="name" required placeholder="Contoh: Manager Operasional"
                            :readonly="name === 'Super Admin'"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    {{-- Grouped Permissions Multi-Select --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-semibold text-gray-800 dark:text-white">
                                Hak Akses (Permissions) Per Modul
                            </label>
                            <span class="text-xs text-gray-500 dark:text-gray-400" x-text="permissions.length + ' permission dipilih'"></span>
                        </div>

                        <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                            @foreach ($modulePermissions as $moduleName => $perms)
                                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                    <div class="flex items-center justify-between border-b border-gray-200/60 pb-2.5 mb-3 dark:border-gray-700/60">
                                        <h6 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                            <span class="inline-block h-2 w-2 rounded-full bg-brand-500"></span>
                                            Modul {{ $moduleName }}
                                        </h6>
                                        <button type="button" @click="toggleModule({{ Js::from($perms) }})"
                                            class="text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                            <span x-text="isModuleChecked({{ Js::from($perms) }}) ? 'Pilih Semua (Uncheck)' : 'Pilih Semua'"></span>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                        @foreach ($perms as $permKey => $permLabel)
                                            <label class="flex items-center gap-2.5 rounded-lg border border-gray-200 bg-white p-2.5 text-xs text-gray-700 hover:bg-brand-50/30 cursor-pointer dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                                <input type="checkbox" name="permissions[]" value="{{ $permKey }}"
                                                    x-model="permissions"
                                                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900" />
                                                <span>{{ $permLabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="open = false"
                            class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                            <span x-text="isEdit ? 'Simpan Perubahan' : 'Buat Role'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
