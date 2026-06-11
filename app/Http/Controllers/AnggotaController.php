<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\SimpananController;

class AnggotaController extends Controller
{
    private const BASE_INLINE_COLUMNS = [
        'nomor_anggota' => ['label' => 'Nomor Anggota', 'type' => 'string', 'max' => 20],
        'nik' => ['label' => 'NIK', 'type' => 'string', 'max' => 20],
        'name' => ['label' => 'Nama', 'type' => 'string', 'max' => 255],
        'jabatan' => ['label' => 'Jabatan', 'type' => 'string', 'max' => 100],
        'limit_ppob' => ['label' => 'Limit PPOB', 'type' => 'numeric'],
        'limit_hutang' => ['label' => 'Limit Hutang', 'type' => 'numeric'],
        'email' => ['label' => 'Email', 'type' => 'email', 'max' => 255],
        'nohp' => ['label' => 'No HP', 'type' => 'string', 'max' => 30],
    ];

    private const LEGACY_INLINE_COLUMNS = [
        'kode_dept' => ['label' => 'KODE_DEPT', 'type' => 'string', 'max' => 5],
        'norek' => ['label' => 'NOREK', 'type' => 'string', 'max' => 10],
        'dept_name' => ['label' => 'DEPT_NAME', 'type' => 'string', 'max' => 15],
        'status_lama' => ['label' => 'STATUS', 'type' => 'string', 'max' => 2],
        'group' => ['label' => 'GROUP', 'type' => 'string', 'max' => 2],
        'unit' => ['label' => 'UNIT', 'type' => 'string', 'max' => 3],
        'unit2' => ['label' => 'UNIT2', 'type' => 'string', 'max' => 3],
        'pokok_ke' => ['label' => 'POKOK_KE', 'type' => 'numeric'],
        'tot_wajib' => ['label' => 'TOT_WAJIB', 'type' => 'numeric'],
        'tot_pokok' => ['label' => 'TOT_POKOK', 'type' => 'numeric'],
        'tot_sbm' => ['label' => 'TOT_SBM', 'type' => 'numeric'],
        'tot_sjt' => ['label' => 'TOT_SJT', 'type' => 'numeric'],
        'shu_sim' => ['label' => 'SHU_SIM', 'type' => 'numeric'],
        'shu_toko' => ['label' => 'SHU_TOKO', 'type' => 'numeric'],
        'tot_sjhu' => ['label' => 'TOT_SJHU', 'type' => 'numeric'],
        'bungasbm' => ['label' => 'BUNGASBM', 'type' => 'numeric'],
        'bungaspw' => ['label' => 'BUNGASPW', 'type' => 'numeric'],
        'bungsim09' => ['label' => 'BUNGSIM09', 'type' => 'numeric'],
        'jasa11' => ['label' => 'JASA11', 'type' => 'numeric'],
        'tot_simp' => ['label' => 'TOT_SIMP', 'type' => 'numeric'],
        'sisa_pin_u' => ['label' => 'SISA_PIN_U', 'type' => 'numeric'],
        'sisa_pin_b' => ['label' => 'SISA_PIN_B', 'type' => 'numeric'],
        'sisa_bkl' => ['label' => 'SISA_BKL', 'type' => 'numeric'],
        'sisa_p_ub' => ['label' => 'SISA_P_UB', 'type' => 'numeric'],
        'tot_pin' => ['label' => 'TOT_PIN', 'type' => 'numeric'],
        'sisa_bri' => ['label' => 'SISA_BRI', 'type' => 'numeric'],
        'sisa_sdr' => ['label' => 'SISA_SDR', 'type' => 'numeric'],
        'sisa_btn' => ['label' => 'SISA_BTN', 'type' => 'numeric'],
        'sisa_bri2' => ['label' => 'SISA_BRI2', 'type' => 'numeric'],
        'sisa_sdr2' => ['label' => 'SISA_SDR2', 'type' => 'numeric'],
        'sisa_sdr3' => ['label' => 'SISA_SDR3', 'type' => 'numeric'],
        'tot_bank' => ['label' => 'TOT_BANK', 'type' => 'numeric'],
        'minus' => ['label' => 'MINUS', 'type' => 'numeric'],
        'ttpot' => ['label' => 'TTPOT', 'type' => 'numeric'],
        'stat_agt' => ['label' => 'STAT_AGT', 'type' => 'string', 'max' => 1],
        'pria' => ['label' => 'PRIA', 'type' => 'string', 'max' => 1],
        'tg_msk' => ['label' => 'TG_MSK', 'type' => 'date'],
        'sdr_ke' => ['label' => 'SDR_KE', 'type' => 'numeric'],
        'pot_sdr' => ['label' => 'POT_SDR', 'type' => 'numeric'],
        'bri_ke' => ['label' => 'BRI_KE', 'type' => 'numeric'],
        'pot_bri' => ['label' => 'POT_BRI', 'type' => 'numeric'],
        'btn_ke' => ['label' => 'BTN_KE', 'type' => 'numeric'],
        'pot_btn' => ['label' => 'POT_BTN', 'type' => 'numeric'],
        'pot_bri2' => ['label' => 'POT_BRI2', 'type' => 'numeric'],
        'bri2_ke' => ['label' => 'BRI2_KE', 'type' => 'numeric'],
        'vi_bni' => ['label' => 'VI_BNI', 'type' => 'numeric'],
        'bni' => ['label' => 'BNI', 'type' => 'numeric'],
        'pot_sdr2' => ['label' => 'POT_SDR2', 'type' => 'numeric'],
        'sdr2_ke' => ['label' => 'SDR2_KE', 'type' => 'numeric'],
        'pot_sdr3' => ['label' => 'POT_SDR3', 'type' => 'numeric'],
        'sdr3_ke' => ['label' => 'SDR3_KE', 'type' => 'numeric'],
        'pot_mtr' => ['label' => 'POT_MTR', 'type' => 'numeric'],
        'tsj' => ['label' => 'TSJ', 'type' => 'numeric'],
        'pot_pokok' => ['label' => 'POT_POKOK', 'type' => 'numeric'],
        'pot_sjt' => ['label' => 'POT_SJT', 'type' => 'numeric'],
        'pot_wajib' => ['label' => 'POT_WAJIB', 'type' => 'numeric'],
        'pot_sbm' => ['label' => 'POT_SBM', 'type' => 'numeric'],
        'pot_uang1' => ['label' => 'POT_UANG1', 'type' => 'numeric'],
        'pot_uang2' => ['label' => 'POT_UANG2', 'type' => 'numeric'],
        'pot_brgu1' => ['label' => 'POT_BRGU1', 'type' => 'numeric'],
        'pot_brgu2' => ['label' => 'POT_BRGU2', 'type' => 'numeric'],
        'pot_bub1' => ['label' => 'POT_BUB1', 'type' => 'numeric'],
        'pot_bub2' => ['label' => 'POT_BUB2', 'type' => 'numeric'],
        'pot_bu1' => ['label' => 'POT_BU1', 'type' => 'numeric'],
        'pot_bu2' => ['label' => 'POT_BU2', 'type' => 'numeric'],
        'pot_brg' => ['label' => 'POT_BRG', 'type' => 'numeric'],
        'pot_uangb' => ['label' => 'POT_UANGB', 'type' => 'numeric'],
        'pot_uang' => ['label' => 'POT_UANG', 'type' => 'numeric'],
        'pot_9pokok' => ['label' => 'POT_9POKOK', 'type' => 'numeric'],
        'pot_beng' => ['label' => 'POT_BENG', 'type' => 'numeric'],
        'beng_ke' => ['label' => 'BENG_KE', 'type' => 'numeric'],
        'pot_tungb' => ['label' => 'POT_TUNGB', 'type' => 'numeric'],
        'potbr1' => ['label' => 'POTBR1', 'type' => 'numeric'],
        'potbr2' => ['label' => 'POTBR2', 'type' => 'numeric'],
        'potbr3' => ['label' => 'POTBR3', 'type' => 'numeric'],
        'potbr4' => ['label' => 'POTBR4', 'type' => 'numeric'],
        'potbr5' => ['label' => 'POTBR5', 'type' => 'numeric'],
        'potbr6' => ['label' => 'POTBR6', 'type' => 'numeric'],
        'angs1' => ['label' => 'ANGS1', 'type' => 'numeric'],
        'angs2' => ['label' => 'ANGS2', 'type' => 'numeric'],
        'angs3' => ['label' => 'ANGS3', 'type' => 'numeric'],
        'angs4' => ['label' => 'ANGS4', 'type' => 'numeric'],
        'angs5' => ['label' => 'ANGS5', 'type' => 'numeric'],
        'uang_ke1' => ['label' => 'UANG_KE1', 'type' => 'numeric'],
        'uang_ke2' => ['label' => 'UANG_KE2', 'type' => 'numeric'],
        'uangb_ke1' => ['label' => 'UANGB_KE1', 'type' => 'numeric'],
        'uangb_ke2' => ['label' => 'UANGB_KE2', 'type' => 'numeric'],
        'sisa_uang1' => ['label' => 'SISA_UANG1', 'type' => 'numeric'],
        'sisa_uang2' => ['label' => 'SISA_UANG2', 'type' => 'numeric'],
        'sisa_ub1' => ['label' => 'SISA_UB1', 'type' => 'numeric'],
        'sisa_ub2' => ['label' => 'SISA_UB2', 'type' => 'numeric'],
        'sisa_beng' => ['label' => 'SISA_BENG', 'type' => 'numeric'],
        'no_urt' => ['label' => 'NO_URT', 'type' => 'numeric'],
        'tot_pot' => ['label' => 'TOT_POT', 'type' => 'numeric'],
        'pot_kop' => ['label' => 'POT_KOP', 'type' => 'numeric'],
        'pot_bank' => ['label' => 'POT_BANK', 'type' => 'numeric'],
        'pot_simp' => ['label' => 'POT_SIMP', 'type' => 'numeric'],
        'jum' => ['label' => 'JUM', 'type' => 'numeric'],
        'sisa_brg1' => ['label' => 'SISA_BRG1', 'type' => 'numeric'],
        'sisa_brg2' => ['label' => 'SISA_BRG2', 'type' => 'numeric'],
        'sisa_brg3' => ['label' => 'SISA_BRG3', 'type' => 'numeric'],
        'sisa_brg4' => ['label' => 'SISA_BRG4', 'type' => 'numeric'],
        'sisa_brg5' => ['label' => 'SISA_BRG5', 'type' => 'numeric'],
        'sisa_brg6' => ['label' => 'SISA_BRG6', 'type' => 'numeric'],
        'sisa_brg7' => ['label' => 'SISA_BRG7', 'type' => 'numeric'],
        'tali' => ['label' => 'TALI', 'type' => 'numeric'],
        'tgl_klr' => ['label' => 'TGL_KLR', 'type' => 'date'],
        'harian' => ['label' => 'HARIAN', 'type' => 'numeric'],
        'shu' => ['label' => 'SHU', 'type' => 'numeric'],
        'shu1' => ['label' => 'SHU1', 'type' => 'numeric'],
        'spsw25' => ['label' => 'SPSW25', 'type' => 'numeric'],
        'ket' => ['label' => 'KET', 'type' => 'string', 'max' => 35],
    ];

