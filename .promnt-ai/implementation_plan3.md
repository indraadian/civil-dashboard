# Audit & Implementation Plan: Requirements Client Civil Dashboard

Laporan audit awal terhadap 11 requirement client beserta rencana teknis implementasinya.

## Hasil Audit Requirement Client

| No | Requirement | Status | Keterangan |
|--- | ----------- | ------ | ---------- |
| 1  | **No. KK** (sebelum NIK) | ⚠️ Sebagian | Kolom `kk` sudah ada di DB, Model, Form, Datatable, Import, & Export. Namun posisinya masih **setelah NIK** pada form modal, export, dan datatable column order. |
| 2  | **Tempat Lahir** (sebelum Tgl Lahir) | ❌ Belum | Kolom `place_of_birth` belum ada di Database, Model `Civil`, Form Create/Edit, Detail View, Datatable, Import, Export, dan Validation. |
| 3  | **Tambahkan Filter** (RW, RT, Status) | ⚠️ Sebagian | Filter `status`, `rt`, `rw` sudah ada di `CivilDataTable.php`, namun perlu verifikasi agar dapat dikombinasikan dengan baik di UI dan terhubung penuh dengan fitur Export. |
| 4  | **Update Data Lama** (Tipe Lokasi & Status NULL) | ⚠️ Sebagian | Perlu migration untuk mengubah kolom `location_type` dan `status` pada tabel `civils` menjadi `nullable()` dengan default `NULL` agar data lama tidak terisi otomatis. |
| 5  | **Reset Akun Admin Lama** | ⚠️ Sebagian | Perlu pembersihan/reset akun lama di `UserSeeder` dan database agar hanya akun admin yang valid yang aktif. |
| 6  | **Form Tambah User** | ⚠️ Sebagian | Halaman manajemen user di `/settings/users` sudah ada, tetapi belum memiliki input & validasi **Konfirmasi Password** (`password_confirmation` + `confirmed`), serta pilihan assign RT/RW untuk user. |
| 7  | **Hak Akses User** | ⚠️ Sebagian | Route user management sudah dibatasi middleware `role:admin`. Perlu proteksi backend tambahan agar user biasa tidak bisa mengubah role/otoritasnya sendiri. |
| 8  | **Pembatasan Wilayah** (Role RW/RT) | ❌ Belum | Tabel `users` belum memiliki kolom `rt` & `rw`. Belum ada `CivilPolicy` atau Query Scope untuk membatasi user role `rw`/`rt` agar hanya bisa melihat/mengedit warga di wilayahnya. |
| 9  | **Verifikasi Import & Export** | ⚠️ Sebagian | Import (`ProcessCivilRowAction`) & Export (`GenerateCivilExportJob`) sudah menggunakan Job background, namun belum menyertakan kolom `place_of_birth` dan urutan kolom belum sesuai (`KK` sebelum `NIK`, `Tempat Lahir` sebelum `Tgl Lahir`). |
| 10 | **Hapus Menu Register** | ✅ Sudah | Teks dan link registrasi mandiri pada halaman Login (`signin.blade.php`) telah dihilangkan. |
| 11 | **Nonaktifkan Self Registration** | ⚠️ Sebagian | Route `GET /register` sudah meredirect ke login, namun endpoint `POST /register` dan method `register()` pada `AuthController.php` masih aktif memproses pembuatan akun jika diakses langsung. |

---

## User Review Required

> [!IMPORTANT]
> **Pembatasan Wilayah (Requirement 8)**: Membutuhkan penambahan kolom `rw` dan `rt` pada tabel `users` agar akun ber-role RW / RT dapat diasosiasikan dengan wilayah tugasnya masing-masing.

> [!WARNING]
> **Struktur Kolom Database (Requirement 1 & 2)**: Penambahan kolom `place_of_birth` dan reorder `kk` sebelum `nik` akan mengubah mapping import & export file Excel/CSV.

---

## Open Questions

> [!NOTE]
> 1. **Reset Akun Admin Lama**: Apakah Anda setuju jika seeder `UserSeeder` diperbarui untuk membuat 1 akun Admin utama bersih, dan akun-akun testing lama di-reset?
> 2. **Form User RT/RW**: Saat Admin membuat User baru ber-role `rw` atau `rt`, Admin akan menginput nomor RW dan/atau RT untuk user tersebut. Apakah alur ini sudah sesuai?

---

## Proposed Changes

### 1. Database Layer (Migrations & Models)

#### [NEW] [2026_07_30_000001_add_place_of_birth_and_update_civils_table.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/database/migrations/2026_07_30_000001_add_place_of_birth_and_update_civils_table.php)
- Menambahkan kolom `place_of_birth` (nullable) sebelum `date_of_birth`.
- Mengubah kolom `location_type` dan `status` menjadi `nullable()` dengan default `null`.

#### [NEW] [2026_07_30_000002_add_rt_rw_to_users_table.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/database/migrations/2026_07_30_000002_add_rt_rw_to_users_table.php)
- Menambahkan kolom `rt` (nullable, string 5) dan `rw` (nullable, string 5) pada tabel `users`.

#### [MODIFY] [Civil.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Models/Civil.php)
- Menambahkan `place_of_birth` ke `$fillable`.
- Menambahkan Query Scope `scopeForUser($query, User $user)` untuk membatasi data warga berdasarkan wilayah role user (Admin: semua, RW: filter `rw`, RT: filter `rw` & `rt`).

#### [MODIFY] [User.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Models/User.php)
- Menambahkan `rt` dan `rw` ke `$fillable`.
- Menambahkan helper methods `isAdmin()`, `isRw()`, `isRt()`.

