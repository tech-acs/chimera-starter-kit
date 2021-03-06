<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $records = User::paginate(env('PAGE_SIZE', 20));
        return view('user.index', compact('records'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('user.manage', compact('user', 'roles'));
    }

    public function update(User $user, Request $request)
    {
        $user->syncRoles([$request->get('role')]);
        return redirect()->route('user.index');
    }

    public function destroy(User $user)
    {
        $user->deleteProfilePhoto();
        $user->usageStats()->delete();
        $user->delete();
        return redirect()->route('user.index');
    }
}
