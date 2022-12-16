<?php

namespace Uneca\Chimera\Components;

use Illuminate\View\Component;

class SimpleCard extends Component
{
    public function __construct()
    {
        //
    }

    public function render()
    {
        return view('chimera::components.simple-card');
    }
}
