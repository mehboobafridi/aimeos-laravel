<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
// use App\Models\ShippingAddress;
// use App\Models\AccessedAddress;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use DB;
use Hash;
use Illuminate\Support\Arr;

class UsersManagmentController extends Controller
{
    public function index(Request $request)
    {
        $data = User::where('user_type', env("APP_ID")) //user of this app
             ->orWhere('user_type', 3) //  user of both apps
             ->orWhere('user_type', 0) // inactive user
             ->paginate(25);

        return view('users-management.index', compact('data'))->with('i', ($request->input('page', 1) - 1) * 5);
    }

    public function create()
    {
        $permission = Permission::where('app_id', env("APP_ID"))->get();
        return view('users-management.create', compact('permission'));
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|same:confirm-password',
                'permission' => 'required'
            ]);
            $input = $request->all();
            $input['password'] = Hash::make($input['password']);

            $user = User::create($input);
            $user->syncPermissions($request->input('permission'));
            return redirect()->route('users-management.index')
                            ->with('success', 'User created successfully');

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function show($id)
    {
        $user = User::find($id);
        return view('users-management.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::find($id);
        $userPermissions = DB::table("model_has_permissions")->where("model_has_permissions.model_id", $id)
            ->pluck('model_has_permissions.permission_id', 'model_has_permissions.permission_id')
            ->all();
        $permissions = Permission::where('app_id', env("APP_ID"))->get();

        // try {
        //     $selectedAddresses = $user->accessedAddresses->pluck('address_id')->toArray();
        
        //     // Fetch all addresses for the select input
        //     $addresses = ShippingAddress::all();
        // } catch (\Throwable $th) {
        //     throw $th;
        // }
        return view('users-management.edit', compact('user', 'permissions', 'userPermissions'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            'permissions' => 'required'
        ]);

        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, array('password'));
        }

        $user = User::find($id);
        $user->update($input);

        try {
            $existingPermissions = $user->permissions()
            ->where('app_id', '!=', env('APP_ID'))
            ->pluck('id')->toArray();

            $inputPermissions = $request->input('permissions');
            $mergedPermissions = array_merge($existingPermissions, $inputPermissions);
            $user->syncPermissions($mergedPermissions);

        } catch (\Throwable $th) {
            throw $th;
        }

        // try {
        //     $user->accessedAddresses()->delete();

        //     // Insert selected addresses for the current user
        //     $selectedAddresses = $request->input('addresses', []);

        //     foreach ($selectedAddresses as $addressId) {
        //         $accessedAddress = new AccessedAddress([
        //             'address_id' => $addressId
        //         ]);
        //         $user->accessedAddresses()->save($accessedAddress);
        //     }
        // } catch (\Throwable $th) {
        //     throw $th;
        // }


        return redirect()->route('users-management.index')
                        ->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->route('users-management.index')
                        ->with('success', 'User deleted successfully');
    }
}
