{{-- DataTable Toolbar: Search + Bulk Actions --}}
@props(['config' => []])

<form @submit.prevent>
    <div class="space-y-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            {{-- Search Input --}}
            <div class="relative flex-1">
                <button type="button" class="absolute left-4 top-1/2 -translate-y-1/2">
                    <svg class="text-gray-400" width="20" height="20" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                <input type="text" x-model.debounce.500ms="search" placeholder="Cari..."
                    class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-[42px] pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[300px]" />
            </div>

            {{-- Column Visibility Toggle --}}
            <div class="relative">
                <button type="button" @click="showColumnToggle = !showColumnToggle"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03]"
                    :class="showColumnToggle ? 'ring-2 ring-blue-500/20 border-blue-300 dark:border-blue-700' : ''">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span class="hidden sm:inline">Kolom</span>
                </button>

                {{-- Column Toggle Dropdown --}}
                <div x-show="showColumnToggle" x-cloak @click.outside="showColumnToggle = false"
                    x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <div
                        class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Tampilkan Kolom
                    </div>
                    <div class="max-h-64 overflow-y-auto p-2 pt-0">
                        @foreach ($config['columns'] ?? [] as $column)
                            @if (($column['type'] ?? '') !== 'action' && ($column['toggleable'] ?? true))
                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]">
                                    <div @click="toggleColumn('{{ $column['field'] }}')"
                                        class="flex h-4 w-4 items-center justify-center rounded border transition-colors"
                                        :class="isColumnVisible('{{ $column['field'] }}') ? 'border-blue-500 bg-blue-500' : 'border-gray-300 dark:border-gray-600'">
                                        <svg x-show="isColumnVisible('{{ $column['field'] }}')" class="h-3 w-3 text-white"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span>{{ $column['label'] }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Bulk Action Buttons --}}
@foreach ($config['bulkActions'] ?? [] as $bulkAction)
    <button x-show="selectedRows.length > 0" x-cloak @click="executeBulkAction({{ Js::from($bulkAction) }})"
        class="inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-4 py-3 text-sm bg-red-500 text-white shadow-theme-xs hover:bg-red-700 disabled:bg-red-300">
        <svg class="cursor-pointer" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        {{ $bulkAction['label'] ?? 'Hapus' }}
    </button>
@endforeach

{{-- Extra toolbar content from parent --}}
{{ $slot }}