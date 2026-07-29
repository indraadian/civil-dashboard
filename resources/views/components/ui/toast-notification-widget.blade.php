<div x-data="{
    toasts: [],
    activeImports: [],
    activeExports: [],
    addToast(toast) {
        const id = Date.now();
        this.toasts.push({
            id,
            title: toast.title || 'Notifikasi',
            message: toast.message || '',
            type: toast.type || 'info',
            downloadUrl: toast.downloadUrl || null,
            importId: toast.importId || null
        });

        setTimeout(() => {
            this.removeToast(id);
        }, 8000);
    },
    removeToast(id) {
        this.toasts = this.toasts.filter(t => t.id !== id);
    },
    pollActiveTasks() {
        fetch('/active-tasks')
            .then(res => res.json())
            .then(data => {
                this.activeImports = data.imports || [];
                this.activeExports = data.exports || [];
            })
            .catch(() => {});
    },
    init() {
        this.pollActiveTasks();
        setInterval(() => this.pollActiveTasks(), 10000);
    }
}" @toast.window="addToast($event.detail)"
class="fixed bottom-5 right-5 z-50 flex flex-col gap-3 w-80 sm:w-96 pointer-events-none">

    {{-- Active Running Tasks Progress Cards --}}
    <template x-for="task in activeImports" :key="'imp-' + task.id">
        <div class="pointer-events-auto rounded-2xl border border-blue-200 bg-white p-4 shadow-xl dark:border-blue-500/30 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-xs font-semibold text-gray-800 dark:text-white">Memproses Impor</span>
                </div>
                <span class="text-xs font-bold text-blue-600 dark:text-blue-400" x-text="task.progress + '%'"></span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate mb-2" x-text="task.filename"></p>
            {{-- Progress bar --}}
            <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                <div class="h-full bg-blue-500 transition-all duration-300 rounded-full" :style="'width: ' + task.progress + '%'"></div>
            </div>
        </div>
    </template>

    <template x-for="task in activeExports" :key="'exp-' + task.id">
        <div class="pointer-events-auto rounded-2xl border border-brand-200 bg-white p-4 shadow-xl dark:border-brand-500/30 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin text-brand-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-xs font-semibold text-gray-800 dark:text-white">Generasi Ekspor</span>
                </div>
                <span class="text-xs font-bold text-brand-500 dark:text-brand-400" x-text="task.progress + '%'"></span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate mb-2" x-text="task.filename"></p>
            <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                <div class="h-full bg-brand-500 transition-all duration-300 rounded-full" :style="'width: ' + task.progress + '%'"></div>
            </div>
        </div>
    </template>

    {{-- Toast Cards --}}
    <template x-for="toast in toasts" :key="toast.id">
        <div class="pointer-events-auto flex items-start gap-3 rounded-2xl border bg-white p-4 shadow-xl transition-all dark:bg-gray-900"
            :class="{
                'border-green-200 dark:border-green-500/30': toast.type === 'success',
                'border-red-200 dark:border-red-500/30': toast.type === 'error',
                'border-blue-200 dark:border-blue-500/30': toast.type === 'info'
            }">
            <div class="mt-0.5 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full"
                :class="{
                    'bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400': toast.type === 'success',
                    'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400': toast.type === 'error',
                    'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400': toast.type === 'info'
                }">
                <svg x-show="toast.type === 'success'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg x-show="toast.type === 'error'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <svg x-show="toast.type === 'info'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <div class="flex-1 space-y-1">
                <h6 class="text-xs font-semibold text-gray-800 dark:text-white" x-text="toast.title"></h6>
                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="toast.message"></p>

                <div class="flex items-center gap-3 pt-1">
                    <template x-if="toast.downloadUrl">
                        <a :href="toast.downloadUrl"
                            class="text-xs font-bold text-brand-500 hover:underline dark:text-brand-400">
                            Unduh File
                        </a>
                    </template>
                    <template x-if="toast.importId">
                        <button @click="$dispatch('open-import-report-modal', { id: toast.importId })"
                            class="text-xs font-bold text-brand-500 hover:underline dark:text-brand-400">
                            Lihat Laporan
                        </button>
                    </template>
                    <button @click="removeToast(toast.id)" type="button" class="text-[11px] text-gray-400 hover:text-gray-600">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
