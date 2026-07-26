@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pengaturan" />

    <div class="space-y-6">
        <div class="grid gap-6 lg:grid-cols-2">
            <a href="{{ route('settings.general') }}"
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Umum</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Kelola setting umum aplikasi dan migrasi database.
                </p>
            </a>

            <a href="{{ route('settings.users') }}"
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">User</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Lihat, tambah, ubah, dan hapus akun pengguna
                    beserta role-nya.</p>
            </a>
        </div>
    </div>
@endsection
