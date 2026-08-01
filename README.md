# QuickCount PILKADES & Civil Dashboard System

**QuickCount PILKADES & Civil Dashboard System** adalah aplikasi manajemen data penduduk (Civil), pemetaan wilayah (TPS, RT, RW), serta monitoring perhitungan suara **Quick Count Pilkades** secara real-time. Aplikasi ini dibangun di atas pondasi **TailAdmin Laravel Dashboard Template** dengan teknologi modern **Laravel 12**, **PHP 8.4**, **Tailwind CSS v4**, **Alpine.js**, dan arsitektur modular yang responsif.

![Dashboard Preview](./tailadmin-laravel.png)

---

## 🎨 Base Dashboard Template

Aplikasi ini menggunakan UI & Layout dasar dari **[TailAdmin Laravel](https://tailadmin.com/laravel)** - salah satu template dashboard Tailwind CSS terpopuler yang telah disesuaikan dengan arsitektur backend Laravel 12, Blade components, dan Alpine.js reactivity.

---

## ✨ Fitur Utama Sistem (Key Features)

### 🗳️ 1. Quick Count Perhitungan Suara TPS
- **Real-Time Monitoring**: Ringkasan perolehan suara per calon, persentase TPS masuk, total suara sah, tidak sah, dan pengguna hak pilih.
- **Dynamic Candidate Votes**: Penginputan suara per TPS secara dinamis sesuai daftar pasangan calon pilkades yang aktif.
- **Validasi Suara (Sum Constraint)**: Validasi otomatis agar `Total Suara Sah + Tidak Sah = Total Pengguna Hak Pilih` (tidak melebihi DPT).
- **Bukti Form C1**: Upload dan preview foto Form C1 per TPS.

### 👥 2. Manajemen Master Candidate (Pasangan Calon)
- Kelola pasangan calon (Nomor urut, Nama lengkap calon, Foto, Status aktif/nonaktif).
- Integrasi penuh ke form penginputan Quick Count dan visualisasi hasil perolehan suara.

### 🇮🇩 3. Manajemen Data Penduduk (Civil)
- Pencatatan NIK, KK, Nama, Tempat/Tgl Lahir, Jenis Kelamin, Dusun, RT, RW, Alamat, Tipe Lokasi (Kampung/Perumahan).
- Kategori Status Politik Warga: **Militan**, **Ngambang**, dan **Lawan** disertai widget statistik indikator.

### 📍 4. Master Wilayah & TPS
- Pengelolaan Master TPS, RT, dan RW beserta kuota DPT.
- **Patch Tool Synchronizer**: Sinkronisasi otomatis data Master RT & RW dari data warga (Civil).

### 🔐 5. User Management & Hak Akses Wilayah
- **Spatie Laravel Permission**: Peran bertingkat (`Super Admin`, `Admin`, `RW`, `RT`, `User / Relawan`).
- **Location Scoping**: Pembatasan hak akses wilayah penginputan data berdasarkan RW dan RT user.
- **Super Admin Wildcard Bypass**: Akses penuh Super Admin ke seluruh modul dan konfigurasi sistem.

### ⚡ 6. Pipeline Async Ekspor & Impor (Queue-Based)
- **Ekspor Data (XLSX & CSV)**: Ekspor berlatar belakang (background queue) dengan filter spesifik per modul.
- **Impor Data Batch**: Pengunggahan berkas Excel/CSV secara chunk (upsert query) hemat memori.
- **Modals & Reports**: Modal dialog ekspor/impor terpadu dan modal laporan rincian error impor.

### 🔒 7. Global Application Lock (Server Activation Lock)
- Penguncian aplikasi global saat `APP_ACTIVE=false` di `.env`.
- Modal non-dismissable mengisolasi akses pengguna non-admin (cegah ESC, click outside, dan tombol back browser).
- Bypass otomatis untuk akun Administrator.

### 🔔 8. Real-Time Notification & Toast Widget
- Widget notifikasi melayang (Toast) di sudut kanan bawah dengan indikator progress bar task background.
- Notifikasi otomatis untuk aksi berhasil, gagal, informasi, dan peringatan session.

---

## 📄 Template Import Resmi (Import Excel Templates)

Sistem menyediakan 7 berkas contoh **Template Import Excel (.xlsx)** yang telah diformat dan disesuaikan 100% dengan validasi sistem:

1. **`template_civil.xlsx`** (Template Import Data Penduduk)
   - Kolom: `NIK`, `KK`, `Nama`, `Tempat Lahir`, `Tanggal Lahir`, `Jenis Kelamin`, `RT`, `RW`, `Dusun`, `Alamat`, `Tipe Lokasi`, `Status`
2. **`template_candidate.xlsx`** (Template Import Master Candidate)
   - Kolom: `Nomor Urut`, `Nama Pasangan Calon`, `Status Aktif`
3. **`template_user.xlsx`** (Template Import User)
   - Kolom: `Nama Lengkap`, `Email`, `Role`, `Password`
4. **`template_tps.xlsx`** (Template Import Master TPS)
   - Kolom: `Kode TPS`, `Nama TPS`, `Lokasi`, `Total DPT`
5. **`template_rt.xlsx`** (Template Import Master RT)
   - Kolom: `Kode RW`, `Kode RT`, `Nama RT`, `Status`
6. **`template_rw.xlsx`** (Template Import Master RW)
   - Kolom: `Kode RW`, `Nama RW`, `Status`
7. **`template_quick_count.xlsx`** (Template Import Quick Count TPS)
   - Kolom: `Kode TPS`, `Perolehan Suara`, `Total DPT`, `Catatan`

> *Berkas template di atas dapat diunduh secara langsung dari modal dialog Impor pada masing-masing modul.*

---

## 📋 Persyaratan Sistem (Requirements)

- **PHP 8.2+** (Direkomendasikan **PHP 8.4**)
- **Composer** (PHP Dependency Manager)
- **Node.js 18+** & **npm**
- **MySQL / MariaDB**

---

## 🚀 Panduan Instalasi (Quick Start)

### 1. Clone Repositori
```bash
git clone https://github.com/indraadian/civil-dashboard.git
cd civil-dashboard
```

### 2. Install Dependensi PHP & Node.js
```bash
composer install
npm install
```

### 3. Konfigurasi Environment
Salin berkas `.env.example` ke `.env`:
```bash
cp .env.example .env
```

Atur kredensial database MySQL Anda di `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=civil_dashboard
DB_USERNAME=root
DB_PASSWORD=your_password

# Pengaturan Kunci Sistem
APP_ACTIVE=true
```

### 4. Generate Key & Link Storage
```bash
php artisan key:generate
php artisan storage:link
```

### 5. Jalankan Migrasi & Seeder Database
```bash
php artisan migrate:fresh --seed
```

---

## 🏃 Menjalankan Aplikasi

### Mode Pengembangan (Development)
Jalankan dev server Laravel, Vite, dan Queue Worker secara bersamaan dengan satu perintah:
```bash
composer run dev
```

Aplikasi dapat diakses di: **[http://localhost:8000](http://localhost:8000)**

### Menjalankan Perintah Terpisah (Opsional)
- **Laravel Dev Server**: `php artisan serve`
- **Vite Asset Compiler**: `npm run dev`
- **Queue Worker**: `php artisan queue:work`

---

## 🧪 Pengujian (Testing)

Proyek ini dilengkapi dengan unit & feature test suite menggunakan **Pest**:
```bash
php artisan test
```

---

## 🛠️ Struktur Proyek (Project Structure)

```
civil-dashboard/
├── app/
│   ├── Actions/            # Single-responsibility actions (Import/Export processors)
│   ├── DataTables/         # Standardized DataTables Definitions, Columns, Filters & Actions
│   ├── Http/
│   │   ├── Controllers/    # Application Controllers
│   │   └── Requests/       # Form Request Validation Rules
│   ├── Models/             # Eloquent Models (Civil, Candidate, QuickCount, Tps, Rt, Rw, User)
│   ├── Policies/           # Authorization Policies
│   ├── Providers/          # Service Providers & View Composers
│   └── Services/           # Export & Import Service Pipelines
├── config/
│   ├── app.php             # General Config & app_status lock setting
│   └── import_templates.php# Configuration & validation rules for import templates
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders (User, RolePermission, Candidate, TPS, Civil)
├── public/
│   └── templates/          # Official downloadable Excel templates (.xlsx)
├── resources/
│   ├── css/                # Tailwind CSS v4 stylesheets
│   ├── js/                 # Alpine.js & Datatable Engine scripts
│   └── views/              # Blade Templates & Reusable UI Components
│       ├── components/ui/  # Reusable UI Components (Modal, Export Modal, Toast, Lock Modal)
│       └── pages/          # Page views (Civil, Quick Count, Settings)
└── routes/
    └── web.php             # Web Routes & Authorization Middlewares
```

---

## 📜 Lisensi & Atribusi

- **TailAdmin Laravel Dashboard**: Hak cipta milik [TailAdmin](https://tailadmin.com).
- **Proyek Civil Dashboard & Quick Count PILKADES**: Dikembangkan untuk manajemen data warga & monitoring pilkades.
