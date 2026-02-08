<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AmbilBarangController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MutasiStockController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\SimpananController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\JasaBengkelController;
use App\Http\Controllers\TransaksiBengkelController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BarangBengkelController;
use App\Http\Controllers\TagihanController;

//LAPORAN
use App\Http\Controllers\LaporanController;

// Bagian Mobile UI
use App\Http\Controllers\Mobile\DashboardController;
use App\Http\Controllers\Mobile\BelanjaController;
use App\Http\Controllers\Mobile\MobileProfileController;
use App\Http\Controllers\Mobile\MobilePinjamanController;
use App\Http\Controllers\Mobile\MobileStokOpnameController;
use App\Http\Controllers\MobileController;

Route::get('/login', [AuthenticatedSessionController::class, 'create']);

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Route::get('/dashboard', function () {
    //     return view('dashboard');
    // })->middleware('global.app:admin')->name('dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->middleware('global.app:admin')->name('dashboard');
    // Route::get('/home', function () {
    //     return view('home');
    // })->middleware('global.app:user')->name('mobile.home');
    // routes/web.php
    Route::get('/admin/pesanan-hari-ini', [AdminDashboardController::class, 'pesananHariIni']);
    Route::get('/admin/data-pesanan-hari-ini', [AdminDashboardController::class, 'pesananHariIniData'])->name('dashboard.pesananHariIniData');

});

// UI untuk mobile end users
Route::middleware(['auth'])->prefix('mobile')->name('mobile.')->group(function () {
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

Route::middleware('auth', 'global.app')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile', [ProfileController::class, 'upload'])->name('profile.upload');
});
Route::prefix('retur')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
    Route::get('/', [ReturController::class, 'index'])->name('retur.form');
    Route::get('/getbarang', [ReturController::class, 'getBarang'])->name('retur.getbarang');
    Route::get('/getbarangbycode', [ReturController::class, 'getBarangByCode'])->name('retur.getbarangbycode');
    Route::post('/store', [ReturController::class, 'store'])->name('retur.store');
    Route::get('/datatable', [ReturController::class, 'getDataTable'])->name('retur.datatable');
    Route::get('/list', [ReturController::class, 'ListData'])->name('retur.list');
});
Route::prefix('ambilbarang')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
    Route::get('/', [AmbilBarangController::class, 'index'])->name('ambil.list');
    Route::get('/getPenjualan', [AmbilBarangController::class, 'getPenjualan'])->name('ambil.getPenjualan');
    Route::get('/getPenjualanDtl/{id}', [AmbilBarangController::class, 'getPenjualanDtl'])->name('ambil.getPenjualanDtl');
    Route::put('/AmbilBarang', [AmbilBarangController::class, 'AmbilBarang'])->name('ambil.AmbilBarang');
    Route::delete('/delitem', [AmbilBarangController::class, 'DeleteItem'])->name('ambil.delitem');
});
Route::prefix('stock')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
    Route::get('/', [StockOpnameController::class, 'index'])->name('stockopname.index'); // daftar barang
    Route::get('/form', [StockOpnameController::class, 'form'])->name('stockopname.form'); // form opname
    Route::get('/getbarang', [StockOpnameController::class, 'getBarang'])->name('stockopname.getbarang');
    Route::get('/getbarangbycode', [StockOpnameController::class, 'getBarangByCode'])->name('stockopname.getbarangbycode');
    Route::post('/store', [StockOpnameController::class, 'store'])->name('stockopname.store');
    Route::post('/mulai', [StockOpnameController::class, 'mulaiOpname'])->name('stockopname.mulai');
    Route::post('/scan', [StockOpnameController::class, 'scanBarang'])->name('stockopname.scan');
    Route::post('/insert-old', [StockOpnameController::class, 'insertFromOld'])->name('stockopname.insertOld');
    Route::post('/verify-password', [StockOpnameController::class, 'verifyPassword'])->name('stockopname.verifyPassword');
    Route::get('/barang-ajax', [StockOpnameController::class, 'getBarangAjax'])->name('stockopname.barangajax');
});

