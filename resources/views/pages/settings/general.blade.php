@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pengaturan Umum" />

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-500/15 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/15 dark:text-red-400">
                <div class="font-medium">Migrasi gagal</div>
                <div class="mt-2 whitespace-pre-line">{{ session('error') }}</div>
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Migrasi Database</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Gunakan tombol di bawah untuk menjalankan migrasi secara
                manual dari panel admin.</p>

            <form action="{{ route('settings.migrate') }}" method="POST" class="mt-6">
                @csrf
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Jalankan Migrasi
                </button>
            </form>
        </div>
    </div>
@endsection
