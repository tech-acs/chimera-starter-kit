<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Mechanisms\ComponentRegistry;
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
            ->merge(['case-stats' => 'Uneca\Chimera\Livewire\CaseStats (default)'])
            ->reverse();
    }

    public function index()
    {
        $records = DataSource::orderBy('rank')->get();
        return view('chimera::developer.data-source.index', compact('records'));
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
        if ($request->boolean('create_queryfragment')) {
            Artisan::call('chimera:make-queryfragment', ['--data-source' => $request->title]);
        }
        return redirect()->route('developer.data-source.index')->withMessage('Record created');
    }

    public function edit(DataSource $dataSource)
    {
        try {
            $dataSource->password;
        } catch (DecryptException $e) {
            DB::table('data_sources')
                ->where('id', $dataSource->id)
                ->update(['password' => Crypt::encryptString('')]);
            $dataSource = DataSource::find($dataSource->id);
        }

        $components = $this->getCaseStatComponentsList();
        return view('chimera::developer.data-source.edit', compact('dataSource', 'components'))
            ->with(['databases' => $this->databases]);
    }

    public function update(DataSource $dataSource, DataSourceRequest $request)
    {
        $columns = $request->only([
            'name', 'title', 'start_date', 'end_date', 'show_on_home_page', 'rank', 'host', 'port', 'database',
            'username', 'password', 'connection_active', 'case_stats_component', 'driver'
        ]);
        if (Gate::denies('developer-mode')) {
            unset($columns['name']);
        }
        $dataSource->update($columns);
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