Route::prefix('master/jasabengkel')
    ->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])
    ->name('master.jasabengkel.')
    ->group(function () {
        Route::get('/', [JasaBengkelController::class, 'index'])->name('index'); // daftar jasa
        Route::get('/getdata', [JasaBengkelController::class, 'getdata'])->name('getdata'); // ajax datatable
        Route::get('/getcode', [JasaBengkelController::class, 'getCode'])->name('getcode'); // generate kode baru
        Route::post('/cekcode', [JasaBengkelController::class, 'cekCode'])->name('cekcode'); // cek kode unik
        Route::post('/store', [JasaBengkelController::class, 'store'])->name('store'); // simpan / update data
        Route::post('/hapus', [JasaBengkelController::class, 'hapus'])->name('hapus'); // hapus data
    });

// Route::prefix('stock')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
//     Route::get('/', [StockOpnameController::class, 'index'])->name('stockopname.form');
//     Route::get('/getbarang', [StockOpnameController::class, 'getBarang'])->name('stockopname.getbarang');
//     Route::get('/getbarangbycode', [StockOpnameController::class, 'getBarangByCode'])->name('stockopname.getbarangbycode');
//     Route::post('/store', [StockOpnameController::class, 'store'])->name('stockopname.store');
//     // Route::get('/datatable', [ReturController::class, 'getDataTable'])->name('retur.datatable');
//     // Route::get('/list', [ReturController::class, 'ListData'])->name('retur.list');
// });
Route::prefix('penerimaan')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
    Route::get('/', [PenerimaanController::class, 'index'])->name('penerimaan.form');
    Route::get('/getbarang', [PenerimaanController::class, 'getBarang'])->name('penerimaan.getbarang');
    Route::get('/getbarangbycode', [PenerimaanController::class, 'getBarangByCode'])->name('penerimaan.getbarangbycode');
    Route::post('/store', [PenerimaanController::class, 'store'])->name('penerimaan.store');
    Route::get('/getsupplier', [PenerimaanController::class, 'getSupplier'])->name('penerimaan.getsupplier');
    Route::post('/store-supplier', [PenerimaanController::class, 'storeSupplier'])->name('penerimaan.store-supplier');
    Route::get('/getinvoice', [PenerimaanController::class, 'getInvoice'])->name('penerimaan.getinvoice');
    Route::get('/riwayat', [PenerimaanController::class, 'Riwayat'])->name('penerimaan.riwayat');
    Route::get('/detail/{id}', [PenerimaanController::class, 'getDetail'])->name('penerimaan.getdetail');
    Route::get('/nota/{invoice}', [PenerimaanController::class, 'nota'])->name('penerimaan.nota');
    Route::post('/revisi', [PenerimaanController::class, 'prosesRevisi'])->name('penerimaan.revisi');
    Route::post('/batalkan/{id}', [PenerimaanController::class, 'batalkanPenerimaan'])->name('penerimaan.batalkan');
    Route::post('/update-status/{id}', [PenerimaanController::class, 'updateStatusBayar'])->name('penerimaan.update-status');
    // Tambahkan route edit jika diperlukan
    Route::get('/edit/{id}', [PenerimaanController::class, 'edit'])->name('penerimaan.edit');
    Route::post('/update/{id}', [PenerimaanController::class, 'update'])->name('penerimaan.update');

    Route::post('/store-barang', [PenerimaanController::class, 'storeBarang'])->name('penerimaan.store-barang');
    Route::get('/kategori', [PenerimaanController::class, 'getKategori'])->name('penerimaan.kategori');
});

// routes/web.php atau di grup route penerimaan
Route::prefix('penerimaan')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
    // ... route yang sudah ada
    
    Route::post('/store-supplier', [PenerimaanController::class, 'storeSupplier'])->name('penerimaan.store-supplier');
});

