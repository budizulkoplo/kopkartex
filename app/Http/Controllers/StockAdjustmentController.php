<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Satuan;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use App\Models\StokUnit;
use App\Models\Unit;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');
        $keyword = trim((string) $request->keyword);
        $unitId = Auth::user()->unit_kerja;

        $query = StockAdjustment::with(['user', 'unit', 'details.barang', 'details.oldSatuan', 'details.newSatuan'])
            ->where('unit_id', $unitId)
            ->whereDate('tanggal_adjustment', '>=', $tanggalAwal)
            ->whereDate('tanggal_adjustment', '<=', $tanggalAkhir)
            ->latest('tanggal_adjustment');

        if ($keyword !== '') {
            $query->where(function ($builder) use ($keyword) {
                $builder->where('kode_adjustment', 'like', '%' . $keyword . '%')
                    ->orWhere('note', 'like', '%' . $keyword . '%');
            });
        }

        return view('transaksi.StockAdjustment', [
            'adjustments' => $query->paginate(20)->withQueryString(),
            'satuans' => Satuan::query()->orderBy('name')->get(),
            'unit' => Unit::find($unitId),
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
            'keyword' => $keyword,
            'kode_adjustment' => $this->generateCode(),
        ]);
    }

    public function getBarang(Request $request)
    {
        $unitId = Auth::user()->unit_kerja;
        $q = trim((string) $request->q);

        $barang = StokUnit::query()
            ->join('barang', 'barang.id', '=', 'stok_unit.barang_id')
            ->leftJoin('satuan', 'satuan.id', '=', 'barang.idsatuan')
            ->where('stok_unit.unit_id', $unitId)
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($query) use ($q) {
                    $query->where('barang.kode_barang', 'like', '%' . $q . '%')
                        ->orWhere('barang.nama_barang', 'like', '%' . $q . '%');
                });
            })
            ->select([
                'barang.id',
                'barang.kode_barang',
                'barang.nama_barang',
                'barang.idsatuan',
                'stok_unit.stok',
                DB::raw('COALESCE(satuan.name, "") as satuan_name'),
            ])
            ->orderBy('barang.nama_barang')
            ->limit(30)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->kode_barang . ' - ' . $item->nama_barang,
                    'kode_barang' => $item->kode_barang,
                    'nama_barang' => $item->nama_barang,
                    'stok' => (float) $item->stok,
                    'idsatuan' => $item->idsatuan,
                    'satuan' => $item->satuan_name,
                ];
            });

        return response()->json($barang);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_adjustment' => 'required|date',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.adjustment_type' => 'required|in:set,add,subtract,convert',
            'items.*.adjustment_value' => 'nullable|numeric|min:0',
            'items.*.new_satuan_id' => 'nullable|exists:satuan,id',
            'items.*.conversion_factor' => 'nullable|numeric|min:0.001',
            'items.*.note' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $unitId = Auth::user()->unit_kerja;

            $header = StockAdjustment::create([
                'kode_adjustment' => $this->generateCode(),
                'tanggal_adjustment' => Carbon::parse($validated['tanggal_adjustment'])->format('Y-m-d H:i:s'),
                'unit_id' => $unitId,
                'user_id' => Auth::id(),
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $stokUnit = StokUnit::query()
                    ->where('barang_id', $item['barang_id'])
                    ->where('unit_id', $unitId)
                    ->lockForUpdate()
                    ->first();

                if (! $stokUnit) {
                    $stokUnit = StokUnit::create([
                        'barang_id' => $item['barang_id'],
                        'unit_id' => $unitId,
                        'stok' => 0,
                    ]);
                }

                $barang = Barang::query()->lockForUpdate()->findOrFail($item['barang_id']);
                $oldStock = (float) $stokUnit->stok;
                $oldSatuanId = $barang->idsatuan;
                $oldSatuanName = optional($barang->satuanRelation)->name;
                $newSatuanId = $oldSatuanId;
                $conversionFactor = null;
                $adjustmentValue = (float) ($item['adjustment_value'] ?? 0);

                switch ($item['adjustment_type']) {
                    case 'set':
                        $newStock = $adjustmentValue;
                        break;

                    case 'add':
                        $newStock = $oldStock + $adjustmentValue;
                        break;

                    case 'subtract':
                        $newStock = $oldStock - $adjustmentValue;
                        if ($newStock < 0) {
                            throw new Exception('Stok barang ' . $barang->nama_barang . ' tidak boleh minus.');
                        }
                        break;

                    case 'convert':
                        $conversionFactor = (float) ($item['conversion_factor'] ?? 0);
                        $newSatuanId = (int) ($item['new_satuan_id'] ?? 0);

                        if ($conversionFactor < 1) {
                            throw new Exception('Faktor konversi barang ' . $barang->nama_barang . ' harus lebih besar dari 0.');
                        }

                        if (! $newSatuanId) {
                            throw new Exception('Satuan baru barang ' . $barang->nama_barang . ' wajib dipilih.');
                        }

                        if ($newSatuanId === (int) $oldSatuanId) {
                            throw new Exception('Satuan baru barang ' . $barang->nama_barang . ' harus berbeda dari satuan lama.');
                        }

                        $newStock = $oldStock * $conversionFactor;
                        $adjustmentValue = $newStock - $oldStock;
                        $barang->idsatuan = $newSatuanId;
                        $barang->save();
                        break;

                    default:
                        throw new Exception('Tipe adjustment tidak dikenali.');
                }

                $stokUnit->stok = $newStock;
                $stokUnit->save();

                $detailNote = trim((string) ($item['note'] ?? ''));
                $newSatuanName = optional(Satuan::find($newSatuanId))->name ?? $oldSatuanName;

                StockAdjustmentDetail::create([
                    'stock_adjustment_id' => $header->id,
                    'barang_id' => $barang->id,
                    'adjustment_type' => $item['adjustment_type'],
                    'old_stock' => $oldStock,
                    'adjustment_value' => $adjustmentValue,
                    'new_stock' => $newStock,
                    'old_satuan_id' => $oldSatuanId,
                    'new_satuan_id' => $newSatuanId,
                    'conversion_factor' => $conversionFactor,
                    'note' => $detailNote !== '' ? $detailNote : $this->defaultDetailNote($item['adjustment_type'], $oldStock, $newStock, $oldSatuanName, $newSatuanName, $conversionFactor),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock adjustment berhasil disimpan.',
                'kode_adjustment' => $header->kode_adjustment,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function detail($id)
    {
        $adjustment = StockAdjustment::with(['user', 'unit', 'details.barang', 'details.oldSatuan', 'details.newSatuan'])
            ->where('unit_id', Auth::user()->unit_kerja)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'header' => [
                'kode_adjustment' => $adjustment->kode_adjustment,
                'tanggal_adjustment' => $adjustment->tanggal_adjustment?->format('d-m-Y H:i'),
                'unit' => $adjustment->unit->name ?? '-',
                'user' => $adjustment->user->name ?? '-',
                'note' => $adjustment->note ?? '-',
            ],
            'details' => $adjustment->details->map(function ($detail) {
                return [
                    'barang' => ($detail->barang->kode_barang ?? '-') . ' - ' . ($detail->barang->nama_barang ?? '-'),
                    'adjustment_type' => $detail->adjustment_type,
                    'old_stock' => $detail->old_stock,
                    'adjustment_value' => $detail->adjustment_value,
                    'new_stock' => $detail->new_stock,
                    'old_satuan' => $detail->oldSatuan->name ?? '-',
                    'new_satuan' => $detail->newSatuan->name ?? '-',
                    'conversion_factor' => $detail->conversion_factor,
                    'note' => $detail->note ?? '-',
                ];
            }),
        ]);
    }

    protected function generateCode(): string
    {
        $totalToday = StockAdjustment::query()
            ->whereDate('created_at', now()->toDateString())
            ->count();

        return 'ADJ-' . now()->format('ymd') . str_pad((string) ($totalToday + 1), 3, '0', STR_PAD_LEFT);
    }

    protected function defaultDetailNote(
        string $type,
        float $oldStock,
        float $newStock,
        ?string $oldSatuanName,
        ?string $newSatuanName,
        ?float $conversionFactor
    ): string {
        if ($type === 'convert') {
            return 'Konversi satuan dari ' . ($oldSatuanName ?: '-') . ' ke ' . ($newSatuanName ?: '-') . ' dengan faktor ' . number_format((float) $conversionFactor, 3, ',', '.') . '. Stok ' . number_format($oldStock, 3, ',', '.') . ' menjadi ' . number_format($newStock, 3, ',', '.') . '.';
        }

        return match ($type) {
            'set' => 'Set stok dari ' . number_format($oldStock, 3, ',', '.') . ' menjadi ' . number_format($newStock, 3, ',', '.') . '.',
            'add' => 'Tambah stok dari ' . number_format($oldStock, 3, ',', '.') . ' menjadi ' . number_format($newStock, 3, ',', '.') . '.',
            'subtract' => 'Kurangi stok dari ' . number_format($oldStock, 3, ',', '.') . ' menjadi ' . number_format($newStock, 3, ',', '.') . '.',
            default => 'Adjustment stok.',
        };
    }
}
