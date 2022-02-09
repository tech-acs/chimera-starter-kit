<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class RoleManager extends Component
{
    public $permissionGroups;
    public $role;
    public $permissions = [];
    public $permissionsUpdateRequired = false;

    public function mount()
    {
        try {
            foreach(($this->permissionGroups ?? []) as $page => $permissionGroup) {
                $this->permissions[$permissionGroup['permission_name']] = $this->role->hasPermissionTo($permissionGroup['permission_name']);
                foreach($permissionGroup['indicators'] as $key => $permission) {
                    $this->permissions[$permission['permission_name']] = $this->role->hasPermissionTo($permission['permission_name']);
                }
            }
        } catch (PermissionDoesNotExist $exception) {
            $this->permissionsUpdateRequired = true;
        }
    }

    public function save()
    {
        $filtered = collect($this->permissions)->filter(function ($value, $key) {
            return $value;
        })->keys();
        $this->role->syncPermissions($filtered);
        $this->emit('roleUpdated');
    }

    public function render()
    {
        return view('livewire.role-manager');
    }
}
