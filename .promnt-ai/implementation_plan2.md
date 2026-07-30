# Refactor Import/Export Module — Civil Dashboard

## Latar Belakang

Aplikasi ini adalah dashboard manajemen data warga (civil) berbasis Laravel 12.
Saat ini fitur Import dan Export sudah ada menggunakan library `maatwebsite/excel`, namun implementasinya **tightly coupled, sinkron (blocking HTTP), dan tidak production-ready**.

---

## Analisis Implementasi Saat Ini

### Arsitektur Yang Ada

```
CivilController
  ├── import()   → Excel::import(new CivilsImport, file)  [SINKRON / BLOCKING]
  └── export()   → Excel::download(new CivilsExport)      [SINKRON / BLOCKING]

app/Imports/CivilsImport.php   → implements ToModel, WithHeadingRow, WithSkipDuplicates
app/Exports/CivilsExport.php   → implements FromQuery, WithMapping, WithHeadings
app/Imports/UsersImport.php    → stub kosong
app/Exports/UsersExport.php    → User::all() (load all ke memory!)
```

### Code Smells Yang Teridentifikasi

| # | File | Masalah | Dampak |
|---|------|---------|--------|
| 1 | `CivilController::import()` | `set_time_limit(0)` — memblock HTTP request sampai selesai | HTTP timeout untuk file besar, buruknya UX |
| 2 | `CivilController::import()` | Validasi inline di controller bukan Form Request | Melanggar SRP, tidak reusable |
| 3 | `CivilController::export()` | `Excel::download()` sinkron — user harus menunggu file di-generate | UX buruk untuk dataset besar |
| 4 | `UsersExport::collection()` | `User::all()` — load semua record ke memori | Memory exhausted untuk data besar |
| 5 | `CivilsImport` | Tidak ada status/progress tracking | User tidak tahu progress import |
| 6 | `CivilsImport` | Tidak ada queue/async — semua berjalan di HTTP request | Server bisa timeout |
| 7 | `CivilController` | Logika bisnis import/export ada di controller | God controller, tidak testable |
| 8 | Semua | Tidak ada tabel `imports`/`exports` | Tidak ada audit trail, tidak bisa tracking |
| 9 | Semua | Tidak ada event/listener | Tidak bisa notifikasi user setelah selesai |
| 10 | Semua | Tidak ada retry/failed job handling | Silent failures |

---

## User Review Required

> [!IMPORTANT]
> **Library `maatwebsite/excel` akan TETAP dipertahankan** karena sudah ada di `composer.json`. Namun cara pemakaiannya akan diubah: dari sinkron → async via Queue Jobs. Import akan menggunakan `WithChunkReading` + `ShouldQueue`. Export akan menggunakan `Queue` trait dari Maatwebsite untuk async generation.

> [!WARNING]
> **Breaking Change pada UX**: Import dan Export tidak lagi langsung mengembalikan file/redirect instan. Frontend perlu diupdate untuk:
> - Import: menampilkan status "sedang diproses" dan bisa polling `GET /imports/{id}`
> - Export: menampilkan "file sedang dibuat" dan polling `GET /exports/{id}` untuk mendapatkan download link
>
> Saya akan **update view blade** yang relevan agar UI tetap berfungsi.

> [!NOTE]
> **Scope Refactor**: Fokus utama adalah `CivilsImport` dan `CivilsExport` karena `UsersImport` dan `UsersExport` masih stub kosong. Keduanya akan dibuat pola yang sama namun tidak akan diaktifkan ke route.

---

## Arsitektur Baru

```
HTTP Layer (Controller)
    ↓
Form Request (Validation)
    ↓
Service Layer (Business Logic)
    ↓
Queue Job (Background Execution)
    ↓
Event → Listener → Notification

Database: imports, exports tables (progress tracking)
```

### Folder Structure Baru

