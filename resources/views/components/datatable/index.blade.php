{{-- DataTable Main Component --}}
@props([
    'config' => [],
    'dataUrl' => '',
    'baseUrl' => '',
    'title' => '',
    'description' => '',
])

@php
    $jsonConfig = array_merge($config, [
        'dataUrl' => $dataUrl,
        'baseUrl' => $baseUrl,
    ]);
@endphp

<div x-data="dataTableEngine({{ Js::from($jsonConfig) }})" x-init="init()">
    <x-tables.data-grid :title="$title" :description="$description">

        {{-- Toolbar: Search + Bulk Actions --}}
        <x-slot name="toolbar">
            <x-datatable.toolbar :config="$config">
                @if (isset($toolbarExtra))
                    {{ $toolbarExtra }}
                @endif
            </x-datatable.toolbar>
        </x-slot>

        {{-- Actions: Total count + Custom buttons --}}
        <x-slot name="actions">
            <span class="text-sm text-gray-500 dark:text-gray-400">
                Total: <span class="font-bold text-gray-700 dark:text-white" x-text="totalData"></span> data
            </span>
            @if (isset($toolbarActions))
                {{ $toolbarActions }}
            @endif
        </x-slot>

        {{-- Filters --}}
        <x-slot name="filters">
            <x-datatable.filters :config="$config" />
        </x-slot>

        {{-- Pagination --}}
        <x-slot name="pagination">
            <x-datatable.pagination />
        </x-slot>

        {{-- Table Head --}}
        <thead class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
            <tr>
                {{-- Checkbox column (if has bulk actions) --}}
                @if (count($config['bulkActions'] ?? []) > 0)
                    <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                        <div class="flex items-center gap-3">
                            <div @click="handleSelectAll()"
                                class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]"
                                :class="selectAll ? 'border-blue-500 dark:border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                <svg :class="selectAll ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">ID</span>
                        </div>
                    </th>
                @endif

                {{-- Dynamic columns --}}
                @foreach ($config['columns'] ?? [] as $column)
                    @if ($column['type'] !== 'action')
                        <th x-show="isColumnVisible('{{ $column['field'] }}')"
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start"
                            @if ($column['sortable'] ?? false)
                                @click="toggleSort('{{ $column['field'] }}')"
                                style="cursor: pointer;"
                            @endif
                        >
                            <div class="flex items-center gap-1">
                                {{ $column['label'] }}
                                @if ($column['sortable'] ?? false)
                                    <span class="inline-flex flex-col">
                                        <svg x-show="getSortIcon('{{ $column['field'] }}') === 'asc'" class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M7 14l5-5 5 5z"/></svg>
                                        <svg x-show="getSortIcon('{{ $column['field'] }}') === 'desc'" class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
                                        <svg x-show="getSortIcon('{{ $column['field'] }}') === 'none'" class="w-3 h-3 text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M7 10l5-5 5 5M7 14l5 5 5-5" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
                                    </span>
                                @endif
                            </div>
                        </th>
                    @else
                        <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">...</th>
                    @endif
                @endforeach
            </tr>
        </thead>

        {{-- Table Body --}}
        <tbody class="relative">
            {{-- Loading Overlay (when refreshing with existing data) --}}
            <tr x-show="loading && rows.length > 0" x-cloak>
                <td colspan="{{ count($config['columns'] ?? []) + (count($config['bulkActions'] ?? []) > 0 ? 1 : 0) }}"
                    class="absolute inset-0 z-10 flex items-center justify-center">
                    <div class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 backdrop-blur-[1px]"></div>
                    <div class="relative flex items-center gap-2 rounded-lg bg-white px-4 py-2 shadow-lg dark:bg-gray-800">
                        <svg class="h-5 w-5 animate-spin text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Memuat...</span>
                    </div>
                </td>
            </tr>

            {{-- Loading State (initial / empty) --}}
            <template x-if="loading && rows.length === 0">
                <tr>
                    <td colspan="{{ count($config['columns'] ?? []) + (count($config['bulkActions'] ?? []) > 0 ? 1 : 0) }}"
                        class="px-6 py-12 text-center">
                        <x-datatable.loading />
                    </td>
                </tr>
            </template>

            {{-- Empty State --}}
            <template x-if="!loading && rows.length === 0">
                <tr>
                    <td colspan="{{ count($config['columns'] ?? []) + (count($config['bulkActions'] ?? []) > 0 ? 1 : 0) }}"
                        class="px-6 py-12 text-center">
                        <x-datatable.empty-state />
                    </td>
                </tr>
            </template>

            {{-- Data Rows --}}
            <template x-for="row in rows" :key="row.id">
                <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                    {{-- Checkbox --}}
                    @if (count($config['bulkActions'] ?? []) > 0)
                        <td class="px-4 sm:px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <div @click="handleRowSelect(row.id)"
                                    class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]"
                                    :class="isSelected(row.id) ? 'border-blue-500 dark:border-blue-500 bg-blue-500' : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                    <svg :class="isSelected(row.id) ? 'block' : 'hidden'" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.id"></span>
                            </div>
                        </td>
                    @endif

                    {{-- Dynamic Columns --}}
                    @foreach ($config['columns'] ?? [] as $column)
                        @if ($column['type'] === 'text')
                            <td x-show="isColumnVisible('{{ $column['field'] }}')" class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400"
                                   x-text="getColumnValue(row, {{ Js::from($column) }})"></p>
                            </td>
                        @elseif ($column['type'] === 'avatar')
                            <td x-show="isColumnVisible('{{ $column['field'] }}')" class="px-4 sm:px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full font-medium text-sm"
                                        :class="[row['{{ $column['field'] }}__avatar_bg'] || 'bg-gray-100', row['{{ $column['field'] }}__avatar_color'] || 'text-gray-500']">
                                        <span x-text="row['{{ $column['field'] }}__initials'] || ''"></span>
                                    </div>
                                    <div>
                                        <span class="mb-0.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400"
                                              x-text="row['{{ $column['field'] }}'] || '-'"></span>
                                    </div>
                                </div>
                            </td>
                        @elseif ($column['type'] === 'date')
                            <td x-show="isColumnVisible('{{ $column['field'] }}')" class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400"
                                   x-text="formatDate(row['{{ $column['field'] }}'], {{ Js::from($column) }})"></p>
                            </td>
                        @elseif ($column['type'] === 'badge')
                            <td x-show="isColumnVisible('{{ $column['field'] }}')" class="px-4 sm:px-6 py-3.5">
                                @php
                                    $badgeMapping = $column['mapping'] ?? [];
                                    $colorClassMap = [
                                        'primary' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
                                        'success' => 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-500',
                                        'error' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
                                        'warning' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-orange-400',
                                        'info' => 'bg-sky-50 text-sky-500 dark:bg-sky-500/15 dark:text-sky-500',
                                        'light' => 'bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-white/80',
                                    ];
                                @endphp
                                <template x-if="row['{{ $column['field'] }}']">
                                    <span class="text-theme-xs inline-block rounded-full px-2 py-0.5 font-medium"
                                        :class="{
                                            @foreach ($badgeMapping as $value => $info)
                                                '{{ $colorClassMap[$info['color']] ?? $colorClassMap['light'] }}': row['{{ $column['field'] }}'] === '{{ $value }}',
                                            @endforeach
                                        }"
                                        x-text="(() => {
                                            const m = {{ Js::from($badgeMapping) }};
                                            const v = row['{{ $column['field'] }}'];
                                            return m[v]?.label || v || '-';
                                        })()"></span>
                                </template>
                            </td>
                        @elseif ($column['type'] === 'action')
                            <td class="px-4 sm:px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    @foreach ($config['actions'] ?? [] as $action)
                                        @if (($action['icon'] ?? '') === 'edit')
                                            <button @click="executeRowAction({{ Js::from($action) }}, row)">
                                                <svg class="text-gray-700 cursor-pointer size-5 hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                        @elseif (($action['icon'] ?? '') === 'delete')
                                            <button @click="executeRowAction({{ Js::from($action) }}, row)">
                                                <svg class="text-gray-700 cursor-pointer size-5 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-500"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @else
                                            <button @click="executeRowAction({{ Js::from($action) }}, row)"
                                                class="text-sm text-gray-700 hover:text-blue-500 dark:text-gray-400">
                                                {{ $action['label'] ?? '' }}
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                        @endif
                    @endforeach
                </tr>
            </template>
        </tbody>
    </x-tables.data-grid>
</div>
