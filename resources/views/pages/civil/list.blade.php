@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Data Penduduk" />

    <div class="space-y-6">

        @if (session('success'))
            <div
                class="flex items-center gap-3 rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-500/15 dark:text-green-400">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M10 2a8 8 0 100 16 8 8 0 000-16zm3.707 7.707l-4.5 4.5a1 1 0 01-1.414 0l-2-2a1 1 0 111.414-1.414L8.5 11.586l3.793-3.793a1 1 0 111.414 1.414z"
                        fill="currentColor" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div
                class="flex flex-col gap-1 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-500/15 dark:text-red-400">
                <div class="font-semibold flex items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z" fill="currentColor" />
                    </svg>
                    Gagal menyimpan data warga:
                </div>
                <ul class="list-disc pl-7 mt-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div x-data="{
            search: '',
            tableRowData: [],
            selectedRows: [],
            selectAll: false,
            handleSelectAll() {
                this.selectAll = !this.selectAll;
                if (this.selectAll) {
                    this.selectedRows = this.tableRowData.map(row => row.id);
                } else {
                    this.selectedRows = [];
                }
            },
            handleRowSelect(id) {
                if (this.selectedRows.includes(id)) {
                    this.selectedRows = this.selectedRows.filter(rowId => rowId !== id);
                } else {
                    this.selectedRows.push(id);
                }
            },
            getLocationClass(locationType) {
                return locationType === 'housing' ?
                    'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400' :
                    'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-500';
            },
            getStatusClass(status) {
                if (status === 'Militan') {
                    return 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-500';
                } else if (status === 'Ngambang') {
                    return 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400';
                } else {
                    return 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400';
                }
            },
            deleteRow(id) {
                if (confirm('Are you sure you want to delete this order?')) {
                    fetch('/civils/' + id, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            this.tableRowData = this.tableRowData.filter(row => row.id !== id);
                            this.selectedRows = this.selectedRows.filter(rowId => rowId !== id);
                            alert('Data deleted successfully.');
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Something went wrong.');
                        });
                }
            },
            deleteSelected() {
                if (confirm('Yakin ingin menghapus ' + this.selectedRows.length + ' data yang dipilih?')) {
                    fetch('/civils/delete-bulk', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ ids: this.selectedRows })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.tableRowData = this.tableRowData.filter(row => !this.selectedRows.includes(row.id));
                                this.selectedRows = [];
                                this.selectAll = false;
                                if (this.tableRowData.length === 0) {
                                    this.getData(this.search, this.currentPage);
                                }
                                alert('Data berhasil dihapus.');
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            alert('Gagal menghapus data.');
                        });
                }
            },
            editRow(id) {
                fetch('/civils/' + id + '/edit')
                    .then(response => response.json())
                    .then(civilData => {
                        $dispatch('open-edit-civil-modal', { data: civilData });
                    })
                    .catch(err => {
                        console.error('Gagal mengambil data:', err);
                        alert('Gagal memuat data warga. Silakan coba lagi.');
                    });
            },
            init() {
                this.$watch('search', () => {
                    this.currentPage = 1;
                    this.getData(this.search, 1);
                });
            },
            getData(query = '', page = 1) {
                fetch(`/civils/data?page=${page}&per_page=${this.perPage}&search=${encodeURIComponent(query)}`)
                    .then(response => {
                        if (response.status === 419 || response.status === 401) {
                            window.location.href = '/login';
                            return;
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.tableRowData = data.data.map(civil => ({
                            id: civil.id,
                            nik: civil.nik,
                            kk: civil.kk || '-',
                            customerName: civil.name,
                            birthDate: civil.date_of_birth ? new Date(civil.date_of_birth).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-',
                            age: civil.date_of_birth ? new Date().getFullYear() - new Date(civil.date_of_birth).getFullYear() : '-',
                            gender: civil.gender || '-',
                            hamlet: civil.hamlet || '-',
                            rt: `RT ${civil.rt}`,
                            rw: `RW ${civil.rw}`,
                            address: civil.address,
                            initials: civil.name.slice(0, 2).toUpperCase(),
                            avatarBg: civil.location_type === 'housing' ? 'bg-blue-100' : 'bg-green-50',
                            avatarColor: civil.location_type === 'housing' ? 'text-blue-500' : 'text-green-600',
                            locationType: civil.location_type,
                            location: civil.location_type === 'housing' ? 'Perumahan' : 'Kampung',
                            status: civil.status
                        }));
                        this.currentPage = data.meta.current_page;
                        this.totalPages = data.meta.last_page;
                        this.totalData = data.meta.total;
                    })
                    .catch(err => {
                        console.error('Gagal mengambil data:', err);
                        alert('Gagal memuat data warga. Silakan coba lagi.');
                    });
            },
            currentPage: 1,
            totalPages: 1,
            perPage: 10,
            totalData: 0,
            search: '',
            goToPage(page) {
                if (page >= 1 && page <= this.totalPages) {
                    this.currentPage = page;
                    this.getData(this.search, this.currentPage);
                }
            },
            prevPage() { if (this.currentPage > 1) this.goToPage(this.currentPage - 1) },
            nextPage() { if (this.currentPage < this.totalPages) this.goToPage(this.currentPage + 1) },
            get displayedPages() {
                let pages = [];
                for (let i = 1; i <= this.totalPages; i++) {
                    if (i === 1 || i === this.totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
                        pages.push(i);
                    } else if (pages[pages.length - 1] !== '...') {
                        pages.push('...');
                    }
                }
                return pages;
            }
        }" x-init="getData();">
            <x-tables.data-grid title="Data Penduduk" description="List warga aktif">
                <x-slot name="toolbar">
                    <form @submit.prevent>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div class="relative">
                                <button type="button" class="absolute -translate-y-1/2 left-4 top-1/2">
                                </button>

                                <input type="text" x-model.debounce.500ms="search" placeholder="Cari warga..."
                                    class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-[42px] pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[300px]" />
                            </div>
                        </div>
                    </form>
                    @if (auth()->user()->role === 'admin')
                        <button x-show="selectedRows.length > 0" x-cloak @click="deleteSelected()"
                            class="inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-4 py-3 text-sm bg-red-500 text-white shadow-theme-xs hover:bg-red-700 disabled:bg-red-300">
                            <svg class="cursor-pointer" width="12" height="12" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus
                        </button>
                    @endif
                </x-slot>

                <x-slot name="actions">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Total: <span class="font-bold text-gray-700 dark:text-white" x-text="totalData"></span> data
                    </span>
                    @if (auth()->user()->role === 'admin')
                        <button x-data="{ loading: false }"
                            @click="
                                                loading = true; 
                                                window.location.assign('{{ route('civils.export') }}');
                                                window.onfocus = () => { loading = false; };"
                            :disabled="loading"
                            class="inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-4 py-3 text-sm bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600 disabled:bg-brand-300">
                            <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M5.25012 3C5.25012 2.58579 5.58591 2.25 6.00012 2.25C6.41433 2.25 6.75012 2.58579 6.75012 3V5.25012L9.00034 5.25012C9.41455 5.25012 9.75034 5.58591 9.75034 6.00012C9.75034 6.41433 9.41455 6.75012 9.00034 6.75012H6.75012V9.00034C6.75012 9.41455 6.41433 9.75034 6.00012 9.75034C5.58591 9.75034 5.25012 9.41455 5.25012 9.00034L5.25012 6.75012H3C2.58579 6.75012 2.25 6.41433 2.25 6.00012C2.25 5.58591 2.58579 5.25012 3 5.25012H5.25012V3Z"
                                    fill=""></path>
                            </svg>
                            <span x-show="!loading">Ekspor</span>
                            <span x-show="loading">Memproses...</span>
                        </button>
                        <button @click="$dispatch('open-import-modal')"
                            class="inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-4 py-3 text-sm bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600 disabled:bg-brand-300">
                            <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M5.25012 3C5.25012 2.58579 5.58591 2.25 6.00012 2.25C6.41433 2.25 6.75012 2.58579 6.75012 3V5.25012L9.00034 5.25012C9.41455 5.25012 9.75034 5.58591 9.75034 6.00012C9.75034 6.41433 9.41455 6.75012 9.00034 6.75012H6.75012V9.00034C6.75012 9.41455 6.41433 9.75034 6.00012 9.75034C5.58591 9.75034 5.25012 9.41455 5.25012 9.00034L5.25012 6.75012H3C2.58579 6.75012 2.25 6.41433 2.25 6.00012C2.25 5.58591 2.58579 5.25012 3 5.25012H5.25012V3Z"
                                    fill=""></path>
                            </svg>
                            Impor
                        </button>
                        <button @click="$dispatch('open-civil-modal')"
                            class="inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-4 py-3 text-sm bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600 disabled:bg-brand-300">
                            <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M5.25012 3C5.25012 2.58579 5.58591 2.25 6.00012 2.25C6.41433 2.25 6.75012 2.58579 6.75012 3V5.25012L9.00034 5.25012C9.41455 5.25012 9.75034 5.58591 9.75034 6.00012C9.75034 6.41433 9.41455 6.75012 9.00034 6.75012H6.75012V9.00034C6.75012 9.41455 6.41433 9.75034 6.00012 9.75034C5.58591 9.75034 5.25012 9.41455 5.25012 9.00034L5.25012 6.75012H3C2.58579 6.75012 2.25 6.41433 2.25 6.00012C2.25 5.58591 2.58579 5.25012 3 5.25012H5.25012V3Z"
                                    fill=""></path>
                            </svg>
                            Tambah
                        </button>
                    @endif
                </x-slot>

                <x-slot name="pagination">
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-white/[0.05]">
                        <div class="flex items-center justify-between">
                            <button @click="prevPage" :disabled="currentPage === 1"
                                :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''"
                                class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:px-3.5">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M2.58301 9.99868C2.58272 10.1909 2.65588 10.3833 2.80249 10.53L7.79915 15.5301C8.09194 15.8231 8.56682 15.8233 8.85981 15.5305C9.15281 15.2377 9.15297 14.7629 8.86018 14.4699L5.14009 10.7472L16.6675 10.7472C17.0817 10.7472 17.4175 10.4114 17.4175 9.99715C17.4175 9.58294 17.0817 9.24715 16.6675 9.24715L5.14554 9.24715L8.86017 5.53016C9.15297 5.23717 9.15282 4.7623 8.85983 4.4695C8.56684 4.1767 8.09197 4.17685 7.79917 4.46984L2.84167 9.43049C2.68321 9.568 2.58301 9.77087 2.58301 9.99715C2.58301 9.99766 2.58301 9.99817 2.58301 9.99868Z"
                                        fill="currentColor" />
                                </svg>
                                <span class="hidden sm:inline">Sebelumnya</span>
                            </button>

                            <span class="block text-sm font-medium text-gray-700 dark:text-gray-400 sm:hidden">
                                Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                            </span>

                            <ul class="hidden items-center gap-0.5 sm:flex">
                                <template x-for="page in displayedPages" :key="page">
                                    <li>
                                        <button x-show="page !== '...'" @click="goToPage(page)"
                                            :class="currentPage === page ? 'bg-blue-500 text-white' :
                                                'text-gray-700 hover:bg-blue-500/[0.08] hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500'"
                                            class="flex h-10 w-10 items-center justify-center rounded-lg text-theme-sm font-medium"
                                            x-text="page"></button>
                                        <span x-show="page === '...'"
                                            class="flex h-10 w-10 items-center justify-center text-gray-500">...</span>
                                    </li>
                                </template>
                            </ul>

                            <button @click="nextPage" :disabled="currentPage === totalPages"
                                :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''"
                                class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:px-3.5">
                                <span class="hidden sm:inline">Selanjutnya</span>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M17.4175 9.9986C17.4178 10.1909 17.3446 10.3832 17.198 10.53L12.2013 15.5301C11.9085 15.8231 11.4337 15.8233 11.1407 15.5305C10.8477 15.2377 10.8475 14.7629 11.1403 14.4699L14.8604 10.7472L3.33301 10.7472C2.91879 10.7472 2.58301 10.4114 2.58301 9.99715C2.58301 9.58294 2.91879 9.24715 3.33301 9.24715L14.8549 9.24715L11.1403 5.53016C10.8475 5.23717 10.8477 4.7623 11.1407 4.4695C11.4336 4.1767 11.9085 4.17685 12.2013 4.46984L17.1588 9.43049C17.3173 9.568 17.4175 9.77087 17.4175 9.99715C17.4175 9.99763 17.4175 9.99812 17.4175 9.9986Z"
                                        fill="currentColor" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </x-slot>

                <thead
                    class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                    <tr>
                        @if (auth()->user()->role === 'admin')
                            <th
                                class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                                <div class="flex items-center gap-3">
                                    <div @click="handleSelectAll()"
                                        class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]"
                                        :class="selectAll ? 'border-blue-500 dark:border-blue-500 bg-blue-500' :
                                            'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                        <svg :class="selectAll ? 'block' : 'hidden'" width="14" height="14"
                                            viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white"
                                                stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">ID</span>
                                </div>
                            </th>
                        @endif
                        <th
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            NIK</th>
                        <th
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            KK</th>
                        <th
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            Nama</th>
                        <th
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            Tanggal Lahir</th>
                        <th
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            Usia</th>
                        <th
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            Jenis Kelamin</th>
                        <th
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            RT</th>
                        <th
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            RW</th>
                        <th
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            Dusun</th>
                        <th
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            Alamat</th>
                        <th
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            Tipe Lokasi</th>
                        <th
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            Status</th>
                        <th
                            class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                            ...</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in tableRowData" :key="row.id">
                        <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                            @if (auth()->user()->role === 'admin')
                                <td class="px-4 sm:px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div @click="handleRowSelect(row.id)"
                                            class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-md border-[1.25px]"
                                            :class="selectedRows.includes(row.id) ?
                                                'border-blue-500 dark:border-blue-500 bg-blue-500' :
                                                'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'">
                                            <svg :class="selectedRows.includes(row.id) ? 'block' : 'hidden'"
                                                width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white"
                                                    stroke-width="1.94437" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400"
                                                x-text="row.id"></span>
                                        </div>
                                    </div>
                                </td>
                            @endif
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.nik"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.kk"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full font-medium text-sm"
                                        :class="[row.avatarBg, row.avatarColor]">
                                        <span x-text="row.initials"></span>
                                    </div>
                                    <div>
                                        <span
                                            class="mb-0.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400"
                                            x-text="row.customerName"></span>
                                        <span class="text-gray-500 text-theme-sm dark:text-gray-400"
                                            x-text="row.customerEmail"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.birthDate"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.age"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.gender"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.rt"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.rw"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.hamlet"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <p class="text-gray-700 text-theme-sm dark:text-gray-400" x-text="row.address"></p>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <span class="text-theme-xs inline-block rounded-full px-2 py-0.5 font-medium"
                                    :class="getLocationClass(row.locationType)" x-text="row.location"></span>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <span class="text-theme-xs inline-block rounded-full px-2 py-0.5 font-medium"
                                    :class="getStatusClass(row.status)" x-text="row.status"></span>
                            </td>
                            <td class="px-4 sm:px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <button @click="editRow(row.id)">
                                        <svg class="text-gray-700 cursor-pointer size-5 hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    @if (auth()->user()->role === 'admin')
                                        <button @click="deleteRow(row.id)">
                                            <svg class="text-gray-700 cursor-pointer size-5 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-500"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </x-tables.data-grid>
        </div>
    </div>

    <!-- add form -->
    <x-ui.modal x-data="{ open: false }" @open-civil-modal.window="open = true" :isOpen="false" class="max-w-[700px]">
        <div
            class="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">

            <div class="px-2 pr-14">
                <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Tambah data penduduk
                </h4>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
                    Isi kolom untuk menambah penduduk baru
                </p>
            </div>

            <form action="{{ route('civils.store') }}" method="POST" class="flex flex-col">
                @csrf

                <div class="custom-scrollbar h-[458px] overflow-y-auto p-2">
                    <div>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">

                            <div class="col-span-2 lg:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    NIK <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nik" placeholder="e.g. 3201xxxxxxxxxxxx" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>

                            <div class="col-span-2 lg:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    KK
                                </label>
                                <input type="text" name="kk" placeholder="e.g. 320101010101010101"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>

                            <div class="col-span-2 lg:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Nama <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" placeholder="e.g. Indra Adian" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>

                            <div class="col-span-2 lg:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Tanggal Lahir <span class="text-red-500">*</span>
                                </label>
                                <x-form.date-picker mode="single" id="date_of_birth" name="date_of_birth"
                                    placeholder="Date of Birth" defaultDate="{{ now()->format('Y-m-d') }}" />
                            </div>

                            <div class="col-span-2 lg:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Jenis Kelamin <span class="text-red-500">*</span>
                                </label>
                                <select name="gender" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="L" class="dark:bg-gray-900">L (Laki-Laki)</option>
                                    <option value="P" class="dark:bg-gray-900">P (Perempuan)</option>
                                </select>
                            </div>

                            <div class="col-span-2 lg:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    RT <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="rt" placeholder="001" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>

                            <div class="col-span-2 lg:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    RW <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="rw" placeholder="002" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>

                            <div class="col-span-2 lg:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Dusun
                                </label>
                                <input type="number" name="hamlet" placeholder="e.g. Dusun 1"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>

                            <div class="col-span-2 lg:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Tipe Lokasi <span class="text-red-500">*</span>
                                </label>
                                <select name="location_type" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="village" class="dark:bg-gray-900">Village (Kampung)</option>
                                    <option value="housing" class="dark:bg-gray-900">Housing (Perumahan)</option>
                                </select>
                            </div>

                            <div class="col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Alamat <span class="text-red-500">*</span>
                                </label>
                                <textarea name="address" rows="3" placeholder="Jl. Anggrek No. 12..." required
                                    class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                            </div>

                            <div class="col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select name="status" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="Militan" class="dark:bg-gray-900">Militan</option>
                                    <option value="Ngambang" class="dark:bg-gray-900">Ngambang</option>
                                    <option value="Lawan" class="dark:bg-gray-900">Lawan</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 px-2 mt-6 lg:justify-end">
                    <button @click="open = false" type="button"
                        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    <!-- edit form -->
    <x-ui.modal x-data="{ open: false }" @open-edit-civil-modal.window="open = true; formData = $event.detail.data"
        :isOpen="false" class="max-w-[700px]">
        <div
            class="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
            <div class="px-2 pr-14">
                <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Ubah Data Penduduk
                </h4>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
                    Ubah kolom di bawah untuk mengubah data
                </p>
            </div>

            <form :action="'/civils/' + formData.id" method="POST" class="flex flex-col">
                @csrf
                @method('PUT')

                <div class="custom-scrollbar h-[458px] overflow-y-auto p-2">
                    <div>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                            @if (auth()->user()->role === 'admin')
                                <div class="col-span-2 lg:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        NIK <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nik" x-model="formData.nik" value="civil.nik"
                                        placeholder="e.g. 3201xxxxxxxxxxxx" required
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>

                                <div class="col-span-2 lg:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        KK
                                    </label>
                                    <input type="text" name="kk" x-model="formData.kk"
                                        placeholder="e.g. 320101010101010101"
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>

                                <div class="col-span-2 lg:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Nama <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" x-model="formData.name"
                                        placeholder="e.g. Indra Adian" required
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>

                                <div class="col-span-2 lg:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Tanggal Lahir <span class="text-red-500">*</span>
                                    </label>

                                    <x-form.date-picker id="date_of_birth" name="date_of_birth"
                                        placeholder="YYYY-MM-DD" />
                                </div>

                                <div class="col-span-2 lg:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Jenis Kelamin <span class="text-red-500">*</span>
                                    </label>
                                    <select name="gender" x-model="formData.gender" required
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                        <option value="L" class="dark:bg-gray-900">L (Laki-Laki)</option>
                                        <option value="P" class="dark:bg-gray-900">P (Perempuan)</option>
                                    </select>
                                </div>

                                <div class="col-span-2 lg:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        RT <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="rt" x-model="formData.rt" placeholder="001"
                                        required
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>

                                <div class="col-span-2 lg:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        RW <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="rw" x-model="formData.rw" placeholder="002"
                                        required
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>
                            @endif

                            <div class="col-span-2 lg:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Dusun
                                </label>
                                <input type="number" name="hamlet" x-model="formData.hamlet"
                                    placeholder="e.g. Dusun Wargakoo"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>

                            <div class="col-span-2 lg:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Tipe Lokasi <span class="text-red-500">*</span>
                                </label>
                                <select name="location_type" x-model="formData.location_type" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="village" class="dark:bg-gray-900">Village (Kampung)</option>
                                    <option value="housing" class="dark:bg-gray-900">Housing (Perumahan)</option>
                                </select>
                            </div>
                            @if (auth()->user()->role === 'admin')
                                <div class="col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Alamat <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="address" x-model="formData.address" rows="3" placeholder="Jl. Anggrek No. 12..." required
                                        class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                                </div>
                            @endif
                            <div class="col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select name="status" x-model="formData.status" required
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="Militan" class="dark:bg-gray-900">Militan</option>
                                    <option value="Ngambang" class="dark:bg-gray-900">Ngambang</option>
                                    <option value="Lawan" class="dark:bg-gray-900">Lawan</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 px-2 mt-6 lg:justify-end">
                    <button @click="open = false" type="button"
                        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    {{-- import form --}}
    <x-ui.modal x-data="{ open: false }" @open-import-modal.window="open = true" :isOpen="false" class="max-w-[700px]">
        <div
            class="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">

            <div class="px-2 pr-14">
                <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Impor Data Penduduk
                </h4>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
                    Unggah file Excel unutk melakukan impor.
                </p>
            </div>

            <form action="{{ route('civils.import') }}" method="POST" enctype="multipart/form-data"
                x-data="{ loading: false }" @submit="loading = true" class="flex flex-col">
                @csrf
                <div x-show="!loading" class="mb-6">
                    <a href="{{ asset('templates/template_civil.xlsx') }}"
                        class="inline-flex items-center text-sm text-brand-500 hover:underline dark:text-brand-400">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Unduh Template Excel (.xlsx)
                    </a>
                </div>
                <div x-show="!loading">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Unggah file
                    </label>
                    <input type="file" name="file" required
                        class="focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:text-white/90 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:placeholder:text-gray-400" />
                </div>

                <div class="flex items-center gap-3 px-2 mt-6 lg:justify-end">
                    <button x-show="!loading" @click="open = false" type="button"
                        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">
                        Batal
                    </button>

                    <button type="submit" :disabled="loading"
                        class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                        <span x-show="!loading">Impor</span>
                        <span x-show="loading">
                            Memproses...
                        </span> </button>
                </div>
            </form>
        </div>
    </x-ui.modal>
@endsection
