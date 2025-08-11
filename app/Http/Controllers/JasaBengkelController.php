<?php

namespace App\Http\Controllers;

use App\Models\JasaBengkel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class JasaBengkelController extends Controller
{
    public function index(Request $request): View
    {
        return view('master.jasabengkel.list');
    }

    public function getdata(Request $request)
    {
        $jasa = JasaBengkel::query();

        return DataTables::of($jasa)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search != '') {
                    $query->where(function ($query2) use ($request) {
                        $query2
                            ->orWhere('nama_jasa', 'like', '%' . $request->search['value'] . '%')
                            ->orWhere('kode_jasa', 'like', '%' . $request->search['value'] . '%');
                    });
                }
            })
            ->editColumn('id', function ($query) {
                return Crypt::encryptString($query->id);
            })
            ->make(true);
    }

    private function genCode()
    {
        $last = JasaBengkel::withTrashed()->orderBy('id', 'desc')->first();
        if (!$last) {
            $num = 1;
        } else {
            $num = intval(substr($last->kode_jasa, 3)) + 1;
        }
        return 'BKL' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function getCode()
    {
        return response()->json($this->genCode(), 200);
    }

    public function cekCode(Request $request)
    {
        $count = JasaBengkel::where('kode_jasa', $request->code)->count();
        return response()->json($count, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_jasa' => 'required',
            'harga' => 'required|numeric'
        ]);

        if ($validatedData) {
            if (!empty($request->idjasa)) {
                $id = Crypt::decryptString($request->idjasa);
                $jasa = JasaBengkel::find($id);
            } else {
                $cn = JasaBengkel::where('kode_jasa', $request->kode_jasa)->count();
                if ($cn > 0)
                    return response()->json('Kode sudah terpakai', 500);

                $jasa = new JasaBengkel;
                $jasa->kode_jasa = $request->kode_jasa;
            }

            $jasa->nama_jasa = $request->nama_jasa;
            $jasa->deskripsi = $request->deskripsi;
            $jasa->harga = $request->harga;
            $jasa->save();

            if ($jasa) {
                return response()->json('success', 200);
            } else {
                return response()->json('gagal', 500);
            }
        }
    }

    public function hapus(Request $request)
    {
        $id = Crypt::decryptString($request->id);
        $jasa = JasaBengkel::find($id);
        $jasa->delete();

        if ($jasa) {
            return response()->json('success', 200);
        } else {
            return response()->json('gagal', 500);
        }
    }
}
