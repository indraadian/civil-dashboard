# 7. Import Template

## Problem

Saat ini seluruh module masih menggunakan template Import milik module Civil.

Akibatnya:

- Format template tidak sesuai dengan kebutuhan masing-masing module.
- Perubahan template pada Civil dapat mempengaruhi module lain.
- Setiap module tidak dapat memiliki struktur import yang berbeda.

## Expected Result

Setiap module harus memiliki template Import masing-masing.

Contoh:

```
Civil
    import-template.xlsx

User
    import-template.xlsx

RW
    import-template.xlsx

RT
    import-template.xlsx

Quick Count
    import-template.xlsx
```

atau mengikuti struktur folder yang sudah digunakan project.

---

## Requirement

- Setiap module memiliki template Import sendiri.
- Download template harus mengambil template berdasarkan module yang sedang dibuka.
- Jangan lagi menggunakan template Civil secara global.
- Gunakan Import Engine yang sudah ada, cukup refactor agar template bersifat per-module.
- Hindari duplicate code, cukup tambahkan mekanisme agar engine dapat menentukan template berdasarkan konfigurasi module.

---

## Configuration Driven

Template Import harus berasal dari konfigurasi module.

Contoh:

- Module menentukan lokasi template melalui Config Class.
- Import Engine membaca konfigurasi tersebut.
- Import Engine mengirimkan template yang sesuai.

Dengan demikian penambahan module baru tidak memerlukan perubahan pada Import Engine.

---

## Final Validation

Pastikan:

- Civil menggunakan template Civil.
- User menggunakan template User.
- RW menggunakan template RW.
- RT menggunakan template RT.
- Quick Count menggunakan template Quick Count.
- Import Engine tetap reusable.
- Tidak ada hardcode path template.
- Tidak ada duplicate code.