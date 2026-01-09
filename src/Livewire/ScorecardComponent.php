<?php

namespace Uneca\Chimera\Livewire;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Uneca\Chimera\Enums\DataStatus;
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

        $this->resolveAreaAndCheckData();
        /*list($this->filterPath,) = $this->areaResolver();
        $this->checkData();*/
    }

    private function resolveAreaAndCheckData()
    {
        list($this->filterPath,) = $this->areaResolver();
        if ($this->scorecard->supportsLevel($this->filterPath)) {
            $this->checkData();
        } else {
            $this->dataStatus = DataStatus::INAPPLICABLE->value;
        }
    }

    public function placeholder()
    {
        return view('chimera::livewire.placeholders.scorecard');
    }

    public function cacheKey(): string
    {
        return implode(':', ['scorecard', $this->scorecard->id, $this->filterPath]);
    }

    public function setPropertiesFromData(): void
    {
        list($this->dataTimestamp, $data) = Cache::get($this->cacheKey());
        if (($data instanceof Collection) && ($data->count() == 2)) {
            list($this->value, $this->diff) = $data;
            $this->dataStatus = DataStatus::RENDERABLE->value;
        } else {
            $this->dataStatus = DataStatus::EMPTY->value;
        }
    }

    public function getDataModel(): Model
    {
        return $this->scorecard;
    }

    public function render()
    {
        return view('chimera::livewire.scorecard');
    }
}
