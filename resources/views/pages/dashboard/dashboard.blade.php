@extends('layouts.app')

@section('content')
  <div x-data="{
      stats: {
          totalWarga: '{{ number_format($totalWarga) }}',
          totalToday: '{{ number_format($totalToday) }}',
          Militan: '{{ number_format($Militan) }}',
          Ngambang: '{{ number_format($Ngambang) }}',
          Lawan: '{{ number_format($Lawan) }}',
          totalTpsCount: '{{ number_format($totalTpsCount) }}',
          tpsSudahInput: '{{ number_format($tpsSudahInput) }}',
          tpsBelumInput: '{{ number_format($tpsBelumInput) }}',
          totalSuara: '{{ number_format($totalSuara) }}',
          totalPemilih: '{{ number_format($totalPemilih) }}',
          progressPercentage: {{ $progressPercentage }}
      },
      initPolling() {
          setInterval(() => {
              fetch('/api/dashboard/stats')
                  .then(res => res.json())
                  .then(data => {
                      this.stats = data;
                  })
                  .catch(() => {});
          }, 10000);
      }
  }" x-init="initPolling()" class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 space-y-6">

      {{-- Quick Count TPS Summary Widget Section --}}
      <div class="space-y-4 pt-2">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-white/90">Ringkasan Quick Count TPS</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Monitoring cepat progres input dan perolehan suara TPS</p>
          </div>
          <a href="{{ route('quick-counts.index') }}"
            class="inline-flex items-center gap-1 text-xs font-semibold text-brand-500 hover:text-brand-600 dark:text-brand-400">
            Lihat Detail Data Grid &rarr;
          </a>
        </div>

        {{-- Hero Grid Layout --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">

          {{-- Total Suara Masuk (LARGE HERO WIDGET) --}}
          <div
            class="col-span-1 sm:col-span-2 lg:col-span-4 xl:col-span-4 rounded-3xl border border-purple-300/40 bg-gradient-to-br from-purple-600 via-indigo-600 to-purple-800 p-7 text-white shadow-xl shadow-purple-500/20 dark:border-purple-800/60 dark:from-purple-900 dark:via-indigo-950 dark:to-purple-950">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
              <div class="flex items-center gap-5">
                <div
                  class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-md text-white border border-white/30 shadow-inner">
                  <svg class="h-11 w-11" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
                <div>
                  <div class="flex items-center gap-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-purple-200">Total Suara Masuk</p>
                    <span
                      class="inline-flex items-center rounded-full bg-emerald-400/20 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-300 backdrop-blur-xs border border-emerald-400/30">
                      LIVE HARI INI
                    </span>
                  </div>
                  <h4 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white mt-1">
                    <span x-text="stats.totalSuara">{{ number_format($totalSuara) }}</span> <span
                      class="text-lg sm:text-xl font-medium text-purple-200">suara</span>
                  </h4>
                  <p class="text-xs text-purple-200/80 mt-1">Total perolehan suara terkumpul dari seluruh TPS yang
                    terdaftar</p>
                </div>
              </div>
            </div>
          </div>

          {{-- Progress Quick Count (%) --}}
          <div
            class="col-span-1 sm:col-span-2 lg:col-span-2 xl:col-span-2 rounded-3xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-purple-50/50 p-7 shadow-xs dark:border-indigo-900/40 dark:from-gray-900 dark:to-indigo-950/40">
            <div class="flex items-center gap-4">
              <div
                class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-500 text-white shadow-md shadow-indigo-500/20 dark:bg-indigo-600">
                <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Progress
                  Quick Count</p>
                <h4 class="text-3xl sm:text-4xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">
                  <span x-text="stats.progressPercentage">{{ $progressPercentage }}</span>%
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"><span x-text="stats.tpsSudahInput">{{ $tpsSudahInput }}</span> dari <span x-text="stats.totalTpsCount">{{ $totalTpsCount }}</span>
                  TPS masuk</p>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total TPS --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
          <div class="flex items-center gap-3">
            <div
              class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total TPS</p>
              <h4 class="text-2xl font-bold text-gray-800 dark:text-white"><span x-text="stats.totalTpsCount">{{ number_format($totalTpsCount) }}</span> <span
                  class="text-xs font-normal text-gray-500">TPS</span></h4>
            </div>
          </div>
        </div>

        {{-- TPS Sudah Input --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
          <div class="flex items-center gap-3">
            <div
              class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500 dark:text-gray-400">TPS Sudah Input</p>
              <h4 class="text-2xl font-bold text-green-600 dark:text-green-400"><span x-text="stats.tpsSudahInput">{{ number_format($tpsSudahInput) }}</span>
                <span class="text-xs font-normal text-gray-500">TPS</span>
              </h4>
            </div>
          </div>
        </div>

        {{-- TPS Belum Input --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
          <div class="flex items-center gap-3">
            <div
              class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500 dark:text-gray-400">TPS Belum Input</p>
              <h4 class="text-2xl font-bold text-orange-600 dark:text-orange-400"><span x-text="stats.tpsBelumInput">{{ number_format($tpsBelumInput) }}</span>
                <span class="text-xs font-normal text-gray-500">TPS</span>
              </h4>
            </div>
          </div>
        </div>

        {{-- Total Pemilih TPS --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
          <div class="flex items-center gap-3">
            <div
              class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pemilih TPS</p>
              <h4 class="text-2xl font-bold text-gray-800 dark:text-white"><span x-text="stats.totalPemilih">{{ number_format($totalPemilih) }}</span></h4>
            </div>
          </div>
        </div>
      </div>

      {{-- Main Civil Metrics --}}
      <x-ecommerce.ecommerce-metrics :total="$totalWarga" :today="$totalToday" :militan="$Militan" :ngambang="$Ngambang"
        :lawan="$Lawan" />

    </div>
  </div>
@endsection