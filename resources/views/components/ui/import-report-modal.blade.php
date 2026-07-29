<x-ui.modal x-data="{
    open: false,
    loading: false,
    report: null,
    fetchReport(id) {
        this.open = true;
        this.loading = true;
        this.report = null;
        fetch(`/imports/${id}/report`)
            .then(res => res.json())
            .then(data => {
                this.report = data;
                this.loading = false;
            })
            .catch(() => {
                this.loading = false;
            });
    }
}" @open-import-report-modal.window="fetchReport($event.detail.id)" :isOpen="false" class="max-w-[650px]">

    <div class="no-scrollbar relative w-full max-w-[650px] overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-9">

        <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
            <div>
                <h4 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                    Laporan Hasil Import
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="report ? report.filename : 'Memuat data...'"></p>
            </div>
            <template x-if="report">
                <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wider"
                    :class="report.status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'"
                    x-text="report.status"></span>
            </template>
        </div>

        <template x-if="loading">
            <div class="py-12 text-center">
                <x-datatable.loading />
            </div>
        </template>

        <template x-if="!loading && report">
            <div class="mt-6 space-y-6">
                {{-- Stats Grid --}}
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-2xl border border-gray-100 bg-gray-50/50 p-4 text-center dark:border-gray-800 dark:bg-white/[0.02]">
                        <span class="text-xs text-gray-400">Total Baris</span>
                        <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white" x-text="report.total_rows"></p>
                    </div>
                    <div class="rounded-2xl border border-green-100 bg-green-50/50 p-4 text-center dark:border-green-500/20 dark:bg-green-500/10">
                        <span class="text-xs text-green-600 dark:text-green-400">Berhasil</span>
                        <p class="mt-1 text-xl font-bold text-green-600 dark:text-green-400" x-text="report.success_rows"></p>
                    </div>
                    <div class="rounded-2xl border border-red-100 bg-red-50/50 p-4 text-center dark:border-red-500/20 dark:bg-red-500/10">
                        <span class="text-xs text-red-600 dark:text-red-400">Gagal</span>
                        <p class="mt-1 text-xl font-bold text-red-600 dark:text-red-400" x-text="report.failed_rows"></p>
                    </div>
                    <div class="rounded-2xl border border-amber-100 bg-amber-50/50 p-4 text-center dark:border-amber-500/20 dark:bg-amber-500/10">
                        <span class="text-xs text-amber-600 dark:text-amber-400">Durasi</span>
                        <p class="mt-1 text-sm font-bold text-amber-600 dark:text-amber-400" x-text="report.duration"></p>
                    </div>
                </div>

                {{-- Time details --}}
                <div class="flex flex-wrap items-center justify-between text-xs text-gray-500 dark:text-gray-400 px-1">
                    <div>Waktu Mulai: <span class="font-medium text-gray-700 dark:text-gray-300" x-text="report.started_at || '-'"></span></div>
                    <div>Waktu Selesai: <span class="font-medium text-gray-700 dark:text-gray-300" x-text="report.finished_at || '-'"></span></div>
                </div>

                {{-- Error summary if failed --}}
                <template x-if="report.error_message">
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-500/30 dark:bg-red-500/10">
                        <h6 class="text-xs font-semibold uppercase tracking-wider text-red-700 dark:text-red-400 mb-1">
                            Ringkasan Kesalahan
                        </h6>
                        <p class="text-xs text-red-600 dark:text-red-300 font-mono whitespace-pre-wrap" x-text="report.error_message"></p>
                    </div>
                </template>

                <div class="flex justify-end pt-2">
                    <button @click="open = false" type="button"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        Tutup
                    </button>
                </div>
            </div>
        </template>
    </div>
</x-ui.modal>
