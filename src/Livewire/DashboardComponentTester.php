<?php

namespace Uneca\Chimera\Livewire;

use Livewire\Component;

class DashboardComponentTester extends Component
{
    public bool $open = false;
    public array $tests = [];

    public function mount()
    {
        $this->tests = [
            [
                'test' => 'The data source is accessible',
                'test_description' => 'The database set as the data source needs to be connectible',
                'result' => 'pending',
                'result_description' => '',
            ],
            [
                'test' => 'The query is successful and returns data',
                'test_description' => 'getData() method should return rows of data',
                'result' => 'pending',
                'result_description' => '',
            ],
            [
                'test' => 'Graph has traces',
                'test_description' => 'getTraces() method should return at least one trace',
                'result' => 'pending',
                'result_description' => '',
            ],
            [
                'test' => 'Graph has layout',
                'test_description' => 'getLayout() method should return some layout',
                'result' => 'pending',
                'result_description' => '',
            ],
            [
                'test' => 'Renders properly',
                'test_description' => 'getLayout() method should return some layout',
                'result' => 'pending',
                'result_description' => '',
            ]
        ];
    }
    public function render()
    {
        return view('chimera::livewire.dashboard-component-tester');
    }
}
