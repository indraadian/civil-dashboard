@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pengaturan Sistem" />

    <div class="space-y-6">
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('settings.general') }}"
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:border-brand-500 dark:border-gray-700 dark:bg-gray-900 transition">
                    <div class="flex items-center gap-3">
                        <span class="rounded-xl bg-purple-50 p-3 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                            ⚙️
                        </span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Maintenance & Migrasi</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Migrasi database & Patch Master RW/RT (Super Admin).</p>
                        </div>
                    </div>
                </a>
            @endif

            <a href="{{ route('settings.users') }}"
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:border-brand-500 dark:border-gray-700 dark:bg-gray-900 transition">
                <div class="flex items-center gap-3">
                    <span class="rounded-xl bg-blue-50 p-3 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        👥
                    </span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Manajemen User</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kelola akun pengguna, role, dan hak akses wilayah.</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('settings.rws') }}"
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:border-brand-500 dark:border-gray-700 dark:bg-gray-900 transition">
                <div class="flex items-center gap-3">
                    <span class="rounded-xl bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                        🏢
                    </span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Master RW</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kelola master data Rukun Warga (RW).</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('settings.rts') }}"
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:border-brand-500 dark:border-gray-700 dark:bg-gray-900 transition">
                <div class="flex items-center gap-3">
                    <span class="rounded-xl bg-amber-50 p-3 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                        🏠
                    </span>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Master RT</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kelola master data Rukun Tetangga (RT) per RW.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection
