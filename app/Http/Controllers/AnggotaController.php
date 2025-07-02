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
                $usr->nomor_anggota = $this->genCode();
                $usr->username = $request->username;
                $usr->password = Hash::make('12345678');
            }
            
            $usr->name = $request->name;
            $usr->email = $request->email;
            $usr->tanggal_masuk = $request->tanggal_masuk;
            $usr->nik = $request->nik;
            $usr->jabatan = $request->jabatan;
            $usr->unit_kerja = $request->unit_kerja;
            $usr->limit_ppob = $request->limit_ppob;
            $usr->limit_hutang = $request->limit_hutang;
            $usr->status = $request->status ?'aktif':'nonaktif';
            $usr->save();
            $usr->assignRole('anggota');

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
        $user = User::where('status','aktif')
        ->whereDoesntHave('roles', function ($query) {
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
