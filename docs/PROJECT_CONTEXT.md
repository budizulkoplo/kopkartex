# Dokumentasi Project Kopkartex

Dokumen ini dibuat sebagai pegangan saat melanjutkan pengembangan Kopkartex dengan prompt di Codex/ChatGPT. Tujuannya supaya konteks dasar project tidak perlu dijelaskan ulang setiap kali.

## Identitas Project

Nama project: Kopkartex

Lokasi lokal:

```text
d:\laragon\www\kopkartex
```

Kopkartex adalah sistem manajemen koperasi berbasis web. Fitur yang terlihat dari struktur project meliputi manajemen anggota, user/role/menu, transaksi toko, transaksi bengkel, simpanan/pinjaman, stok, tagihan, laporan, dan mobile UI untuk anggota.

## Tech Stack

- Backend: Laravel 12
- PHP: 8.2 atau lebih baru
- Database: MySQL
- Frontend server-rendered: Blade
- UI: Bootstrap 5, AdminLTE, jQuery
- Build tool: Vite
- CSS utility: Tailwind tersedia, tetapi UI utama banyak memakai Bootstrap/AdminLTE
- Auth scaffold: Laravel Breeze
- Permission: `spatie/laravel-permission`
- Server-side table: `yajra/laravel-datatables-oracle`
- Tambahan penting: `mike42/escpos-php`, `predis/predis`, `laravel/octane`, `spatie/image`

## Setup Lokal

Perintah umum dari root project:

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan serve
npm run dev
```

Perintah test/build:

```bash
composer test
php artisan test
npm run build
```

Catatan environment:
- File `.env` ada di lokal.
- Saat dokumen dibuat tidak terlihat file `.env.example`.
- Jangan membagikan atau commit isi `.env`.
- README menyebut database `kopkartex`, user `root`, password kosong, tetapi tetap verifikasi `.env` lokal saat menjalankan.

## Struktur Folder

```text
app/
  Helpers/
  Http/Controllers/
  Http/Controllers/Mobile/
  Http/Middleware/
  Models/
  Providers/
bootstrap/
config/
database/
  migrations/
  seeders/
public/
resources/
  views/
routes/
  web.php
  auth.php
