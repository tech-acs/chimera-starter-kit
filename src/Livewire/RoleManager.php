<?php

namespace Uneca\Chimera\Livewire;

use Uneca\Chimera\Enums\PageableTypes;
use Uneca\Chimera\Models\Gauge;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\MapIndicator;
use Uneca\Chimera\Models\Page;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Uneca\Chimera\Models\Report;
use Uneca\Chimera\Models\Scorecard;

class RoleManager extends Component
{
    public $permissionGroups;
    public $role;
    public array $permissions = [];

    public function mount()
    {
        $groups = collect();

        $indicators = [
            [
                'title' => 'Indicators',
                'description' => 'All indicators',
                'permission_name' => 'indicators',
                'permissionables' => Indicator::all()->map(function ($record) {
                    return [
                        'title' => $record->title,
                        'description' => "Scope: {$record->scope->value}<br />Pages: " . $record->pages()->pluck('title')->join(', ', ', and '),
                        'permission_name' => $record->permission_name,
                    ];
                }),
                'count' => Indicator::count(),
            ]
        ];
        $groups = $groups->merge($indicators);

        $mapIndicators = [
            [
                'title' => 'Map Indicators',
                'description' => 'All map indicators',
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
        $groups = $groups->merge($mapIndicators);

        $reports = [
            [
                'title' => 'Reports',
                'description' => 'All reports',
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

        $gauges = [
            [
                'title' => 'Gauges',
                'description' => 'All gauges',
                'permission_name' => 'gauges',
                'permissionables' => Gauge::all()->map(function ($gauge) {
                    return [
                        'title' => $gauge->title,
                        'description' => $gauge->subtitle,
                        'permission_name' => $gauge->permission_name,
                    ];
                }),
                'count' => Gauge::all()->count(),
            ]
        ];
        $groups = $groups->merge($gauges);

        $scorecards = [
            [
                'title' => 'Scorecards',
                'description' => 'All scorecards',
                'permission_name' => 'scorecards',
                'permissionables' => Scorecard::all()->map(function ($scorecard) {
                    return [
                        'title' => $scorecard->title,
                        'description' => 'Scope: ' . $scorecard->scope->value,
                        'permission_name' => $scorecard->permission_name,
                    ];
                }),
                'count' => Scorecard::all()->count(),
            ]
        ];
        $groups = $groups->merge($scorecards);

        $pages = [
            [
                'title' => 'Pages',
                'description' => 'All pages (of Indicators, Map Indicators and Reports)',
                'permission_name' => 'pages',
                'permissionables' => Page::all()->map(function ($page) {
                    return [
                        'title' => $page->title . " ({$page->for->value})",
                        'description' => $page->description,
                        'permission_name' => $page->permission_name,
                    ];
                }),
                'count' => Page::all()->count(),
            ]
        ];
        $groups = $groups->merge($pages);

        $this->permissionGroups = $groups;
        foreach (($this->permissionGroups ?? []) as $permissionGroup) {
            //Permission::firstOrCreate(['guard_name' => 'web', 'name' => $permissionGroup['permission_name']]);
            //$this->permissions[$permissionGroup['permission_name']] = $this->role->hasPermissionTo($permissionGroup['permission_name']);
            foreach ($permissionGroup['permissionables'] as $permissionable) {
                Permission::firstOrCreate(['guard_name' => 'web', 'name' => $permissionable['permission_name']]);
                $this->permissions[$permissionable['permission_name']] = $this->role->hasPermissionTo($permissionable['permission_name']);
            }
        }
        //dump($this->permissions);
    }

    public function save()
    {
        $filtered = collect($this->permissions)->filter(fn($value, $key) => $value)->keys();
        $this->role->syncPermissions($filtered);
        $this->dispatch('roleUpdated');
    }

    public function render()
    {
        return view('chimera::livewire.role-manager');
    }
}
