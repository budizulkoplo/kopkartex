<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class MenuController extends Controller
{
    public function index(Request $request): view
    {
        return view('master.menu.list', [
            'user' => $request->user(),
            'roles' =>  Role::with('permissions')->get(),
        ]);
    }
    public function datamenu($role){
        $menu=array();
        $data = Menu::orderBy('seq')->get();
        
        foreach ( $data as $value) {
            $cekparent = Menu::where('parent_id',$value->id)->orderBy('seq')->count();
            $hd=array(
                'text'=>$value->name,
                'id'=>$value->id,
                'icon'=>$value->icon,
                'parent'=>empty($value->parent_id)?'#':$value->parent_id,
                'state'=>
                array(
                    'opened'=>true,
                    'role'=>(empty($value->parent_id)?true:false),
                    'disabled'=>(empty($value->parent_id) && $cekparent>0 ? true : false),
                    //'disabled'=>false,
                    'selected'=> $value->role_names->contains($role),
                    )
                );
            array_push( $menu,$hd);
            //return response()->json($level);
        }
        return response()->json($menu); 
    }
    public function update(Request $request){
        $data = Menu::findOrFail($request->id);
        $roles = $data->role_names->all();

        if ($request->aktif === 'true') {
            $roles[] = $request->gp;
        } else {
            $roles = array_values(array_filter($roles, fn ($role) => $role !== $request->gp));
        }

        $data->syncRoleNames($roles);
        $data->save();

        return response()->json($data);
    }
}
