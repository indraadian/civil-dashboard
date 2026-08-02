@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Manajemen User & Hak Akses Wilayah" />

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
                    Gagal menyimpan data user:
                </div>
                <ul class="list-disc pl-7 mt-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- DataTable Component --}}
        <x-datatable :config="$config" data-url="{{ route('settings.users.data') }}" base-url="/settings/users"
            title="Daftar User">
        </x-datatable>
    </div>

    {{-- Modal Tambah User --}}
    <x-ui.modal x-data='userFormModal({!! json_encode($rws) !!})' x-on:open-user-modal.window="openModal()" :isOpen="false"
        class="max-w-[700px]">
        <div
            class="relative w-full max-w-[700px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Tambah User Baru</h4>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Isi akun dan tentukan wilayah yang dapat diakses oleh
                user ini.</p>

            <form action="{{ route('settings.users.store') }}" method="POST" class="space-y-5">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Lengkap <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" required placeholder="Nama User"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email <span
                                class="text-red-500">*</span></label>
                        <input type="email" name="email" required placeholder="user@gmail.com"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Role Sistem <span
                                class="text-red-500">*</span></label>
                        <select name="role" x-model="role" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach ($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}
                                    {{ $r->name === 'User' ? '(Dibatasi Wilayah)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Password <span
                                class="text-red-500">*</span></label>
                        <input type="password" name="password" required placeholder="******"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Konfirmasi Password
                            <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required placeholder="******"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>

                {{-- Scope Wilayah Section --}}
                <div x-show="role.toLowerCase() != 'admin' && role.toLowerCase() != 'super admin'"
                    class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-semibold text-gray-800 dark:text-white">Pengaturan Hak Akses
                            Wilayah</label>
                        <button type="button" @click="addScope()"
                            class="text-xs bg-brand-50 text-brand-600 font-medium px-3 py-1.5 rounded-lg hover:bg-brand-100 transition">
                            + Tambah Wilayah Access
                        </button>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Pilih RW dan tentukan apakah user dapat
                        mengakses seluruh RT atau RT tertentu.</p>

                    <div class="space-y-3">
                        <template x-for="(scope, index) in scopes" :key="index">
                            <div
                                class="p-4 border border-gray-200 rounded-xl bg-gray-50/50 dark:border-gray-700 dark:bg-gray-800/40 relative">
                                <button type="button" @click="removeScope(index)"
                                    class="absolute top-3 right-3 text-red-500 hover:text-red-700 text-xs font-semibold">
                                    Hapus
                                </button>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Pilih
                                            RW</label>
                                        <select :name="'scopes[' + index + '][rw_id]'" x-model="scope.rw_id"
                                            @change="onRwChange(scope)"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                            <option value="">-- Pilih RW --</option>
                                            <template x-for="rwItem in masterRws" :key="rwItem.id">
                                                <option :value="rwItem.id"
                                                    x-text="'RW ' + rwItem.code + (rwItem.name ? ' (' + rwItem.name + ')' : '')">
                                                </option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Cakupan
                                            RT</label>
                                        <div
                                            class="max-h-36 overflow-y-auto custom-scrollbar border border-gray-200 rounded-lg p-2 bg-white dark:border-gray-700 dark:bg-gray-900 space-y-1">
                                            {{-- Item 'Semua RT' berada paling atas list --}}
                                            <label
                                                class="flex items-center gap-2 text-xs py-1 px-1 border-b border-gray-100 dark:border-gray-800 font-semibold text-brand-600 dark:text-brand-400">
                                                <input type="checkbox" x-model="scope.all_rts"
                                                    @change="if(scope.all_rts) scope.selected_rt_ids = []"
                                                    class="rounded text-brand-500" />
                                                <span>Semua RT pada RW ini</span>
                                            </label>

                                            {{-- Daftar RT individual --}}
                                            <template x-for="rtItem in getRtsForRw(scope.rw_id)" :key="rtItem.id">
                                                <label
                                                    class="flex items-center gap-2 text-xs py-1 px-1 rounded transition hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                                    :class="{ 'opacity-50 pointer-events-none': scope.all_rts }">

                                                    <input type="checkbox" :name="'scopes[' + index + '][rt_ids][]'"
                                                        :value="rtItem.id" x-model="scope.selected_rt_ids"
                                                        :disabled="scope.all_rts" class="rounded text-brand-500" />
                                                    <span class="text-gray-800 dark:text-white" x-text="'RT ' + rtItem.code + (rtItem.name ? ' (' + rtItem.name
                                                                        + ')' : '' )"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Batal</button>
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">Simpan User</button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    {{-- Modal Edit User --}}
    <x-ui.modal x-data='userFormModal({!! json_encode($rws) !!})'
        x-on:open-edit-user-modal.window="openEditModal($event.detail.data)" :isOpen="false" class="max-w-[700px]">
        <div
            class="relative w-full max-w-[700px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Ubah User & Hak Akses</h4>

            <form :action="'/settings/users/' + formData.id" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama
                            Lengkap</label>
                        <input type="text" name="name" x-model="formData.name" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
                        <input type="email" name="email" x-model="formData.email" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Role Sistem</label>
                        <select name="role" x-model="role" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach ($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}
                                    {{ $r->name === 'User' ? '(Dibatasi Wilayah)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Password Baru
                            (opsional)</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak diubah"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Konfirmasi Password
                            Baru</label>
                        <input type="password" name="password_confirmation" placeholder="Kosongkan jika tidak diubah"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>

                {{-- Scope Wilayah Section --}}
                <div x-show="role.toLowerCase() != 'admin' && role.toLowerCase() != 'super admin'"
                    class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-semibold text-gray-800 dark:text-white">Pengaturan Hak Akses
                            Wilayah</label>
                        <button type="button" @click="addScope()"
                            class="text-xs bg-brand-50 text-brand-600 font-medium px-3 py-1.5 rounded-lg hover:bg-brand-100 transition">
                            + Tambah Wilayah Access
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(scope, index) in scopes" :key="index">
                            <div
                                class="p-4 border border-gray-200 rounded-xl bg-gray-50/50 dark:border-gray-700 dark:bg-gray-800/40 relative">
                                <button type="button" @click="removeScope(index)"
                                    class="absolute top-3 right-3 text-red-500 hover:text-red-700 text-xs font-semibold">
                                    Hapus
                                </button>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Pilih
                                            RW</label>
                                        <select :name="'scopes[' + index + '][rw_id]'" x-model="scope.rw_id"
                                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                            <option value="">-- Pilih RW --</option>
                                            <template x-for="rwItem in masterRws" :key="rwItem.id">
                                                <option :value="rwItem.id"
                                                    x-text="'RW ' + rwItem.code + (rwItem.name ? ' (' + rwItem.name + ')' : '')">
                                                </option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Cakupan
                                            RT</label>
                                        <div
                                            class="max-h-36 overflow-y-auto custom-scrollbar border border-gray-200 rounded-lg p-2 bg-white dark:border-gray-700 dark:bg-gray-900 space-y-1">
                                            {{-- Item 'Semua RT' berada paling atas list --}}
                                            <label
                                                class="flex items-center gap-2 text-xs py-1 px-1 border-b border-gray-100 dark:border-gray-800 font-semibold text-brand-600 dark:text-brand-400">
                                                <input type="checkbox" x-model="scope.all_rts"
                                                    @change="if(scope.all_rts) scope.selected_rt_ids = []"
                                                    class="rounded text-brand-500" />
                                                <span>Semua RT pada RW ini</span>
                                            </label>

                                            {{-- Daftar RT individual --}}
                                            <template x-for="rtItem in getRtsForRw(scope.rw_id)" :key="rtItem.id">
                                                <label
                                                    class="flex items-center gap-2 text-xs py-1 px-1 rounded transition hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                                    :class="{ 'opacity-50 pointer-events-none': scope.all_rts }">
                                                    <input type="checkbox" :name="'scopes[' + index + '][rt_ids][]'"
                                                        :value="rtItem.id" x-model="scope.selected_rt_ids"
                                                        :disabled="scope.all_rts" class="rounded text-brand-500" />
                                                    <span class="text-gray-800 dark:text-white"
                                                        x-text="'RT ' + rtItem.code + (rtItem.name ? ' (' + rtItem.name + ')' : '')"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Batal</button>
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">Simpan
                        Perubahan</button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    <script>
        function userFormModal(masterRws) {
            return {
                open: false,
                role: 'user',
                masterRws: masterRws || [],
                formData: {},
                scopes: [],
                openModal() {
                    this.open = true;
                    this.role = 'User';
                    this.scopes = [{ rw_id: '', all_rts: true, selected_rt_ids: [] }];
                },
                openEditModal(user) {
                    this.open = true;
                    this.formData = user;
                    this.role = user.spatie_role || user.role;
                    this.scopes = [];

                    if (user.location_scopes && user.location_scopes.length > 0) {
                        const grouped = {};
                        user.location_scopes.forEach(s => {
                            if (!grouped[s.rw_id]) {
                                grouped[s.rw_id] = { rw_id: s.rw_id, all_rts: false, selected_rt_ids: [] };
                            }
                            if (s.rt_id === null) {
                                grouped[s.rw_id].all_rts = true;
                            } else {
                                grouped[s.rw_id].selected_rt_ids.push(s.rt_id);
                            }
                        });
                        this.scopes = Object.values(grouped);
                    } else {
                        this.scopes = [{ rw_id: '', all_rts: true, selected_rt_ids: [] }];
                    }
                },
                addScope() {
                    this.scopes.push({ rw_id: '', all_rts: true, selected_rt_ids: [] });
                },
                removeScope(index) {
                    this.scopes.splice(index, 1);
                },
                getRtsForRw(rwId) {
                    if (!rwId) return [];
                    const rw = this.masterRws.find(r => r.id == rwId);
                    return rw && rw.rts ? rw.rts : [];
                },
                onRwChange(scope) {
                    scope.selected_rt_ids = [];
                }
            }
        }

        function editUser(id) {
            fetch(`/settings/users/${id}/edit`)
                .then(res => res.json())
                .then(user => {
                    window.dispatchEvent(new CustomEvent('open-edit-user-modal', { detail: { data: user } }));
                });
        }

        function deleteUser(id, button) {
            if (!confirm('Yakin ingin menghapus user ini?')) return;
            fetch(`/settings/users/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => window.location.reload());
        }
    </script>

    {{-- Export Modal Component --}}
    <x-ui.export-modal :action="route('settings.users.export')" title="Ekspor Data User"
        description="Pilih format file dan filter pengguna yang ingin diekspor.">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Filter Role</label>
            <select name="role"
                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">Semua Role</option>
                <option value="super_admin">Super Admin</option>
                <option value="admin">Admin</option>
                <option value="rw">RW</option>
                <option value="rt">RT</option>
                <option value="user">User / Relawan</option>
            </select>
        </div>
    </x-ui.export-modal>

    {{-- Import Modal Component --}}
    <x-ui.import-modal module="user" :action="route('settings.users.import')" title="Impor Data User"
        description="Unggah file CSV / Excel untuk mengimpor atau memperbarui data akun pengguna." :validationRules="[
            '<strong>Nama Lengkap</strong>: Wajib diisi.',
            '<strong>Email</strong>: Email unik pengguna.',
            '<strong>Role</strong>: `admin`, `rw`, `rt`, atau `user` (default: `user`).',
            '<strong>Password</strong>: Password akun (default: `12345678`).',
        ]" />
@endsection