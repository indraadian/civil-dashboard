# Audit & Refactor Plan: Master RW/RT, User Location Scope & Role Hak Akses

Dokumen rencana refactor komprehensif berdasarkan permintaan terbaru pada `.promnt-ai/promnt.md`.

---

## Laporan Audit & Alasan Refactor

### Identifikasi Kode Saat Ini
1. **Tabel Users**: Sebelumnya kolom `rw` dan `rt` ditambahkan langsung ke tabel `users`.
   - *Kelemahan*: Setiap user hanya bisa mengelola 1 RT/RW. Tidak mendukung 1 user yang mengelola beberapa RT/RW sekaligus.
   - *Tindakan Refactor*: Menghapus kolom `rw` & `rt` dari `users` dan menggantinya dengan tabel pivot/mapping `user_location_scopes`.
2. **Sistem Role & Menu Maintenance**:
   - Previously: Hanya ada `admin` dan `user`. Menunya belum memisahkan `super_admin` untuk halaman Maintenance (Migration & Patch).
   - *Tindakan Refactor*: Menambahkan role `super_admin`. `super_admin` memiliki akses penuh termasuk Maintenance. `admin` hanya memiliki akses operasional (CRUD Warga, Import/Export, Master RW/RT, CRUD User), tetapi **tidak bisa** mengakses menu Maintenance (Migrasi & Patch).
3. **Master Data RW & RT**:
   - Previously: RW dan RT hanya berupa string mentah pada tabel `civils`. Belum ada tabel master khusus.
   - *Tindakan Refactor*: Membuat tabel master `rws` dan `rts` serta fitur otomatis **Patch Master RW & RT** untuk melakukan ekstraksi awal dari data `civils`.

---

## User Review Required

> [!IMPORTANT]
> **Struktur Hak Akses Wilayah Baru**:
> 1. Role bertugas mengatur **hak akses fitur** (`super_admin`, `admin`, `user`).
> 2. Wilayah data yang dapat diakses ditentukan oleh tabel `user_location_scopes` (`user_id`, `rw_id`, `rt_id`).
> 3. `rt_id` NULL = Akses seluruh RT pada RW tersebut. `rt_id` terisi = Akses RT spesifik.

> [!WARNING]
> **Akses Menu Maintenance**:
> Hanya **Super Admin** yang diperbolehkan mengakses menu `/settings/general` (Jalankan Migrasi & Patch Master RW/RT). Role `admin` biasa akan ditolak (403 Forbidden).

---

## Proposed Architecture & Changes

### 1. Database Schema & Migrations

#### [NEW] [2026_07_30_000003_create_rws_table.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/database/migrations/2026_07_30_000003_create_rws_table.php)
- Tabel `rws`: `id`, `code` (string unique), `name` (string nullable), `is_active` (boolean default true), `timestamps`.

#### [NEW] [2026_07_30_000004_create_rts_table.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/database/migrations/2026_07_30_000004_create_rts_table.php)
- Tabel `rts`: `id`, `rw_id` (foreign key `rws.id` cascade), `code` (string), `name` (string nullable), `is_active` (boolean default true), `timestamps`. Unique constraint (`rw_id`, `code`).

#### [NEW] [2026_07_30_000005_create_user_location_scopes_table.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/database/migrations/2026_07_30_000005_create_user_location_scopes_table.php)
- Tabel `user_location_scopes`: `id`, `user_id` (fk `users`), `rw_id` (fk `rws`), `rt_id` (fk `rts` nullable), `timestamps`. Index (`user_id`, `rw_id`, `rt_id`).

#### [NEW] [2026_07_30_000006_refactor_users_table_remove_rt_rw.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/database/migrations/2026_07_30_000006_refactor_users_table_remove_rt_rw.php)
- Menghapus kolom `rt` dan `rw` dari tabel `users`.

---

### 2. Models & Services

#### [NEW] [Rw.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Models/Rw.php) & [Rt.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Models/Rt.php)
- Relation: `Rw` hasMany `Rt`. `Rt` belongsTo `Rw`.

#### [NEW] [UserLocationScope.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Models/UserLocationScope.php)
- Relation: belongsTo `User`, `Rw`, `Rt`.