```
app/
├── Actions/
│   └── Import/
│       └── ProcessCivilRowAction.php        [NEW] Single row processing
├── Events/
│   ├── ImportCompleted.php                  [NEW]
│   ├── ImportFailed.php                     [NEW]
│   ├── ExportCompleted.php                  [NEW]
│   └── ExportFailed.php                     [NEW]
├── Exports/
│   ├── CivilsExport.php                     [REFACTOR] → async via Queue
│   └── UsersExport.php                      [KEEP stub]
├── Http/
│   ├── Controllers/
│   │   └── CivilController.php             [MODIFY] import/export methods
│   └── Requests/
│       ├── ImportCivilRequest.php           [NEW] dedicated Form Request
│       └── ExportCivilRequest.php           [NEW] dedicated Form Request
├── Imports/
│   ├── CivilsImport.php                    [REFACTOR] → WithChunkReading + ShouldQueue
│   └── UsersImport.php                     [KEEP stub]
├── Jobs/
│   ├── ProcessCivilImportJob.php           [NEW] Queue job untuk import
│   └── GenerateCivilExportJob.php          [NEW] Queue job untuk export
├── Listeners/
│   ├── HandleImportCompleted.php           [NEW]
│   ├── HandleImportFailed.php              [NEW]
│   ├── HandleExportCompleted.php           [NEW]
│   └── HandleExportFailed.php             [NEW]
├── Models/
│   ├── Civil.php                           [KEEP]
│   ├── CivilImport.php                     [NEW] Eloquent model untuk tabel imports
│   ├── CivilExport.php                     [NEW] Eloquent model untuk tabel exports
│   └── User.php                            [KEEP]
├── Notifications/
│   ├── ImportCompletedNotification.php     [NEW]
│   └── ExportCompletedNotification.php     [NEW]
├── Policies/
│   ├── CivilImportPolicy.php               [NEW]
│   └── CivilExportPolicy.php              [NEW]
└── Services/
    ├── CivilImportService.php              [NEW] Orchestrate import flow
    └── CivilExportService.php             [NEW] Orchestrate export flow

database/migrations/
├── ...existing migrations...
├── xxxx_create_imports_table.php           [NEW]
└── xxxx_create_exports_table.php           [NEW]

routes/
└── web.php                                 [MODIFY] tambah progress API routes

tests/
├── Feature/
│   ├── CivilImportTest.php                 [NEW]
│   └── CivilExportTest.php                 [NEW]
└── Unit/
    ├── CivilImportServiceTest.php          [NEW]
    └── CivilExportServiceTest.php          [NEW]
```

---

## Proposed Changes (Per File)

### Database Layer

#### [NEW] `xxxx_create_imports_table.php`
Tabel `imports`: `id`, `filename`, `stored_path`, `status` (enum: pending/processing/completed/failed/cancelled), `progress` (int 0-100), `total_rows`, `processed_rows`, `failed_rows`, `error_message`, `started_at`, `finished_at`, `created_by`, `timestamps`.

#### [NEW] `xxxx_create_exports_table.php`
Tabel `exports`: `id`, `filename`, `stored_path`, `status`, `progress`, `total_rows`, `processed_rows`, `download_url`, `expires_at`, `started_at`, `finished_at`, `created_by`, `timestamps`.

---

### Models

#### [NEW] [CivilImport.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Models/CivilImport.php)
Model Eloquent untuk tabel `imports`. Berisi: `$fillable`, `$casts` (dates, int), scope untuk status, dan relasi ke User.

#### [NEW] [CivilExport.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Models/CivilExport.php)
Model Eloquent untuk tabel `exports`. Berisi: `$fillable`, `$casts`, method `isExpired()`, method `downloadUrl()`.

---

### Form Requests

#### [NEW] [ImportCivilRequest.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Requests/ImportCivilRequest.php)
Validasi: `file` required, MIME: `xlsx,xls,csv`, max size 10MB.

#### [NEW] [ExportCivilRequest.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Requests/ExportCivilRequest.php)
Validasi parameter export: format opsional (xlsx/csv), filter opsional.

---

### Services

#### [NEW] [CivilImportService.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Services/CivilImportService.php)
- `initiate(ImportCivilRequest $request): CivilImport`
  1. Validasi file (sudah via Form Request)
  2. Simpan file ke `storage/imports/`
  3. Buat record di tabel `imports` (status: pending)
  4. Dispatch `ProcessCivilImportJob`
  5. Return model `CivilImport`

#### [NEW] [CivilExportService.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Services/CivilExportService.php)
- `initiate(int $userId): CivilExport`
  1. Buat record di tabel `exports` (status: pending)
  2. Dispatch `GenerateCivilExportJob`
  3. Return model `CivilExport`
- `generateDownloadUrl(CivilExport $export): string` — temporary signed URL

---

### Jobs

#### [NEW] [ProcessCivilImportJob.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Jobs/ProcessCivilImportJob.php)
- Implements `ShouldQueue`
- Properties: `$tries = 3`, `$timeout = 3600`
- `handle()`:
  1. Update status → `processing`, set `started_at`
  2. Baca file via `LazyCollection`/chunk (1000 rows)
  3. Panggil `ProcessCivilRowAction` per row
  4. Update `processed_rows` dan `progress` setiap chunk
  5. Jika selesai: status → `completed`, `finished_at`, fire `ImportCompleted`
