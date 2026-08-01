@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pengaturan Sistem & Maintenance" />

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-500/15 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/15 dark:text-red-400">
                <div class="font-medium">Terjadi Kesalahan</div>
                <div class="mt-2 whitespace-pre-line">{{ session('error') }}</div>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Migrasi Database</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Gunakan tombol di bawah untuk menjalankan migrasi database secara manual dari panel Super Admin.</p>

                <form action="{{ route('settings.migrate') }}" method="POST" class="mt-6">
                    @csrf
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">
                        Jalankan Migrasi
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Patch Master RW & RT</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Sinkronisasi otomatis membuat data Master RW & RT berdasarkan data Warga (Civil) yang ada. Aman dijalankan berulang kali (idempotent).</p>

                <form action="{{ route('settings.patch-locations') }}" method="POST" class="mt-6">
                    @csrf
                    <button type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 transition">
                        Patch Master RW & RT
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Sync Role & Permission</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Sinkronisasi otomatis role dasar (Super Admin, Admin, User) dan pemetaan hak akses permission modul aplikasi.</p>

                <form action="{{ route('settings.sync-roles-permissions') }}" method="POST" class="mt-6">
                    @csrf
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 transition">
                        Sync Role & Permission
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
