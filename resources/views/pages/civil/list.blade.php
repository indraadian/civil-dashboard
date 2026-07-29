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

        {{-- DataTable Component --}}
        <x-datatable
            :config="$config"
            data-url="{{ route('api.civils.data') }}"
            base-url="/civils"
            title="Data Penduduk"
        >
        </x-datatable>
    </div>

    {{-- Add Civil Modal --}}
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
                                    <option value="" disabled selected class="dark:bg-gray-900">Pilih Tipe Lokasi</option>
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

    {{-- Edit Civil Modal --}}
    <x-ui.modal x-data="{ open: false, formData: {} }" @open-edit-civil-modal.window="open = true; formData = $event.detail.data"
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
                                    <input type="text" name="nik" x-model="formData.nik"
                                        placeholder="e.g. 3201xxxxxxxxxxxx" required
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>

                                <div class="col-span-2 lg:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">KK</label>
                                    <input type="text" name="kk" x-model="formData.kk" placeholder="e.g. 320101010101010101"
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>

                                <div class="col-span-2 lg:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Nama <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" x-model="formData.name" placeholder="e.g. Indra Adian" required
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>

                                <div class="col-span-2 lg:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Tanggal Lahir <span class="text-red-500">*</span>
                                    </label>
                                    <x-form.date-picker id="date_of_birth" name="date_of_birth" placeholder="YYYY-MM-DD" />
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
                                    <input type="text" name="rt" x-model="formData.rt" placeholder="001" required
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>

                                <div class="col-span-2 lg:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        RW <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="rw" x-model="formData.rw" placeholder="002" required
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>
                            @endif

                            <div class="col-span-2 lg:col-span-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Dusun</label>
                                <input type="number" name="hamlet" x-model="formData.hamlet" placeholder="e.g. Dusun Wargakoo"
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

    {{-- Import Modal --}}
    <x-ui.modal x-data="{ open: false }" @open-import-modal.window="open = true" :isOpen="false" class="max-w-[700px]">
        <div class="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">

            <div class="px-1 pr-12 mb-6">
                <h4 class="mb-1 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    Impor Data Penduduk
                </h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Unggah file Excel untuk memproses impor data warga secara otomatis di background.
                </p>
            </div>

            <form action="{{ route('civils.import') }}" method="POST" enctype="multipart/form-data"
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

                {{-- Download Template Button --}}
                <div class="flex items-center justify-between rounded-2xl border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-500/20 dark:bg-brand-500/10">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-500 text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h6 class="text-xs font-semibold text-gray-800 dark:text-white">Template Excel Standard</h6>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Gunakan format kolom yang sesuai agar data tervalidasi dengan benar.</p>
                        </div>
                    </div>
                    <a href="{{ asset('templates/template_civil.xlsx') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-2 text-xs font-semibold text-brand-600 shadow-xs hover:bg-brand-50 dark:bg-gray-800 dark:text-brand-400 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Template
                    </a>
                </div>

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

                        <input x-ref="fileInput" type="file" name="file" accept=".xlsx,.csv" required @change="handleFileSelect($event)"
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
                                <p class="text-xs text-gray-400 mt-1">Format didukung: XLSX, CSV (Maksimal 10MB)</p>
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
                <div x-data="{ showRules: false }" class="rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                    <button type="button" @click="showRules = !showRules"
                        class="flex w-full items-center justify-between bg-gray-50 px-4 py-2.5 text-xs font-semibold text-gray-700 dark:bg-white/[0.02] dark:text-gray-300">
                        <span>Petunjuk Validasi Kolom</span>
                        <svg class="h-4 w-4 transition-transform" :class="showRules ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="showRules" x-cloak class="p-4 text-xs space-y-1.5 text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                        <p>• <strong>NIK</strong>: Wajib, 16 digit angka (unik).</p>
                        <p>• <strong>KK</strong>: 16 digit angka (opsional).</p>
                        <p>• <strong>Nama & Alamat</strong>: Wajib diisi.</p>
                        <p>• <strong>Tanggal Lahir</strong>: Format `DD-MM-YYYY` (contoh: 15-08-1995).</p>
                        <p>• <strong>Jenis Kelamin</strong>: `L` (Laki-Laki) atau `P` (Perempuan).</p>
                        <p>• <strong>RT & RW</strong>: Format 3 digit (contoh: `001`, `002`).</p>
                        <p>• <strong>Tipe Lokasi</strong>: `kampung` / `village` atau `housing` / `perumahan`.</p>
                        <p>• <strong>Status</strong>: `Militan`, `Ngambang`, atau `Lawan` (default: Ngambang).</p>
                    </div>
                </div>

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
@endsection
