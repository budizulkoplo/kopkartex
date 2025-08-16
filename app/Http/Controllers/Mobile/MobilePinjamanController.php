<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PinjamanHdr;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MobilePinjamanController extends BaseMobileController
{
    // Form pengajuan pinjaman
    public function create()
    {
        $user = Auth::user();
        return view('mobile.pinjaman.create', compact('user'));
    }

    // Simpan pengajuan
    public function store(Request $request)
    {
        $request->validate([
            'nominal_pengajuan' => 'required|numeric',
            'tenor' => 'required|integer',
            'jaminan' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        $pinjaman = PinjamanHdr::create([
            'id_pinjaman' => Str::uuid(),
            'tgl_pengajuan' => Carbon::now()->format('Y-m-d'),
            'nomor_anggota' => $user->nomor_anggota,
            'gaji' => $user->gaji,
            'nominal_pengajuan' => $request->nominal_pengajuan,
            'tenor' => $request->tenor,
            'jaminan' => $request->jaminan,
            'status' => 'pending',
        ]);

        return redirect()->route('mobile.pinjaman.create')->with('success', 'Pengajuan pinjaman berhasil dibuat.');
    }

    // Daftar pengajuan user
    public function index()
    {
        $user = Auth::user();
        $pinjaman = PinjamanHdr::where('nomor_anggota', $user->nomor_anggota)
                    ->orderBy('tgl_pengajuan','desc')->get();
        return view('mobile.pinjaman.index', compact('pinjaman'));
    }
}
