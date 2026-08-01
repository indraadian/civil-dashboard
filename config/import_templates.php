<?php

return [
    'civil' => [
        'title' => 'Template Import Data Penduduk (Civil)',
        'file' => 'template_civil.xlsx',
        'filename' => 'template_import_civil.xlsx',
        'validationRules' => [
            '<strong>NIK</strong>: Wajib, 16 digit angka (unik).',
            '<strong>KK</strong>: 16 digit angka (opsional).',
            '<strong>Nama & Alamat</strong>: Wajib diisi.',
            '<strong>Tanggal Lahir</strong>: Format `DD-MM-YYYY` (contoh: 15-08-1995).',
            '<strong>Jenis Kelamin</strong>: `L` (Laki-Laki) atau `P` (Perempuan).',
            '<strong>RT & RW</strong>: Format 3 digit (contoh: `001`, `002`).',
            '<strong>Tipe Lokasi</strong>: `kampung` / `village` atau `housing` / `perumahan`.',
            '<strong>Status</strong>: `Militan`, `Ngambang`, atau `Lawan` (default: Ngambang).',
        ],
    ],
    'tps' => [
        'title' => 'Template Import Master TPS',
        'file' => 'template_tps.xlsx',
        'filename' => 'template_import_tps.xlsx',
        'validationRules' => [
            '<strong>Kode TPS</strong>: Kode unik TPS (contoh: `TPS-001`).',
            '<strong>Nama TPS</strong>: Nama lengkap TPS (contoh: `TPS 01 - RW 01`).',
            '<strong>Lokasi</strong>: Lokasi atau alamat TPS (opsional).',
            '<strong>Total DPT / Pemilih</strong>: Angka jumlah pemilih di TPS.',
        ],
    ],
    'rw' => [
        'title' => 'Template Import Master RW',
        'file' => 'template_rw.xlsx',
        'filename' => 'template_import_rw.xlsx',
        'validationRules' => [
            '<strong>Kode RW</strong>: Wajib, format 3 digit (contoh: `001`, `002`).',
            '<strong>Nama RW</strong>: Nama lengkap RW (opsional, contoh: `RW 001 Sukamaju`).',
            '<strong>Status</strong>: `Aktif` atau `Non-Aktif` (default: Aktif).',
        ],
    ],
    'rt' => [
        'title' => 'Template Import Master RT',
        'file' => 'template_rt.xlsx',
        'filename' => 'template_import_rt.xlsx',
        'validationRules' => [
            '<strong>Kode RW</strong>: Wajib, format 3 digit (contoh: `001`).',
            '<strong>Kode RT</strong>: Wajib, format 3 digit (contoh: `001`, `002`).',
            '<strong>Nama RT</strong>: Nama lengkap RT (opsional, contoh: `RT 001 Mawar`).',
            '<strong>Status</strong>: `Aktif` atau `Non-Aktif` (default: Aktif).',
        ],
    ],
    'quick_count' => [
        'title' => 'Template Import Quick Count TPS',
        'file' => 'template_quick_count.xlsx',
        'filename' => 'template_import_quick_count.xlsx',
        'validationRules' => [
            '<strong>Kode TPS / Nama TPS</strong>: Wajib, kode atau nama TPS (contoh: `TPS-001`).',
            '<strong>Perolehan Suara</strong>: Angka jumlah suara terkumpul.',
            '<strong>Total Pemilih</strong>: Angka total DPT / pemilih di TPS.',
            '<strong>Catatan</strong>: Catatan tambahan di TPS (opsional).',
        ],
    ],
    'user' => [
        'title' => 'Template Import User',
        'file' => 'template_user.xlsx',
        'filename' => 'template_import_user.xlsx',
        'validationRules' => [
            '<strong>Nama Lengkap</strong>: Wajib diisi.',
            '<strong>Email</strong>: Email unik pengguna.',
            '<strong>Role</strong>: `admin` atau `user`.',
        ],
    ],
];
