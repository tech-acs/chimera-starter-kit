<?php

namespace Uneca\Chimera\Livewire;

use Illuminate\Support\Facades\Cookie;
use Livewire\Component;

class UserPageSizeAdjuster extends Component
{
    public bool $modalOpen = false;
    public int $defaultPageSize = 2;
    public int $pageSize = 2;
    public array $pageSizeOptions = [2, 4, 6, 8, 10, 12, 20, 24, 30, 50];

    public function mount()
    {
        $this->defaultPageSize = settings('indicators_per_page', 2);
        $this->pageSize = request()->cookie('indicators_per_page', settings('indicators_per_page', 2));
    }

    public function applyAndSave()
    {
        Cookie::queue(Cookie::forever('indicators_per_page', $this->pageSize));
        $this->dispatch('saved');
    }

    public function render()
    {
        return view('chimera::livewire.user-page-size-adjuster');
    }
}