Route::prefix('penjualan')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
    Route::get('/', [PenjualanController::class, 'index'])->name('jual.form');
    Route::get('/umum', [PenjualanController::class, 'indexUmum'])->name('jual.umum.form');
    
    Route::get('/getbarang', [PenjualanController::class, 'getBarang'])->name('jual.getbarang');
    Route::get('/getbarang-umum', [PenjualanController::class, 'getBarangUmum'])->name('jual.umum.getbarang');
    
    Route::get('/getanggota', [PenjualanController::class, 'getAnggota'])->name('jual.getanggota');
    
    Route::get('/getinv', [PenjualanController::class, 'getInvoice'])->name('jual.getinv');
    Route::get('/getinv-umum', [PenjualanController::class, 'getInvoiceUmum'])->name('jual.umum.getinv');
    
    Route::get('/getbarangbycode', [PenjualanController::class, 'getBarangByCode'])->name('jual.getbarangbycode');
    Route::get('/getbarangbycode-umum', [PenjualanController::class, 'getBarangByCodeUmum'])->name('jual.umum.getbarangbycode');
    
    Route::post('/store', [PenjualanController::class, 'Store'])->name('jual.store');
    Route::post('/store-umum', [PenjualanController::class, 'StoreUmum'])->name('jual.umum.store');
    
    Route::get('/nota/{invoice}', [PenjualanController::class, 'nota'])->name('jual.nota');
    Route::post('/cektanggungan', [PenjualanController::class, 'CekTanggungan'])->name('jual.cektanggungan');
    Route::get('/riwayat', [PenjualanController::class, 'RiwayatPenjualan'])->name('jual.riwayat');

    Route::get('detail/{id}', [PenjualanController::class, 'getDetail'])->name('penjualan.detail');
    Route::post('retur', [PenjualanController::class, 'prosesRetur'])->name('penjualan.retur');
});

Route::prefix('approval')->middleware(['auth', 'verified', 'role:superadmin|admin|hrd|pengurus', 'global.app'])->group(function () {
    Route::get('/', [ApprovalController::class, 'index'])->name('app.list');
    Route::get('/gethutang', [ApprovalController::class, 'getHutang'])->name('app.gethutang');
    Route::put('/setapp', [ApprovalController::class, 'setapproval'])->name('app.set');
    Route::delete('/batal', [ApprovalController::class, 'Batal'])->name('app.batal');
    Route::get('/dtlcicilan', [ApprovalController::class, 'CicilanDtl'])->name('app.dtlcicilan');
    
});
Route::prefix('bengkel')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
        Route::get('/', [TransaksiBengkelController::class, 'index'])->name('bengkel.form');
        Route::get('/getbarang', [TransaksiBengkelController::class, 'getBarang'])->name('bengkel.getbarang');
        Route::get('/getanggota', [TransaksiBengkelController::class, 'getAnggota'])->name('bengkel.getanggota');
        Route::get('/getjasa', [TransaksiBengkelController::class, 'getJasa'])->name('bengkel.getjasa');
        Route::get('/getinv', [TransaksiBengkelController::class, 'getInvoice'])->name('bengkel.getinv');
        Route::get('/getbarangbycode', [TransaksiBengkelController::class, 'getBarangByCode'])->name('bengkel.getbarangbycode');
        Route::post('/store', [TransaksiBengkelController::class, 'store'])->name('bengkel.store');
        Route::get('/nota/{invoice}', [TransaksiBengkelController::class, 'nota'])->name('bengkel.nota');
    });
    
Route::prefix('mutasi')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
    Route::get('/', [MutasiStockController::class, 'index'])->name('mutasi.list');
    Route::get('/form', [MutasiStockController::class, 'FormMutasi'])->name('mutasi.form');
    Route::get('/getdata', [MutasiStockController::class, 'GetData'])->name('mutasi.getdata');
    Route::get('/dtl', [MutasiStockController::class, 'GetDataDTL'])->name('mutasi.dtl');
    Route::get('/getbarangbycode', [MutasiStockController::class, 'getBarangByCode'])->name('mutasi.getbarangbycode');
    Route::get('/getbarang', [MutasiStockController::class, 'getBarang'])->name('mutasi.getbarang');
    Route::post('/store', [MutasiStockController::class, 'store'])->name('mutasi.store');
    Route::post('/kembalikan', [MutasiStockController::class, 'Kembalikan'])->name('mutasi.kembalikan');
    Route::get('/nota/{id}', [MutasiStockController::class, 'nota'])->name('mutasi.nota'); 
    Route::post('/update-status', [MutasiStockController::class, 'updateStatus'])->name('mutasi.updateStatus');
    Route::get('/detail/{id}', [MutasiStockController::class, 'detail'])->name('mutasi.detail');
    Route::post('/batalkan', [MutasiStockController::class, 'batalkan'])->name('mutasi.batalkan');
});

Route::prefix('simpanan')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
        Route::get('/', [SimpananController::class,'index'])->name('simpanan.list');
        Route::get('/getdata', [SimpananController::class,'getData'])->name('simpanan.getdata');
        Route::post('/store', [SimpananController::class,'store'])->name('simpanan.store');
    });


