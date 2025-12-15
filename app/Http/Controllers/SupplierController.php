<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index()
    {
        return view('master.supplier.list');
    }

    public function getdata(Request $request)
    {
        $suppliers = Supplier::query();

        return DataTables::of($suppliers)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value'] != '') {
                    $query->where(function ($q) use ($request) {
                        $q->orWhere('nama_supplier', 'like', '%' . $request->search['value'] . '%')
                          ->orWhere('kode_supplier', 'like', '%' . $request->search['value'] . '%');
                    });
                }
            })
            ->editColumn('id', function ($q) {
                return Crypt::encryptString($q->id);
            })
            ->make(true);
    }

    private function genCode()
    {
        $total = Supplier::withTrashed()->whereDate('created_at', date("Y-m-d"))->count();
        $nomorUrut = $total + 1;
        return 'SUP-' . date("ymd") . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
    }

    public function getCode()
    {
        return response()->json($this->genCode(), 200);
    }

    public function CekCode(Request $request)
    {
        $count = Supplier::where('kode_supplier', $request->code)->count();
        return response()->json($count, 200);
    }

    public function Store(Request $request)
    {
        $request->validate([
            'kode_supplier' => 'required|string|max:20',
            'nama_supplier' => 'required|string|max:255',
            'npwp'          => 'nullable|string|max:50',
            'alamat'        => 'nullable|string|max:255',
            'telp'          => 'nullable|string|max:15',
            'kontak_person' => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:50',
            'rekening'      => 'nullable|string|max:20',
            'bank'          => 'nullable|string|max:20',
        ]);

        if (!empty($request->idsupplier)) {
            $id = Crypt::decryptString($request->idsupplier);
            $supplier = Supplier::findOrFail($id);
        } else {
            if (Supplier::where('kode_supplier', $request->kode_supplier)->exists()) {
                return response()->json('Kode sudah terpakai', 500);
            }
            $supplier = new Supplier;
            $supplier->kode_supplier = $request->kode_supplier;
        }

        $supplier->nama_supplier = $request->nama_supplier;
        $supplier->npwp          = $request->npwp;
        $supplier->alamat        = $request->alamat;
        $supplier->telp          = $request->telp;
        $supplier->kontak_person = $request->kontak_person;
        $supplier->email         = $request->email;
        $supplier->rekening      = $request->rekening;
        $supplier->bank          = $request->bank;

        $supplier->save();

        return response()->json('success', 200);
    }

    public function Hapus(Request $request)
    {
        $id = Crypt::decryptString($request->id);
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json('success', 200);
    }
}
