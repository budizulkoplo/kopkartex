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

        return view('transaksi.BengkelNota', compact('hdr','dtl','cicilan'));
    }

    public function riwayat(Request $request)
    {
        $tanggal_awal  = $request->tanggal_awal ?? now()->startOfMonth()->toDateString();
        $tanggal_akhir = $request->tanggal_akhir ?? now()->toDateString();

        $data = TransaksiBengkel::with('user')
            ->whereDate('tanggal','>=',$tanggal_awal)
            ->whereDate('tanggal','<=',$tanggal_akhir)
            ->orderByDesc('tanggal')
            ->get();

        return view('transaksi.RiwayatBengkel', compact(
            'data',
            'tanggal_awal',
            'tanggal_akhir'
        ));
    }

    /**
     * Menampilkan form revisi transaksi
     */
    public function revise($id): View
    {
        $transaksi = TransaksiBengkel::with(['details' => function($q) {
            $q->with(['barang', 'jasa']);
        }, 'user', 'anggota']) // Tambahkan relasi user dan anggota
        ->findOrFail($id);

        // Debug: cek apakah details terload
        // dd($transaksi->details); // Uncomment untuk debug

        // Cek apakah transaksi bisa direvisi
        if ($transaksi->status == 'canceled') {
            return redirect()->route('bengkel.riwayat')
                ->with('error', 'Transaksi yang sudah dibatalkan tidak dapat direvisi');
        }

        // Cek apakah transaksi sudah lama (lebih dari 1 hari)
        if (Carbon::parse($transaksi->created_at)->diffInDays(now()) > 1) {
            return redirect()->route('bengkel.riwayat')
                ->with('error', 'Transaksi hanya dapat direvisi maksimal 1 hari setelah transaksi');
        }

        return view('transaksi.BengkelRevise', [
            'transaksi' => $transaksi,
            'unit' => Auth::user()->unit_kerja, // Ini untuk keperluan lain jika perlu
        ]);
    }

    /**
     * Update transaksi revisi
     */
    public function reviseUpdate(Request $request, $id)
    {
        // Ubah validasi items
        $request->validate([
            'tanggal'     => 'required|date',
            'grandtotal'  => 'required|numeric|min:0',
            'subtotal'    => 'required|numeric|min:0',
            'metodebayar' => 'required|string',
            'items'       => 'required|json', // Ubah dari array jadi json
        ]);

        DB::beginTransaction();

        try {
            $transaksi = TransaksiBengkel::findOrFail($id);

            // Cek status
            if ($transaksi->status == 'canceled') {
                throw new Exception('Tidak dapat merevisi transaksi yang sudah dibatalkan');
            }

            // =============================
            // KEMBALIKAN STOK LAMA
            // =============================
            $detailsLama = TransaksiBengkelDetail::where('transaksi_bengkel_id', $id)
                ->where('jenis', 'barang')
                ->get();

            foreach ($detailsLama as $detail) {
                $stok = StokUnit::where('unit_id', Auth::user()->unit_kerja)
                    ->where('barang_id', $detail->barang_id)
                    ->first();

                if ($stok) {
                    $stok->increment('stok', $detail->qty);
                }
            }

            // =============================
            // HAPUS DETAIL & CICILAN LAMA
            // =============================
            TransaksiBengkelDetail::where('transaksi_bengkel_id', $id)->delete();
            
            if ($transaksi->metode_bayar == 'cicilan') {
                TransaksiBengkelCicilan::where('transaksi_bengkel_id', $id)->delete();
            }

            // =============================
            // UPDATE HEADER
            // =============================
            $date = Carbon::parse($request->tanggal)->setTimeFrom(Carbon::now());

            $transaksi->tanggal = $date->toDateTimeString();
            $transaksi->grandtotal = $request->grandtotal;
            $transaksi->subtotal = $request->subtotal;
            $transaksi->metode_bayar = $request->metodebayar;
            $transaksi->customer = $request->customer;
            $transaksi->anggota_id = $request->idcustomer;
            $transaksi->diskon = $request->diskon ?? 0;
            $transaksi->updated_at = now();

            // Set status berdasarkan metode bayar baru
            if ($request->metodebayar == 'tunai') {
                $transaksi->status = 'lunas';
                $transaksi->tenor = 0;
                $transaksi->kembali = $request->kembali ?? 0;
                $transaksi->dibayar = $request->dibayar ?? 0;
                $transaksi->bunga_barang = 0;
            } else { // cicilan
                if (!$request->idcustomer) {
                    throw new Exception('Anggota wajib diisi untuk cicilan');
                }

                $transaksi->status = 'hutang';
                $transaksi->tenor = $request->jmlcicilan;
                $transaksi->bunga_barang = 0;
                $transaksi->kembali = 0;
                $transaksi->dibayar = 0;
            }

            $transaksi->save();

            // =============================
            // PROSES ITEMS BARU (decode JSON)
            // =============================
            $items = json_decode($request->items, true);
            
            if (!is_array($items)) {
                throw new Exception('Format items tidak valid');
            }

            foreach ($items as $item) {
                if ($item['jenis'] == 'jasa') {
                    // Simpan jasa
                    TransaksiBengkelDetail::create([
                        'transaksi_bengkel_id' => $transaksi->id,
                        'jenis' => 'jasa',
                        'jasa_id' => $item['id'],
                        'harga' => $item['harga'],
                        'qty' => 1,
                        'total' => $item['harga'],
                    ]);
                } else {
                    // Simpan barang dan kurangi stok
                    $barang = Barang::find($item['id']);
                    
                    // Cek stok
                    $stok = StokUnit::where('unit_id', Auth::user()->unit_kerja)
                        ->where('barang_id', $item['id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$stok || $stok->stok < $item['qty']) {
                        throw new Exception('Stok ' . $barang->nama_barang . ' tidak mencukupi. Stok tersedia: ' . ($stok->stok ?? 0));
                    }

                    TransaksiBengkelDetail::create([
                        'transaksi_bengkel_id' => $transaksi->id,
                        'jenis' => 'barang',
                        'barang_id' => $item['id'],
                        'qty' => $item['qty'],
                        'harga' => $item['harga'],
                        'total' => $item['qty'] * $item['harga'],
                    ]);

                    // Kurangi stok
                    $stok->decrement('stok', $item['qty']);
                }
            }

            // =============================
            // PROSES CICILAN JIKA METODE CICILAN
            // =============================
            if ($request->metodebayar == 'cicilan') {
                $this->prosesCicilanRevisi($transaksi, $request);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil direvisi',
                'invoice' => $transaksi->nomor_invoice
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal merevisi transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Proses cicilan untuk transaksi revisi
     */
    private function prosesCicilanRevisi($transaksi, $request)
    {
        $details = TransaksiBengkelDetail::where('transaksi_bengkel_id', $transaksi->id)->get();

        $totalCicilan0 = 0; // Non-cicilan
        $totalCicilan1 = 0; // Bisa dicicil

        foreach ($details as $item) {
            if ($item->jenis == 'jasa') {
                $totalCicilan0 += $item->total;
                continue;
            }

            $barang = DB::table('barang')
                ->join('kategori', 'kategori.id', '=', 'barang.idkategori')
                ->where('barang.id', $item->barang_id)
                ->select('kategori.cicilan')
                ->first();

            if ($barang) {
                if ($barang->cicilan == 0) {
                    $totalCicilan0 += $item->total;
                } else {
                    $totalCicilan1 += $item->total;
                }
            }
        }

        // Validasi limit anggota
        $user = User::find($request->idcustomer);

        $totalToko = PenjualanCicil::where([
            'anggota_id' => $request->idcustomer,
            'status' => 'hutang'
        ])->sum('total_cicilan');

        $totalBengkel = TransaksiBengkelCicilan::where([
            'anggota_id' => $request->idcustomer,
            'status' => 'hutang'
        ])->sum('total_cicilan');

        $totalCicilanLain = $totalToko + $totalBengkel;

        $batas = (!empty($user->limit_hutang) && $user->limit_hutang > 0)
            ? $user->limit_hutang
            : 0.35 * $user->gaji;

        // Hitung cicilan pertama
        $bunga = KonfigBunga::select('bunga_barang')->first();
        $cicilanPertamaKategori1 = 0;

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
            $cicilanPertamaKategori1 = $result[0]->jumlah;
        }

        $totalCicilanBaru = $cicilanPertamaKategori1 + $totalCicilan0;

        if (($totalCicilanLain + $totalCicilanBaru) > $batas) {
            throw new Exception('Melebihi batas limit hutang. Sisa limit: Rp ' . number_format($batas - $totalCicilanLain, 0, ',', '.'));
        }

        // Generate cicilan baru
        if ($totalCicilan0 > 0) {
            TransaksiBengkelCicilan::create([
                'transaksi_bengkel_id' => $transaksi->id,
                'cicilan' => 1,
                'anggota_id' => $request->idcustomer,
                'pokok' => $totalCicilan0,
                'bunga' => 0,
                'total_cicilan' => $totalCicilan0,
                'status' => 'hutang',
                'kategori' => 0
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
                    'cicilan' => $i,
                    'anggota_id' => $request->idcustomer,
                    'pokok' => $result[0]->jumlah,
                    'bunga' => 0,
                    'total_cicilan' => $result[0]->jumlah,
                    'status' => 'hutang',
                    'kategori' => 1
                ]);
            }
        }
    }

    /**
     * Membatalkan transaksi
     */
    public function cancel($id)
    {
        DB::beginTransaction();

        try {
            $transaksi = TransaksiBengkel::findOrFail($id);

            // Cek status
            if ($transaksi->status == 'canceled') {
                return redirect()->back()->with('error', 'Transaksi sudah dibatalkan sebelumnya');
            }

            // Cek apakah transaksi sudah lama (lebih dari 1 hari)
            if (Carbon::parse($transaksi->created_at)->diffInDays(now()) > 1) {
                return redirect()->back()->with('error', 'Transaksi hanya dapat dibatalkan maksimal 1 hari setelah transaksi');
            }

            // Kembalikan stok
            $details = TransaksiBengkelDetail::where('transaksi_bengkel_id', $id)
                ->where('jenis', 'barang')
                ->get();

            foreach ($details as $detail) {
                $stok = StokUnit::where('unit_id', Auth::user()->unit_kerja)
                    ->where('barang_id', $detail->barang_id)
                    ->first();

                if ($stok) {
                    $stok->increment('stok', $detail->qty);
                }
            }

            // Update status transaksi
            $transaksi->status = 'canceled';
            $transaksi->deleted_at = now();
            $transaksi->save();

            // Jika cicilan, hapus atau update status
            if ($transaksi->metode_bayar == 'cicilan') {
                TransaksiBengkelCicilan::where('transaksi_bengkel_id', $id)
                    ->update(['status' => 'canceled']);
            }

            DB::commit();

            return redirect()->route('bengkel.riwayat')
                ->with('success', 'Transaksi berhasil dibatalkan');

        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    } 

    public function cetak($id)
    {
        $trx = TransaksiBengkel::where('id',$id)->firstOrFail();
        return redirect()->route('bengkel.nota',$trx->nomor_invoice);
    }
}