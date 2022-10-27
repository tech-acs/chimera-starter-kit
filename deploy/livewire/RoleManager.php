<?php

namespace App\Http\Livewire;

use App\Models\Page;
use App\Models\Report;
//use App\Services\PermissionHarmonizer;
use Livewire\Component;

class RoleManager extends Component
{
    public $permissionGroups;
    public $role;
    public array $permissions = [];

    public function mount()
    {
        $groups = collect([]);

        $pages = Page::with('indicators')
            ->withCount('indicators')
            ->get()
            ->map(function ($page) {
                return [
                    'title' => $page->title,
                    'description' => $page->description,
                    'permission_name' => $page->permission_name,
                    'permissionables' => $page->indicators->map(function ($indicator) {
                        return [
                            'title' => $indicator->title,
                            'description' => $indicator->description,
                            'permission_name' => $indicator->permission_name,
                        ];
                    }),
                    'count' => $page->indicators_count,
                ];
            });
        $groups = $groups->merge($pages);

        $reports = [
            [
                'title' => 'Reports',
                'description' => 'This is the reports page',
                'permission_name' => 'reports',
                'permissionables' => Report::all()->map(function ($report) {
                    return [
                        'title' => $report->title,
                        'description' => $report->description,
                        'permission_name' => $report->permission_name,
                    ];
                }),
                'count' => Report::all()->count(),
            ]
        ];
        $groups = $groups->merge($reports);

        $this->permissionGroups = $groups;
        foreach(($this->permissionGroups ?? []) as $permissionGroup) {
            $this->permissions[$permissionGroup['permission_name']] = $this->role->hasPermissionTo($permissionGroup['permission_name']);
            foreach($permissionGroup['permissionables'] as $permissionable) {
                $this->permissions[$permissionable['permission_name']] = $this->role->hasPermissionTo($permissionable['permission_name']);
            }
        }
        //dump($this->permissionGroups);
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
