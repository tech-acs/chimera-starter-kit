<?php

namespace Uneca\Chimera\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Services\DashboardComponentFactory;

class IndicatorTester extends Component
{
    public bool $modalOpen = false;
    public Indicator $indicator;
    public array $tests = [
        'is_data_source_connectible' => [
            'test' => 'The data source is accessible',
            'test_description' => 'The database set as the data source needs to be connectible',
            'result' => 'pending',
            'result_description' => '',
        ],
        'returns_data' => [
            'test' => 'The query is successful and returns data',
            'test_description' => 'getData() method should return rows of data',
            'result' => 'pending',
            'result_description' => '',
        ],
        'returns_traces' => [
            'test' => 'Graph has traces',
            'test_description' => 'getTraces() method should return at least one trace',
            'result' => 'pending',
            'result_description' => '',
        ],
        /*'has_layout' => [
            'test' => 'Graph has layout',
            'test_description' => 'getLayout() method should return some layout',
            'result' => 'pending',
            'result_description' => '',
        ],*/
        'graph_is_valid' => [
            'test' => 'Renders properly',
            'test_description' => 'getLayout() method should return some layout',
            'result' => 'pending',
            'result_description' => '',
        ]
    ];

    private function isDataSourceConnectible(): array
    {
        try {
            DB::connection($this->indicator->data_source)->getPdo();
            return ['result' => 'passed', 'result_description' => 'The database is connectable'];
        } catch (\Exception $exception) {
            return ['result' => 'failed', 'result_description' => $exception->getMessage()];
        }
    }

    private function returnsData(): array
    {
        $instance = DashboardComponentFactory::makeIndicator($this->indicator);
        $data = $instance->getData([]);
        if ($data->isEmpty()) {
            return ['result' => 'failed', 'result_description' => 'No data returned'];
        } else {
            return ['result' => 'passed', 'result_description' => $data->count() . ' rows returned'];
        }
    }

    private function returnsTraces(): array
    {
        return ['result' => 'pending', 'result_description' => 'not implemented, yet!'];
    }

    private function graphIsValid(): array
    {
        return ['result' => 'pending', 'result_description' => 'not implemented, yet!'];
    }

    private function runTest(string $testName): array
    {
        return match ($testName) {
            'is_data_source_connectible' => $this->isDataSourceConnectible(),
            'returns_data' => $this->returnsData(),
            'returns_traces' => $this->returnsTraces(),
            'graph_is_valid' => $this->graphIsValid(),
            default => []
        };
    }

    public function start()
    {
        foreach ($this->tests as $name => $test) {
            $this->tests[$name]['result'] = 'running';
            $this->tests[$name] = [...$this->tests[$name], ...$this->runTest($name)];
        }
    }

    public function render()
    {
        return view('chimera::livewire.indicator-tester');
    }
}
