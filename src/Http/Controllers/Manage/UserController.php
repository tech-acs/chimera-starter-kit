<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\Invitation;
use Uneca\Chimera\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Uneca\Chimera\Services\SmartTableColumn;
use Uneca\Chimera\Services\SmartTableData;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return (new SmartTableData(User::with('roles'), $request))
            ->columns([
                SmartTableColumn::make('name')
                    ->sortable()
                    ->setBladeTemplate('<div class="flex items-center">
                                                        <div class="flex-shrink-0 h-8 w-8">
                                                            <img class="h-8 w-8 rounded-full object-cover" src="{{ $row->profile_photo_url }}" alt="{{ $row->name }}" />
                                                        </div>
                                                        <div class="ml-2 font-medium text-gray-900">
                                                            {{ $row->name }}
                                                        </div>
                                                    </div>'),
                SmartTableColumn::make('email')
                    ->sortable(),
                SmartTableColumn::make('created_at')
                    ->setLabel('Created')
                    ->sortable()
                    ->setBladeTemplate('{{ $row->created_at->locale(app()->getLocale())->isoFormat("ll") }}'),
                SmartTableColumn::make('role')
                    ->setBladeTemplate('<div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-gray-600">{{ $row->roles->pluck("name")->join(", ") }}</div>'),
            ])
            ->searchable(['name', 'email'])
            ->sortBy('name')
            ->view('chimera::user.index', ['users_count' => User::count(), 'invitations_count' => Invitation::count()]);
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('chimera::user.manage', compact('user', 'roles'));
    }

    public function update(User $user, Request $request)
    {
        $user->syncRoles([$request->get('role')]);
        return redirect()->route('user.index')->withMessage('User role updated');
    }

    public function destroy(User $user)
    {
        $user->deleteProfilePhoto();
        $user->usageStats()->delete();
        $user->areaRestrictions()->delete();
        $user->announcements()->delete();

        // ToDo: finish this!
        //$user->reports()->dissociate();
        //$user->permissions;

        $user->delete();
        return redirect()->route('user.index')
            ->withMessage('The user and all related resources have been removed from the application');
    }
}
