<div
    class="relative"
    x-data="{
        dropdownOpen: false,
        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;
        },
        closeDropdown() {
            this.dropdownOpen = false;
        }
    }"
    @click.away="closeDropdown()">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button
            type="submit"
            class="flex w-full items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            Keluar
        </button>
    </form>
</div>