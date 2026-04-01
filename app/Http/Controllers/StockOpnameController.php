<?php

namespace App\Http\Controllers;

use App\Models\StockOpnameDTL;
use App\Models\StockOpnameHDR;
use App\Models\StokUnit;
use App\Models\Barang;
use App\Models\ModalAwal;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class StockOpnameController extends Controller
{
    private function resolveBulan(?string $bulan): string
    {
        if (!is_string($bulan) || !preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            return Carbon::now()->format('Y-m');
        }

        try {
            return Carbon::createFromFormat('Y-m', $bulan)->format('Y-m');
        } catch (\Throwable $e) {
            return Carbon::now()->format('Y-m');
        }
    }

    public function index(Request $request): View
    {
        $bulan = $this->resolveBulan($request->bulan);
        $startDate = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();
        
        $unitId = Auth::user()->unit_kerja;
        
        // Cek apakah sudah ada data opname untuk bulan ini
        $hasData = StockOpnameHDR::where('id_unit', $unitId)
            ->whereBetween('tgl_opname', [$startDate, $endDate])
            ->exists();
            
        return view('transaksi.StockOpnameList', compact('bulan', 'hasData'));
    }

    public function mulaiOpname(Request $request)
    {
        $request->validate([
            'tgl_opname' => 'required|date'
        ]);
        
        $unitId = Auth::user()->unit_kerja;
        $userId = Auth::user()->id;
        $userName = Auth::user()->name;
        $tglOpname = Carbon::parse($request->tgl_opname)->format('Y-m-d');

        $bulanOpname = Carbon::parse($tglOpname)->format('Y-m');
        $startDate = Carbon::createFromFormat('Y-m', $bulanOpname)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $bulanOpname)->endOfMonth();

        DB::beginTransaction();
        try {
            // 1. Hapus data lama jika ada
            $existingOpnames = StockOpnameHDR::where('id_unit', $unitId)
                ->whereBetween('tgl_opname', [$startDate, $endDate])
                ->get();
                
            foreach ($existingOpnames as $opname) {
                // Hapus detail terlebih dahulu
                StockOpnameDTL::where('opnameid', $opname->id)->delete();
                $opname->delete();
            }

            // 2. Ambil semua barang yang ada di unit
            $barangList = DB::table('barang')
                ->leftJoin('stok_unit', function ($join) use ($unitId) {
                    $join->on('barang.id', '=', 'stok_unit.barang_id')
                        ->where('stok_unit.unit_id', '=', $unitId)
                        ->whereNull('stok_unit.deleted_at');
                })
                ->where(function ($q) use ($unitId) {
                    if ($unitId == 5) {
                        $q->where('barang.kelompok_unit', 'bengkel');
                    } else {
                        $q->where('barang.kelompok_unit', '<>', 'bengkel');
                    }
                })
                ->select(
                    'barang.id as id_barang',
                    'barang.kode_barang',
                    DB::raw('IFNULL(stok_unit.stok, 0) as stok_sistem')
                )
                ->orderBy('barang.kode_barang')
                ->get();


            // 3. Insert data baru
            foreach ($barangList as $barang) {
                $opnameHdr = new StockOpnameHDR();
                $opnameHdr->tgl_opname = $tglOpname;
                $opnameHdr->id_unit = $unitId;
                $opnameHdr->id_barang = $barang->id_barang;
                $opnameHdr->kode_barang = $barang->kode_barang;
                $opnameHdr->stock_sistem = $barang->stok_sistem;
                $opnameHdr->stock_fisik = 0; // Belum diisi
                $opnameHdr->user = $userId;
                $opnameHdr->status = 'pending';
                $opnameHdr->save();
            }

            DB::commit();
            
            return redirect()->route('stockopname.index', ['bulan' => $bulanOpname])
                ->with('success', 'Stock opname bulan ' . $bulanOpname . ' berhasil dimulai. Silakan mulai input stok fisik.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Gagal memulai stock opname: ' . $e->getMessage()]);
        }
    }

    public function form(Request $request): View
    {
        $selectedBarang = null;
        $existingData = null;
        $unitId = Auth::user()->unit_kerja;
        $bulan = $this->resolveBulan($request->bulan);

        if ($request->has('barang_id')) {
            $barangId = $request->barang_id;
            
            // Ambil data barang
            $selectedBarang = DB::table('barang')
                ->where('id', $barangId)
                ->select('id', 'kode_barang as code', 'nama_barang as text')
                ->first();
                
            if ($selectedBarang) {
                // Cek apakah sudah ada data opname untuk bulan ini
                $startDate = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
                $endDate = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();
                
                $existingData = StockOpnameHDR::with('details')
                    ->where('id_unit', $unitId)
                    ->where('id_barang', $barangId)
                    ->whereBetween('tgl_opname', [$startDate, $endDate])
                    ->first();
                    
                // Ambil stok sistem saat ini
                $stokSistem = StokUnit::where('barang_id', $barangId)
                    ->where('unit_id', $unitId)
                    ->value('stok') ?? 0;
                    
                $selectedBarang->stok_sistem = $stokSistem;
            }
        }

        return view('transaksi.StockOpname', compact('selectedBarang', 'existingData', 'bulan'));
    }

    public function getBarang(Request $request)
    {
        $unitId = Auth::user()->unit_kerja;
        $query = $request->q;
        
        $barang = DB::table('barang')
            ->leftJoin('stok_unit', function($join) use ($unitId) {
                $join->on('barang.id', '=', 'stok_unit.barang_id')
                    ->where('stok_unit.unit_id', $unitId);
            })
            ->where(function($q) use ($query) {
                $q->where('barang.kode_barang', 'like', "%{$query}%")
                  ->orWhere('barang.nama_barang', 'like', "%{$query}%");
            })
            ->select(
                'barang.id',
                'barang.kode_barang as code',
                'barang.nama_barang as text',
                DB::raw('IFNULL(stok_unit.stok, 0) as stok')
            )
            ->limit(50)
            ->get();
            
        return response()->json($barang);
    }

    public function getBarangByCode(Request $request)
    {
        $unitId = Auth::user()->unit_kerja;
        
        $barang = DB::table('barang')
            ->leftJoin('stok_unit', function($join) use ($unitId) {
                $join->on('barang.id', '=', 'stok_unit.barang_id')
                    ->where('stok_unit.unit_id', $unitId);
            })
            ->where('barang.kode_barang', $request->kode)
            ->select(
                'barang.id',
                'barang.kode_barang as code',
                'barang.nama_barang as text',
                DB::raw('IFNULL(stok_unit.stok, 0) as stok')
            )
            ->first();
            
        if ($barang) {
            return response()->json($barang);
        }
        
        return response()->json(['error' => 'Barang tidak ditemukan'], 404);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validasi
            $request->validate([
                'tgl_opname' => 'required|date',
                'barang_id' => 'required|integer',
                'qty' => 'required|array',
                'qty.*' => 'required|numeric|min:0',
                'exp' => 'required|array',
                'exp.*' => 'nullable|date'
            ]);

            $unitId = Auth::user()->unit_kerja;
            $userId = Auth::user()->id;
            $userName = Auth::user()->name;
            $barangId = $request->barang_id;
            $tglOpname = $request->tgl_opname;
            
            // Ambil data barang
            $barang = Barang::findOrFail($barangId);
            
            // Hitung total stok fisik
            $totalFisik = array_sum(array_map('floatval', $request->qty));
            
            // Ambil stok sistem
            $stokSistem = StokUnit::where('barang_id', $barangId)
                ->where('unit_id', $unitId)
                ->value('stok') ?? 0;
                
            // Cek apakah sudah ada data opname untuk bulan ini
            $bulan = Carbon::parse($tglOpname)->format('Y-m');
            $startDate = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();
            
            $opnameHdr = StockOpnameHDR::where('id_unit', $unitId)
                ->where('id_barang', $barangId)
                ->whereBetween('tgl_opname', [$startDate, $endDate])
                ->first();
                
            if (!$opnameHdr) {
                // Buat baru
                $opnameHdr = new StockOpnameHDR();
                $opnameHdr->tgl_opname = $tglOpname;
                $opnameHdr->id_unit = $unitId;
                $opnameHdr->id_barang = $barangId;
                $opnameHdr->kode_barang = $barang->kode_barang;
            }
            
            // Update header
            $opnameHdr->stock_sistem = $stokSistem;
            $opnameHdr->stock_fisik = $totalFisik;
            $opnameHdr->user = $userId;
            $opnameHdr->status = 'sukses';
            $opnameHdr->keterangan = $request->keterangan ?? null;
            $opnameHdr->save();
            
            // Hapus detail lama
            StockOpnameDTL::where('opnameid', $opnameHdr->id)->delete();
            
            // Simpan detail baru
            foreach ($request->qty as $index => $qty) {
                if ($qty > 0) {
                    $detail = new StockOpnameDTL();
                    $detail->opnameid = $opnameHdr->id;
                    $detail->id_barang = $barangId;
                    $detail->qty = $qty;
                    $detail->expired_date = !empty($request->exp[$index]) ? $request->exp[$index] : null;
                    $detail->save();
                }
            }
            
            // Update stok unit
            $stokUnit = StokUnit::where('barang_id', $barangId)
                ->where('unit_id', $unitId)
                ->first();
                
            if (!$stokUnit) {
                $stokUnit = new StokUnit();
                $stokUnit->barang_id = $barangId;
                $stokUnit->unit_id = $unitId;
            }
            
            $stokUnit->stok = $totalFisik;
            $stokUnit->save();

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Stock opname berhasil disimpan.',
                'redirect' => route('stockopname.index', ['bulan' => $bulan])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan stock opname: ' . $e->getMessage()
            ], 500);
        }
    }

    public function scanBarang(Request $request)
    {
        $request->validate([
            'kode' => 'required|string'
        ]);
        
        $kode = $request->kode;
        $unitId = Auth::user()->unit_kerja;
        $bulan = $this->resolveBulan($request->bulan);

        // Cari di barang
        $barang = DB::table('barang')
            ->where('kode_barang', $kode)
            ->select('id', 'kode_barang', 'nama_barang')
            ->first();

        if ($barang) {
            // Cek apakah sudah ada data opname untuk bulan ini
            $startDate = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();
            
            $existingOpname = StockOpnameHDR::where('id_unit', $unitId)
                ->where('id_barang', $barang->id)
                ->whereBetween('tgl_opname', [$startDate, $endDate])
                ->exists();
                
            return response()->json([
                'status' => 'found',
                'data' => $barang,
                'has_data' => $existingOpname,
                'form_url' => route('stockopname.form', [
                    'barang_id' => $barang->id,
                    'bulan' => $bulan
                ])
            ]);
        }

        // Cari di barang_ori (master lama)
        $barangOld = DB::table('barang_ori')
            ->where('kode_barang', $kode)
            ->first();

        if ($barangOld) {
            return response()->json([
                'status' => 'old',
                'data' => $barangOld
            ]);
        }

        return response()->json([
            'status' => 'notfound',
            'message' => 'Barang tidak ditemukan'
        ], 404);
    }

    public function insertFromOld(Request $request)
    {
        $request->validate([
            'kode' => 'required|string'
        ]);
        
        $kode = $request->kode;
        $unitId = Auth::user()->unit_kerja;

        $barangOld = DB::table('barang_ori')
            ->where('kode_barang', $kode)
            ->first();

        if (!$barangOld) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan di master lama'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Insert ke barang
            $barangId = DB::table('barang')->insertGetId([
                'kode_barang' => $barangOld->kode_barang,
                'nama_barang' => $barangOld->nama_barang,
                'kategori' => $barangOld->kategori,
                'satuan' => $barangOld->satuan,
                'harga_beli' => $barangOld->harga_beli ?? 0,
                'harga_jual' => $barangOld->harga_jual ?? 0,
                'kelompok_unit' => $barangOld->kelompok_unit ?? 'toko',
                'img' => $barangOld->img,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert ke stok_unit untuk semua unit
            $units = DB::table('unit')->pluck('id');
            foreach ($units as $uId) {
                DB::table('stok_unit')->insert([
                    'barang_id' => $barangId,
                    'unit_id' => $uId,
                    'stok' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil ditambahkan dari master lama',
                'data' => [
                    'id' => $barangId,
                    'kode_barang' => $barangOld->kode_barang,
                    'nama_barang' => $barangOld->nama_barang
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan barang: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        if (Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['valid' => true]);
        }

        return response()->json(['valid' => false], 401);
    }

    public function getBarangAjax(Request $request)
    {
        $unitId = Auth::user()->unit_kerja;
        $bulan = $this->resolveBulan($request->bulan);

        $startDate = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $endDate   = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();

        $query = StockOpnameHDR::query()
            ->join('barang', 'stock_opname.id_barang', '=', 'barang.id')
            ->where('stock_opname.id_unit', $unitId)
            ->whereBetween('stock_opname.tgl_opname', [$startDate, $endDate])
            ->whereNull('stock_opname.deleted_at')
            ->select([
                'stock_opname.id as opname_id',
                'stock_opname.id_barang',
                'stock_opname.kode_barang',
                'stock_opname.stock_sistem',
                'stock_opname.stock_fisik',
                'stock_opname.status',
                'stock_opname.tgl_opname',
                'barang.nama_barang'
            ]);

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('aksi', function($row) use ($bulan) {

                $url = route('stockopname.form', [
                    'barang_id' => $row->id_barang,
                    'bulan'     => $bulan
                ]);

                $btnClass = $row->status == 'sukses' ? 'btn-warning' : 'btn-primary';
                $btnText  = $row->status == 'sukses' ? 'Revisi' : 'Input';
                $btnIcon  = $row->status == 'sukses' ? 'bi-pencil-square' : 'bi-input-cursor';

                return '<a href="'.$url.'" class="btn btn-sm '.$btnClass.'">
                    <i class="bi '.$btnIcon.'"></i> '.$btnText.'
                </a>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Menyelesaikan stock opname dan menyimpan ke modal_awal
     */
    public function selesaiOpname(Request $request)
    {
        $request->validate([
            'bulan' => 'required|date_format:Y-m'
        ]);

        $unitId = Auth::user()->unit_kerja;
        $bulan = $request->bulan;
        
        $startDate = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();

        DB::beginTransaction();
        try {
            // 1. Hapus data modal_awal untuk periode dan unit yang sama jika ada
            ModalAwal::where('periode', $bulan)
                ->where('unit_id', $unitId)
                ->delete();

            // 2. Ambil semua barang yang seharusnya ada di unit ini
            $semuaBarang = DB::table('barang')
                ->leftJoin('stok_unit', function ($join) use ($unitId) {
                    $join->on('barang.id', '=', 'stok_unit.barang_id')
                        ->where('stok_unit.unit_id', '=', $unitId)
                        ->whereNull('stok_unit.deleted_at');
                })
                ->where(function ($q) use ($unitId) {
                    if ($unitId == 5) {
                        $q->where('barang.kelompok_unit', 'bengkel');
                    } else {
                        $q->where('barang.kelompok_unit', '<>', 'bengkel');
                    }
                })
                ->select(
                    'barang.id as id_barang',
                    'barang.kode_barang',
                    'barang.nama_barang',
                    'barang.harga_beli', // Menggunakan harga_beli sebagai modal
                    DB::raw('IFNULL(stok_unit.stok, 0) as stok_sistem')
                )
                ->get();

            // 3. Ambil data opname yang sudah diinput (status sukses)
            $dataOpname = StockOpnameHDR::where('id_unit', $unitId)
                ->whereBetween('tgl_opname', [$startDate, $endDate])
                ->get()
                ->keyBy('id_barang'); // Group by id_barang untuk mudah diakses

            // 4. Insert ke modal_awal untuk semua barang
            foreach ($semuaBarang as $barang) {
                // Cek apakah barang ini sudah diopname
                $opnameBarang = $dataOpname->get($barang->id_barang);
                
                // Jika sudah diopname, ambil stok_fisik, jika belum, set 0
                $stokFisik = $opnameBarang ? $opnameBarang->stock_fisik : 0;
                
                // Ambil harga beli dari barang
                $hargaBeli = $barang->harga_beli ?? 0;
                
                ModalAwal::create([
                    'periode' => $bulan,
                    'barang_id' => $barang->id_barang,
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'harga_modal' => $hargaBeli, // Simpan harga_beli ke field harga_modal
                    'unit_id' => $unitId,
                    'stok' => $stokFisik,
                    'nilai_total_barang' => $stokFisik * $hargaBeli
                ]);
            }

            // 5. Update status semua opname menjadi 'selesai' 
            // (termasuk yang statusnya pending akan dianggap selesai dengan stok 0)
            StockOpnameHDR::where('id_unit', $unitId)
                ->whereBetween('tgl_opname', [$startDate, $endDate])
                ->update(['status' => 'selesai']);

            // 6. Untuk barang yang belum ada record opname sama sekali, buat record baru dengan status selesai dan stok 0
            $barangYangSudahAdaRecord = StockOpnameHDR::where('id_unit', $unitId)
                ->whereBetween('tgl_opname', [$startDate, $endDate])
                ->pluck('id_barang')
                ->toArray();
                
            $barangBelumAdaRecord = $semuaBarang->filter(function ($barang) use ($barangYangSudahAdaRecord) {
                return !in_array($barang->id_barang, $barangYangSudahAdaRecord);
            });
            
            foreach ($barangBelumAdaRecord as $barang) {
                StockOpnameHDR::create([
                    'tgl_opname' => $endDate, // Gunakan tanggal terakhir bulan
                    'id_unit' => $unitId,
                    'id_barang' => $barang->id_barang,
                    'kode_barang' => $barang->kode_barang,
                    'stock_sistem' => $barang->stok_sistem,
                    'stock_fisik' => 0,
                    'user' => Auth::user()->id,
                    'status' => 'selesai',
                    'keterangan' => 'Auto generate saat selesai opname (stok 0)'
                ]);
            }

            DB::commit();

            // Hitung total modal
            $totalModal = ModalAwal::where('periode', $bulan)
                ->where('unit_id', $unitId)
                ->sum('nilai_total_barang');
                
            $jumlahBarangStokNol = $barangBelumAdaRecord->count();

            return response()->json([
                'success' => true,
                'message' => 'Stock opname selesai. Data modal awal berhasil disimpan.',
                'data' => [
                    'total_barang' => $semuaBarang->count(),
                    'barang_diopname' => $dataOpname->count(),
                    'barang_stok_nol' => $jumlahBarangStokNol,
                    'total_modal' => number_format($totalModal, 2),
                    'periode' => $bulan
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan stock opname: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan data modal awal
     */
    public function modalAwal(Request $request): View
    {
        $bulan = $this->resolveBulan($request->bulan);
        $unitId = Auth::user()->unit_kerja;
        
        $dataModal = ModalAwal::with(['barang', 'unit'])
            ->where('periode', $bulan)
            ->where('unit_id', $unitId)
            ->orderBy('kode_barang')
            ->get();
            
        $totalModal = $dataModal->sum('nilai_total_barang');
        
        return view('laporan.modal_awal', compact('dataModal', 'bulan', 'totalModal'));
    }

    /**
     * API untuk datatable modal awal
     */
    public function getModalAwalAjax(Request $request)
    {
        $unitId = Auth::user()->unit_kerja;
        $bulan = $this->resolveBulan($request->bulan);

        $query = ModalAwal::query()
            ->where('periode', $bulan)
            ->where('unit_id', $unitId)
            ->select([
                'id',
                'kode_barang',
                'nama_barang',
                'harga_beli',
                'stok',
                'nilai_total_barang'
            ]);

        return datatables()->of($query)
            ->addIndexColumn()
            ->editColumn('harga_beli', function($row) {
                return number_format($row->harga_beli, 2);
            })
            ->editColumn('nilai_total_barang', function($row) {
                return number_format($row->nilai_total_barang, 2);
            })
            ->make(true);
    }
}
