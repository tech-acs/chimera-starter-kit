<?php

namespace Uneca\Chimera\Components;

use Illuminate\View\Component;
use Uneca\Chimera\Services\SmartTableData;

class SmartTable extends Component
{
    public function __construct(public SmartTableData $smartTableData, public ?string $customActionSubView = null)
    {
    }

    public function render()
    {
        return view('chimera::components.smart-table');
    }
}
