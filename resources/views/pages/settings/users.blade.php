@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Manajemen User" />

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-500/15 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        <x-tables.data-grid title="Daftar User" description="Tambah, edit, dan atur role pengguna dari sini.">
            <x-slot name="toolbar">
                <form @submit.prevent>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative">
                            <button type="button" class="absolute -translate-y-1/2 left-4 top-1/2">
                            </button>

                            <input type="text" x-model.debounce.500ms="search" placeholder="Cari user..."
                                class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-[42px] pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[300px]" />
                        </div>
                    </div>
                </form>
            </x-slot>

            <x-slot name="actions">
                <button onclick="document.getElementById('user-form').classList.remove('hidden')"
                    class="inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-4 py-3 text-sm bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600 disabled:bg-brand-300">
                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M5.25012 3C5.25012 2.58579 5.58591 2.25 6.00012 2.25C6.41433 2.25 6.75012 2.58579 6.75012 3V5.25012L9.00034 5.25012C9.41455 5.25012 9.75034 5.58591 9.75034 6.00012C9.75034 6.41433 9.41455 6.75012 9.00034 6.75012H6.75012V9.00034C6.75012 9.41455 6.41433 9.75034 6.00012 9.75034C5.58591 9.75034 5.25012 9.41455 5.25012 9.00034L5.25012 6.75012H3C2.58579 6.75012 2.25 6.41433 2.25 6.00012C2.25 5.58591 2.58579 5.25012 3 5.25012H5.25012V3Z"
                            fill=""></path>
                    </svg>
                    Tambah
                </button>
            </x-slot>

            <x-slot name="pagination">
                <div class="px-6 py-4 border-t border-gray-200 dark:border-white/[0.05]">
                    <div class="flex items-center justify-between">
                        <button @click="prevPage" :disabled="currentPage === 1"
                            :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''"
                            class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:px-3.5">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M2.58301 9.99868C2.58272 10.1909 2.65588 10.3833 2.80249 10.53L7.79915 15.5301C8.09194 15.8231 8.56682 15.8233 8.85981 15.5305C9.15281 15.2377 9.15297 14.7629 8.86018 14.4699L5.14009 10.7472L16.6675 10.7472C17.0817 10.7472 17.4175 10.4114 17.4175 9.99715C17.4175 9.58294 17.0817 9.24715 16.6675 9.24715L5.14554 9.24715L8.86017 5.53016C9.15297 5.23717 9.15282 4.7623 8.85983 4.4695C8.56684 4.1767 8.09197 4.17685 7.79917 4.46984L2.84167 9.43049C2.68321 9.568 2.58301 9.77087 2.58301 9.99715C2.58301 9.99766 2.58301 9.99817 2.58301 9.99868Z"
                                    fill="currentColor" />
                            </svg>
                            <span class="hidden sm:inline">Sebelumnya</span>
                        </button>

                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-400 sm:hidden">
                            Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                        </span>

                        <ul class="hidden items-center gap-0.5 sm:flex">
                            <template x-for="page in displayedPages" :key="page">
                                <li>
                                    <button x-show="page !== '...'" @click="goToPage(page)"
                                        :class="currentPage === page ? 'bg-blue-500 text-white' :
                                            'text-gray-700 hover:bg-blue-500/[0.08] hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500'"
                                        class="flex h-10 w-10 items-center justify-center rounded-lg text-theme-sm font-medium"
                                        x-text="page"></button>
                                    <span x-show="page === '...'"
                                        class="flex h-10 w-10 items-center justify-center text-gray-500">...</span>
                                </li>
                            </template>
                        </ul>

                        <button @click="nextPage" :disabled="currentPage === totalPages"
                            :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''"
                            class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:px-3.5">
                            <span class="hidden sm:inline">Selanjutnya</span>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M17.4175 9.9986C17.4178 10.1909 17.3446 10.3832 17.198 10.53L12.2013 15.5301C11.9085 15.8231 11.4337 15.8233 11.1407 15.5305C10.8477 15.2377 10.8475 14.7629 11.1403 14.4699L14.8604 10.7472L3.33301 10.7472C2.91879 10.7472 2.58301 10.4114 2.58301 9.99715C2.58301 9.58294 2.91879 9.24715 3.33301 9.24715L14.8549 9.24715L11.1403 5.53016C10.8475 5.23717 10.8477 4.7623 11.1407 4.4695C11.4336 4.1767 11.9085 4.17685 12.2013 4.46984L17.1588 9.43049C17.3173 9.568 17.4175 9.77087 17.4175 9.99715C17.4175 9.99763 17.4175 9.99812 17.4175 9.9986Z"
                                    fill="currentColor" />
                            </svg>
                        </button>
                    </div>
                </div>
            </x-slot>

            <thead
                class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                        Nama</th>
                    <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                        Email</th>
                    <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                        Role</th>
                    <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                        Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                        <td class="px-4 sm:px-6 py-3.5">
                            <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                                {{ $user->name }}</p>
                        </td>
                        <td class="px-4 sm:px-6 py-3.5">
                            <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                                {{ $user->email }}</p>
                        </td>
                        <td class="py-3">
                            <span
                                class="rounded-full px-2 py-1 text-xs font-medium {{ $user->role === 'admin' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $user->role }}
                            </span>
                        </td>


                        <td class="px-4 sm:px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <button @click="editUser({{ $user->id }})">
                                    <svg class="text-gray-700 cursor-pointer size-5 hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                @if (auth()->id() !== $user->id)
                                    <form action="{{ route('settings.users.destroy', $user->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-tables.data-grid>

        <form id="user-form" action="{{ route('settings.users.store') }}" method="POST"
            class="mt-6 hidden rounded-xl border border-gray-200 p-4 dark:border-gray-700">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama</label>
                    <input type="text" name="name" required
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
                    <input type="email" name="email" required
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Password</label>
                    <input type="password" name="password" required
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Role</label>
                    <select name="role" class="h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex gap-3">
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white">Simpan</button>
                <button type="button" onclick="document.getElementById('user-form').classList.add('hidden')"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700">Batal</button>
            </div>
        </form>
    </div>

    <script>
        function editUser(id) {
            fetch(`/settings/users/${id}/edit`)
                .then(res => res.json())
                .then(user => {
                    const form = document.getElementById('user-form');
                    form.classList.remove('hidden');
                    form.action = `/settings/users/${id}`;
                    form.querySelector('input[name="name"]').value = user.name;
                    form.querySelector('input[name="email"]').value = user.email;
                    form.querySelector('select[name="role"]').value = user.role;
                    const passwordInput = form.querySelector('input[name="password"]');
                    passwordInput.required = false;
                    passwordInput.value = '';
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    if (!form.querySelector('input[name="_method"]')) {
                        form.appendChild(methodInput);
                    } else {
                        form.querySelector('input[name="_method"]').value = 'PUT';
                    }
                });
        }
    </script>
@endsection
