# Refactor Plan - Migrate Hardcoded CRUD to Configuration-Driven Architecture

## Background

Project ini telah memiliki sebuah CRUD Engine berbasis **Configuration-Driven Architecture**.

Module **Civil** merupakan implementasi referensi (gold standard) dan telah mengikuti arsitektur yang diinginkan.

Namun implementasi terbaru untuk module:

* User
* RW
* RT

dibuat secara hardcode sehingga tidak mengikuti pola yang telah digunakan oleh module Civil.

Hal ini menyebabkan inkonsistensi arsitektur, duplikasi kode, dan menyulitkan maintenance.

Seluruh implementasi tersebut harus direfactor.

---

# Objective

Seluruh CRUD untuk:

* User
* RW
* RT

**WAJIB mengikuti arsitektur, struktur folder, flow, naming convention, lifecycle, dan pola implementasi yang sama seperti module Civil.**

Gunakan module **Civil** sebagai acuan utama.

Jangan membuat pendekatan baru.

Jangan membuat implementasi khusus.

Jangan membuat struktur folder yang berbeda.

Jika engine belum mendukung kebutuhan User, RW, atau RT, maka lakukan improvement pada CRUD Engine agar mampu menangani kebutuhan tersebut secara generik.

**Prioritas utama adalah memperluas kemampuan CRUD Engine, bukan membuat exception pada module tertentu.**

---

# Audit Existing Engine

Sebelum melakukan perubahan:

1. Audit implementasi module Civil.
2. Audit CRUD Engine yang digunakan Civil.
3. Bandingkan implementasi User, RW, dan RT.
4. Identifikasi seluruh bagian yang berbeda.
5. Refactor hingga seluruh pattern sama dengan Civil.

Jika terdapat perbedaan implementasi, gunakan pendekatan Civil sebagai standar.

---

# Configuration Driven Only

Seluruh definisi berikut harus berasal dari konfigurasi, sama seperti module Civil:

* Table Definition
* Form Definition
* Filter Definition
* Actions
* Validation
* Import
* Export
* Detail View
* Bulk Actions
* Toolbar
* Permission
* UI Behaviour

Tidak boleh ada HTML, Blade, Livewire, atau logic CRUD yang ditulis secara hardcode apabila Civil sudah menyediakannya melalui konfigurasi.

---

# Form

Pastikan Form User, RW, dan RT menggunakan mekanisme yang sama seperti Civil.

Jika membutuhkan field baru seperti:

* Password
* Assign Role
* Multi Select
* Location Scope
* Relation Selector

buat reusable field/component baru pada CRUD Engine.

Jangan membuat implementasi yang hanya digunakan oleh User.

---

# Table

Seluruh table harus menggunakan Data Grid Engine yang sama dengan Civil.

Seluruh fitur berikut harus tetap bekerja:

* Sorting
* Searching
* Pagination
* Bulk Action
* Selection
* Formatter
* Badge
* Action Column
* Responsive Behaviour

---

# Filter

Filter harus menggunakan Filter Engine yang sama dengan Civil.

Jika dibutuhkan filter baru:

* Relation Filter
* Multi Select Filter

tambahkan ke CRUD Engine.

Jangan membuat query khusus di halaman User, RW, atau RT.

---

# Import & Export

Apabila module mendukung import/export, implementasinya harus menggunakan mekanisme yang sama dengan Civil.

Tidak boleh membuat controller, service, atau flow berbeda.

---

# Action

Gunakan Action Engine yang sama dengan Civil.

Semua action harus berasal dari konfigurasi.

Jika diperlukan action baru, tambahkan ke engine agar dapat digunakan oleh module lain.

---

# Authorization

Authorization tetap menggunakan sistem Role & Permission yang telah dibuat.

Authorization tidak boleh di-hardcode pada halaman tertentu.

---

# Reusable First

Jika menemukan kebutuhan baru selama refactor:

❌ Jangan membuat solusi khusus.

✅ Tambahkan kemampuan baru pada CRUD Engine.

Targetnya adalah agar module lain di masa depan dapat menggunakan kemampuan tersebut tanpa perubahan tambahan.

---

# Code Quality

Seluruh implementasi wajib mengikuti:

* Laravel 12 Best Practice
* Clean Architecture yang digunakan project
* SOLID
* DRY
* KISS
* PSR-12

Hindari:

* duplicate code
* hardcoded UI
* hardcoded query
* hardcoded validation
* hardcoded action

---

# Backward Compatibility

Refactor tidak boleh merusak:

* Civil
* Import
* Export
* Filter
* Data Grid
* Reusable Components

Semua module lama harus tetap berjalan seperti sebelumnya.

---

# Final Validation

Sebelum menyelesaikan pekerjaan, lakukan review menyeluruh.

Pastikan:

* User mengikuti pola implementasi Civil.
* RW mengikuti pola implementasi Civil.
* RT mengikuti pola implementasi Civil.
* Struktur folder konsisten dengan Civil.
* Service, Config, Resource, UI, Form, Table, Filter, Action, dan Lifecycle konsisten dengan Civil.
* Tidak ada implementasi hardcode yang tersisa.
* Tidak ada duplicate code.
* Seluruh fitur CRUD berjalan normal.

**Module Civil adalah referensi utama. Apabila terdapat perbedaan implementasi, ikuti pendekatan yang digunakan oleh Civil, bukan membuat pola baru.**