- `failed()`: status → `failed`, catat `error_message`, fire `ImportFailed`

#### [NEW] [GenerateCivilExportJob.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Jobs/GenerateCivilExportJob.php)
- Implements `ShouldQueue`
- `handle()`:
  1. Update status → `processing`
  2. Stream data via `Civil::cursor()` atau `LazyCollection`
  3. Tulis ke temp file via `PhpSpreadsheet` stream / `CivilsExport` dengan Queue concern
  4. Upload ke Storage
  5. Generate signed URL dengan `expires_at`
  6. Status → `completed`, fire `ExportCompleted`
- `failed()`: status → `failed`, fire `ExportFailed`

---

### Actions

#### [NEW] [ProcessCivilRowAction.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Actions/Import/ProcessCivilRowAction.php)
Single responsibility: transform 1 row data dan upsert ke DB.
- `execute(array $row): void` — parse NIK, KK, tanggal, location_type lalu `Civil::upsert()`

---

### Events

#### [NEW] Events: `ImportCompleted`, `ImportFailed`, `ExportCompleted`, `ExportFailed`
Setiap event membawa model yang relevan (`CivilImport` atau `CivilExport`).

---

### Listeners

#### [NEW] Listeners:
- `HandleImportCompleted` → kirim `ImportCompletedNotification` ke user
- `HandleImportFailed` → `Log::error()`, kirim notifikasi gagal
- `HandleExportCompleted` → kirim `ExportCompletedNotification` dengan download link
- `HandleExportFailed` → `Log::error()`, kirim notifikasi gagal

---

### Notifications

#### [NEW] `ImportCompletedNotification` — via Mail + Database channel
#### [NEW] `ExportCompletedNotification` — via Mail + Database channel (dengan download link)

---

### Policies

#### [NEW] `CivilImportPolicy` — hanya role `admin` yang bisa import
#### [NEW] `CivilExportPolicy` — hanya role `admin` yang bisa export

---

### Controller

#### [MODIFY] [CivilController.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Controllers/CivilController.php)

```php
// SEBELUM:
public function import(Request $request): RedirectResponse
{
    $request->validate([...]); // inline validation
    set_time_limit(0);          // anti-pattern!
    Excel::import(new CivilsImport, $request->file('file')); // blocking!
    return back()->with('success', '...');
}

// SESUDAH:
public function import(ImportCivilRequest $request): RedirectResponse
{
    $import = $this->importService->initiate($request);
    return back()->with('info', "Import sedang diproses. ID: {$import->id}");
}

// SEBELUM:
public function export(): BinaryFileResponse
{
    return Excel::download(new CivilsExport, 'civils.xlsx'); // blocking!
}

// SESUDAH:
public function export(ExportCivilRequest $request): RedirectResponse
{
    $export = $this->exportService->initiate(auth()->id());
    return back()->with('info', "Export sedang diproses. ID: {$export->id}");
}
```

Tambah 2 method baru:
- `importProgress(CivilImport $import): JsonResponse` — `GET /imports/{id}`
- `exportProgress(CivilExport $export): JsonResponse` — `GET /exports/{id}`

---

### Routes

#### [MODIFY] [web.php](file:///d:/Projects/Laravel\Apps\civil-dashboard\routes\web.php)

Tambah routes:
```php
Route::get('/imports/{import}', [CivilController::class, 'importProgress'])->name('civils.import.progress');
Route::get('/exports/{export}', [CivilController::class, 'exportProgress'])->name('civils.export.progress');
```

---

### Import Class (Refactor)

#### [MODIFY] [CivilsImport.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Imports/CivilsImport.php)

Diubah dari `ToModel` → digunakan oleh Job dengan `LazyCollection` langsung, bukan melalui `Excel::import()` sinkron. Class ini akan menjadi DTO/value object untuk field mapping saja.

---

### Tests

#### [NEW] `tests/Feature/CivilImportTest.php`
- Test upload file → redirect dengan info message
- Test job di-dispatch (Queue::fake())
- Test progress endpoint mengembalikan JSON yang benar
- Test validasi file invalid

#### [NEW] `tests/Feature/CivilExportTest.php`
- Test export request → redirect dengan info message
- Test job di-dispatch
- Test download setelah export selesai

#### [NEW] `tests/Unit/CivilImportServiceTest.php`
- Test service menyimpan file
- Test service membuat record import
- Test service men-dispatch job

#### [NEW] `tests/Unit/ProcessCivilRowActionTest.php`
- Test row valid diproses dengan benar
- Test row dengan NIK kosong di-skip
- Test row dengan tanggal tidak valid tidak melempar exception

---

