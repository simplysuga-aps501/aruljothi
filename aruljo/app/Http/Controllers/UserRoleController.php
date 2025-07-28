<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;


class UserRoleController extends Controller
{
    //
    public function index()
    {
        // Fetch all users except the one with ID 1 (the admin)
        $list = User::where('id', '!=', 1)->get();
        $role = Role::where('id', '!=', 1)->get();

        return view("users.users-list",compact('list','role'));
    }
    
    public function role(Request $request, $id) {
        $user = User::find($id);

        // Make sure role_id is always an array
        $roleIds = $request->input('role_id', []);

        $role = Role::whereIn('id', $roleIds)
            ->where('id', '!=', 1)
            ->pluck('name')
            ->toArray();

        $user->syncRoles($role);

        return redirect()->back()->with('success', 'Role '.implode(', ', $role).' is updated for '.$user->email);
    }

}
