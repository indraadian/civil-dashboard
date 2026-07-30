@php
    $user = auth()->user();
    if (!$user) return;

    $initials = strtoupper(substr($user->name, 0, 2));

    $roleLabels = [
        'super_admin' => 'Super Admin',
        'admin'       => 'Admin',
        'user'        => 'User Staff',
    ];

    $roleClasses = [
        'super_admin' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400 border border-purple-200 dark:border-purple-800',
        'admin'       => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400 border border-blue-200 dark:border-blue-800',
        'user'        => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800',
    ];

    $roleLabel = $roleLabels[$user->role] ?? ucfirst($user->role);
    $roleClass = $roleClasses[$user->role] ?? $roleClasses['user'];

    $scopes = $user->isAdmin() ? collect() : $user->locationScopes()->with(['rw', 'rt'])->get();
@endphp

<div class="relative"
    x-data="{
        dropdownOpen: false,
        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;
        },
        closeDropdown() {
            this.dropdownOpen = false;
        }
    }"
    @click.away="closeDropdown()">

    <!-- Profile Trigger Button -->
    <button @click="toggleDropdown()"
        class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:outline-none">
        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-brand-500 text-white font-bold text-sm shadow-theme-xs">
            {{ $initials }}
        </div>
        <div class="hidden sm:flex flex-col text-left">
            <span class="text-xs font-semibold text-gray-800 dark:text-white/90 leading-tight">
                {{ $user->name }}
            </span>
            <span class="text-[11px] text-gray-500 dark:text-gray-400">
                {{ $roleLabel }}
            </span>
        </div>
        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
            :class="{ 'rotate-180': dropdownOpen }"
            viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="dropdownOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-72 rounded-2xl bg-white p-3 shadow-theme-lg border border-gray-100 dark:border-gray-800 dark:bg-gray-900 z-99999">
        
        <!-- User Info Card Header -->
        <div class="p-3 mb-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-11 h-11 rounded-full bg-brand-500 text-white font-bold text-base shadow-sm shrink-0">
                    {{ $initials }}
                </div>
                <div class="overflow-hidden">
                    <h5 class="text-sm font-bold text-gray-800 dark:text-white truncate">
                        {{ $user->name }}
                    </h5>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ $user->email }}
                    </p>
                    <span class="inline-block mt-1 text-[10px] font-medium px-2 py-0.5 rounded-md {{ $roleClass }}">
                        {{ $roleLabel }}
                    </span>
                </div>
            </div>

            <!-- Scope Ringkasan -->
            <div class="mt-3 pt-2.5 border-t border-gray-200/60 dark:border-gray-700/60 text-xs">
                <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400 block mb-1">Cakupan Wilayah:</span>
                @if ($user->isAdmin())
                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-blue-600 dark:text-blue-400">
                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 9.009a6.002 6.002 0 0110.606-3.072 4.001 4.001 0 00-3.938 4.063 2 2 0 01-2 2h-.5c-.868 0-1.637.545-1.92 1.364A3.992 3.992 0 004.332 9.01z" clip-rule="evenodd" />
                        </svg>
                        Akses Penuh (Seluruh Wilayah)
                    </span>
                @elseif($scopes->isNotEmpty())
                    <div class="max-h-20 overflow-y-auto space-y-1 custom-scrollbar">
                        @foreach ($scopes->groupBy('rw_id') as $rwId => $group)
                            @php $rw = $group->first()->rw; @endphp
                            <div class="text-[11px] text-gray-700 dark:text-gray-300">
                                <span class="font-semibold text-brand-600 dark:text-brand-400">RW {{ $rw?->code ?? '-' }}</span>:
                                @if ($group->contains(fn($s) => is_null($s->rt_id)))
                                    <span class="text-gray-500">Semua RT</span>
                                @else
                                    <span class="text-gray-500">RT {{ $group->pluck('rt.code')->filter()->implode(', RT ') }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <span class="text-[11px] text-red-500">Belum ada wilayah ditugaskan</span>
                @endif
            </div>
        </div>

        <!-- Menu Links -->
        <div class="space-y-1">
            <button @click="$dispatch('open-profile-modal'); closeDropdown();"
                class="flex w-full items-center gap-3 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
                Profil Saya
            </button>

            @if ($user->isAdmin())
                <a href="{{ route('settings') }}"
                    class="flex w-full items-center gap-3 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.532 1.532 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.532 1.532 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                    Pengaturan Sistem
                </a>
            @endif
        </div>

        <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

        <!-- Logout Form -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="flex w-full items-center gap-3 px-3 py-2 text-xs font-medium text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                <svg class="w-4 h-4 text-red-500 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 001 1h5a1 1 0 100-2H4V5h4a1 1 0 100-2H3zm9.707 3.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H7a1 1 0 110-2h7.586l-1.879-1.879a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                Keluar
            </button>
        </form>
    </div>
</div>