## Request Flow (Baru)

```
POST /civils/import
    → ImportCivilRequest (validate MIME, size)
    → CivilImportService::initiate()
        → Storage::put(file)
        → CivilImport::create([status: pending])
        → ProcessCivilImportJob::dispatch($import)
    ← 302 redirect + flash "sedang diproses"

[BACKGROUND - Queue Worker]
ProcessCivilImportJob::handle()
    → CivilImport::update([status: processing])
    → LazyCollection::make(open file)->chunk(1000)
        → foreach chunk: ProcessCivilRowAction::execute()
        → Civil::upsert(chunk_data)
        → CivilImport::update([processed_rows++, progress%])
    → CivilImport::update([status: completed])
    → ImportCompleted::dispatch($import)
        → HandleImportCompleted::handle()
            → Notification::send(user, ImportCompletedNotification)
```

## Queue Flow (Export)

```
GET /civils/export (atau POST dengan filter)
    → ExportCivilRequest (validate params)
    → CivilExportService::initiate()
        → CivilExport::create([status: pending])
        → GenerateCivilExportJob::dispatch($export)
    ← 302 redirect + flash "sedang diproses"

[BACKGROUND - Queue Worker]
GenerateCivilExportJob::handle()
    → CivilExport::update([status: processing])
    → Civil::cursor() → stream ke temp file
    → Storage::put(temp file)
    → generate signed URL (expires: 24h)
    → CivilExport::update([status: completed, download_url, expires_at])
    → ExportCompleted::dispatch($export)
        → HandleExportCompleted::handle()
            → Notification::send(user, ExportCompletedNotification dengan URL)
```

## Sequence Diagram — Import

```
User    Controller    Service    Storage    DB        Queue     Job
 |          |            |          |        |           |        |
 |--POST--->|            |          |        |           |        |
 |          |--validate->|          |        |           |        |
 |          |            |--store-->|        |           |        |
 |          |            |<-path----|        |           |        |
 |          |            |--create record--->|           |        |
 |          |            |<-CivilImport------|           |        |
 |          |            |--dispatch-------->|           |        |
 |          |<-CivilImport|                  |           |        |
 |<--302----|            |                  |           |        |
 |          |            |                  |           |------->|
 |          |            |                  |           |  handle()|
 |          |            |                  |<--update status-----|
 |          |            |                  |<--upsert rows-------|
 |          |            |                  |<--update progress---|
 |          |            |                  |<--update completed--|
 |          |            |                  |   fire ImportCompleted
 |          |            |                  |           notify User
```

---

## Alasan Setiap Keputusan Arsitektur

| Keputusan | Alasan |
|-----------|--------|
| **Service Layer** | Memisahkan logika bisnis dari HTTP layer, mudah di-test |
| **Queue Jobs** | HTTP request tidak pernah block — user langsung dapat response |
| **LazyCollection/cursor()** | Memory usage rendah bahkan untuk 100K+ rows |
| **`Civil::upsert()`** | Batch insert + handle duplikat dalam satu query (N+1 problem solved) |
| **Tabel `imports`/`exports`** | Audit trail, progress tracking, bisa di-retry |
| **Events + Listeners** | Decoupled notification — mudah tambah channel baru |
| **Form Request** | Validasi terpisah dari controller, reusable, testable |
| **Actions** | Single responsibility — 1 class 1 tugas (SRP) |
| **Policy** | Authorization terpusat, tidak scattered di controller |
| **`$tries = 3`** | Job idempotent, bisa diretry jika gagal sementara |
| **Signed URL** | Export file tidak perlu disimpan publik, expires otomatis |
| **`maatwebsite/excel` retained** | Sudah ada di project, `WithChunkReading` + `ShouldQueue` sudah tersedia |

---

## Verification Plan

### Automated Tests
```bash
php artisan test --filter=CivilImport
php artisan test --filter=CivilExport
```

### Manual Verification
1. Upload file Excel dengan 100 rows → cek job masuk ke tabel `jobs`
2. Jalankan queue worker → cek record di tabel `imports` berubah ke `completed`
3. Poll `GET /imports/{id}` → cek response JSON dengan progress
4. Request export → cek record di tabel `exports` → polling status → download

---

## Open Questions

> [!IMPORTANT]
> **Apakah Anda ingin UI/View blade diupdate** untuk menampilkan status polling (real-time progress bar) atau cukup flash message saja untuk MVP ini?

> [!NOTE]
> **Notifikasi**: Saat ini `MAIL_MAILER=log`, jadi notifikasi akan ke log file. Apakah perlu diaktifkan notifikasi ke database (bell icon) juga?
