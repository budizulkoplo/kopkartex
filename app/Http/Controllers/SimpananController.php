<?php

namespace App\Http\Controllers;

use App\Models\SimpananHdr;
use App\Models\SimpananDtl;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SimpananController extends Controller
{
    public function index()
    {
        $simpanan = SimpananHdr::with('anggota')->get();
        $anggota = User::all();
        return view('simpanan.index', compact('simpanan','anggota'));
    }

    public function create(): View
    {
        $anggota = User::all();
        return view('simpanan.create', compact('anggota'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // generate norek otomatis berdasarkan jenis simpanan
            $norek = $this->genNorek($request->jenis_simpanan);

            $simpanan = new SimpananHdr();
            $simpanan->id_anggota   = $request->id_anggota;
            $simpanan->norek        = $norek;
            $simpanan->nama_pemilik = $request->nama_pemilik;
            $simpanan->jenis_simpanan = $request->jenis_simpanan;
            $simpanan->saldo        = $request->nominal;
            $simpanan->save();

            // simpan transaksi pertama di detail
            $dtl = new SimpananDtl();
            $dtl->idsimpanan  = $simpanan->idsimpanan; // pakai primary key yg benar
            $dtl->nominal     = $request->nominal;
            $dtl->saldo_awal  = 0;
            $dtl->saldo_ahir  = $request->nominal;
            $dtl->save();

            DB::commit();
            return response()->json(['message' => 'Simpanan berhasil dibuat', 'norek' => $norek]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan simpanan', 'message' => $e->getMessage()], 500);
        }
    }

    public function setor(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $simpanan = SimpananHdr::findOrFail($id);

            $saldo_awal = $simpanan->saldo;
            $simpanan->saldo += $request->nominal;
            $simpanan->save();

            $dtl = new SimpananDtl();
            $dtl->idsimpanan  = $simpanan->id;
            $dtl->nominal     = $request->nominal;
            $dtl->saldo_awal  = $saldo_awal;
            $dtl->saldo_ahir  = $simpanan->saldo;
            $dtl->save();

            DB::commit();
            return response()->json(['message' => 'Setoran berhasil', 'saldo' => $simpanan->saldo]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal setor', 'message' => $e->getMessage()], 500);
        }
    }

    public function tarik(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $simpanan = SimpananHdr::findOrFail($id);

            if ($simpanan->saldo < $request->nominal) {
                return response()->json(['error' => 'Saldo tidak cukup'], 400);
            }

            $saldo_awal = $simpanan->saldo;
            $simpanan->saldo -= $request->nominal;
            $simpanan->save();

            $dtl = new SimpananDtl();
            $dtl->idsimpanan  = $simpanan->id;
            $dtl->nominal     = -$request->nominal; // negatif untuk penarikan
            $dtl->saldo_awal  = $saldo_awal;
            $dtl->saldo_ahir  = $simpanan->saldo;
            $dtl->save();

            DB::commit();
            return response()->json(['message' => 'Penarikan berhasil', 'saldo' => $simpanan->saldo]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal tarik', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id): View
    {
        $simpanan = SimpananHdr::with('details')->findOrFail($id);
        return view('simpanan.show', compact('simpanan'));
    }

    public function genNorek($jenis)
    {
        $prefix = match ($jenis) {
            'Simpanan Pokok'     => 'SP',
            'Simpanan Wajib'     => 'SW',
            'Simpanan Sukarela'  => 'SS',
            'Simpanan Kelompok'  => 'SKL',
            default     => '99',
        };

        $today = date("ymd");
        $total = SimpananHdr::withTrashed()
                    ->where('jenis_simpanan', $jenis)
                    ->whereDate('created_at', date("Y-m-d"))
                    ->count();

        $urut = str_pad($total + 1, 4, '0', STR_PAD_LEFT);
        return $prefix . $today . $urut; // contoh: 01 250818 0001
    }

    public function getData()
    {
        $simpanan = SimpananHdr::with('anggota')->get();
        return response()->json($simpanan);
    }

}
