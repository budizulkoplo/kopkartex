<?php

use App\Http\Controllers\AmbilBarangController;
use App\Http\Controllers\AnggotaController;
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

// Bagian Mobile UI
use App\Http\Controllers\Mobile\DashboardController;
use App\Http\Controllers\Mobile\BelanjaController;
use App\Http\Controllers\Mobile\MobileProfileController;
use App\Http\Controllers\Mobile\MobilePinjamanController;

Route::get('/', [AuthenticatedSessionController::class, 'create']);
// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'global.app'])->name('dashboard');

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
});
Route::prefix('stock')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
    Route::get('/', [StockOpnameController::class, 'index'])->name('stockopname.index'); // <- tampilan daftar barang
    Route::get('/form', [StockOpnameController::class, 'form'])->name('stockopname.form'); // <- tampilkan form opname
    Route::get('/getbarang', [StockOpnameController::class, 'getBarang'])->name('stockopname.getbarang');
    Route::get('/getbarangbycode', [StockOpnameController::class, 'getBarangByCode'])->name('stockopname.getbarangbycode');
    Route::post('/store', [StockOpnameController::class, 'store'])->name('stockopname.store');
    Route::post('/stockopname/mulai', [StockOpnameController::class, 'mulaiOpname'])->name('stockopname.mulai');
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
    Route::get('ss',function(){
        dd(Hash::make('12345678'));
    });
    Route::get('/', [PenerimaanController::class, 'index'])->name('penerimaan.form');
    Route::get('/getbarang', [PenerimaanController::class, 'getBarang'])->name('penerimaan.getbarang');
    Route::get('/getbarangbycode', [PenerimaanController::class, 'getBarangByCode'])->name('penerimaan.getbarangbycode');
    Route::post('/store', [PenerimaanController::class, 'store'])->name('penerimaan.store');
    
});

Route::prefix('penjualan')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->group(function () {
    Route::get('/', [PenjualanController::class, 'index'])->name('jual.form');
    Route::get('/getbarang', [PenjualanController::class, 'getBarang'])->name('jual.getbarang');
    Route::get('/getanggota', [PenjualanController::class, 'getAnggota'])->name('jual.getanggota');
    Route::get('/getinv', [PenjualanController::class, 'getInvoice'])->name('jual.getinv');
    Route::get('/getbarangbycode', [PenjualanController::class, 'getBarangByCode'])->name('jual.getbarangbycode');
    Route::post('/store', [PenjualanController::class, 'Store'])->name('jual.store');
    Route::get('/nota/{invoice}', [PenjualanController::class, 'nota'])->name('jual.nota');
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
    Route::get('/assignRole', [UsersController::class, 'kasihRole'])->name('users.assignRole');
    Route::post('/password/update', [UsersController::class, 'updatePassword'])->name('users.updatepassword');
    Route::get('/getcode', [UsersController::class, 'getcode'])->name('users.getcode');
    Route::post('/store', [UsersController::class, 'Store'])->name('users.store');
});
Route::prefix('anggota')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->namespace('Anggota')->group(function () {
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

// UI untuk mobile end users
Route::middleware(['auth'])->prefix('mobile')->name('mobile.')->group(function () {
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
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


require __DIR__.'/auth.php';
