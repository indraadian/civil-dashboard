@if ($users->isEmpty())
    <tr>
        <td colspan="4" class="px-4 py-8 text-center">
            <div
                class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-400">
                Tidak ada user yang cocok dengan pencarian ini.
            </div>
        </td>
    </tr>
@else
    @foreach ($users as $user)
        <tr class="border-b border-gray-100 dark:border-white/[0.05]" data-user-id="{{ $user->id }}">
            <td class="px-4 sm:px-6 py-3.5">
                <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                    {{ $user->name }}</p>
            </td>
            <td class="px-4 sm:px-6 py-3.5">
                <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                    {{ $user->email }}</p>
            </td>
            <td class="py-3">
                <span
                    class="rounded-full px-2 py-1 text-xs font-medium {{ $user->role === 'admin' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ $user->role }}
                </span>
            </td>

            <td class="px-4 sm:px-6 py-3.5">
                <div class="flex items-center gap-3">
                    <button @click="editUser({{ $user->id }})">
                        <svg class="text-gray-700 cursor-pointer size-5 hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    @if (auth()->id() !== $user->id)
                        <button type="button" onclick="deleteUser({{ $user->id }}, this)"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-700 transition hover:border-red-300 hover:text-red-500 dark:border-gray-700 dark:text-gray-400 dark:hover:border-red-500/40 dark:hover:text-red-500">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
@endif