    public function index(Request $request): View
    {
        return view('master.anggota.list', [
            'roles' => Role::with('permissions')->get(),
            'allroles' => Role::all(),
            'unit' => Unit::all(),
            'baseColumns' => self::BASE_INLINE_COLUMNS,
            'legacyColumns' => self::LEGACY_INLINE_COLUMNS,
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
           
            $isNewUser = empty($request->fidusers);

            if(! $isNewUser){
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
            $usr->gaji = $request->gaji;
            $usr->limit_ppob = $request->limit_ppob;
            $usr->limit_hutang = $request->limit_hutang;
            $usr->nohp = $request->nohp;
            $usr->status = $request->status ?'aktif':'nonaktif';
            $usr->ui = 'user';
            $usr->save();
            $usr->assignRole('anggota');

            if ($isNewUser) {
                $simpananController = new SimpananController();

                // auto buat simpanan pokok
                DB::beginTransaction();
                try {
                    $simpanan = new \App\Models\SimpananHdr();
                    $simpanan->id_anggota     = $usr->id;
                    $simpanan->norek          = $simpananController->genNorek('Simpanan Pokok');
                    $simpanan->nama_pemilik   = $usr->name;
                    $simpanan->jenis_simpanan = 'Simpanan Pokok';
                    $simpanan->saldo          = $request->simpanan_awal ?? 0;
                    $simpanan->save();

                    // Buat detail pertama
                    $dtl = new \App\Models\SimpananDtl();
                    $dtl->idsimpanan = $simpanan->idsimpanan;
                    $dtl->nominal    = $request->simpanan_awal ?? 0;
                    $dtl->saldo_awal = 0;
                    $dtl->saldo_ahir = $request->simpanan_awal ?? 0;
                    $dtl->save();

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['error' => 'Gagal membuat Simpanan Pokok', 'message' => $e->getMessage()], 500);
                }
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
        $user = User::where('ui', 'user') // ⬅️ filter ui = user
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

    public function inlineUpdate(Request $request)
    {
        $editableColumns = self::BASE_INLINE_COLUMNS + self::LEGACY_INLINE_COLUMNS;

        $payload = $request->validate([
            'id' => ['required', 'exists:users,id'],
            'field' => ['required', Rule::in(array_keys($editableColumns))],
            'value' => ['nullable'],
        ]);

        $column = $editableColumns[$payload['field']];
        $rules = ['nullable'];

        if ($column['type'] === 'email') {
            $rules[] = 'email';
        } elseif ($column['type'] === 'numeric') {
            $rules[] = 'numeric';
        } elseif ($column['type'] === 'date') {
            $rules[] = 'date';
        } else {
            $rules[] = 'string';
        }

        if (isset($column['max'])) {
            $rules[] = 'max:' . $column['max'];
        }

        $request->validate([
            'value' => $rules,
        ]);

        $value = $payload['value'];

        if ($value === '') {
            $value = $column['type'] === 'numeric' ? 0 : null;
        }

        $user = User::findOrFail($payload['id']);
        $user->{$payload['field']} = $value;
        $user->save();

        return response()->json([
            'success' => true,
            'field' => $payload['field'],
            'value' => $user->{$payload['field']},
        ]);
    }

}