Route::prefix('users')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->namespace('Users')->group(function () {
    Route::get('/list', [UsersController::class, 'index'])->name('users.list');
    Route::get('/permission', [UserRoleController::class, 'PermissionByRole']);
    Route::post('/add', [UserRoleController::class, 'addRole']);
    Route::delete('/delr', [UserRoleController::class, 'deleteRole']);
    Route::delete('/delp', [UserRoleController::class, 'deletePermission']);
    Route::get('/getdata', [UsersController::class, 'getdata'])->name('users.getdata');
    Route::post('/assignRole', [UsersController::class, 'kasihRole'])->name('users.assignRole');
    Route::post('/password/update', [UsersController::class, 'updatePassword'])->name('users.updatepassword');
    Route::get('/getcode', [UsersController::class, 'getcode'])->name('users.getcode');
    Route::post('/store', [UsersController::class, 'Store'])->name('users.store');
});
Route::prefix('anggota')->middleware(['auth', 'verified', 'role:superadmin|admin|bendahara', 'global.app'])->namespace('Anggota')->group(function () {
    Route::get('/list', [AnggotaController::class, 'index'])->name('anggota.list');
    Route::get('/getdata', [AnggotaController::class, 'getdata'])->name('anggota.getdata');
    Route::post('/password/update', [AnggotaController::class, 'updatePassword'])->name('anggota.updatepassword');
    Route::get('/getcode', [AnggotaController::class, 'getcode'])->name('anggota.getcode');
    Route::post('/store', [AnggotaController::class, 'Store'])->name('anggota.store');
});
Route::prefix('unit')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
    Route::get('/', [UnitController::class, 'index'])->name('unit.list');
    Route::get('/add', [UnitController::class, 'AddForm'])->name('unit.add');
    Route::get('/edit/{id}', [UnitController::class, 'EditForm'])->name('unit.edit');
    Route::post('/store', [UnitController::class, 'Store'])->name('unit.StorePost');
    Route::put('/store/{id}', [UnitController::class, 'Store'])->name('unit.StorePut');
    Route::get('/hapus/{id}', [UnitController::class, 'Hapus'])->name('unit.Hapus');
});
Route::prefix('barang')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
    Route::get('/', [BarangController::class, 'index'])->name('barang.list');
    Route::get('/getdata', [BarangController::class, 'getdata'])->name('barang.getdata');
    Route::post('/store', [BarangController::class, 'Store'])->name('barang.store');
    Route::get('/getcode', [BarangController::class, 'getCode'])->name('barang.getcode');
    Route::get('/cekcode', [BarangController::class, 'CekCode'])->name('barang.cekcode');
    Route::delete('/hapus', [BarangController::class, 'Hapus'])->name('barang.hapus');
});

    
    // Barang Bengkel
Route::prefix('barangbengkel')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
        Route::get('/', [BarangBengkelController::class, 'index'])->name('barangbengkel.index');
        Route::get('/getdata', [BarangBengkelController::class, 'getdata'])->name('barangbengkel.getdata');
        Route::get('/getcode', [BarangBengkelController::class, 'getCode'])->name('barangbengkel.getcode');
        Route::post('/cekcode', [BarangBengkelController::class, 'CekCode'])->name('barangbengkel.cekcode');
        Route::post('/store', [BarangBengkelController::class, 'Store'])->name('barangbengkel.store');
        Route::post('/quickadd', [BarangBengkelController::class, 'quickAdd'])->name('barangbengkel.quickadd');
        Route::delete('/hapus', [BarangBengkelController::class, 'Hapus'])->name('barangbengkel.hapus');
        Route::get('/getsingledata', [BarangBengkelController::class, 'getSingleData'])->name('barangbengkel.getsingledata');
    });

    

Route::prefix('supplier')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
    Route::get('/', [SupplierController::class, 'index'])->name('supplier.list');
    Route::get('/getdata', [SupplierController::class, 'getdata'])->name('supplier.getdata');
    Route::post('/store', [SupplierController::class, 'Store'])->name('supplier.store');
    Route::get('/getcode', [SupplierController::class, 'getCode'])->name('supplier.getcode');
    Route::get('/cekcode', [SupplierController::class, 'CekCode'])->name('supplier.cekcode');
    Route::delete('/hapus', [SupplierController::class, 'Hapus'])->name('supplier.hapus');
});

