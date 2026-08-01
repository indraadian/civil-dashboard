@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Daftar Permission System" />

    <div class="space-y-6">

        @if (session('success'))
            <div class="flex items-center gap-3 rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-500/15 dark:text-green-400">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm3.707 7.707l-4.5 4.5a1 1 0 01-1.414 0l-2-2a1 1 0 111.414-1.414L8.5 11.586l3.793-3.793a1 1 0 111.414 1.414z" fill="currentColor" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Info Banner --}}
        <div class="flex items-start gap-3 rounded-2xl border border-blue-200 bg-blue-50/60 p-4 text-sm text-blue-800 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-300">
            <svg class="h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h6 class="font-semibold text-blue-900 dark:text-blue-200">Manajemen Permission Sistem</h6>
                <p class="mt-0.5 text-xs text-blue-700 dark:text-blue-300">
                    Daftar permission dihasilkan secara otomatis dari definisi modul. Tekan tombol <strong>Sync Permission</strong> di bawah untuk mensinkronkan permission jika terdapat penambahan fitur/modul baru.
                </p>
            </div>
        </div>

        {{-- Configuration-Driven DataTable --}}
        <x-datatable :config="$config" data-url="{{ route('settings.permissions.data') }}" base-url="/settings/permissions"
            title="Master Permission System" description="Daftar seluruh hak akses yang terdaftar di aplikasi" />

    </div>
@endsection
