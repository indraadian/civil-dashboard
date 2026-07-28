{{-- DataTable Filters --}}
@props(['config' => []])

<template x-if="activeFilters.length > 0">
    <form @submit.prevent>
        <div class="space-y-3">
            <template x-for="(filter, index) in activeFilters" :key="index">
                <div class="grid gap-3 sm:grid-cols-[220px_1fr_auto]">
                    {{-- Filter Field Select --}}
                    <div>
                        <select x-model="filter.field"
                            @change="filter.value = ''; applyFilter()"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            <template x-for="f in config.filters" :key="f.field">
                                <option :value="f.field" x-text="f.label"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Filter Value Input --}}
                    <div>
                        {{-- Select type filter --}}
                        <template x-if="getFilterType(filter.field) === 'select' || getFilterType(filter.field) === 'boolean'">
                            <select x-model="filter.value"
                                @change="applyFilter()"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="">-- Pilih --</option>
                                <template x-for="opt in getFilterOptions(filter.field)" :key="opt.value">
                                    <option :value="opt.value" x-text="opt.label"></option>
                                </template>
                            </select>
                        </template>

                        {{-- Text type filter --}}
                        <template x-if="getFilterType(filter.field) === 'text'">
                            <input type="text"
                                x-model="filter.value"
                                @input="applyFilter()"
                                placeholder="Ketik nilai filter..."
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                        </template>

                        {{-- Date type filter --}}
                        <template x-if="getFilterType(filter.field) === 'date'">
                            <input type="date"
                                x-model="filter.value"
                                @change="applyFilter()"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                        </template>
                    </div>

                    {{-- Filter Buttons --}}
                    <div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click.prevent="addFilter()"
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03]">
                                    Tambah Filter
                                </button>
                                <button type="button" @click.prevent="resetFilters()"
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03]">
                                    Reset Filter
                                </button>
                                <button type="button" @click.prevent="removeFilter(index)"
                                    class="inline-flex items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-3 text-sm font-medium text-red-700 shadow-theme-xs hover:bg-gray-50 dark:border-red-700 dark:bg-red-800 dark:text-red-200 dark:hover:bg-white/[0.03]">
                                    Hapus Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </form>
</template>

{{-- Add Filter button (when no active filters) --}}
<template x-if="activeFilters.length === 0">
    <button type="button" @click="addFilter()"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03]">
        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        Filter
    </button>
</template>
