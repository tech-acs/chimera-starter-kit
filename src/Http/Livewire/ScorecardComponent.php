<?php

namespace Uneca\Chimera\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Uneca\Chimera\Models\Scorecard;
use Uneca\Chimera\Services\ScorecardCaching;
use Uneca\Chimera\Services\Theme;

class ScorecardComponent extends Component
{
    public Scorecard $scorecard;
    public string $title;
    public int|float|string $value;
    public string $bgColor;
    public ?int $diff = null;
    public string $unit = '%';
    public Carbon $dataTimestamp;

    public function mount(Scorecard $scorecard, $index)
    {
        $this->scorecard = $scorecard;
        $this->title = $this->scorecard->title;
        $index = $index % count(Theme::colors());
        $this->bgColor = Theme::colors()[$index];
    }

    public function getData(array $filter): int|float|string
    {
        return 'N/A';
    }

    final public function setValue()
    {
        $this->dataTimestamp = Carbon::now();
        try {
            if (config('chimera.cache.enabled')) {
                $caching = new ScorecardCaching($this->scorecard, []);
                $this->dataTimestamp = $caching->getTimestamp();
                logger($caching->key, ['Is cached?' => Cache::tags($caching->tags())->has($caching->key)]);
                $this->value = Cache::tags($caching->tags())
                    ->rememberForever($caching->key, function () use ($caching) {
                        $caching->stamp();
                        $this->dataTimestamp = Carbon::now();
                        return $this->getData($caching->filter);;
                    });
            } else {
                $this->value = $this->getData([]);
            }
        } catch (\Exception $exception) {
            logger("Exception occurred while trying to cache (in ScorecardComponent.php)", ['Exception: ' => $exception]);
            $this->value = $this->getData([]);
        }
    }

    public function render()
    {
        return view('chimera::livewire.scorecard');
    }
}
