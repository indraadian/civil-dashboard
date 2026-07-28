{{-- DataTable Empty State --}}
@props([
    'icon' => null,
    'title' => 'Tidak ada data',
    'description' => 'Belum ada data yang tersedia.',
])

<div class="flex flex-col items-center justify-center py-8">
    <svg class="mb-4 h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
    </svg>
    <h3 class="mb-1 text-lg font-medium text-gray-700 dark:text-gray-300">{{ $title }}</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>

    @if (isset($action))
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>
