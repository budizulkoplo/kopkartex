# Rancangan Fitur Kartu Stok

Dokumen ini mencatat rancangan fitur kartu stok untuk transaksi toko koperasi Kopkartex.

## Tujuan

Kartu stok adalah buku besar pergerakan stok per barang dan per unit/lokasi. Semua transaksi yang mengubah stok harus mencatat mutasi ke tabel `kartu_stok`, sehingga laporan dapat menampilkan urutan transaksi berikut saldo berjalan.

Sumber mutasi yang wajib dicatat:
- Penjualan toko: stok keluar.
- Retur penjualan: stok masuk.
- Penerimaan/pembelian: stok masuk.
- Retur pembelian ke supplier: stok keluar.
- Stock adjustment: stok masuk, keluar, atau set saldo.
- Mutasi/pemindahan stok antar unit/lokasi: stok keluar dari unit asal dan stok masuk ke unit tujuan.
- Stock opname: selisih stok opname sebagai penyesuaian stok masuk/keluar.
- Revisi atau pembatalan transaksi: dicatat sebagai mutasi pembalik, bukan menghapus histori kartu stok.

## Tabel Utama: `kartu_stok`

Migration:

```text
database/migrations/2026_05_30_000001_create_kartu_stok_table.php
```

Model:

```text
app/Models/KartuStok.php
```

Service pencatatan aktif:

```text
app/Services/KartuStokService.php
```

Kolom penting:

- `tanggal`: tanggal dan jam mutasi stok.
- `barang_id`: barang yang bergerak.
- `unit_id`: unit/lokasi tempat saldo berubah.
- `jenis_transaksi`: tipe sumber mutasi.
- `arah`: `masuk`, `keluar`, atau `set`.
- `qty_masuk`: jumlah stok masuk.
- `qty_keluar`: jumlah stok keluar.
- `saldo_awal`: saldo sebelum mutasi.
- `saldo_akhir`: saldo setelah mutasi.
- `harga_pokok`: harga pokok/satuan saat mutasi, jika tersedia.
- `nilai_mutasi`: nilai rupiah mutasi, jika perlu laporan nilai stok.
- `nomor_referensi`: nomor invoice/nota/dokumen.
- `referensi_tipe`: nama tabel/model sumber, misalnya `penjualan`, `penerimaan`, `stock_adjustments`.
- `referensi_id`: id header transaksi sumber.
- `referensi_detail_id`: id detail transaksi sumber.
- `unit_lawan_id`: unit tujuan/asal untuk mutasi antar unit.
- `batch_id`: UUID untuk mengelompokkan beberapa baris dari satu transaksi.
- `dibalik_dari_id`: id baris kartu stok yang dibalik saat batal/revisi.
- `created_user`: user yang membuat transaksi.
- `keterangan`: catatan tambahan.

Nilai `jenis_transaksi` yang disarankan:

```text
penjualan
retur_penjualan
penerimaan
retur_pembelian
stock_adjustment
mutasi_keluar
mutasi_masuk
stock_opname
pembatalan
revisi
saldo_awal
```

Aturan pencatatan:
- Untuk `masuk`, isi `qty_masuk`, `qty_keluar = 0`, dan `saldo_akhir = saldo_awal + qty_masuk`.
- Untuk `keluar`, isi `qty_keluar`, `qty_masuk = 0`, dan `saldo_akhir = saldo_awal - qty_keluar`.
- Untuk `set`, saldo akhir berasal dari angka target. Selisih positif bisa disimpan sebagai `qty_masuk`, selisih negatif sebagai `qty_keluar`.
- Jangan update atau delete baris kartu stok untuk koreksi. Buat baris pembalik dengan `dibalik_dari_id`.

## Tabel Snapshot: `kartu_stok_saldo_bulanan`

Migration:

```text
database/migrations/2026_05_30_000002_create_kartu_stok_saldo_bulanan_table.php
```

Model:

```text
app/Models/KartuStokSaldoBulanan.php
```

Tabel ini menyimpan saldo ringkasan per periode `YYYY-MM`, barang, dan unit. Fungsinya untuk mempercepat laporan ketika data kartu stok sudah besar.

