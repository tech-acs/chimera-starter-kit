<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Livewire\Features\SupportConsoleCommands\Commands\ComponentParser;
use Livewire\Mechanisms\ComponentRegistry;
use ReflectionClass;
use Uneca\Chimera\Http\Requests\DataSourceRequest;
use Uneca\Chimera\Models\DataSource;
use Illuminate\Support\Facades\Storage;

class DataSourceController extends Controller
{
    private array $databases = [
        'MySQL 5.7+/MariaDB 10.3+' => 'mysql',
        'PostgreSQL 10.0+' => 'pgsql',
        'SQLite 3.8.8+' => 'sqlite',
        'SQL Server 2017+' => 'sqlsrv',
    ];

    public function index()
    {
        $records = DataSource::orderBy('rank')->get();
        return view('chimera::developer.data-source.index', compact('records'));
    }

    private function getCaseStatComponentsList()
    {
        $filesystem = Storage::build([
            'driver' => 'local',
            'root' => base_path(),
        ]);
        return collect($filesystem->allFiles('app/Livewire'))
            ->filter(function($file) {
                return str($file)->contains('CaseStats');
            })
            ->mapWithKeys(function($file) {
                $componentName = str($file)->after('app/Livewire/')->before('.php')->kebab()->__toString();
                $qualifiedName = str((new ComponentRegistry)->getClass($componentName))->ltrim("\\");
                return [$componentName => $qualifiedName];
            })
            ->merge(['case-stats' => 'Uneca\Chimera\Http\Livewire\CaseStats (default)'])
            ->reverse();
    }

    public function create()
    {
        $components = $this->getCaseStatComponentsList();
        return view('chimera::developer.data-source.create', compact('components'))
            ->with(['databases' => $this->databases]);
    }

    public function store(DataSourceRequest $request)
    {
        DataSource::create($request->only([
            'name', 'title', 'start_date', 'end_date', 'show_on_home_page', 'rank', 'host', 'port', 'database',
            'username', 'password', 'connection_active', 'case_stats_component', 'driver'
        ]));
        return redirect()->route('developer.data-source.index')->withMessage('Record created');
    }

    public function edit(DataSource $dataSource)
    {
        $components = $this->getCaseStatComponentsList();
        return view('chimera::developer.data-source.edit', compact('dataSource', 'components'))
            ->with(['databases' => $this->databases]);
    }

    public function update(DataSource $dataSource, DataSourceRequest $request)
    {
        $dataSource->update($request->only([
            'name', 'title', 'start_date', 'end_date', 'show_on_home_page', 'rank', 'host', 'port', 'database',
            'username', 'password', 'connection_active', 'case_stats_component', 'driver'
        ]));
        return redirect()->route('developer.data-source.index')->withMessage('Record updated');
    }

    public function destroy(DataSource $dataSource)
    {
        $dataSource->delete();
        return redirect()->route('developer.data-source.index')->withMessage('Record deleted');
    }

    public function test(DataSource $dataSource)
    {
        $results = $dataSource->test();
        $passesTest = $results->reduce(function ($carry, $item) {
            return $carry && $item['passes'];
        }, true);
        if ($passesTest) {
            return redirect()->route('developer.data-source.index')
                ->withMessage('Connection test successful');
        } else {
            return redirect()->route('data-source.index')
                ->withErrors($results->pluck('message')->filter()->all());
        }
    }
}
