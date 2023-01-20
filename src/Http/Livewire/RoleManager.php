<?php

namespace Uneca\Chimera\Http\Livewire;

use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\Page;
use Uneca\Chimera\Models\Report;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Uneca\Chimera\Models\Scorecard;

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

        Permission::firstOrCreate(['guard_name' => 'web', 'name' => 'reports']);
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

        Permission::firstOrCreate(['guard_name' => 'web', 'name' => 'scorecards']);
        $scorecards = [
            [
                'title' => 'Scorecards',
                'description' => 'This is the scorecards section',
                'permission_name' => 'scorecards',
                'permissionables' => Scorecard::all()->map(function ($scorecard) {
                    return [
                        'title' => $scorecard->title,
                        'description' => '',
                        'permission_name' => $scorecard->permission_name,
                    ];
                }),
                'count' => Scorecard::all()->count(),
            ]
        ];
        $groups = $groups->merge($scorecards);

        Permission::firstOrCreate(['guard_name' => 'web', 'name' => 'maps']);
        $maps = [
            [
                'title' => 'Map Indicators',
                'description' => 'This is the maps page',
                'permission_name' => 'maps',
                'permissionables' => MapIndicator::all()->map(function ($record) {
                    return [
                        'title' => $record->title,
                        'description' => $record->description,
                        'permission_name' => $record->permission_name,
                    ];
                }),
                'count' => MapIndicator::count(),
            ]
        ];
        $groups = $groups->merge($maps);

        $this->permissionGroups = $groups;
        foreach (($this->permissionGroups ?? []) as $permissionGroup) {
            Permission::firstOrCreate(['guard_name' => 'web', 'name' => $permissionGroup['permission_name']]);
            $this->permissions[$permissionGroup['permission_name']] = $this->role->hasPermissionTo($permissionGroup['permission_name']);
            foreach ($permissionGroup['permissionables'] as $permissionable) {
                Permission::firstOrCreate(['guard_name' => 'web', 'name' => $permissionable['permission_name']]);
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
        return view('chimera::livewire.role-manager');
    }
}
