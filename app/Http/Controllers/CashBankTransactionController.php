<?php

namespace App\Http\Controllers;

use App\Models\CashBankBank;
use App\Models\CashBankCoa;
use App\Models\CashBankDocumentCode;
use App\Models\CashBankTransaction;
use App\Models\CashBankTransactionDetail;
use App\Models\Penerimaan;
use App\Models\Supplier;
use App\Models\Unit;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CashBankTransactionController extends Controller
{
    public function umum(): View
    {
        return $this->form('umum');
    }

    public function hutang(): View
    {
        return $this->form('pembayaran_hutang');
    }

    private function form(string $jenis): View
    {
        return view('cashbank.transaksi.form', [
            'jenis' => $jenis,
            'title' => $jenis === 'pembayaran_hutang' ? 'Cash Bank Pembayaran Hutang' : 'Cash Bank Umum',
            'nomor' => $this->genCode($jenis),
            'units' => Unit::query()->select('id', 'nama_unit')->orderBy('nama_unit')->get()->unique('id'),
            'documents' => CashBankDocumentCode::where('is_active', true)->orderBy('kode')->get(),
            'coas' => CashBankCoa::where('is_active', true)->orderBy('kode_akun')->get(),
            'banks' => CashBankBank::with('coa')->where('is_active', true)->orderBy('nama_bank')->get(),
            'recentLogs' => CashBankTransaction::with(['logs.user'])
                ->where('jenis', $jenis)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis' => ['required', Rule::in(['umum', 'pembayaran_hutang'])],
            'unit_id' => ['required', 'exists:unit,id'],
            'document_code_id' => ['required', 'exists:cashbank_document_codes,id'],
            'coa_id' => ['required', 'exists:cashbank_coas,id'],
            'bank_id' => ['nullable', 'exists:cashbank_banks,id'],
            'tgl_transaksi' => ['required', 'date'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'dibayar_kepada' => ['required', 'string', 'max:150'],
            'guna_membayar' => ['nullable', 'string'],
            'sejumlah' => ['required', 'numeric', 'min:0.01'],
            'dibayar_dengan' => ['required', Rule::in(['cash', 'kredit'])],
            'detail' => ['nullable', 'array'],
            'detail.*.coa_id' => ['nullable', 'exists:cashbank_coas,id'],
            'detail.*.penerimaan_id' => ['nullable', 'integer'],
            'detail.*.nomor_invoice' => ['nullable', 'string', 'max:50'],
            'detail.*.nilai_invoice' => ['nullable', 'numeric', 'min:0'],
            'detail.*.sudah_dibayar' => ['nullable', 'numeric', 'min:0'],
            'detail.*.jumlah_bayar' => ['nullable', 'numeric', 'min:0'],
            'detail.*.sisa' => ['nullable', 'numeric', 'min:0'],
            'detail.*.keterangan' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $transaction = CashBankTransaction::create([
                'nomor_transaksi' => $this->genCode($validated['jenis']),
                'jenis' => $validated['jenis'],
                'unit_id' => $validated['unit_id'],
                'document_code_id' => $validated['document_code_id'],
                'coa_id' => $validated['coa_id'],
                'bank_id' => $validated['bank_id'] ?? null,
                'tgl_transaksi' => Carbon::parse($validated['tgl_transaksi'])->format('Y-m-d'),
                'supplier_id' => $validated['supplier_id'] ?? null,
                'dibayar_kepada' => $validated['dibayar_kepada'],
                'guna_membayar' => $validated['guna_membayar'] ?? null,
                'sejumlah' => $validated['sejumlah'],
                'dibayar_dengan' => $validated['dibayar_dengan'],
                'status' => 'posted',
                'created_user' => Auth::id(),
            ]);

            $totalDetail = 0;
            foreach ($validated['detail'] ?? [] as $row) {
                $jumlahBayar = (float) ($row['jumlah_bayar'] ?? 0);
                if ($jumlahBayar <= 0) {
                    continue;
                }

                $totalDetail += $jumlahBayar;
                CashBankTransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'coa_id' => $row['coa_id'] ?? $validated['coa_id'],
                    'penerimaan_id' => $row['penerimaan_id'] ?? null,
                    'nomor_invoice' => $row['nomor_invoice'] ?? null,
                    'nilai_invoice' => $row['nilai_invoice'] ?? 0,
                    'sudah_dibayar' => $row['sudah_dibayar'] ?? 0,
                    'jumlah_bayar' => $jumlahBayar,
                    'sisa' => $row['sisa'] ?? 0,
                    'keterangan' => $row['keterangan'] ?? null,
                ]);
            }

            if ($totalDetail > 0 && abs($totalDetail - (float) $validated['sejumlah']) > 0.01) {
                throw new Exception('Total detail pembayaran harus sama dengan nominal sejumlah.');
            }

            $transaction->logs()->create([
                'aksi' => 'created',
                'keterangan' => 'Transaksi cashbank dibuat',
                'payload' => [
                    'nomor_transaksi' => $transaction->nomor_transaksi,
                    'sejumlah' => $transaction->sejumlah,
                    'detail_count' => $transaction->details()->count(),
                ],
                'created_user' => Auth::id(),
            ]);

            $this->syncPenerimaanStatus($transaction);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi cashbank berhasil disimpan.',
                'nomor' => $transaction->nomor_transaksi,
                'nota_url' => route(
                    $transaction->jenis === 'pembayaran_hutang'
                        ? 'cashbank.transactions.hutang.nota'
                        : 'cashbank.transactions.umum.nota',
                    $transaction->nomor_transaksi
                ),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getNumber(Request $request)
    {
        return response()->json($this->genCode($request->input('jenis', 'umum')));
    }

    public function quickDocument(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:30', 'unique:cashbank_document_codes,kode'],
            'nama' => ['required', 'string', 'max:100'],
            'prefix' => ['nullable', 'string', 'max:20'],
        ]);

        $document = CashBankDocumentCode::create($validated + ['is_active' => true]);

        return response()->json(['success' => true, 'data' => $document]);
    }

    public function quickCoa(Request $request)
    {
        $validated = $request->validate([
            'kode_akun' => ['required', 'string', 'max:50', 'unique:cashbank_coas,kode_akun'],
            'nama_akun' => ['required', 'string', 'max:150'],
            'tipe' => ['required', Rule::in(['kas', 'bank', 'hutang', 'biaya', 'pendapatan', 'lainnya'])],
        ]);

        $coa = CashBankCoa::create($validated + ['is_active' => true]);

        return response()->json(['success' => true, 'data' => $coa]);
    }

    public function quickSupplier(Request $request)
    {
        $validated = $request->validate([
            'nama_supplier' => ['required', 'string', 'max:255'],
            'telp' => ['nullable', 'string', 'max:15'],
            'alamat' => ['nullable', 'string', 'max:255'],
        ]);

        $supplier = Supplier::create([
            'kode_supplier' => 'SUP-' . date('ymd') . str_pad(Supplier::withTrashed()->whereDate('created_at', now()->toDateString())->count() + 1, 3, '0', STR_PAD_LEFT),
            'nama_supplier' => $validated['nama_supplier'],
            'telp' => $validated['telp'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
        ]);

        return response()->json(['success' => true, 'data' => [
            'id' => $supplier->id,
            'kode_supplier' => $supplier->kode_supplier,
            'nama_supplier' => $supplier->nama_supplier,
            'text' => $supplier->nama_supplier,
        ]]);
    }

    public function suppliers(Request $request)
    {
        $q = $request->input('q', '');

        return Supplier::query()
            ->where(function ($query) use ($q) {
                $query->where('nama_supplier', 'like', "%{$q}%")
                    ->orWhere('kode_supplier', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get(['id', 'kode_supplier', 'nama_supplier'])
            ->map(fn ($supplier) => [
                'id' => $supplier->id,
                'kode_supplier' => $supplier->kode_supplier,
                'text' => $supplier->nama_supplier,
            ]);
    }

    public function invoiceSearch(Request $request)
    {
        $supplierId = $request->input('supplier_id');
        $q = $request->input('q', '');

        $paidSub = DB::table('cashbank_transaction_details as cbd')
            ->join('cashbank_transactions as cb', 'cb.id', '=', 'cbd.transaction_id')
            ->whereNull('cb.deleted_at')
            ->where('cb.status', 'posted')
            ->select('cbd.penerimaan_id', DB::raw('SUM(cbd.jumlah_bayar) as total_bayar'))
            ->groupBy('cbd.penerimaan_id');

        $query = Penerimaan::query()
            ->leftJoinSub($paidSub, 'paid', 'paid.penerimaan_id', '=', 'penerimaan.idpenerimaan')
            ->select(
                'penerimaan.idpenerimaan',
                'penerimaan.nomor_invoice',
                'penerimaan.nama_supplier',
                'penerimaan.grandtotal',
                DB::raw('COALESCE(paid.total_bayar, 0) as sudah_dibayar'),
                DB::raw('(COALESCE(penerimaan.grandtotal, 0) - COALESCE(paid.total_bayar, 0)) as sisa')
            )
            ->whereNull('penerimaan.deleted_at')
            ->where(function ($query) {
                $query->where('penerimaan.metode_bayar', 'tempo')
                    ->orWhere('penerimaan.status_bayar', '!=', 'paid');
            })
            ->having('sisa', '>', 0);

        if ($supplierId) {
            $query->where('penerimaan.idsupplier', $supplierId);
        }

        if ($q !== '') {
            $query->where('penerimaan.nomor_invoice', 'like', "%{$q}%");
        }

        return $query->orderBy('penerimaan.tgl_penerimaan', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($row) => [
                'id' => $row->idpenerimaan,
                'text' => $row->nomor_invoice,
                'nomor_invoice' => $row->nomor_invoice,
                'nama_supplier' => $row->nama_supplier,
                'nilai_invoice' => (float) $row->grandtotal,
                'sudah_dibayar' => (float) $row->sudah_dibayar,
                'sisa' => (float) $row->sisa,
            ]);
    }

    public function logs(Request $request)
    {
        $jenis = $request->input('jenis', 'umum');

        $transactions = CashBankTransaction::with(['logs.user'])
            ->where('jenis', $jenis)
            ->latest()
            ->limit(8)
            ->get();

        return view('cashbank.transaksi.partials.logs', ['transactions' => $transactions]);
    }

    public function nota(string $nomor): View
    {
        $transaction = CashBankTransaction::with([
            'details.coa',
            'logs.user',
            'unit',
            'documentCode',
            'coa',
            'bank',
            'supplier',
            'creator',
        ])->where('nomor_transaksi', $nomor)->firstOrFail();

        return view('cashbank.transaksi.nota', compact('transaction'));
    }

    private function genCode(string $jenis): string
    {
        $prefix = $jenis === 'pembayaran_hutang' ? 'CBH' : 'CBU';
        $total = CashBankTransaction::withTrashed()
            ->whereDate('created_at', now()->toDateString())
            ->where('jenis', $jenis)
            ->count();

        return $prefix . '-' . date('ymd') . str_pad($total + 1, 3, '0', STR_PAD_LEFT);
    }

    private function syncPenerimaanStatus(CashBankTransaction $transaction): void
    {
        foreach ($transaction->details as $detail) {
            if (! $detail->penerimaan_id) {
                continue;
            }

            $totalBayar = CashBankTransactionDetail::query()
                ->join('cashbank_transactions as cb', 'cb.id', '=', 'cashbank_transaction_details.transaction_id')
                ->whereNull('cb.deleted_at')
                ->where('cb.status', 'posted')
                ->where('cashbank_transaction_details.penerimaan_id', $detail->penerimaan_id)
                ->sum('cashbank_transaction_details.jumlah_bayar');

            $penerimaan = Penerimaan::find($detail->penerimaan_id);
            if (! $penerimaan) {
                continue;
            }

            if ($totalBayar >= (float) $penerimaan->grandtotal) {
                $penerimaan->status_bayar = 'paid';
                $penerimaan->tgl_lunas = now();
            } else {
                $penerimaan->status_bayar = 'pending';
            }
            $penerimaan->save();
        }
    }
}
