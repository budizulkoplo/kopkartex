<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanCicil;
use App\Models\PenjualanDetail;
use App\Models\PinjamanDtl;
use App\Models\PinjamanHdr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ApprovalController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.Approval', []);
    }
    public function CicilanDtl(Request $request)
    {
        $pinjam = PinjamanHdr::find($request->id);
        $sql = "
        WITH RECURSIVE seq AS (
            SELECT 1 AS n
            UNION ALL
            SELECT n+1 FROM seq WHERE n < {$pinjam->tenor}
        )
        SELECT 
            n AS cicilan_ke,
            hitung_pokok({$pinjam->nominal_pengajuan}, {$pinjam->tenor}) AS pokok,
            hitung_bunga({$pinjam->nominal_pengajuan}, {$pinjam->bunga_pinjaman}, {$pinjam->tenor}, n) AS bunga,
            hitung_cicilan({$pinjam->nominal_pengajuan}, {$pinjam->bunga_pinjaman}, {$pinjam->tenor}, n) AS jumlah
        FROM seq;
        ";
        $result = DB::select($sql);
        return response()->json($result);
    }
    public function Batal(Request $request)
    {
        $batal = PinjamanHdr::find($request->id);
        $batal->canceled_at = now();
        $batal->canceled_user = Auth::user()->id;
        $batal->canceled_note = $request->alasan;
        $batal->save();
        $batal->delete();
    }
    public function getHutang(Request $request)
    {
        if($request->jenis == 'pinjaman'){
            $jual = PinjamanHdr::join('users','users.nomor_anggota','pinjaman_hdr.nomor_anggota')
            ->whereBetween('pinjaman_hdr.tgl_pengajuan', [$request->startdate, $request->enddate])
            ->select('pinjaman_hdr.*','users.name as UserName');
            if(Auth::user()->hasRole('hrd')){
                $jual->where('VarCicilan',1);
            }
        }else{
            $jual = Penjualan::where(['metode_bayar'=>'cicilan'])
            ->whereBetween(DB::raw('DATE(tanggal)'), [$request->startdate, $request->enddate]);
            if(Auth::user()->hasRole('hrd')){
                $jual->where('VarCicilan',1);
            }
            if(Auth::user()->hasRole('admin')){
                $jual->where('unit_id',Auth::user()->unit_kerja);
            }
        }
        return DataTables::of($jual)->addIndexColumn()->make(true);
    }
    public function setapproval(Request $request){
        $code=$request->code;
        if($request->jenis === 'penjualan')
        $cek=Penjualan::find($code);
        else
        $cek=PinjamanHdr::find($code);

        if($cek->VarCicilan === 0){
            if($request->fld == 'approval1' && $cek->approval3 == 1)
            return response()->json(['error' => true,'message' => 'Dokumen sudah disetujui Pengurus!',], 422);
            if($request->fld == 'approval3' && $cek->approval1 == 0)
            return response()->json(['error' => true,'message' => 'Menunggu persetujuan Petugas!',], 422);
        }
        if($cek->VarCicilan === 1){
            if($request->fld == 'approval1' && $cek->approval2 == 1)
            return response()->json(['error' => true,'message' => 'Dokumen sudah disetujui HRD!',], 422);
            if(($request->fld == 'approval2' || $request->fld == 'approval3') && $cek->approval1 == 0)
            return response()->json(['error' => true,'message' => 'Menunggu persetujuan Petugas!',], 422);
            if($request->fld == 'approval3' && $cek->approval2 == 0)
            return response()->json(['error' => true,'message' => 'Menunggu persetujuan HRD!',], 422);
        }
        if($request->jenis === 'penjualan'){
            $update=Penjualan::where('id', $code)->update([
                $request->fld =>$request->chk ,
                $request->fld.'_at' =>now() ,
                $request->fld.'_user' => Auth::user()->id,
            ]);
            if( $request->fld == 'approval3' && $request->chk == 1){
                $penjualan=Penjualan::find($code);
                for ($i = 1; $i <= $penjualan->tenor; $i++) {
                    $pokok = DB::select("SELECT hitung_pokok(?, ?) AS jumlah", [$penjualan->grandtotal, $penjualan->tenor]);
                    $bunga = DB::select("SELECT hitung_bunga(?, ?, ?, ?) AS jumlah", [$penjualan->grandtotal, $penjualan->bunga_barang, $penjualan->tenor, $i]);
                    $cicilan = new PenjualanCicil();
                    $cicilan->penjualan_id = $penjualan->id;
                    $cicilan->cicilan = $i;
                    $cicilan->pokok = $pokok[0]->jumlah;
                    $cicilan->bunga = $bunga[0]->jumlah;
                    $cicilan->total_cicilan = $pokok[0]->jumlah+$bunga[0]->jumlah;
                    $cicilan->status = 'hutang';
                    $cicilan->save();
                }
            }
        }else{
             $update=PinjamanHdr::where('id', $code)->update([
                $request->fld =>$request->chk ,
                $request->fld.'_at' =>now() ,
                $request->fld.'_user' => Auth::user()->id,
            ]);
            if( $request->fld == 'approval3' && $request->chk == 1){
                $pinjaman=PinjamanHdr::find($code);
                for ($i = 1; $i <= $pinjaman->tenor; $i++) {
                    $pokok = DB::select("SELECT hitung_pokok(?, ?) AS jumlah", [$pinjaman->nominal_pengajuan, $pinjaman->tenor]);
                    $bunga = DB::select("SELECT hitung_bunga(?, ?, ?, ?) AS jumlah", [$pinjaman->nominal_pengajuan, $pinjaman->bunga_pinjaman, $pinjaman->tenor, $i]);
                    $cicilan = new PinjamanDtl();
                    $cicilan->id_pinjaman = $pinjaman->id_pinjaman;
                    $cicilan->cicilan = $i;
                    $cicilan->pokok = $pokok[0]->jumlah;
                    $cicilan->bunga = $bunga[0]->jumlah;
                    $cicilan->total_cicilan = $pokok[0]->jumlah+$bunga[0]->jumlah;
                    $cicilan->status = 'hutang';
                    $cicilan->save();
                }
            }
        }
        if($update){
        return response()->json(true);
        }else{
        return response()->json(false);}
    }
}
