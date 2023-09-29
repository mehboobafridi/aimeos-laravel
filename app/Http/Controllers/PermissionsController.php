<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;

class PermissionsController extends Controller
{
    public function index(Request $request)
    {
        $permissions = Permission::where('app_id', env("APP_ID"))->orderBy('id', 'DESC')->paginate(25);

        return view('users-management.permissions', compact('permissions'))
            ->with('i', ($request->input('page', 1) - 1) * 25);
    }


    public function create()
    {
        return view('users-management.create-permission');
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:permissions,name',

        ]);

        $permission = Permission::create(['name' => $request->input('name')]);

        return redirect()->route('permissions.index')
                        ->with('success', 'Permission created successfully');
    }

    public function destroy($id)
    {
        DB::table("permissions")->where('id', $id)->delete();
        return redirect()->route('permissions.index')
                        ->with('success', 'Permission deleted successfully');
    }
}
