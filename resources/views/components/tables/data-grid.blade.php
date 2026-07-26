@props([
    'title' => null,
    'description' => null,
])

<div
    class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
    <div class="flex flex-col gap-4 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            {{ $toolbar ?? '' }}
        </div>

        <div class="flex items-center gap-3">
            {{ $actions ?? '' }}
        </div>
    </div>

    <div class="max-w-full overflow-x-auto">
        <table class="min-w-full">
            {{ $slot }}
        </table>
    </div>

    @if (trim($pagination ?? '') !== '')
        <div class="px-6 py-4 border-t border-gray-200 dark:border-white/[0.05]">
            {{ $pagination }}
        </div>
    @endif
</div>