Route::get('/ss', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'global.app']);

Route::prefix('roles')->middleware(['auth', 'verified', 'role:superadmin', 'global.app'])->group(function () {
    Route::get('/list', [UserRoleController::class, 'index'])->name('roles.list');
    Route::get('/permission', [UserRoleController::class, 'PermissionByRole']);
    Route::post('/add', [UserRoleController::class, 'addRole']);
    Route::delete('/delr', [UserRoleController::class, 'deleteRole']);
    Route::delete('/delp', [UserRoleController::class, 'deletePermission']);
    Route::post('/swcp', [UserRoleController::class, 'PermissionfromRole'])->name('roles.switch');
});
Route::prefix('menu')->middleware(['auth', 'verified', 'role:superadmin', 'global.app'])->namespace('menus')->group(function () {
    Route::get('/list', [MenuController::class, 'index'])->name('menu.list');
    Route::get('/data/{role}', [MenuController::class, 'datamenu'])->name('menu.data');
    Route::put('/update', [MenuController::class, 'update'])->name('menu.update');
    Route::get('/test', function () {
        return response()->json(request()->menu);
    });
});
Route::prefix('doc')->middleware(['auth', 'verified'])->group(function () {
    Route::get('download/{filename}', function ($filename) {
        if (!Auth::check()) {abort(403);}
        $path = storage_path("app/private/doc/{$filename}");
        if (!file_exists($path)) {abort(404);}
        return Response::download($path);
    });
    Route::get('file/{path}/{filename}', function ($path,$filename) {
        if (!Auth::check()) {abort(403);}
        $path = storage_path("app/private/img/{$path}/{$filename}");
        if (!File::exists($path)) {abort(404);}
        $file = File::get($path);
        $type = File::mimeType($path);
        return Response::make($file, 200)->header("Content-Type", $type);
    });
});
Route::prefix('retur')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
    Route::get('/', [ReturController::class, 'index'])->name('retur.form');
        return response()->json(request()->menu);
});

//LAPORAN
Route::prefix('laporan')->middleware(['auth', 'verified', 'role:superadmin|admin|bendahara', 'global.app'])->group(function () {
    Route::get('/stok-barang', [LaporanController::class, 'stokBarang'])->name('laporan.stokbarang');
    Route::get('/stok-barang/data', [LaporanController::class, 'stokBarangData'])->name('laporan.stokbarang.data');
    Route::get('/penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');
    Route::get('/penjualan/data', [LaporanController::class, 'penjualanData'])->name('laporan.penjualan.data');
    Route::get('/penerimaan', [LaporanController::class, 'penerimaanLaporan'])->name('laporan.penerimaan');
    Route::get('/penerimaan/data', [LaporanController::class, 'penerimaanData'])->name('laporan.penerimaan.data');
    Route::get('/retur', [LaporanController::class, 'retur'])->name('laporan.retur');
    Route::get('/retur/data', [LaporanController::class, 'returData'])->name('laporan.retur.data');
    Route::get('/stok-opname', [LaporanController::class, 'stokOpname'])->name('laporan.stokopname');
    Route::get('/stok-opname/data', [LaporanController::class, 'stokOpnameData'])->name('laporan.stokopname.data');
    Route::get('/mutasi-stok', [LaporanController::class, 'mutasiStok'])->name('laporan.mutasi_stok');
    Route::get('/mutasi-stok/data', [LaporanController::class, 'mutasiStokData'])->name('laporan.mutasi_stok.data');
    Route::get('/penjualan-detail', [LaporanController::class, 'penjualanDetail'])->name('laporan.penjualan_detail');
    Route::get('/penjualan-detail/data', [LaporanController::class, 'penjualanDetailData'])->name('laporan.penjualan_detail.data');
    Route::get('/tagihan', [LaporanController::class, 'penjualanTagihan'])->name('laporan.tagihan');
    Route::get('/tagihan/data', [LaporanController::class, 'penjualanTagihanData'])->name('laporan.tagihan.data');
    Route::get('/laporan/tagihan/statistics', [LaporanController::class, 'getStatistics'])
        ->name('laporan.tagihan.statistics');
    Route::post('/tagihan/pelunasan', [LaporanController::class, 'pelunasanTagihan'])->name('laporan.tagihan.pelunasan');
    Route::post('/tagihan/pelunasan-semua', [LaporanController::class, 'pelunasanSemuaTagihan'])->name('laporan.tagihan.pelunasan_semua');

});