#### [MODIFY] [User.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Models/User.php)
- Method `isSuperAdmin()`, `isAdmin()`, `isUser()`.
- Relation `locationScopes()` hasMany `UserLocationScope`.

#### [MODIFY] [Civil.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Models/Civil.php)
- Scope `scopeForUser($query, ?User $user)`:
  - Super Admin & Admin: Semua data.
  - User: Filter berdasarkan kode RW & RT dari relasi `userLocationScopes` milik user.

#### [NEW] [LocationSyncService.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Services/LocationSyncService.php)
- Class service untuk eksekusi **Patch Master RW & RT**:
  - Membaca `civils` → `firstOrCreate` RW → `firstOrCreate` RT.
  - Mengembalikan summary: `['new_rws' => int, 'new_rts' => int, 'skipped' => int]`.

---

### 3. Controllers, Routes & Middleware

#### [MODIFY] [ValidateRole.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Middleware/ValidateRole.php)
- Super Admin otomatis lolos pada middleware role operasional (`admin`), tetapi route Maintenance dikhususkan hanya untuk `super_admin`.

#### [NEW] [RwController.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Controllers/RwController.php) & [RtController.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Controllers/RtController.php)
- Controller CRUD Master Data RW & RT (`/settings/rws`, `/settings/rts`).

#### [MODIFY] [SettingController.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Controllers/SettingController.php)
- Method `patchLocations()`: Menjalankan `LocationSyncService` dan menampilkan flash message hasil patch.
- Method `storeUser()` & `updateUser()`: Menyimpan dan memperbarui data `user_location_scopes`.

#### [MODIFY] [web.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/routes/web.php)
- Memisahkan middleware `role:super_admin` untuk route Maintenance (`/settings/general`, `/settings/migrate`, `/settings/patch-locations`).
- Route untuk Master RW & RT (`/settings/rws`, `/settings/rts`).

---

### 4. Views & Dynamic User Scope UI

#### [MODIFY] [general.blade.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/resources/views/pages/settings/general.blade.php)
- Menambahkan card **Patch Master RW & RT** beserta tombol eksekusi dan indikator hasil patch.

#### [NEW] [rws.blade.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/resources/views/pages/settings/rws.blade.php) & [rts.blade.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/resources/views/pages/settings/rts.blade.php)
- Halaman CRUD Master Data RW dan RT (Kode, Nama, Status Aktif).

#### [MODIFY] [users.blade.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/resources/views/pages/settings/users.blade.php)
- Dynamic UI Alpine.js pada Modal Tambah/Edit User:
  - Tombol "+ Tambah Scope Wilayah"
  - Select RW → Fetch/Tampilkan RT pada RW tersebut → Checkbox "Semua RT" atau Checklist RT Spesifik.

---

## Verification Plan

### Automated Tests
- Syntax check `php -l` untuk seluruh file PHP baru.

### Manual Verification
1. **Migration & Patch Test**:
   - Jalankan `php artisan migrate`.
   - Jalankan tombol **Patch Master RW & RT** di halaman Maintenance (`/settings/general`).
   - Verifikasi tabel `rws` dan `rts` terisi dari data `civils`.
2. **Role & Maintenance Access Test**:
   - Login sebagai `Admin`: Coba akses `/settings/general` -> Harus 403 Forbidden.
   - Login sebagai `Super Admin`: Akses `/settings/general` -> Berhasil (Bisa Run Migration & Patch).
3. **User Scope UI & Saving Test**:
   - Buka `/settings/users`, buat User baru. Tambahkan 2 scope wilayah:
     - RW 01 (Semua RT)
     - RW 03 (RT 01 & RT 02)
   - Simpan dan buka kembali modal Edit untuk memastikan data scope terisi.
4. **Backend Authorization & Filter Test**:
   - Login sebagai User tersebut, buka `/civils`: Pastikan hanya warga di RW 01 (semua RT) dan RW 03 (RT 01 & 02) yang muncul.
   - Test Export Excel: Pastikan baris Excel hasil ekspor terfilter sesuai scope user.
