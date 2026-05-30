# Kopkartex Codex Guide

Gunakan file ini sebagai panduan cepat saat melanjutkan project Kopkartex.

## Ringkasan Project

Kopkartex adalah aplikasi koperasi berbasis Laravel untuk transaksi toko, bengkel, simpanan/pinjaman, stok, tagihan, laporan, user/role, dan mobile UI anggota.

Stack utama:
- Laravel 12, PHP 8.2+
- Blade, Bootstrap/AdminLTE, jQuery
- Vite, Tailwind, Alpine
- MySQL
- Spatie Laravel Permission
- Yajra DataTables

## Perintah Penting

Jalankan dari root project `d:\laragon\www\kopkartex`.

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan serve
npm run dev
composer test
npm run build
```

Catatan: project ini memakai `.env` lokal dan tidak terlihat ada `.env.example` saat dokumentasi dibuat. Jangan commit isi `.env`.

## Struktur Penting

- `routes/web.php`: routing utama admin, transaksi, laporan, mobile, auth.
- `app/Http/Controllers`: controller fitur.
- `app/Http/Controllers/Mobile`: controller mobile UI anggota.
- `app/Models`: model Eloquent untuk tabel koperasi.
- `app/Http/Middleware`: middleware akses menu, status user, dan global app.
- `resources/views`: Blade admin, transaksi, laporan, mobile, auth.
- `resources/views/layouts/app.blade.php`: layout admin utama.
- `resources/views/layouts/mobile.blade.php`: layout mobile.
- `resources/views/partials/head.blade.php`: CSS/plugin global.
- `resources/views/partials/script.blade.php`: JS/plugin global.
- `database/migrations`: skema database.
- `database/seeders`: seeder awal.
- `public` dan `assets`: asset statis/plugin.

## Pola Akses dan UI

- Route admin umumnya memakai middleware `auth`, `verified`, `menu.access`, `global.app`.
- Route superadmin memakai `role:superadmin`.
- Middleware `global.app` membangun tree menu ke request berdasarkan role user.
- Middleware `menu.access` mengecek akses berdasarkan `menus.link` yang berisi nama route, misalnya `barang.list`.
- User dengan `ui = user` diarahkan ke mobile (`mobile.home`), sedangkan `ui = admin` ke dashboard.
- Layout admin memakai AdminLTE/Bootstrap, DataTables, Select2, DateRangePicker, SweetAlert, dan plugin lain dari `public/plugins`.
- Untuk tabel AJAX, ikuti pola controller existing dengan Yajra DataTables dan route `.../data` atau `.../getdata`.

## Modul Besar

- Dashboard: `AdminDashboardController`, view `dashboard.blade.php`.
- Master data: anggota, user, role, menu, unit, barang toko, barang bengkel, supplier, jasa bengkel, kategori bengkel.
- Transaksi toko: penjualan, penerimaan, retur, ambil barang, tagihan.
- Transaksi bengkel: transaksi bengkel, jasa, barang bengkel, riwayat/revisi/cetak.
- Stok: mutasi stok, stock opname, stock adjustment, stok unit.
- Koperasi: simpanan, pinjaman/angsuran, PINBRG, SHU.
- Laporan: stok, penjualan, penerimaan, retur, opname, mutasi, tagihan, harian toko/bengkel, modal awal.
- Mobile: home, belanja, cart, checkout, riwayat, profile, pinjaman, PPOB, stok opname.

## Konvensi Saat Mengubah Kode

- Ikuti pola controller dan Blade yang sudah ada, terutama route name, AJAX response, DataTables, dan Bootstrap classes.
- Jika menambah menu baru, pastikan ada route name dan data `menus.link` sesuai route name agar `menu.access` tidak memblokir.
- Jika menambah route admin biasa, gunakan `$menuAccessMiddleware` di `routes/web.php` bila fitur harus muncul sebagai menu.
- Jika menambah halaman admin, gunakan layout `<x-app-layout>` dan siapkan `$pagetitle`, `$csscustom`, atau `$jscustom` sesuai pola view existing.
- Untuk perubahan stok/transaksi, cek model dan controller terkait karena beberapa proses saling mempengaruhi `stok_unit`, detail transaksi, status pembayaran, dan riwayat.
- Jangan ubah file backup lama seperti `PenjualanController20250815.php` atau view bertanggal kecuali memang diminta.
- Jangan menaruh secret dari `.env` ke dokumentasi atau commit.

## Hal yang Perlu Diperhatikan

- `routes/web.php` besar dan memiliki beberapa route group berulang. Baca area sekitar route sebelum menambah atau mengubah route.
- Ada potongan route `retur` di bagian bawah `web.php` yang tampak tidak normal karena berisi `return response()->json(request()->menu);` di dalam group. Jangan rapikan otomatis kecuali diminta, tapi waspadai saat debug route.
- README lama tampak encoding-nya rusak untuk emoji/simbol. Dokumentasi baru ini memakai ASCII agar aman.
- Seeder default hanya membuat `test@example.com`; data role/menu lengkap mungkin berasal dari database lokal/import lain.

## Dokumentasi Lengkap

Baca juga `docs/PROJECT_CONTEXT.md` untuk peta project yang lebih detail dan template prompt.

Untuk rancangan fitur kartu stok, baca `docs/KARTU_STOK_DESIGN.md`.
