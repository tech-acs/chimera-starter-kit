<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\Invitation;
use Uneca\Chimera\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $sortColumn = $request->get('sort') ?? 'name';
        $records = User::with('roles')
            ->when(! empty($search), function ($query) use ($search) {
                $query->where('name', 'ilike', "%$search%")
                    ->orWhere('email', 'ilike', "$search%");
            })
            ->orderBy($sortColumn)
            ->paginate(config('chimera.records_per_page'));
        return view('chimera::user.index', ['records' => $records, 'users_count' => User::count(), 'invitations_count' => Invitation::count()]);
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('chimera::user.manage', compact('user', 'roles'));
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
