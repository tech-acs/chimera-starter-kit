<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $records = User::paginate(env('PAGE_SIZE', 20));
        return view('users.index', compact('records'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.manage', compact('user', 'roles'));
    }

    public function update(User $user, Request $request)
    {
        $user->syncRoles([$request->get('role')]);
        return redirect()->route('users.index');
    }

    public function destroy(User $user)
    {
        $user->deleteProfilePhoto();
        $user->usageStats()->delete();
        $user->delete();
        return redirect()->route('users.index');
    }
}
