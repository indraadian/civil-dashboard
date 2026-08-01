@props([
    'module' => null,
    'action' => null,
    'title' => 'Impor Data',
    'description' => 'Unggah file Excel/CSV untuk memproses impor data secara otomatis di background.',
    'templateUrl' => null,
    'templateTitle' => null,
    'validationRules' => null,
    'maxSize' => '10MB',
    'accept' => '.xlsx,.csv',
    'eventName' => 'open-import-modal',
])

@php
    $targetAction = $action ?? (Route::has('civils.import') ? route('civils.import') : '#');
    $config = config("import_templates.{$module}");

    $resolvedTemplateUrl = $templateUrl ?? ($config ? route('imports.template', $module) : ($module ? asset("templates/template_{$module}.xlsx") : null));
    $resolvedTemplateTitle = $templateTitle ?? ($config['title'] ?? 'Template Excel Standard');
    $resolvedValidationRules = $validationRules ?? ($config['validationRules'] ?? null);
@endphp

<x-ui.modal x-data="{ open: false }" :isOpen="false" class="max-w-[700px]">
    <div x-on:{{ $eventName }}.window="open = true" class="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">

        <div class="px-1 pr-12 mb-6">
            <h4 class="mb-1 text-2xl font-semibold text-gray-800 dark:text-white/90">
                {{ $title }}
            </h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $description }}
            </p>
        </div>

        <form action="{{ $targetAction }}" method="POST" enctype="multipart/form-data"
            x-data="{
                loading: false,
                isDragging: false,
                fileName: '',
                handleFileSelect(e) {
                    const files = e.target.files || e.dataTransfer.files;
                    if (files.length > 0) {
                        this.fileName = files[0].name;
                        this.$refs.fileInput.files = files;
                    }
                }
            }"
            @submit="open = false; $dispatch('toast', { title: 'Impor Dimulai', message: 'File impor sedang diproses di background.', type: 'info' })"
            class="flex flex-col gap-5">
            @csrf

            {{-- Download Template Box (opsional) --}}
            @if ($resolvedTemplateUrl)
                <div class="flex items-center justify-between rounded-2xl border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-500/20 dark:bg-brand-500/10">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-500 text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h6 class="text-xs font-semibold text-gray-800 dark:text-white">{{ $resolvedTemplateTitle }}</h6>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Gunakan format kolom yang sesuai agar data tervalidasi dengan benar.</p>
                        </div>
                    </div>
                    <a href="{{ $resolvedTemplateUrl }}" download
                        class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-2 text-xs font-semibold text-brand-600 shadow-xs hover:bg-brand-50 dark:bg-gray-800 dark:text-brand-400 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Template
                    </a>
                </div>
            @endif

            {{-- Drag & Drop Upload Zone --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Pilih File Impor <span class="text-red-500">*</span>
                </label>

                <div @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="isDragging = false; handleFileSelect($event)"
                    class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-8 text-center transition-colors cursor-pointer"
                    :class="isDragging ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-500/10' : 'border-gray-300 hover:border-brand-400 dark:border-gray-700 dark:hover:border-brand-500'">

                    <input x-ref="fileInput" type="file" name="file" accept="{{ $accept }}" required @change="handleFileSelect($event)"
                        class="absolute inset-0 z-10 opacity-0 cursor-pointer" />

                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 mb-3">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>

                    <template x-if="!fileName">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tarik & lepas file di sini, atau <span class="text-brand-500 underline">Cari File</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Format didukung: {{ strtoupper(str_replace(['.', ' '], '', $accept)) }} (Maksimal {{ $maxSize }})</p>
                        </div>
                    </template>

                    <template x-if="fileName">
                        <div class="flex items-center gap-2 text-sm font-semibold text-brand-600 dark:text-brand-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="fileName"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Validation Rules Accordion --}}
            @if (!empty($resolvedValidationRules))
                <div x-data="{ showRules: false }" class="rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                    <button type="button" @click="showRules = !showRules"
                        class="flex w-full items-center justify-between bg-gray-50 px-4 py-2.5 text-xs font-semibold text-gray-700 dark:bg-white/[0.02] dark:text-gray-300">
                        <span>Petunjuk Validasi Kolom</span>
                        <svg class="h-4 w-4 transition-transform" :class="showRules ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="showRules" x-cloak class="p-4 text-xs space-y-1.5 text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                        @foreach ($resolvedValidationRules as $rule)
                            <p>• {!! $rule !!}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-3 mt-4 lg:justify-end">
                <button @click="open = false" type="button"
                    class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">
                    Batal
                </button>
                <button type="submit" :disabled="loading"
                    class="flex w-full justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                    Mulai Impor
                </button>
            </div>
        </form>
    </div>
</x-ui.modal>
