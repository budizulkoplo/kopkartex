<?php

namespace App\Http\Controllers;

use App\Models\TransaksiBengkel;
use App\Models\TransaksiBengkelDetail;
use App\Models\JasaBengkel;
use App\Models\Barang;
use App\Models\KonfigBunga;
use App\Models\User;
use App\Models\PenjualanCicil; // Tambahkan model ini
use App\Models\StokUnit;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransaksiBengkelController extends Controller
{
    public function index(): View
    {
        return view('transaksi.Bengkel', [
            'invoice' => $this->genCode(),
            'unit' => Auth::user()->unit_kerja,
        ]);
    }

    private function genCode(): string
    {
        $total = TransaksiBengkel::withTrashed()
            ->whereDate('created_at', now()->toDateString())
            ->count();

        return 'BKL-' . now()->format('ymd') . str_pad($total + 1, 3, '0', STR_PAD_LEFT);
    }

    public function getAnggota(Request $request){
        $query = $request->get('query');

        $users = User::leftJoin('penjualan_cicilan', function($join) {
            $join->on('penjualan_cicilan.anggota_id', '=', 'users.id')
                ->where('penjualan_cicilan.status', '=', 'hutang');
        })
        ->where(function ($q) use ($query) {
            $q->where('users.nomor_anggota', 'LIKE', "%{$query}%")
            ->orWhere('users.name', 'LIKE', "%{$query}%");
        })
        ->select(
            'users.id',
            'users.name',
            'users.nomor_anggota',
            'users.limit_hutang',
            'users.gaji',
            DB::raw('SUM(penjualan_cicilan.pokok) as total_pokok')
        )
        ->groupBy('users.id', 'users.name', 'users.nomor_anggota', 'users.limit_hutang', 'users.gaji')
        ->get();

        $formatted = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'nomor_anggota' => $user->nomor_anggota,
                'limit_hutang' => $user->limit_hutang - $user->total_pokok,
                'total_pokok' => $user->total_pokok,
                'gaji' => $user->gaji,
            ];
        });

        return response()->json($formatted);
    }

    public function getBarang(Request $request)
    {
        $keyword = trim($request->q ?? '');

        $barang = StokUnit::join('barang', 'barang.id', '=', 'stok_unit.barang_id')
            ->join('kategori', 'kategori.id', '=', 'barang.idkategori')
            ->where('stok_unit.unit_id', Auth::user()->unit_kerja)
            ->where('barang.kelompok_unit', 'bengkel')
            ->whereRaw("CONCAT(barang.kode_barang, ' ', barang.nama_barang) LIKE ?", ["%{$keyword}%"])
            ->select(
                'barang.kode_barang',
                'barang.id',
                'barang.kode_barang as code',
                'barang.nama_barang as text',
                'stok_unit.stok',
                'barang.harga_beli',
                'barang.harga_jual',
                'kategori.cicilan as kategori_cicilan'
            )
            ->get();

        return response()->json($barang);
    }

    public function getBarangByCode(Request $request){
        $barang = StokUnit::join('barang','barang.id','stok_unit.barang_id')
        ->join('kategori','kategori.id','=','barang.idkategori')
        ->where("barang.kode_barang", "=",$request->kode)
        ->where("stok_unit.unit_id", "=",Auth::user()->unit_kerja)
        ->select(
            'barang.kode_barang',
            'barang.id',
            'barang.kode_barang as code',
            'barang.nama_barang as text',
            'stok_unit.stok',
            'barang.harga_beli',
            'barang.harga_jual',
            'kategori.cicilan as kategori_cicilan'
        )
        ->first();
        if($barang){
            return response()->json($barang);
        }else{
            return response()->json('error',404);
        }
        
    }

    public function getJasa(Request $request)
    {
        $keyword = trim($request->q ?? '');

        $jasa = JasaBengkel::where('nama_jasa', 'LIKE', "%{$keyword}%")
            ->select('id', 'nama_jasa as text', 'harga')
            ->get();

        return response()->json($jasa);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'       => 'required|date',
            'grandtotal'    => 'required|numeric|min:0',
            'subtotal'      => 'required|numeric|min:0',
            'diskon'        => 'nullable|numeric|min:0',
            'metodebayar'   => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $date = Carbon::parse($request->tanggal)->setTimeFrom(Carbon::now());
            
            // Create transaction header
            $transaksi = new TransaksiBengkel();
            $transaksi->nomor_invoice = $this->genCode();
            $transaksi->tanggal = $date->toDateTimeString();
            $transaksi->grandtotal = $request->grandtotal;
            $transaksi->subtotal = $request->subtotal;
            $transaksi->metode_bayar = $request->metodebayar;
            $transaksi->customer = $request->customer;
            $transaksi->anggota_id = $request->idcustomer;
            $transaksi->diskon = $request->diskon ?? 0;
            $transaksi->created_user = Auth::id();
            
            if($request->metodebayar == 'cicilan'){
                $bunga = KonfigBunga::select('bunga_barang')->first();
                $transaksi->status = 'hutang';
                $transaksi->tenor = $request->jmlcicilan;
                $user = User::find($request->idcustomer);
                
                // Hitung total per kategori cicilan (barang saja, jasa dianggap cicilan 1)
                $totalCicilan0 = 0; // Barang dengan cicilan 1x
                $totalCicilan1 = 0; // Barang dengan cicilan fleksibel
                
                // Cek barang dengan kategori cicilan
                if (!empty($request->idbarang)) {
                    foreach ($request->idbarang as $i => $idBarang) {
                        $barang = DB::table('barang')
                            ->join('kategori', 'kategori.id', '=', 'barang.idkategori')
                            ->where('barang.id', $idBarang)
                            ->select('kategori.cicilan')
                            ->first();
                        
                        if($barang) {
                            $subtotal = ($request->qty[$i] ?? 0) * ($request->harga_jual[$i] ?? 0);
                            if($barang->cicilan == 0) {
                                $totalCicilan0 += $subtotal;
                            } else {
                                $totalCicilan1 += $subtotal;
                            }
                        }
                    }
                }
                
                // Jasa selalu cicilan 1x (kategori 0)
                $totalJasa = 0;
                if (!empty($request->jasa_harga)) {
                    foreach ($request->jasa_harga as $harga) {
                        $totalJasa += ($harga ?? 0);
                    }
                }
                $totalCicilan0 += $totalJasa; // Tambahkan jasa ke cicilan 1x

                // Hitung cicilan pertama untuk kategori 1 menggunakan fungsi toko
                $cicilanpertamaKategori1 = 0;
                if($totalCicilan1 > 0 && $request->jmlcicilan > 0) {
                    $result = DB::select("SELECT hitung_cicilan_toko(?, ?, ?, ?) AS jumlah", [
                        $totalCicilan1, 
                        $bunga->bunga_barang, 
                        $request->jmlcicilan, 
                        1
                    ]);
                    $cicilanpertamaKategori1 = $result[0]->jumlah;
                }
                
                $cicilanpertama = $cicilanpertamaKategori1 + $totalCicilan0; // Tambahkan cicilan 0

                // Cek limit hutang
                $totalcicilan = PenjualanCicil::where(['anggota_id'=>$request->idcustomer,'status'=>'hutang'])
                    ->sum('total_cicilan');

                if (!empty($user->limit_hutang) && $user->limit_hutang > 0) {
                    $batas = $user->limit_hutang;
                } else {
                    $batas = 0.35 * $user->gaji;
                }
                
                if (($totalcicilan+$cicilanpertama) > $batas) {
                    return response()->json('Tidak dapat diproses, Melebihi batas limit', 500);
                }

                // Untuk toko selalu tanpa bunga
                $transaksi->bunga_barang = 0;
                $transaksi->kembali = 0;
                $transaksi->dibayar = 0;
            } else {
                // Tunai
                $transaksi->status = 'lunas';
                $transaksi->tenor = 0;
                $transaksi->kembali = $request->kembali;
                $transaksi->dibayar = $request->dibayar;
            }
            
            $transaksi->save();
            
            // Simpan jasa
            if (!empty($request->jasa_id)) {
                foreach ($request->jasa_id as $i => $idJasa) {
                    $harga = $request->jasa_harga[$i] ?? 0;

                    TransaksiBengkelDetail::create([
                        'transaksi_bengkel_id' => $transaksi->id,
                        'jenis'                => 'jasa',
                        'jasa_id'              => $idJasa,
                        'harga'                => $harga,
                        'qty'                  => 1,
                        'total'                => $harga,
                    ]);
                }
            }

            // Simpan barang
            if (!empty($request->idbarang)) {
                foreach ($request->idbarang as $i => $idBarang) {
                    $harga = $request->harga_jual[$i] ?? 0;
                    $qty   = $request->qty[$i] ?? 0;

                    // Cek stok
                    $stok = StokUnit::where('unit_id', Auth::user()->unit_kerja)
                        ->where('barang_id', $idBarang)
                        ->lockForUpdate()
                        ->first();

                    if (!$stok || $stok->stok < $qty) {
                        throw new Exception("Stok barang tidak mencukupi.");
                    }

                    TransaksiBengkelDetail::create([
                        'transaksi_bengkel_id' => $transaksi->id,
                        'jenis'                => 'barang',
                        'barang_id'            => $idBarang,
                        'harga'                => $harga,
                        'qty'                  => $qty,
                        'total'                => $harga * $qty,
                    ]);

                    // Kurangi stok
                    $stok->decrement('stok', $qty);
                }
            }
            
            // Buat cicilan jika metode cicilan
            if($request->metodebayar == 'cicilan'){
                // Buat cicilan untuk kategori 0 (hanya 1 cicilan) - termasuk jasa
                if($totalCicilan0 > 0) {
                    $cicilan0 = new PenjualanCicil();
                    $cicilan0->penjualan_id = $transaksi->id;
                    $cicilan0->cicilan = 1;
                    $cicilan0->anggota_id = $request->idcustomer;
                    $cicilan0->pokok = $totalCicilan0;
                    $cicilan0->bunga = 0;
                    $cicilan0->total_cicilan = $totalCicilan0;
                    $cicilan0->status = 'hutang';
                    $cicilan0->kategori = 0;
                    $cicilan0->jenis_transaksi = 'bengkel'; // Tambahkan jenis transaksi
                    $cicilan0->save();
                }
                
                // Buat cicilan untuk kategori 1 (sesuai jumlah cicilan)
                if($totalCicilan1 > 0 && $request->jmlcicilan > 0) {
                    for ($i = 1; $i <= $request->jmlcicilan; $i++) {
                        $result = DB::select("SELECT hitung_cicilan_toko(?, ?, ?, ?) AS jumlah", [
                            $totalCicilan1, 
                            $bunga->bunga_barang, 
                            $request->jmlcicilan, 
                            $i
                        ]);
                        
                        $cicilan = new PenjualanCicil();
                        $cicilan->penjualan_id = $transaksi->id;
                        $cicilan->cicilan = $i;
                        $cicilan->anggota_id = $request->idcustomer;
                        $cicilan->pokok = $result[0]->jumlah;
                        $cicilan->bunga = 0;
                        $cicilan->total_cicilan = $result[0]->jumlah;
                        $cicilan->status = 'hutang';
                        $cicilan->kategori = 1;
                        $cicilan->jenis_transaksi = 'bengkel'; // Tambahkan jenis transaksi
                        $cicilan->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaksi bengkel berhasil disimpan',
                'invoice' => $transaksi->nomor_invoice
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan transaksi',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function nota($invoice): View
    {
        $hdr = TransaksiBengkel::join('users','users.id','transaksi_bengkels.created_user')
            ->select('transaksi_bengkels.*','users.name as kasir')
            ->where('transaksi_bengkels.nomor_invoice',$invoice)->first();

        // Query untuk barang
        $barangQuery = DB::table('transaksi_bengkel_details')
            ->join('barang', 'barang.id', '=', 'transaksi_bengkel_details.barang_id')
            ->select(
                'barang.nama_barang',
                'transaksi_bengkel_details.qty',
                'transaksi_bengkel_details.harga',
                DB::raw("'barang' as tipe")
            )
            ->where('transaksi_bengkel_id', $hdr->id)
            ->where('jenis', 'barang');

        $jasaQuery = DB::table('transaksi_bengkel_details')
            ->join('jasa_bengkel', 'jasa_bengkel.id', '=', 'transaksi_bengkel_details.jasa_id')
            ->select(
                'jasa_bengkel.nama_jasa as nama_barang',
                'transaksi_bengkel_details.qty',
                'transaksi_bengkel_details.harga',
                DB::raw("'jasa' as tipe")
            )
            ->where('transaksi_bengkel_id', $hdr->id)
            ->where('jenis', 'jasa');

        $dtl = $barangQuery
            ->unionAll($jasaQuery)
            ->orderBy('nama_barang')
            ->get();

        return view('transaksi.bengkelNota', compact('hdr', 'dtl'));
    }
}