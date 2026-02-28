<?php

namespace App\Http\Controllers;

use App\Models\TransaksiBengkel;
use App\Models\TransaksiBengkelDetail;
use App\Models\JasaBengkel;
use App\Models\Barang;
use App\Models\KonfigBunga;
use App\Models\User;
use App\Models\TransaksiBengkelCicilan;
use App\Models\PenjualanCicil;
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
            ->where('stok_unit.stok', '>', 0)
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
            'tanggal'     => 'required|date',
            'grandtotal'  => 'required|numeric|min:0',
            'subtotal'    => 'required|numeric|min:0',
            'metodebayar' => 'required|string',
        ]);

        DB::beginTransaction();

        try {

            $date = Carbon::parse($request->tanggal)->setTimeFrom(Carbon::now());

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

            // =============================
            // SET STATUS
            // =============================

            if ($request->metodebayar == 'tunai') {

                $transaksi->status = 'lunas';
                $transaksi->tenor = 0;
                $transaksi->kembali = $request->kembali;
                $transaksi->dibayar = $request->dibayar;

            } else {

                if (!$request->idcustomer) {
                    DB::rollBack();
                    return response()->json('Anggota wajib diisi untuk cicilan',500);
                }

                $transaksi->status = 'hutang';
                $transaksi->tenor = $request->jmlcicilan;
                $transaksi->bunga_barang = 0;
                $transaksi->kembali = 0;
                $transaksi->dibayar = 0;
            }

            $transaksi->save();

            // =============================
            // SIMPAN DETAIL (UNTUK SEMUA)
            // =============================

            // Simpan jasa
            if (!empty($request->jasa_id)) {

                foreach ($request->jasa_id as $i => $idJasa) {

                    $harga = $request->jasa_harga[$i] ?? 0;

                    TransaksiBengkelDetail::create([
                        'transaksi_bengkel_id' => $transaksi->id,
                        'jenis'  => 'jasa',
                        'jasa_id'=> $idJasa,
                        'harga'  => $harga,
                        'qty'    => 1,
                        'total'  => $harga,
                    ]);
                }
            }

            // Simpan barang + kurangi stok
            if (!empty($request->idbarang)) {

                foreach ($request->idbarang as $i => $idBarang) {

                    $qty   = $request->qty[$i];
                    $harga = $request->harga_jual[$i];

                    $stok = StokUnit::where('unit_id', Auth::user()->unit_kerja)
                        ->where('barang_id', $idBarang)
                        ->lockForUpdate()
                        ->first();

                    if (!$stok || $stok->stok < $qty) {
                        DB::rollBack();
                        return response()->json('Stok tidak mencukupi', 500);
                    }

                    TransaksiBengkelDetail::create([
                        'transaksi_bengkel_id' => $transaksi->id,
                        'jenis'     => 'barang',
                        'barang_id' => $idBarang,
                        'qty'       => $qty,
                        'harga'     => $harga,
                        'total'     => $qty * $harga,
                    ]);

                    $stok->decrement('stok', $qty);
                }
            }

            // =============================
            // JIKA CICILAN → LANJUT LOGIC
            // =============================

            if ($request->metodebayar == 'cicilan') {

                $details = TransaksiBengkelDetail::where(
                    'transaksi_bengkel_id',
                    $transaksi->id
                )->get();

                $totalCicilan0 = 0;
                $totalCicilan1 = 0;

                foreach ($details as $item) {

                    if ($item->jenis == 'jasa') {
                        $totalCicilan0 += $item->total;
                        continue;
                    }

                    $barang = DB::table('barang')
                        ->join('kategori','kategori.id','=','barang.idkategori')
                        ->where('barang.id',$item->barang_id)
                        ->select('kategori.cicilan')
                        ->first();

                    if ($barang) {
                        if ($barang->cicilan == 0)
                            $totalCicilan0 += $item->total;
                        else
                            $totalCicilan1 += $item->total;
                    }
                }

                $bunga = KonfigBunga::select('bunga_barang')->first();

                $cicilanpertamaKategori1 = 0;

                if ($totalCicilan1 > 0 && $request->jmlcicilan > 0) {

                    $result = DB::select(
                        "SELECT hitung_cicilan_toko(?, ?, ?, ?) AS jumlah",
                        [
                            $totalCicilan1,
                            $bunga->bunga_barang,
                            $request->jmlcicilan,
                            1
                        ]
                    );

                    $cicilanpertamaKategori1 = $result[0]->jumlah;
                }

                $cicilanpertama = $cicilanpertamaKategori1 + $totalCicilan0;

                // VALIDASI LIMIT

                $user = User::find($request->idcustomer);

                $totalToko = PenjualanCicil::where([
                    'anggota_id'=>$request->idcustomer,
                    'status'=>'hutang'
                ])->sum('total_cicilan');

                $totalBengkel = TransaksiBengkelCicilan::where([
                    'anggota_id'=>$request->idcustomer,
                    'status'=>'hutang'
                ])->sum('total_cicilan');

                $totalcicilan = $totalToko + $totalBengkel;

                $batas = (!empty($user->limit_hutang) && $user->limit_hutang > 0)
                    ? $user->limit_hutang
                    : 0.35 * $user->gaji;

                if (($totalcicilan + $cicilanpertama) > $batas) {
                    DB::rollBack();
                    return response()->json(
                        'Tidak dapat diproses, Melebihi batas limit',
                        500
                    );
                }

                // GENERATE CICILAN

                if ($totalCicilan0 > 0) {

                    TransaksiBengkelCicilan::create([
                        'transaksi_bengkel_id' => $transaksi->id,
                        'cicilan'       => 1,
                        'anggota_id'    => $request->idcustomer,
                        'pokok'         => $totalCicilan0,
                        'bunga'         => 0,
                        'total_cicilan' => $totalCicilan0,
                        'status'        => 'hutang',
                        'kategori'      => 0
                    ]);
                }

                if ($totalCicilan1 > 0) {

                    for ($i = 1; $i <= $request->jmlcicilan; $i++) {

                        $result = DB::select(
                            "SELECT hitung_cicilan_toko(?, ?, ?, ?) AS jumlah",
                            [
                                $totalCicilan1,
                                $bunga->bunga_barang,
                                $request->jmlcicilan,
                                $i
                            ]
                        );

                        TransaksiBengkelCicilan::create([
                            'transaksi_bengkel_id' => $transaksi->id,
                            'cicilan'       => $i,
                            'anggota_id'    => $request->idcustomer,
                            'pokok'         => $result[0]->jumlah,
                            'bunga'         => 0,
                            'total_cicilan' => $result[0]->jumlah,
                            'status'        => 'hutang',
                            'kategori'      => 1
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message'=>'Transaksi berhasil',
                'invoice'=>$transaksi->nomor_invoice
            ]);

        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'error'=>'Gagal menyimpan transaksi',
                'message'=>$e->getMessage()
            ],500);
        }
    }

    public function nota($invoice): View
    {
        $hdr = TransaksiBengkel::join('users','users.id','transaksi_bengkels.created_user')
            ->select('transaksi_bengkels.*','users.name as kasir')
            ->where('transaksi_bengkels.nomor_invoice',$invoice)
            ->firstOrFail();

        $dtl = TransaksiBengkelDetail::with(['barang','jasa'])
            ->where('transaksi_bengkel_id',$hdr->id)
            ->orderBy('id','asc')
            ->get();

        $cicilan = collect();

        if ($hdr->metode_bayar == 'cicilan') {
            $cicilan = TransaksiBengkelCicilan::where(
                'transaksi_bengkel_id',
                $hdr->id
            )->orderBy('cicilan','asc')->get();
        }

        return view('transaksi.bengkelNota', compact('hdr','dtl','cicilan'));
    }
}