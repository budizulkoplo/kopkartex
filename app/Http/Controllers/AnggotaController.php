<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\SimpananController;

class AnggotaController extends Controller
{
    public function index(Request $request): View
    {
        return view('master.anggota.list', [
            'roles' => Role::with('permissions')->get(),
            'allroles' => Role::all(),
            'unit' => Unit::all(),
        ]);
    }
    public function updatePassword(Request $request)
    {
        // Validate the input
        $request->validate([
            'new_password' => 'required|min:8',
            'userid' => 'required',
        ]);
        
        $user = User::find($request->userid);
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Check if current password matches
        // if (!Hash::check($request->current_password, $user->password)) {
        //     return back()->withErrors(['current_password' => 'Current password is incorrect']);
        // }

        // Update the password
        return back()->with('success', 'Password updated successfully');
    }
    public function Store(Request $request){
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'nik' => 'required',
        ]);
        
        if($validatedData){
           
            if(!empty($request->fidusers)){
                $id=Crypt::decryptString($request->fidusers);
                $usr = User::find($id);
            }else{
                $usr = new User;
                $usr->nomor_anggota = $request->nomor_anggota;
                $usr->username = $request->username;
                $usr->password = Hash::make('12345678');
            }
            
            $usr->name = $request->name;
            $usr->email = $request->email;
            $usr->tanggal_masuk = $request->tanggal_masuk;
            $usr->nik = $request->nik;
            $usr->jabatan = $request->jabatan;
            $usr->unit_kerja = 0;
            $usr->limit_ppob = $request->limit_ppob;
            $usr->limit_hutang = $request->limit_hutang;
            $usr->nohp = $request->nohp;
            $usr->status = $request->status ?'aktif':'nonaktif';
            $usr->ui = 'user';
            $usr->save();
            $usr->assignRole('anggota');

            $simpananController = new SimpananController();

                // auto buat simpanan pokok
                DB::beginTransaction();
                try {
                    $simpanan = new \App\Models\SimpananHdr();
                    $simpanan->id_anggota     = $usr->id;
                    $simpanan->norek          = $simpananController->genNorek('Simpanan Pokok');
                    $simpanan->nama_pemilik   = $usr->name;
                    $simpanan->jenis_simpanan = 'Simpanan Pokok';
                    $simpanan->saldo          = $request->simpanan_awal;
                    $simpanan->save();

                    // Buat detail pertama
                    $dtl = new \App\Models\SimpananDtl();
                    $dtl->idsimpanan = $simpanan->idsimpanan;
                    $dtl->nominal    = $request->simpanan_awal;
                    $dtl->saldo_awal = 0;
                    $dtl->saldo_ahir = $request->simpanan_awal;
                    $dtl->save();

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['error' => 'Gagal membuat Simpanan Pokok', 'message' => $e->getMessage()], 500);
                }

            if($usr){
                return response()->json('success', 200);
            }else{
                return response()->json('gagal', 500);
            }
        }
    }

    function genCode(){
        $total = User::withTrashed()->whereDate('created_at', date("Y-m-d"))->count();
        $nomorUrut = $total + 1;
        $newcode='KTX-'.date("ymd").str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
        return $newcode;
    }
    public function getCode(){
        return response()->json($this->genCode(), 200);
    }
    public function getdata(Request $request)
    {
        $user = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'superadmin');
        });

        return DataTables::of($user)
            ->addIndexColumn()
            ->addColumn('idusers', function ($row) {
                return Crypt::encryptString($row->id);
            })
            ->make(true);
    }

}
