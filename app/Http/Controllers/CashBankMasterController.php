<?php

namespace App\Http\Controllers;

use App\Models\CashBankBank;
use App\Models\CashBankCoa;
use App\Models\CashBankDocumentCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CashBankMasterController extends Controller
{
    public function documentCodes()
    {
        return view('cashbank.master.document-codes', [
            'banks' => $this->bankOptions(),
        ]);
    }

    public function documentCodeData()
    {
        return DataTables::of(CashBankDocumentCode::with('bank'))
            ->addIndexColumn()
            ->addColumn('bank_label', fn ($row) => $row->bank ? $row->bank->kode_akun . ' - ' . $row->bank->nama_akun : '-')
            ->addColumn('account_label', fn ($row) => $row->bank ? trim(($row->bank->kode_akun ?? '-') . ' - ' . ($row->bank->nama_akun ?? '-')) : '-')
            ->make(true);
    }

    public function storeDocumentCode(Request $request)
    {
        $id = $request->input('id');
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:30', Rule::unique('cashbank_document_codes', 'kode')->ignore($id)],
            'nama' => ['required', 'string', 'max:100'],
            'prefix' => ['nullable', 'string', 'max:20'],
            'bank_id' => ['nullable', 'exists:cashbank_banks,id'],
            'transaction_type' => ['required', Rule::in(['payment', 'receipt'])],
            'keterangan' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['coa_id'] = null;

        $validated['is_active'] = $request->boolean('is_active', true);
        $record = CashBankDocumentCode::updateOrCreate(['id' => $id], $validated);

        return response()->json(['success' => true, 'data' => $record]);
    }

    public function deleteDocumentCode(Request $request)
    {
        CashBankDocumentCode::findOrFail($request->id)->delete();

        return response()->json(['success' => true]);
    }

    public function coas()
    {
        return view('cashbank.master.coas');
    }

    public function coaData()
    {
        return DataTables::of(CashBankCoa::query())
            ->addIndexColumn()
            ->make(true);
    }

    public function storeCoa(Request $request)
    {
        $id = $request->input('id');
        $validated = $request->validate([
            'kode_akun' => ['required', 'string', 'max:50', Rule::unique('cashbank_coas', 'kode_akun')->ignore($id)],
            'nama_akun' => ['required', 'string', 'max:150'],
            'tipe' => ['required', Rule::in(['kas', 'bank', 'hutang', 'biaya', 'pendapatan', 'lainnya'])],
            'keterangan' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $record = CashBankCoa::updateOrCreate(['id' => $id], $validated);

        return response()->json(['success' => true, 'data' => $record]);
    }

    public function deleteCoa(Request $request)
    {
        CashBankCoa::findOrFail($request->id)->delete();

        return response()->json(['success' => true]);
    }

    public function banks()
    {
        return view('cashbank.master.banks', [
            'coas' => CashBankCoa::where('is_active', true)->orderBy('kode_akun')->get(),
        ]);
    }

    public function bankData()
    {
        return DataTables::of($this->bankOptions())
            ->addIndexColumn()
            ->addColumn('att4', fn ($row) => $this->cashBankCoaAtt4($row->kode_akun))
            ->make(true);
    }

    public function storeBank(Request $request)
    {
        $id = $request->input('id');
        $validated = $request->validate([
            'kode_akun' => ['required', 'string', 'max:50', Rule::unique('cashbank_banks', 'kode_akun')->ignore($id)],
            'nama_akun' => ['required', 'string', 'max:150'],
            'nomor_rekening' => ['nullable', 'string', 'max:50'],
            'nama_bank' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['kode_bank'] = $validated['kode_akun'];
        $validated['nama_rekening'] = $validated['nama_akun'];
        $validated['coa_id'] = null;
        $validated['is_active'] = $request->boolean('is_active', true);
        $record = CashBankBank::updateOrCreate(['id' => $id], $validated);

        return response()->json(['success' => true, 'data' => $record]);
    }

    public function deleteBank(Request $request)
    {
        CashBankBank::findOrFail($request->id)->delete();

        return response()->json(['success' => true]);
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
                ->get();
        }

        return CashBankBank::where('is_active', true)->orderBy('kode_akun')->get();
    }

    private function cashBankCoaAtt4(?string $kodeAkun): string
    {
        if (! $kodeAkun || ! Schema::hasColumn('cashbank_coas', 'att4')) {
            return '-';
        }

        return CashBankCoa::where('kode_akun', $kodeAkun)->value('att4') ?: '-';
    }
}
