@php
    $user = auth()->user();
@endphp

@if ($user)
    @php
        $initials = strtoupper(substr($user->name, 0, 2));

        $roleLabels = [
            'super_admin' => 'Super Administrator',
            'admin'       => 'Administrator',
            'user'        => 'User Staff',
        ];

        $roleClasses = [
            'super_admin' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400 border border-purple-200 dark:border-purple-800',
            'admin'       => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400 border border-blue-200 dark:border-blue-800',
            'user'        => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800',
        ];

        $roleLabel = $roleLabels[$user->role] ?? ucfirst($user->role);
        $roleClass = $roleClasses[$user->role] ?? $roleClasses['user'];
        $scopes    = $user->isAdmin() ? collect() : $user->locationScopes()->with(['rw', 'rt'])->get();
    @endphp

    <x-ui.modal x-data="{ open: false }" x-on:open-profile-modal.window="open = true" :isOpen="false" class="max-w-[550px]">
        <div class="relative w-full max-w-[550px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
            {{-- Profile Header --}}
            <div class="flex items-center gap-4 pb-6 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-brand-500 text-white font-bold text-2xl shadow-theme-md shrink-0">
                    {{ $initials }}
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ $user->name }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $user->email }}
                    </p>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="inline-block text-xs font-semibold px-2.5 py-0.5 rounded-md {{ $roleClass }}">
                            {{ $roleLabel }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Aktif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Detail Profile Section --}}
            <div class="py-5 space-y-4">
                {{-- Account Details --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-gray-200/70 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 block">ID Pengguna</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-white">#{{ $user->id }}</span>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 flex items-center gap-3">
                        <div class="p-2 rounded-xl bg-gray-200/70 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 block">Terdaftar Sejak</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-white">{{ $user->created_at?->format('d M Y') ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Location Scope Access Details --}}
                <div class="pt-2">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">
                        Hak Akses Wilayah Operasional
                    </h5>

                    @if ($user->isAdmin())
                        <div class="p-4 rounded-2xl bg-blue-50/70 dark:bg-blue-500/10 border border-blue-200/80 dark:border-blue-800/50 flex items-start gap-3">
                            <div class="p-2.5 rounded-xl bg-blue-500 text-white shrink-0">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 9.009a6.002 6.002 0 0110.606-3.072 4.001 4.001 0 00-3.938 4.063 2 2 0 01-2 2h-.5c-.868 0-1.637.545-1.92 1.364A3.992 3.992 0 004.332 9.01z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h6 class="text-sm font-semibold text-blue-900 dark:text-blue-300">Akses Global Administrator</h6>
                                <p class="text-xs text-blue-700 dark:text-blue-400 mt-0.5 leading-relaxed">
                                    Sebagai {{ $roleLabel }}, Anda memiliki akses penuh ke seluruh data RW dan RT serta fitur pengelolaan sistem.
                                </p>
                            </div>
                        </div>
                    @elseif($scopes->isNotEmpty())
                        <div class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar">
                            @foreach ($scopes->groupBy('rw_id') as $rwId => $group)
                                @php $rw = $group->first()->rw; @endphp
                                <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-500/15 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold text-xs">
                                            RW
                                        </div>
                                        <div>
                                            <span class="text-sm font-bold text-gray-800 dark:text-white">
                                                RW {{ $rw?->code ?? '-' }}
                                            </span>
                                            @if ($rw?->name)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">({{ $rw->name }})</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        @if ($group->contains(fn($s) => is_null($s->rt_id)))
                                            <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">
                                                Semua RT
                                            </span>
                                        @else
                                            <div class="flex flex-wrap justify-end gap-1">
                                                @foreach ($group->pluck('rt.code')->filter() as $rtCode)
                                                    <span class="text-xs font-medium px-2 py-0.5 rounded-md bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                        RT {{ $rtCode }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-800/50 text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span>Belum ada wilayah RW/RT yang ditugaskan untuk akun ini. Hubungi Administrator jika membutuhkan akses wilayah.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Footer Button --}}
            <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                <button type="button" @click="open = false"
                    class="rounded-xl border border-gray-300 dark:border-gray-700 px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </x-ui.modal>
@endif