storage/
tests/
```

Folder paling sering disentuh:
- `routes/web.php` untuk routing fitur.
- `app/Http/Controllers` untuk logic admin/transaksi/laporan.
- `app/Http/Controllers/Mobile` untuk logic mobile anggota.
- `app/Models` untuk mapping tabel dan relasi.
- `resources/views` untuk Blade.
- `resources/views/partials/head.blade.php` dan `resources/views/partials/script.blade.php` untuk asset global.

## Routing

Routing utama ada di `routes/web.php`.

Middleware umum:

```php
$menuAccessMiddleware = ['auth', 'verified', 'menu.access', 'global.app'];
$superadminMiddleware = ['auth', 'verified', 'role:superadmin', 'global.app'];
```

Pola route:
- `/dashboard`: dashboard admin.
- `/mobile/...`: halaman mobile anggota.
- `/profile`: profil user login.
- `/master/...`: master data.
- `/penjualan`, `/penerimaan`, `/retur`, `/tagihan`: transaksi toko.
- `/bengkel`: transaksi bengkel.
- `/stock`, `/stock-adjustment`, `/mutasi`: stok.
- `/laporan/...`: laporan.
- `/roles`, `/menu`: pengaturan superadmin.

Auth route bawaan Breeze ada di `routes/auth.php`.

## Middleware dan Akses Menu

Middleware didaftarkan di `bootstrap/app.php`.

Alias penting:
- `menu.access`: `App\Http\Middleware\EnsureMenuAccess`
- `global.app`: `App\Http\Middleware\GlobalApp`
- `active.user`: `App\Http\Middleware\EnsureUserIsActive`
- `role`: Spatie `RoleMiddleware`

`GlobalApp`:
- Memastikan user login.
- Bisa membatasi UI lewat parameter, misalnya `global.app:admin`.
- Membangun tree menu dari tabel `menus` berdasarkan role user.
- Menyisipkan menu ke request sebagai `request()->menu`.

`EnsureMenuAccess`:
- Superadmin selalu lolos.
- User lain dicek lewat tabel `menus`.
- Kolom `menus.link` harus sesuai nama route, misalnya `barang.list`.
- Jika route tidak punya name, akses bisa gagal dengan 403.
- Jika menu link `barang.list`, middleware juga mengizinkan route turunan seperti `barang.getdata`.

Model `Menu`:
- Tabel: `menus`
- Role disimpan sebagai string semicolon, contoh `;superadmin;admin;`.
- Method penting: `hasRoleAccess()` dan `syncRoleNames()`.

## User dan Role

Model `User`:
- Menggunakan `HasRoles` dari Spatie.
- Menggunakan `SoftDeletes`.
- Implement `MustVerifyEmail`.
- Relasi `unit()` ke `Unit` lewat kolom `unit_kerja`.
- Kolom penting yang terlihat dipakai: `ui`, `unit_kerja`, `password`, `email_verified_at`, `username`.

Alur UI:
- User dengan `ui = user` diarahkan ke `mobile.home`.
- User dengan `ui = admin` diarahkan ke `dashboard`.
- Dashboard memakai unit user untuk membedakan konteks toko/bengkel.

## Layout dan Asset

Layout admin:

```text
resources/views/layouts/app.blade.php
```

Layout mobile:

```text
resources/views/layouts/mobile.blade.php
```

Asset global CSS:

```text
resources/views/partials/head.blade.php
```

Asset global JS:

```text
resources/views/partials/script.blade.php
```

Plugin global yang sudah tersedia:
- Bootstrap
- AdminLTE
- jQuery
- DataTables + Buttons + Responsive
- Select2
- Moment
- DateRangePicker
- Bootstrap Datepicker
- Tooltipster/Tippy
- EasyAutocomplete
- Quill
- jsTree
- WebDataRocks
- SweetAlert2 dari npm/package

## Modul dan File Utama

Dashboard:
- Controller: `app/Http/Controllers/AdminDashboardController.php`
- View: `resources/views/dashboard.blade.php`
- Partial pesanan: `resources/views/partials/_pesananHariIni.blade.php`

Master data:
- Anggota: `AnggotaController`, view `master/anggota/list.blade.php`
- Users: `UsersController`, view `master/users/list.blade.php`
- Roles: `UserRoleController`, view `master/role/list.blade.php`
- Menu: `MenuController`, view `master/menu/list.blade.php`
- Unit: `UnitController`, view `master/unit`
- Barang toko: `BarangController`, view `master/barang/list.blade.php`
- Barang bengkel: `BarangBengkelController`, view `master/barangbengkel/list.blade.php`
- Supplier: `SupplierController`, view `master/supplier/list.blade.php`
- Jasa bengkel: `JasaBengkelController`, view `master/jasabengkel/list.blade.php`
- Kategori bengkel: `KategoriBengkelController`, view `master/kategori_bengkel`

Transaksi toko:
- Penjualan: `PenjualanController`, view `transaksi/Penjualan*.blade.php`
- Penerimaan: `PenerimaanController`, view `transaksi/penerimaan*.blade.php`
- Retur: `ReturController`, view `transaksi/ReturBarang*.blade.php`
- Ambil barang: `AmbilBarangController`, view `transaksi/AmbilBarang.blade.php`
- Tagihan: `TagihanController`

Transaksi bengkel:
- Controller: `TransaksiBengkelController`
- View: `transaksi/Bengkel*.blade.php`, `transaksi/RiwayatBengkel.blade.php`

Stok:
- Stock opname: `StockOpnameController`
- Stock adjustment: `StockAdjustmentController`
- Mutasi stok: `MutasiStockController`

Laporan:
- Controller: `LaporanController`
- View folder: `resources/views/laporan`
- Route prefix: `/laporan`

Mobile:
- Dashboard mobile: `Mobile/DashboardController`
- Belanja: `Mobile/BelanjaController`
- Profile: `Mobile/MobileProfileController`
- Pinjaman: `Mobile/MobilePinjamanController`
- Stok opname: `Mobile/MobileStokOpnameController`
- PPOB: `MobileController`

## Pola Pengembangan

Saat menambah fitur admin:
1. Tambah route bernama di `routes/web.php`.
2. Gunakan middleware `$menuAccessMiddleware` jika fitur perlu kontrol akses menu.
3. Buat/update controller di `app/Http/Controllers`.
4. Buat/update Blade di `resources/views`.
5. Jika fitur masuk sidebar, tambahkan data menu di tabel `menus` dengan `link` sama seperti route name.
6. Jika tabel memakai AJAX, ikuti pola `getdata`, `.../data`, atau Yajra DataTables yang sudah ada.

Saat menambah laporan:
1. Tambah method di `LaporanController`.
2. Tambah route di group `/laporan`.
3. Buat view di `resources/views/laporan`.
4. Gunakan filter tanggal/unit/status mengikuti pola laporan existing.
5. Untuk export, cek pola method `...Export` yang sudah ada.

Saat menambah fitur mobile:
1. Tambah route prefix `mobile`.
2. Gunakan controller di namespace `App\Http\Controllers\Mobile` jika fiturnya khusus mobile.
3. Gunakan layout mobile.
4. Pastikan user `ui = user` tetap mengarah ke mobile.

## Konvensi Kode

- Gunakan nama route yang konsisten, misalnya `barang.list`, `barang.getdata`, `laporan.stokbarang.data`.
- Controller existing memakai campuran nama method PascalCase dan camelCase. Untuk kode baru, lebih baik ikuti area file yang sedang diedit agar konsisten.
- Banyak transaksi memakai soft delete dan kolom `deleted_at`; saat query historis, cek apakah perlu `whereNull('deleted_at')`.
- Untuk uang, gunakan helper rupiah/terbilang yang sudah ada di `app/Helpers`.
- Untuk akses berdasarkan unit, cek `auth()->user()->unit_kerja` atau relasi `unit`.
- Untuk stok, perhatikan tabel/model `stok_unit`, `mutasi_stok`, detail transaksi, dan status transaksi agar tidak membuat stok tidak sinkron.

## Catatan Database dan Model

Model yang terlihat:
- User, Menu, Unit
- Barang, Satuan, Kategori, Supplier, StokUnit
- KartuStok, KartuStokSaldoBulanan
- Penerimaan, PenerimaanDtl
- Penjualan, PenjualanDetail, PenjualanCicil
- ReturBarang, ReturBarangDetail
- MutasiStok, MutasiStokDetail
- StockOpnameHDR, StockOpnameDTL
- StockAdjustment, StockAdjustmentDetail
- TransaksiBengkel, TransaksiBengkelDetail, TransaksiBengkelCicilan
- SimpananHdr, SimpananDtl
- PinjamanHdr, PinjamanDtl, Angsuran, Pinbrg
- KontribusiSHU, ModalAwal, KonfigBunga
- JasaBengkel

Sebelum mengubah query, cek migration/model terkait karena beberapa nama tabel tidak sepenuhnya standar Laravel, misalnya `penjualan`, `barang`, `stok_unit`, dan tabel bengkel.

Rancangan kartu stok:
- Dokumentasi: `docs/KARTU_STOK_DESIGN.md`
- Tabel ledger: `kartu_stok`
- Tabel snapshot: `kartu_stok_saldo_bulanan`
- Model: `App\Models\KartuStok` dan `App\Models\KartuStokSaldoBulanan`
- Prinsip: semua perubahan stok dicatat sebagai mutasi baru; revisi/pembatalan memakai mutasi pembalik, bukan menghapus histori.

## Risiko/Kondisi Saat Dokumentasi Dibuat

- `routes/web.php` sangat panjang dan ada route group yang berulang. Baca konteks sekitar sebelum menambah route agar tidak bentrok.
- Ada potongan route `retur` di dekat bawah file yang tampak janggal:

```php
Route::prefix('retur')->middleware($menuAccessMiddleware)->group(function () {
    Route::get('/', [ReturController::class, 'index'])->name('retur.form');
        return response()->json(request()->menu);
});
```

Jangan otomatis menghapusnya kecuali memang tugasnya memperbaiki routing, tetapi ingat ini bisa mempengaruhi debug route.

- README lama punya karakter rusak pada emoji/simbol. Dokumentasi baru sengaja memakai ASCII.
- Seeder default belum mencerminkan data produksi/lokal penuh.

## Template Prompt untuk Lanjutan Project

Contoh prompt yang enak dipakai:

```text
Saya mau lanjut di project kopkartex. Baca AGENTS.md dan docs/PROJECT_CONTEXT.md dulu.
Tolong tambahkan fitur [nama fitur].
Area yang terkait: [controller/view/route jika tahu].
Kebutuhan:
- [poin 1]
- [poin 2]
- [poin 3]
Pastikan mengikuti pola route middleware menu.access dan UI AdminLTE yang sudah ada.
```

Contoh prompt bugfix:

```text
Di kopkartex ada bug pada [halaman/route].
Gejalanya: [jelaskan error/perilaku].
Tolong telusuri dari route, controller, view, dan model terkait, lalu perbaiki.
Jangan ubah fitur lain di luar kebutuhan bug ini.
```

Contoh prompt laporan:

```text
Tambahkan laporan [nama laporan] di kopkartex.
Filter: tanggal dari/sampai, unit, status.
Output: DataTables dan export Excel jika mudah mengikuti pola existing.
Tambahkan route, controller method, view, dan menu note jika perlu.
```