Kolom penting:
- `periode`: format `YYYY-MM`.
- `barang_id`: barang.
- `unit_id`: unit/lokasi.
- `saldo_awal`: saldo awal bulan.
- `total_masuk`: total mutasi masuk selama bulan.
- `total_keluar`: total mutasi keluar selama bulan.
- `saldo_akhir`: saldo akhir bulan.
- `generated_at`: waktu proses generate.
- `generated_by`: user yang menjalankan proses.

Tabel ini bersifat turunan dari `kartu_stok`. Jika ada koreksi transaksi masa lalu, snapshot periode terkait harus digenerate ulang.

## Alur Integrasi Ke Transaksi Existing

Integrasi dibuat melalui service khusus:

```text
app/Services/KartuStokService.php
```

Tanggung jawab service:
- Mengunci row `stok_unit` dengan `lockForUpdate`.
- Membaca saldo awal.
- Mengubah saldo di `stok_unit`.
- Menulis baris `kartu_stok`.
- Mengembalikan saldo akhir.

Controller yang sudah diintegrasikan:
- `PenjualanController`: penjualan, retur penjualan, revisi penjualan.
- `PenerimaanController`: penerimaan, revisi penerimaan, pembatalan penerimaan.
- `ReturController`: retur pembelian dan pembatalan retur pembelian.
- `MutasiStockController`: mutasi keluar/masuk antar unit dan pembatalan/pengembalian mutasi.
- `StockAdjustmentController`: stock adjustment.
- `StockOpnameController`: stock opname.
- `TransaksiBengkelController`: transaksi bengkel, revisi bengkel, pembatalan bengkel.
- `BarangBengkelController`: update stok manual barang bengkel.

Menu laporan sudah dibuat di group `/laporan`.

## Rancangan Menu Laporan

Route yang disarankan:

```php
Route::get('/kartu-stok', [LaporanController::class, 'kartuStok'])->name('laporan.kartu_stok');
Route::get('/kartu-stok/data', [LaporanController::class, 'kartuStokData'])->name('laporan.kartu_stok.data');
```

Filter laporan:
- Tanggal awal dan akhir.
- Unit/lokasi.
- Barang.
- Jenis transaksi.
- Nomor referensi.

Kolom tampilan:
- Tanggal.
- Kode barang.
- Nama barang.
- Unit.
- Jenis transaksi.
- Nomor referensi.
- Keterangan.
- Qty masuk.
- Qty keluar.
- Saldo akhir.
- User.

Untuk tampilan per barang, tambahkan baris saldo awal sebelum tanggal awal:
- Ambil dari snapshot bulanan terdekat jika tersedia.
- Jika snapshot belum tersedia, hitung dari `kartu_stok` sebelum tanggal awal.

## Catatan Penting

- `stok_unit` tetap menjadi tabel saldo stok realtime.
- `kartu_stok` menjadi audit trail historis dan sumber laporan.
- Revisi dan pembatalan transaksi jangan menghapus histori lama; buat mutasi pembalik agar jejak stok tetap bisa diaudit.
- Mutasi antar unit wajib menghasilkan dua catatan dengan `batch_id` yang sama.
- Sebelum integrasi penuh, data lama perlu proses backfill agar laporan kartu stok historis tidak kosong.

## Rencana Backfill Data Lama

Backfill dapat dibuat sebagai command artisan, misalnya:

```text
php artisan kartu-stok:backfill
```

Urutan sumber data untuk backfill:
1. Penerimaan detail.
2. Penjualan detail.
3. Retur penjualan/retur pembelian.
4. Mutasi stok.
5. Stock adjustment.
6. Stock opname.

Karena beberapa transaksi lama sudah pernah mengubah `stok_unit`, backfill hanya boleh mengisi `kartu_stok`, bukan mengubah saldo realtime.

Implementasi backfill saat ini:

```text
app/Services/KartuStokBackfillService.php
```

Service ini membaca histori transaksi existing dan mengisi `kartu_stok` tanpa mengubah `stok_unit`. Backfill awal sudah dijalankan saat integrasi ledger dibuat.
