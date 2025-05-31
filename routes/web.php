<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MutasiStockController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimpananController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
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
});
Route::prefix('penerimaan')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->namespace('simpanan')->group(function () {
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
Route::prefix('simpanan')->middleware(['auth', 'verified', 'role:superadmin|admin', 'global.app'])->namespace('simpanan')->group(function () {
    Route::get('/', [SimpananController::class, 'index'])->name('simpanan.list');
    Route::get('/getdata', [SimpananController::class, 'getdata'])->name('simpanan.getdata');
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

require __DIR__.'/auth.php';
