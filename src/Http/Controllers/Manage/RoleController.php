<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $records = Role::with('permissions')->get();
        return view('chimera::role.index', compact('records'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name'
        ]);
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        Role::create(['name' => $request->get('name'), 'guard_name' => 'web']);
        return redirect()->route('role.index');
    }

    public function edit(Role $role)
    {
        if ($role->name === 'Super Admin') {
            abort(403, 'Unauthorized action');
        }
        return view('chimera::role.manage', compact('role'));
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            abort(403, 'Unauthorized action');
        }
        if ($role->users->count() > 0) {
            $message = 'There are users assigned this role. Please make sure no user is assigned the role before deleting it';
        } else {
            $role->delete();
            $message = 'The role has been deleted';
        }
        return redirect()->route('role.index')->withMessage($message);
    }
}
