@props([
    'isLocked' => false,
])

@if ($isLocked)
    <div x-data="{
        init() {
            window.history.pushState(null, null, window.location.href);
            window.onpopstate = function () {
                window.history.pushState(null, null, window.location.href);
            };
        }
    }"
    @keydown.escape.window.prevent
    class="fixed inset-0 z-[99999] overflow-y-auto">

        {{-- Dark Backdrop with backdrop-blur --}}
        <div class="fixed inset-0 bg-gray-950/80 backdrop-blur-md transition-opacity"></div>

        {{-- Modal Dialog Container --}}
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6 relative z-10">
            <div class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white p-6 text-left shadow-2xl transition-all dark:bg-gray-900 dark:border dark:border-gray-800 sm:p-8">
                {{-- Lock Icon Badge --}}
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 shadow-inner dark:bg-amber-500/10 dark:text-amber-400">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>

                {{-- Title --}}
                <h3 class="mb-2 text-center text-xl font-bold text-gray-900 dark:text-white">
                    Layanan Sementara Tidak Tersedia
                </h3>

                {{-- Message --}}
                <p class="mb-6 text-center text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    Sistem belum dapat diakses karena proses aktivasi server belum selesai. Silakan hubungi administrator apabila memerlukan bantuan.
                </p>

                {{-- Logout Form & Button --}}
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-500/30 transition-all hover:bg-brand-600 focus:outline-hidden dark:bg-brand-600 dark:hover:bg-brand-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Tutup / Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif
