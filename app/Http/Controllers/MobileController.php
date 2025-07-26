<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    public function index()
{
    $user = Auth::user();

    // Redirect kalau bukan user
    if ($user->ui !== 'user') {
        return redirect()->route('dashboard');
    }

    // Ambil menu dari tabel mobilemenu
    $drawerMenus = \Illuminate\Support\Facades\DB::table('mobilemenu')
        ->where('status', 'drawer')
        ->whereRaw("FIND_IN_SET(?, level)", [$user->ui])
        ->orderBy('idmenu')
        ->get();

    return view('mobile.index', compact('user', 'drawerMenus'));
}

}
