<?php

namespace App\Http\Controllers;

use App\Models\StockOpnameDTL;
use App\Models\StockOpnameHDR;
use App\Models\StokUnit;
use App\Models\Barang;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class StockOpnameController extends Controller
{
    public function index(Request $request): View
    {
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');
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
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');

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
                'qty.*' => 'required|integer|min:0',
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
            $totalFisik = array_sum($request->qty);
            
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
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');

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
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');
        
        $startDate = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();

        $query = StockOpnameHDR::with(['barang'])
            ->where('id_unit', $unitId)
            ->whereBetween('tgl_opname', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select([
                'id as opname_id',
                'id_barang',
                'kode_barang',
                'stock_sistem',
                'stock_fisik',
                'status',
                'tgl_opname'
            ]);

        return datatables()->eloquent($query)
            ->addIndexColumn()
            ->addColumn('nama_barang', function($row) {
                return $row->barang->nama_barang ?? '-';
            })
            ->addColumn('aksi', function($row) use ($bulan) {
                $url = route('stockopname.form', [
                    'barang_id' => $row->id_barang,
                    'bulan' => $bulan
                ]);
                
                $btnClass = $row->status == 'sukses' ? 'btn-warning' : 'btn-primary';
                $btnText = $row->status == 'sukses' ? 'Revisi' : 'Input';
                $btnIcon = $row->status == 'sukses' ? 'bi-pencil-square' : 'bi-input-cursor';
                
                return '<a href="'.$url.'" class="btn btn-sm '.$btnClass.'">
                    <i class="bi '.$btnIcon.'"></i> '.$btnText.'
                </a>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }
}