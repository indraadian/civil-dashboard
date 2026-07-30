@props([
    'action' => null,
    'title' => 'Ekspor Data Warga',
    'description' => 'Pilih format dan filter data yang ingin diekspor ke file.',
    'eventName' => 'open-export-modal',
    'formats' => [
        'xlsx' => 'Excel (.xlsx)',
        'csv' => 'CSV (.csv)',
    ],
])

@php
    $targetAction = $action ?? (Route::has('civils.export') ? route('civils.export') : '#');
@endphp

<x-ui.modal x-data="{ open: false, format: '{{ array_key_first($formats) }}', status: '', hamlet: '', rt: '', rw: '' }"
    :isOpen="false" class="max-w-[550px]">
    <div x-on:{{ $eventName }}.window="open = true" class="no-scrollbar relative w-full max-w-[550px] overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-9">

        <div class="px-1 pr-10">
            <h4 class="mb-1 text-2xl font-semibold text-gray-800 dark:text-white/90">
                {{ $title }}
            </h4>
            <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">
                {{ $description }}
            </p>
        </div>

        <form action="{{ $targetAction }}" method="POST"
            @submit="open = false; $dispatch('toast', { title: 'Ekspor Dimulai', message: 'Proses generasi file ekspor telah berjalan di background.', type: 'info' })">
            @csrf

            <div class="space-y-4">
                {{-- Format Select --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Format File <span class="text-red-500">*</span>
                    </label>
                    <select name="format" x-model="format" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @foreach ($formats as $val => $label)
                            <option value="{{ $val }}" class="dark:bg-gray-900">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Slot Filter Spesifik Modul --}}
                @if ($slot->isNotEmpty())
                    <div class="space-y-4 pt-1">
                        {{ $slot }}
                    </div>
                @else
                    {{-- Default Civil Filters (Fallback if slot empty) --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Filter Status
                        </label>
                        <select name="status" x-model="status"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="" class="dark:bg-gray-900">Semua Status</option>
                            <option value="Militan" class="dark:bg-gray-900">Militan</option>
                            <option value="Ngambang" class="dark:bg-gray-900">Ngambang</option>
                            <option value="Lawan" class="dark:bg-gray-900">Lawan</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Filter Dusun
                        </label>
                        <input type="text" name="hamlet" x-model="hamlet" placeholder="e.g. Dusun 1 (opsional)"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Filter RT
                            </label>
                            <input type="text" name="rt" x-model="rt" placeholder="e.g. 001 (opsional)"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Filter RW
                            </label>
                            <input type="text" name="rw" x-model="rw" placeholder="e.g. 002 (opsional)"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                    </div>
                @endif

                {{-- Background Processing Note --}}
                <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-3.5 dark:border-blue-500/20 dark:bg-blue-500/10">
                    <div class="flex items-start gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs text-blue-700 dark:text-blue-300">
                            Proses ekspor berjalan di background. Anda dapat melanjutkan aktivitas lain tanpa menunggu dialog ini.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 lg:justify-end">
                <button @click="open = false" type="button"
                    class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">
                    Batal
                </button>
                <button type="submit"
                    class="flex w-full justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                    Mulai Ekspor
                </button>
            </div>
        </form>
    </div>
</x-ui.modal>
