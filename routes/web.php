<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'global.app'])->name('dashboard');

Route::middleware('auth', 'global.app')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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
