<div x-data="{
    open: false,
    unreadCount: 0,
    notifications: [],
    loading: false,
    fetchNotifications() {
        fetch('/notifications')
            .then(res => res.json())
            .then(data => {
                this.unreadCount = data.unread_count;
                this.notifications = data.notifications;
            })
            .catch(() => {});
    },
    markAllRead() {
        fetch('/notifications/mark-as-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(() => {
            this.unreadCount = 0;
            this.notifications.forEach(n => n.is_read = true);
        });
    },
    init() {
        this.fetchNotifications();
        setInterval(() => this.fetchNotifications(), 15000);
    }
}" class="relative">
    <!-- Notification Bell Button -->
    <button @click="open = !open" type="button"
        class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-dark-900 h-11 w-11 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
        aria-label="Notification Center">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-2.83-2h5.66A3 3 0 0110 18z"
                fill="currentColor" />
        </svg>
        <!-- Unread Badge Indicator -->
        <span x-show="unreadCount > 0" x-cloak
            class="absolute top-0 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-xs"
            x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
    </button>

    <!-- Notification Dropdown -->
    <div x-show="open" x-cloak @click.outside="open = false"
        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 w-80 sm:w-96 origin-top-right rounded-2xl border border-gray-200 bg-white p-4 shadow-xl dark:border-gray-800 dark:bg-gray-900">

        <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <h5 class="font-semibold text-gray-800 dark:text-white/90">Notifikasi</h5>
                <span x-show="unreadCount > 0"
                    class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400"
                    x-text="unreadCount + ' Baru'"></span>
            </div>
            <button x-show="unreadCount > 0" @click="markAllRead()" type="button"
                class="text-xs font-medium text-brand-500 hover:underline dark:text-brand-400">
                Tandai dibaca
            </button>
        </div>

        <!-- Notification List -->
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
            <template x-if="notifications.length === 0">
                <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    Tidak ada notifikasi
                </div>
            </template>

            <template x-for="item in notifications" :key="item.id">
                <div class="flex items-start gap-3 py-3 transition-colors hover:bg-gray-50/50 dark:hover:bg-white/[0.02]"
                    :class="!item.is_read ? 'bg-brand-50/30 dark:bg-brand-500/5' : ''">
                    <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full"
                        :class="{
                            'bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400': item.type === 'export_completed' || item.type === 'import_completed',
                            'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400': item.type === 'export_failed' || item.type === 'import_failed',
                            'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400': !item.type.includes('completed') && !item.type.includes('failed')
                        }">
                        <svg x-show="item.type === 'export_completed' || item.type === 'import_completed'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg x-show="item.type === 'export_failed' || item.type === 'import_failed'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>

                    <div class="flex-1 space-y-1">
                        <p class="text-xs text-gray-700 dark:text-gray-300" x-text="item.message"></p>
                        <template x-if="item.download_url">
                            <a :href="item.download_url"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-brand-500 hover:underline dark:text-brand-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Unduh File Export
                            </a>
                        </template>
                        <span class="block text-[10px] text-gray-400" x-text="item.created_at"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
