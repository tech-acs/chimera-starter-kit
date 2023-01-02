<?php

namespace Uneca\Chimera\Http\Livewire;

use Livewire\Component;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Services\Theme;

class ScorecardComponent extends Component
{
    public Scorecard $scorecard;
    public string $title;
    public int|float|string $value;
    public string $bgColor;
    public int $diff = 0;
    public string $unit = '%';

    protected function getData(array $filter): int|float|string
    {
        return 'N/A';
    }

    final public function setData()
    {
        $this->value = $this->getData([]);
    }

    public function mount(Scorecard $scorecard, $index)
    {
        $this->scorecard = $scorecard;
        $this->title = $this->scorecard->title;
        $this->bgColor = Theme::colors()[$index];
    }

    public function render()
    {
        return view('chimera::livewire.scorecard');
    }
}
