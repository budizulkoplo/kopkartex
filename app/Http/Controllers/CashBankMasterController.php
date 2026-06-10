<?php

namespace App\Http\Controllers;

use App\Models\CashBankBank;
use App\Models\CashBankCoa;
use App\Models\CashBankDocumentCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CashBankMasterController extends Controller
{
    public function documentCodes()
    {
        return view('cashbank.master.document-codes');
    }

    public function documentCodeData()
    {
        return DataTables::of(CashBankDocumentCode::query())
            ->addIndexColumn()
            ->make(true);
    }

    public function storeDocumentCode(Request $request)
    {
        $id = $request->input('id');
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:30', Rule::unique('cashbank_document_codes', 'kode')->ignore($id)],
            'nama' => ['required', 'string', 'max:100'],
            'prefix' => ['nullable', 'string', 'max:20'],
            'keterangan' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

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
        return DataTables::of(CashBankBank::with('coa'))
            ->addIndexColumn()
            ->addColumn('coa_label', fn ($row) => $row->coa ? $row->coa->kode_akun . ' - ' . $row->coa->nama_akun : '-')
            ->make(true);
    }

    public function storeBank(Request $request)
    {
        $id = $request->input('id');
        $validated = $request->validate([
            'kode_bank' => ['required', 'string', 'max:30', Rule::unique('cashbank_banks', 'kode_bank')->ignore($id)],
            'nama_bank' => ['required', 'string', 'max:100'],
            'nomor_rekening' => ['nullable', 'string', 'max:50'],
            'nama_rekening' => ['nullable', 'string', 'max:100'],
            'coa_id' => ['nullable', 'exists:cashbank_coas,id'],
            'keterangan' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $record = CashBankBank::updateOrCreate(['id' => $id], $validated);

        return response()->json(['success' => true, 'data' => $record]);
    }

    public function deleteBank(Request $request)
    {
        CashBankBank::findOrFail($request->id)->delete();

        return response()->json(['success' => true]);
    }
}
