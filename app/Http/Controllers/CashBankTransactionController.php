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
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            'title' => $jenis === 'pembayaran_hutang' ? 'Cashbank Pembayaran Supplier' : 'Cash Bank Pembayaran Umum',
            'nomor' => $this->genCode($jenis),
            'units' => $this->unitOptions(),
            'documents' => CashBankDocumentCode::with('bank')->where('is_active', true)->orderBy('kode')->get(),
            'coas' => CashBankCoa::where('is_active', true)->orderBy('kode_akun')->get(),
            'banks' => $this->bankOptions(),
        ]);
    }

    public function hutangHistory(Request $request): View
    {
        $start = $request->input('tanggal_awal', now()->startOfMonth()->toDateString());
        $end = $request->input('tanggal_akhir', now()->toDateString());
        $keyword = trim((string) $request->input('q', ''));

        $transactions = CashBankTransaction::with(['details.coa', 'logs.user', 'unit', 'documentCode', 'bank', 'supplier'])
            ->where('jenis', 'pembayaran_hutang')
            ->whereBetween('tgl_transaksi', [$start, $end])
            ->when($keyword !== '', function ($query) use ($keyword): void {
                $query->where(function ($query) use ($keyword): void {
                    $query->where('nomor_transaksi', 'like', "%{$keyword}%")
                        ->orWhere('dibayar_kepada', 'like', "%{$keyword}%")
                        ->orWhere('no_ref_nota', 'like', "%{$keyword}%");
                });
            })
            ->latest('tgl_transaksi')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('cashbank.transaksi.riwayat-hutang', [
            'title' => 'Riwayat Pembayaran Supplier',
            'transactions' => $transactions,
            'tanggal_awal' => $start,
            'tanggal_akhir' => $end,
            'keyword' => $keyword,
            'units' => $this->unitOptions(),
            'documents' => CashBankDocumentCode::with('bank')->where('is_active', true)->orderBy('kode')->get(),
            'coas' => CashBankCoa::where('is_active', true)->orderBy('kode_akun')->get(),
            'banks' => $this->bankOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedTransaction($request);

        DB::beginTransaction();
        try {
            $transaction = CashBankTransaction::create([
                'nomor_transaksi' => $this->genCode($validated['jenis']),
                ...$this->transactionPayload($validated),
                'status' => 'posted',
                'created_user' => Auth::id(),
            ]);

            $this->replaceDetails($transaction, $validated);
            $this->fillReferenceFromDetails($transaction);

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

    public function show(CashBankTransaction $transaction)
    {
        abort_unless($transaction->jenis === 'pembayaran_hutang', 404);

        $transaction->load(['details.coa', 'logs.user', 'documentCode', 'bank', 'supplier']);

        return response()->json([
            'id' => $transaction->id,
            'nomor_transaksi' => $transaction->nomor_transaksi,
            'jenis' => $transaction->jenis,
            'unit_id' => $transaction->unit_id,
            'document_code_id' => $transaction->document_code_id,
            'coa_id' => $transaction->coa_id,
            'bank_id' => $transaction->bank_id,
            'tgl_transaksi' => optional($transaction->tgl_transaksi)->format('Y-m-d'),
            'periode' => $transaction->periode,
            'supplier_id' => $transaction->supplier_id,
            'supplier_code' => $transaction->supplier->kode_supplier ?? '',
            'supplier_name' => $transaction->supplier->nama_supplier ?? '',
            'dibayar_kepada' => $transaction->dibayar_kepada,
            'guna_membayar' => $transaction->guna_membayar,
            'no_ref_nota' => $transaction->no_ref_nota,
            'sejumlah' => (float) $transaction->sejumlah,
            'dibayar_dengan' => $transaction->dibayar_dengan,
            'no_cash_cek_giro' => $transaction->no_cash_cek_giro,
            'tgl_giro_cek' => optional($transaction->tgl_giro_cek)->format('Y-m-d'),
            'details' => $transaction->details->map(fn ($detail): array => [
                'coa_id' => $detail->coa_id,
                'coa_label' => $detail->coa ? $detail->coa->kode_akun.' - '.$detail->coa->nama_akun : '',
                'penerimaan_id' => $detail->penerimaan_id,
                'nomor_invoice' => $detail->nomor_invoice,
                'nilai_invoice' => (float) $detail->nilai_invoice,
                'sudah_dibayar' => (float) $detail->sudah_dibayar,
                'jumlah_bayar' => (float) $detail->jumlah_bayar,
                'sisa' => (float) $detail->sisa,
                'keterangan' => $detail->keterangan,
            ])->values(),
        ]);
    }

    public function update(Request $request, CashBankTransaction $transaction)
    {
        abort_unless($transaction->jenis === 'pembayaran_hutang', 404);

        $validated = $this->validatedTransaction($request);
        $oldPenerimaanIds = $transaction->details()->whereNotNull('penerimaan_id')->pluck('penerimaan_id')->all();

        DB::beginTransaction();
        try {
            $before = [
                'sejumlah' => (float) $transaction->sejumlah,
                'detail_count' => $transaction->details()->count(),
                'penerimaan_ids' => $oldPenerimaanIds,
            ];

            $transaction->update($this->transactionPayload($validated, $transaction));
            $this->replaceDetails($transaction, $validated);
            $this->fillReferenceFromDetails($transaction);

            $transaction->logs()->create([
                'aksi' => 'updated',
                'keterangan' => 'Transaksi cashbank diperbarui',
                'payload' => [
                    'before' => $before,
                    'after' => [
                        'sejumlah' => (float) $transaction->fresh()->sejumlah,
                        'detail_count' => $transaction->details()->count(),
                    ],
                ],
                'created_user' => Auth::id(),
            ]);

            $this->syncPenerimaanIds(array_merge(
                $oldPenerimaanIds,
                $transaction->details()->whereNotNull('penerimaan_id')->pluck('penerimaan_id')->all()
            ));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi cashbank berhasil diperbarui.',
                'nota_url' => route('cashbank.transactions.hutang.nota', $transaction->nomor_transaksi),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(CashBankTransaction $transaction)
    {
        abort_unless($transaction->jenis === 'pembayaran_hutang', 404);

        $penerimaanIds = $transaction->details()->whereNotNull('penerimaan_id')->pluck('penerimaan_id')->all();

        DB::beginTransaction();
        try {
            $transaction->logs()->create([
                'aksi' => 'deleted',
                'keterangan' => 'Transaksi cashbank dihapus',
                'payload' => [
                    'nomor_transaksi' => $transaction->nomor_transaksi,
                    'sejumlah' => $transaction->sejumlah,
                    'penerimaan_ids' => $penerimaanIds,
                ],
                'created_user' => Auth::id(),
            ]);
            $transaction->delete();
            $this->syncPenerimaanIds($penerimaanIds);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transaksi cashbank berhasil dihapus.']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function validatedTransaction(Request $request): array
    {
        return $request->validate([
            'jenis' => ['required', Rule::in(['umum', 'pembayaran_hutang'])],
            'unit_id' => ['required', 'integer'],
            'document_code_id' => ['required', 'exists:cashbank_document_codes,id'],
            'coa_id' => ['nullable', 'exists:cashbank_coas,id'],
            'bank_id' => ['nullable', 'exists:cashbank_banks,id'],
            'tgl_transaksi' => ['required', 'date'],
            'periode' => ['nullable', 'string', 'max:6'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'dibayar_kepada' => ['required', 'string', 'max:150'],
            'guna_membayar' => ['nullable', 'string'],
            'no_ref_nota' => ['nullable', 'string'],
            'sejumlah' => ['required', 'numeric', 'min:0.01'],
            'dibayar_dengan' => ['required', Rule::in(['cash', 'kredit'])],
            'no_cash_cek_giro' => ['nullable', 'string', 'max:80'],
            'tgl_giro_cek' => ['nullable', 'date'],
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
    }

    private function transactionPayload(array $validated, ?CashBankTransaction $transaction = null): array
    {
        return [
                'jenis' => $validated['jenis'],
                'unit_id' => $validated['unit_id'],
                'document_code_id' => $validated['document_code_id'],
                'coa_id' => $validated['coa_id'] ?? $transaction?->coa_id,
                'bank_id' => $validated['bank_id'] ?? null,
                'tgl_transaksi' => Carbon::parse($validated['tgl_transaksi'])->format('Y-m-d'),
                'periode' => $validated['periode'] ?? Carbon::parse($validated['tgl_transaksi'])->format('Ym'),
                'supplier_id' => $validated['supplier_id'] ?? null,
                'dibayar_kepada' => $validated['dibayar_kepada'],
                'guna_membayar' => $validated['guna_membayar'] ?? null,
                'no_ref_nota' => $validated['no_ref_nota'] ?? null,
                'sejumlah' => $validated['sejumlah'],
                'dibayar_dengan' => $validated['dibayar_dengan'],
                'no_cash_cek_giro' => $validated['no_cash_cek_giro'] ?? null,
                'tgl_giro_cek' => null,
        ];
    }

    private function replaceDetails(CashBankTransaction $transaction, array $validated): void
    {
        $transaction->details()->delete();
        $totalDetail = 0;

        foreach ($validated['detail'] ?? [] as $row) {
                $jumlahBayar = (float) ($row['jumlah_bayar'] ?? 0);
                if ($jumlahBayar <= 0) {
                    continue;
                }

                $totalDetail += $jumlahBayar;
                CashBankTransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'coa_id' => $row['coa_id'] ?? $validated['coa_id'] ?? $transaction->coa_id,
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
    }

    private function fillReferenceFromDetails(CashBankTransaction $transaction): void
    {
        $invoiceRefs = $transaction->details()
            ->whereNotNull('nomor_invoice')
            ->pluck('nomor_invoice')
            ->filter()
            ->values()
            ->all();

        if (empty($transaction->no_ref_nota) && ! empty($invoiceRefs)) {
            $transaction->no_ref_nota = implode(',', $invoiceRefs);
            $transaction->save();
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
            'bank_id' => ['nullable', 'exists:cashbank_banks,id'],
            'transaction_type' => ['nullable', Rule::in(['payment', 'receipt'])],
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
            ->get(['id', 'kode_supplier', 'nama_supplier', 'alamat', 'telp', 'kontak_person', 'email'])
            ->map(fn ($supplier) => [
                'id' => $supplier->id,
                'kode_supplier' => $supplier->kode_supplier,
                'text' => $supplier->nama_supplier,
                'alamat' => $supplier->alamat,
                'telp' => $supplier->telp,
                'kontak_person' => $supplier->kontak_person,
                'email' => $supplier->email,
            ]);
    }

    public function members(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        return User::query()
            ->whereNull('deleted_at')
            ->whereNotNull('nomor_anggota')
            ->where(function ($query) use ($q): void {
                $query->where('nomor_anggota', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            })
            ->orderBy('nomor_anggota')
            ->limit(20)
            ->get(['id', 'nomor_anggota', 'name'])
            ->map(fn ($member) => [
                'id' => $member->id,
                'nomor_anggota' => $member->nomor_anggota,
                'text' => $member->name,
            ]);
    }

    public function invoiceSearch(Request $request)
    {
        $supplierId = $request->input('supplier_id');
        $supplierCode = $request->input('supplier_code');
        $q = $request->input('q', '');

        if (! $supplierId && $supplierCode) {
            $supplierId = Supplier::where('kode_supplier', $supplierCode)->value('id');
        }

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
                'penerimaan.idsupplier',
                'penerimaan.kode_supplier',
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
                'supplier_id' => $row->idsupplier,
                'kode_supplier' => $row->kode_supplier,
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

    private function bankOptions()
    {
        if (Schema::hasColumn('cashbank_coas', 'att4')) {
            $cashBankCoas = CashBankCoa::query()
                ->where('is_active', true)
                ->whereIn(DB::raw('UPPER(att4)'), ['KAS', 'BANK'])
                ->orderBy('kode_akun')
                ->get();

            $cashBankCoas->each(function (CashBankCoa $coa): void {
                    CashBankBank::updateOrCreate(
                        ['kode_bank' => $coa->kode_akun],
                        [
                            'nama_bank' => $coa->nama_akun,
                            'kode_akun' => $coa->kode_akun,
                            'nama_akun' => $coa->nama_akun,
                            'nama_rekening' => $coa->nama_akun,
                            'coa_id' => null,
                            'is_active' => true,
                        ]
                    );
                });

            return CashBankBank::where('is_active', true)
                ->whereIn('kode_akun', $cashBankCoas->pluck('kode_akun')->filter()->values())
                ->orderBy('kode_akun')
                ->orderBy('nama_bank')
                ->get();
        }

        return CashBankBank::where('is_active', true)
            ->orderBy('kode_akun')
            ->orderBy('nama_bank')
            ->get();
    }

    private function unitOptions()
    {
        if (
            Schema::hasColumn('unit', 'unit_usaha') &&
            Schema::hasColumn('unit', 'nama_unit_usaha')
        ) {
            return DB::table('unit')
                ->selectRaw('unit_usaha as id, nama_unit_usaha as nama_unit')
                ->whereNotNull('unit_usaha')
                ->whereNotNull('nama_unit_usaha')
                ->whereNull('deleted_at')
                ->distinct()
                ->orderBy('unit_usaha')
                ->get();
        }

        return Unit::query()
            ->select('id', 'nama_unit')
            ->orderBy('nama_unit')
            ->get()
            ->unique('id')
            ->values();
    }

    private function syncPenerimaanStatus(CashBankTransaction $transaction): void
    {
        $this->syncPenerimaanIds($transaction->details->pluck('penerimaan_id')->filter()->all());
    }

    private function syncPenerimaanIds(array $penerimaanIds): void
    {
        foreach (array_unique(array_filter($penerimaanIds)) as $penerimaanId) {
            $totalBayar = CashBankTransactionDetail::query()
                ->join('cashbank_transactions as cb', 'cb.id', '=', 'cashbank_transaction_details.transaction_id')
                ->whereNull('cb.deleted_at')
                ->where('cb.status', 'posted')
                ->where('cashbank_transaction_details.penerimaan_id', $penerimaanId)
                ->sum('cashbank_transaction_details.jumlah_bayar');

            $penerimaan = Penerimaan::find($penerimaanId);
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