#### [MODIFY] [UserSeeder.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/database/seeders/UserSeeder.php)
- Menyediakan akun admin default yang bersih.

---

### 2. Authorization & Security

#### [NEW] [CivilPolicy.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Policies/CivilPolicy.php)
- `viewAny`: Admin, RW, RT boleh melihat data.
- `view` / `update`: Admin penuh; RW hanya jika `civil.rw == user.rw`; RT hanya jika `civil.rw == user.rw` AND `civil.rt == user.rt`.
- `create` / `delete`: Admin penuh; RW/RT disesuaikan dengan aturan wilayah.

#### [MODIFY] [AuthController.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Controllers/AuthController.php)
- Menutup total endpoint `register` (menghapus/menolak request `POST /register`).

#### [MODIFY] [web.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/routes/web.php)
- Menghapus route `POST /register` dan `GET /register`.

---

### 3. Controller & DataTables

#### [MODIFY] [CivilDataTable.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/DataTables/Definitions/CivilDataTable.php)
- Mengubah urutan kolom: `kk` (No. KK) sebelum `nik`, `place_of_birth` (Tempat Lahir) sebelum `date_of_birth` (Tanggal Lahir).
- Menerapkan scope pembatasan wilayah pada method `query()`.
- Memastikan filter `rw`, `rt`, dan `status` dapat dikombinasikan dengan baik.

#### [MODIFY] [CivilController.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Controllers/CivilController.php)
- Memastikan method `store`, `update`, `destroy`, `export` menerapkan Authorization / Policy.

#### [MODIFY] [SettingController.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Controllers/SettingController.php)
- Mendukung simpan/edit User dengan field `rt`, `rw`, dan `password_confirmation`.

---

### 4. Form Requests & Validation

#### [MODIFY] [StoreCivilRequest.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Requests/StoreCivilRequest.php) & [UpdateCivilRequest.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Requests/UpdateCivilRequest.php)
- Menambahkan aturan validasi untuk `place_of_birth` (`nullable`, `string`, `max:255`).

#### [MODIFY] [StoreUserRequest.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Requests/StoreUserRequest.php) & [UpdateUserRequest.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Requests/UpdateUserRequest.php)
- Menambahkan validasi `password` dengan rule `confirmed` (`password_confirmation`).
- Menambahkan validasi `rt` dan `rw`.
- Memastikan validasi role di backend (`in:admin,rw,rt,user`).

---

### 5. Views & UI Components

#### [MODIFY] [civils.blade.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/resources/views/pages/civil/civils.blade.php)
- Reorder field pada Modal Tambah & Edit Warga:
  1. `KK` (sebelum NIK)
  2. `NIK`
  3. `Nama`
  4. `Tempat Lahir` (sebelum Tanggal Lahir)
  5. `Tanggal Lahir`
  6. dst.

#### [MODIFY] [users.blade.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/resources/views/pages/settings/users.blade.php) & Partial Form User
- Menambahkan field **Konfirmasi Password** pada form modal Tambah/Edit User.
- Menambahkan dropdown/input **Role** (Admin, RW, RT, User) serta field conditional **RT** dan **RW** saat role RW/RT dipilih.

---

### 6. Import & Export Verification

#### [MODIFY] [CivilsImport.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Imports/CivilsImport.php) & [ProcessCivilRowAction.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Actions/Import/ProcessCivilRowAction.php)
- Menambahkan mapping kolom `tempat_lahir` / `place_of_birth`.
- Menyesuaikan urutan kolom yang dibaca (`kk` sebelum `nik`, `tempat_lahir` sebelum `tanggal_lahir`).

#### [MODIFY] [CivilsExport.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Exports/CivilsExport.php) & [GenerateCivilExportJob.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Jobs/GenerateCivilExportJob.php)
- Menyesuaikan urutan heading & data row export:
  `['No. KK', 'NIK', 'Nama Lengkap', 'Tempat Lahir', 'Tanggal Lahir', 'Usia', 'Jenis Kelamin', 'RT', 'RW', 'Dusun', 'Alamat', 'Tipe Lokasi', 'Status']`.
- Memastikan job export menerapkan filter RW, RT, Status dan scope pembatasan wilayah user.

---

## Verification Plan

### Automated Tests
- Menjalankan test suite Laravel via PHPUnit/Pest jika tersedia: `php artisan test`.

### Manual Verification
1. **Verifikasi Database & Migration**:
   - Jalankan `php artisan migrate` dan pautkan data baru/lama untuk memastikan `place_of_birth`, `kk`, `location_type`, `status` tersimpan dengan benar.
2. **Verifikasi Auth & Self Registration**:
   - Coba akses `GET /register` dan `POST /register` untuk memastikan endpoint dinonaktifkan total (redirect/404).
3. **Verifikasi User Management**:
   - Buat user baru dengan password & konfirmasi password via `/settings/users`. Test bahwa validasi `confirmed` bekerja.
   - Buat user dengan role `RW` (misal RW 002) dan role `RT` (misal RT 001 RW 002).
4. **Verifikasi Pembatasan Wilayah (Authorization)**:
   - Login sebagai user RW 002: pastikan hanya data warga dengan RW 002 yang muncul di table dan bisa di-edit.
   - Login sebagai user RT 001: pastikan hanya data warga RT 001 RW 002 yang dapat diakses.
5. **Verifikasi Import & Export**:
   - Test import file Excel dengan kolom `No. KK`, `NIK`, `Nama`, `Tempat Lahir`, `Tanggal Lahir`.
   - Test export data dan periksa file Excel hasil download untuk memastikan urutan kolom sesuai requirement client.
