<?php

namespace Uneca\Chimera\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Services\APCA;
use Uneca\Chimera\Services\ColorPalette;
use Uneca\Chimera\Traits\AreaResolver;
use Uneca\Chimera\Traits\Cachable;

abstract class ScorecardComponent extends Component
{
    use Cachable;
    use AreaResolver;

    public Scorecard $scorecard;
    public string $title;
    public int|float|string $value = '';
    public int|float|string|null $diff = null;
    public string $unit = '%';
    public string $bgColor;
    public string $fgColor;
    public Carbon $dataTimestamp;

    public function mount(int $index)
    {
        $this->title = $this->scorecard->title;
        $currentPalette = ColorPalette::palette(settings('color_palette'));
        $totalColors = count($currentPalette->colors);
        $this->bgColor = $currentPalette->colors[$index % $totalColors];
        $this->fgColor = APCA::decideBlackOrWhiteTextColor($this->bgColor);
        list($this->filterPath,) = $this->areaResolver();
        $this->checkData();
    }

    public function placeholder()
    {
        return view('chimera::livewire.placeholders.scorecard');
    }

    public function cacheKey(): string
    {
        return implode(':', ['score-card', $this->scorecard->id, $this->filterPath]);
    }

    public function setPropertiesFromData(): void
    {
        list($this->dataTimestamp, $data) = Cache::get($this->cacheKey());
        list($this->value, $this->diff) = $data;
    }

    public function render()
    {
        return view('chimera::livewire.scorecard');
    }
}