Route::middleware(['auth'])->prefix('mobile/belanja')->name('mobile.belanja.')->group(function () {
    Route::get('/', [BelanjaController::class, 'index'])->name('toko');
    Route::get('/produk/{unitId}', [BelanjaController::class, 'produk'])->name('produk');

    // keranjang
    Route::post('/cart/add', [BelanjaController::class, 'addToCart'])->name('cart.add');
    Route::get('/cart', [BelanjaController::class, 'cart'])->name('cart');
    Route::post('/cart/update', [BelanjaController::class, 'updateCart'])->name('cart.update');
    Route::post('/cart/remove', [BelanjaController::class, 'removeFromCart'])->name('cart.remove');

    // checkout
    Route::get('/checkout', [BelanjaController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/process', [BelanjaController::class, 'processCheckout'])->name('checkout.process');

    // riwayat belanja
    Route::get('/history', [BelanjaController::class, 'history'])->name('history');
    Route::get('/history/{id}', [BelanjaController::class, 'historyDetail'])->name('history.detail');
    Route::delete('/cancel/{id}', [BelanjaController::class, 'cancelOrder'])->name('cancel');
    // Route untuk mendapatkan detail penjualan


});

Route::middleware(['auth'])->prefix('mobile')->name('mobile.')->group(function () {
    Route::get('/profile', [MobileProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [MobileProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth'])->prefix('mobile')->name('mobile.')->group(function () {
    Route::get('/pinjaman', [MobilePinjamanController::class, 'index'])->name('pinjaman.index');
    Route::get('/pinjaman/create', [MobilePinjamanController::class, 'create'])->name('pinjaman.create');
    Route::post('/pinjaman/store', [MobilePinjamanController::class, 'store'])->name('pinjaman.store');
});


Route::middleware(['auth'])->prefix('mobile')->name('mobile.')->group(function () {
    Route::get('/ppob', [MobileController::class, 'ppob'])->name('ppob');
});


Route::middleware(['auth'])->prefix('mobile')->name('mobile.')->group(function () {
    // Halaman scan opname
    Route::get('/stokopname', [MobileStokOpnameController::class, 'index'])->name('stokopname.index');

    // Hasil scan barcode (post)
    Route::post('/stokopname/scan', [MobileStokOpnameController::class, 'scanResult'])->name('stokopname.scan');

    // Form opname barang hasil scan
    Route::get('/stokopname/create/{id}', [MobileStokOpnameController::class, 'create'])->name('stokopname.create');

    // Simpan opname
    Route::post('/stokopname/store', [MobileStokOpnameController::class, 'store'])->name('stokopname.store');
});

Route::prefix('tagihan')->middleware(['auth', 'verified', 'role:superadmin|admin|bendahara', 'global.app'])->group(function () {
    // Transaksi
    Route::get('/', [TagihanController::class, 'index'])->name('tagihan.index');
    Route::get('/get-barang', [TagihanController::class, 'getBarangTagihan'])->name('tagihan.get_barang');
    Route::get('/get-anggota', [TagihanController::class, 'getAnggota'])->name('tagihan.get_anggota');
    Route::get('/get-invoice', [TagihanController::class, 'getInvoice'])->name('tagihan.get_invoice');
    Route::post('/store', [TagihanController::class, 'store'])->name('tagihan.store');
    Route::get('/nota/{invoice}', [TagihanController::class, 'nota'])->name('tagihan.nota');
    
    // Riwayat
    Route::get('/riwayat', [TagihanController::class, 'riwayat'])->name('tagihan.riwayat');
    Route::get('/riwayat/data', [TagihanController::class, 'riwayatData'])->name('tagihan.riwayat.data');
    Route::post('/pelunasan', [TagihanController::class, 'pelunasan'])->name('tagihan.pelunasan');
    
    // Laporan
    Route::get('/laporan', [TagihanController::class, 'laporan'])->name('tagihan.laporan');
    Route::get('/laporan/data', [TagihanController::class, 'laporanData'])->name('tagihan.laporan.data');
    Route::get('/export-excel', [TagihanController::class, 'exportExcel'])->name('tagihan.export_excel');
});


require __DIR__.'/auth.php';
