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
          totalSuaraSah: '{{ number_format($totalSuaraSah, 0, ',', '.') }}',
          totalSuaraTidakSah: '{{ number_format($totalSuaraTidakSah, 0, ',', '.') }}',
          totalPemilih: '{{ number_format($totalPemilih, 0, ',', '.') }}',
          progressPercentage: {{ $progressPercentage }},
          candidateVotes: {{ Js::from(array_map(fn($v) => number_format($v, 0, ',', '.'), $candidateVotesMap)) }},
          recentQuickCounts: []
      },
      initPolling() {
          setInterval(() => {
              fetch('/api/dashboard/stats')
                  .then(res => res.json())
                  .then(data => {
                      this.stats = data;
                  })
                  .catch(() => {});
          }, 5000);
      }
  }" x-init="initPolling()" class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 space-y-6">

      {{-- Quick Count TPS Summary Widget Section --}}
      <div class="space-y-4 pt-2">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-white/90">Monitoring Quick Count Realtime</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Monitoring perolehan suara pasangan calon per TPS secara live</p>
          </div>
          <a href="{{ route('quick-counts.index') }}"
            class="inline-flex items-center gap-1 text-xs font-semibold text-brand-500 hover:text-brand-600 dark:text-brand-400">
            Kelola & Input Data Grid &rarr;
          </a>
        </div>

        {{-- Dynamic Per-Candidate Vote Summary Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          @foreach ($candidates as $candidate)
            <div class="rounded-3xl border border-brand-200 bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 p-6 text-white shadow-xl dark:border-brand-800/60 dark:from-brand-900 dark:to-brand-950">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                  @if ($candidate->photo_url)
                    <img src="{{ $candidate->photo_url }}" class="h-16 w-16 rounded-2xl object-cover border-2 border-white/40 shadow-inner" />
                  @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 text-2xl font-black border border-white/30">
                      #{{ $candidate->number }}
                    </div>
                  @endif
                  <div>
                    <span class="inline-flex items-center rounded-full bg-white/20 px-2.5 py-0.5 text-[10px] font-bold tracking-wider uppercase text-white backdrop-blur-xs">
                      PASLON {{ $candidate->number }}
                    </span>
                    <h4 class="text-3xl font-black text-white mt-1">
                      <span x-text="stats.candidateVotes[{{ $candidate->id }}] || '0'">{{ number_format($candidateVotesMap[$candidate->id] ?? 0, 0, ',', '.') }}</span>
                      <span class="text-sm font-normal opacity-80">suara</span>
                    </h4>
                    <p class="text-xs text-white/80 font-medium truncate max-w-[140px]">{{ $candidate->name }}</p>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        {{-- Quick Count Metric Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {{-- Total Suara Sah --}}
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Suara Sah</p>
                <h4 class="text-2xl font-bold text-green-600 dark:text-green-400"><span x-text="stats.totalSuaraSah">{{ number_format($totalSuaraSah, 0, ',', '.') }}</span></h4>
              </div>
            </div>
          </div>

          {{-- Suara Tidak Sah --}}
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Suara Tidak Sah</p>
                <h4 class="text-2xl font-bold text-red-600 dark:text-red-400"><span x-text="stats.totalSuaraTidakSah">{{ number_format($totalSuaraTidakSah, 0, ',', '.') }}</span></h4>
              </div>
            </div>
          </div>

          {{-- Total Pemilih TPS --}}
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pengguna Hak Pilih</p>
                <h4 class="text-2xl font-bold text-gray-800 dark:text-white"><span x-text="stats.totalPemilih">{{ number_format($totalPemilih, 0, ',', '.') }}</span></h4>
              </div>
            </div>
          </div>

          {{-- Progress TPS --}}
          <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Progress TPS (<span x-text="stats.tpsSudahInput">{{ $tpsSudahInput }}</span>/<span x-text="stats.totalTpsCount">{{ $totalTpsCount }}</span>)</p>
                <h4 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400"><span x-text="stats.progressPercentage">{{ $progressPercentage }}</span>%</h4>
              </div>
            </div>
          </div>
        </div>

        {{-- Realtime Monitoring Live Table (Ordered by updated_at DESC) --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h4 class="text-lg font-bold text-gray-800 dark:text-white">Live Feed Realtime Perolehan Suara TPS</h4>
              <p class="text-xs text-gray-500 dark:text-gray-400">Urutan teratas merupakan data TPS yang paling baru diperbarui</p>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
              <span class="h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
              Auto-updating
            </span>
          </div>

          <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
              <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                  <th scope="col" class="px-4 py-3">TPS</th>
                  <th scope="col" class="px-4 py-3">Petugas</th>
                  <th scope="col" class="px-4 py-3">No. HP</th>
                  <th scope="col" class="px-4 py-3">Suara Sah</th>
                  <th scope="col" class="px-4 py-3">Tidak Sah</th>
                  <th scope="col" class="px-4 py-3">Total Pemilih</th>
                  <th scope="col" class="px-4 py-3">Waktu Update</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                <template x-if="stats.recentQuickCounts && stats.recentQuickCounts.length > 0">
                  <template x-for="item in stats.recentQuickCounts" :key="item.id">
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
                      <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white" x-text="item.tps_name"></td>
                      <td class="px-4 py-3 text-gray-800 dark:text-gray-200" x-text="item.officer_name"></td>
                      <td class="px-4 py-3 text-gray-600 dark:text-gray-400" x-text="item.officer_phone"></td>
                      <td class="px-4 py-3 font-bold text-green-600 dark:text-green-400" x-text="item.valid_votes"></td>
                      <td class="px-4 py-3 font-bold text-red-600 dark:text-red-400" x-text="item.invalid_votes"></td>
                      <td class="px-4 py-3 text-gray-900 dark:text-white font-medium" x-text="item.total_voters"></td>
                      <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400" x-text="item.updated_at"></td>
                    </tr>
                  </template>
                </template>
                <template x-if="!stats.recentQuickCounts || stats.recentQuickCounts.length === 0">
                  @forelse ($recentQuickCounts as $qc)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
                      <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $qc->tps->name ?? '-' }}</td>
                      <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $qc->officer_name }}</td>
                      <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $qc->officer_phone }}</td>
                      <td class="px-4 py-3 font-bold text-green-600 dark:text-green-400">{{ number_format($qc->details->sum('vote_count'), 0, ',', '.') }}</td>
                      <td class="px-4 py-3 font-bold text-red-600 dark:text-red-400">{{ number_format($qc->invalid_votes, 0, ',', '.') }}</td>
                      <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">{{ number_format($qc->total_voters, 0, ',', '.') }}</td>
                      <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $qc->updated_at->format('H:i:s') }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="px-4 py-6 text-center text-gray-400">Belum ada data Quick Count TPS yang diinput.</td>
                    </tr>
                  @endforelse
                </template>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      {{-- Main Civil Metrics --}}
      <x-ecommerce.ecommerce-metrics :total="$totalWarga" :today="$totalToday" :militan="$Militan" :ngambang="$Ngambang"
        :lawan="$Lawan" />

    </div>
  </div>
@endsection