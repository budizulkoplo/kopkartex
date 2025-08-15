<?php

namespace App\Http\Controllers;

use App\Models\TransaksiBengkel;
use App\Models\TransaksiBengkelDetail;
use App\Models\JasaBengkel;
use App\Models\User;
use App\Models\Barang;
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

        $users = User::where('name', 'LIKE', "%{$query}%")
                    ->select('id', 'name')
                    ->get();

        return response()->json($users);
    }

    public function getBarang(Request $request)
    {
        $keyword = trim($request->q ?? '');

        $barang = StokUnit::join('barang', 'barang.id', '=', 'stok_unit.barang_id')
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
                'barang.harga_jual'
            )
            ->get();

        return response()->json($barang);
    }

    public function getBarangByCode(Request $request){
        $barang = StokUnit::join('barang','barang.id','stok_unit.barang_id')
        ->where("barang.kode_barang", "=",$request->kode)
        ->where("stok_unit.unit_id", "=",Auth::user()->unit_kerja)
        ->select('barang.kode_barang','barang.id','barang.kode_barang as code','barang.nama_barang as text','stok_unit.stok','barang.harga_beli','barang.harga_jual')
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
            // 'dibayar'       => 'required|numeric|min:0',
            // 'kembali'       => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Simpan header transaksi
            $transaksi = TransaksiBengkel::create([
                'nomor_invoice' => $this->genCode(),
                'tanggal'       => Carbon::parse($request->tanggal)->setTimeFrom(Carbon::now()),
                'grandtotal'    => $request->grandtotal,
                'subtotal'      => $request->subtotal,
                'diskon'        => $request->diskon ?? 0,
                'metode_bayar'  => $request->metodebayar,
                'customer'      => $request->customer,
                'anggota_id'    => $request->idcustomer,
                'dibayar'       => $request->dibayar,
                'kembali'       => $request->kembali,
                'created_user'  => Auth::id(),
            ]);

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

                    // Cek stok dulu
                    $stok = StokUnit::where('unit_id', Auth::user()->unit_kerja)
                        ->where('barang_id', $idBarang)
                        ->lockForUpdate()
                        ->first();

                    if (!$stok || $stok->stok < $qty) {
                        throw new Exception("Stok barang {$idBarang} tidak mencukupi.");
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
        $hdr=TransaksiBengkel::join('users','users.id','transaksi_bengkels.created_user')
            ->select('transaksi_bengkels.*','users.name as kasir')
            ->where('transaksi_bengkels.nomor_invoice',$invoice)->first();

        // Query untuk barang
    $barangQuery = DB::table('transaksi_bengkel_details')
        ->join('barang', 'barang.id', '=', 'transaksi_bengkel_details.barang_id')
        ->select(
            'barang.nama_barang',
            'transaksi_bengkel_details.qty',
            'transaksi_bengkel_details.harga',
            DB::raw("'barang' as tipe") // kasih label tipe
        )
        ->where('transaksi_bengkel_id', $hdr->id)
        ->where('jenis', 'barang');

    $jasaQuery = DB::table('transaksi_bengkel_details')
        ->join('jasa_bengkel', 'jasa_bengkel.id', '=', 'transaksi_bengkel_details.jasa_id')
        ->select(
            'jasa_bengkel.nama_jasa as nama_barang',
            'transaksi_bengkel_details.qty',
            'transaksi_bengkel_details.harga',
            DB::raw("'jasa' as tipe") // kasih label tipe
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
