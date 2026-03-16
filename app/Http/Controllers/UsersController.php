<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UsersController extends Controller
{
    public function index(Request $request): View
    {
        $assignableRoles = Role::query()
            ->when(! $request->user()->hasRole('superadmin'), fn ($query) => $query->where('name', '!=', 'superadmin'))
            ->orderBy('name')
            ->get();

        return view('master.users.list', [
            'roles' => Role::orderBy('name')->get(),
            'assignableRoles' => $assignableRoles,
            'unit' => Unit::all(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|min:8',
            'userid' => 'required',
        ]);

        $user = User::findOrFail($request->userid);
        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password updated successfully');
    }

    public function kasihRole(Request $request)
    {
        $payload = $request->validate([
            'iduser' => ['required', 'exists:users,id'],
            'name' => ['nullable', 'array'],
            'name.*' => ['string', Rule::exists('roles', 'name')],
        ]);

        $user = User::findOrFail($payload['iduser']);
        $roles = collect($payload['name'] ?? [])
            ->when(! $request->user()->hasRole('superadmin'), fn ($collection) => $collection->reject(fn ($role) => $role === 'superadmin'))
            ->values()
            ->all();

        $user->syncRoles($roles);

        return response()->json([
            'success' => true,
            'roles' => $user->fresh()->getRoleNames()->values(),
        ]);
    }

    public function updateStatus(Request $request)
    {
        $payload = $request->validate([
            'id' => ['required', 'exists:users,id'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ]);

        $user = User::findOrFail($payload['id']);
        $user->status = $payload['status'];
        $user->save();

        return response()->json([
            'success' => true,
            'status' => $user->status,
        ]);
    }

    public function Store(Request $request)
    {
        $userId = null;

        if (! empty($request->fidusers)) {
            $userId = Crypt::decryptString($request->fidusers);
        }

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'tanggal_masuk' => ['required', 'date'],
            'nik' => ['required', 'string', 'max:20'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'unit_kerja' => ['nullable', 'exists:unit,id'],
            'status' => ['nullable'],
        ]);

        if ($userId) {
            $usr = User::findOrFail($userId);
        } else {
            $usr = new User;
            $usr->nomor_anggota = $this->genCode();
            $usr->password = Hash::make('12345678');
        }

        $usr->name = $validatedData['name'];
        $usr->username = $validatedData['username'];
        $usr->email = $validatedData['email'];
        $usr->tanggal_masuk = $validatedData['tanggal_masuk'];
        $usr->nik = $validatedData['nik'];
        $usr->jabatan = $validatedData['jabatan'] ?? null;
        $usr->unit_kerja = $validatedData['unit_kerja'] ?? null;
        $usr->status = $request->boolean('status') ? 'aktif' : 'nonaktif';
        $usr->save();

        return response()->json('success', 200);
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
        $users = User::query()
            ->with(['roles:id,name', 'unit:id,nama_unit'])
            ->select('users.*');

        if ($request->filled('role') && $request->role !== 'all') {
            $users->whereHas('roles', fn ($query) => $query->where('roles.id', $request->role));
        }

        return DataTables::eloquent($users)
            ->addIndexColumn()
            ->addColumn('idusers', fn ($row) => Crypt::encryptString($row->id))
            ->addColumn('nama_unit', fn ($row) => $row->unit->nama_unit ?? '-')
            ->addColumn('role_names', fn ($row) => $row->roles->pluck('name')->values()->all())
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');

                if (! $search) {
                    return;
                }

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('users.name', 'like', '%' . $search . '%')
                        ->orWhere('users.username', 'like', '%' . $search . '%')
                        ->orWhere('users.nomor_anggota', 'like', '%' . $search . '%')
                        ->orWhere('users.nik', 'like', '%' . $search . '%')
                        ->orWhere('users.email', 'like', '%' . $search . '%');
                });
            })
            ->toJson();
    }
}
