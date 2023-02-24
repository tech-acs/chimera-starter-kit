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
    public int|float|string $value = '';
    public int|float|string|null $diff = null;
    public string $diffTitle;
    public string $unit = '%';
    public string $bgColor;
    public Carbon $dataTimestamp;

    public function mount($index)
    {
        $this->title = $this->scorecard->title;
        $index = $index % count(Theme::colors());
        $this->bgColor = Theme::colors()[$index];
    }

    public function getData(array $filter): array
    {
        return [$this->value, $this->diff];
    }

    final public function setValue()
    {
        $user = auth()->user();
        $filter = $user->areaRestrictionAsFilter();
        $analytics = ['user_id' => auth()->id(), 'source' => 'Cache', 'level' => empty($filter) ? null : count($filter), 'started_at' => time(), 'completed_at' => null];
        $this->dataTimestamp = Carbon::now();
        try {
            if (config('chimera.cache.enabled')) {
                $caching = new ScorecardCaching($this->scorecard, []);
                $this->dataTimestamp = $caching->getTimestamp();
                list($this->value, $this->diff) = Cache::tags($caching->tags())
                    ->remember($caching->key, config('chimera.cache.ttl'), function () use ($caching, &$analytics) {
                        $caching->stamp();
                        $this->dataTimestamp = Carbon::now();
                        $analytics['source'] = 'Caching';
                        return $this->getData($caching->filter);
                    });
            } else {
                $analytics['source'] = 'Not caching';
                list($this->value, $this->diff) = $this->getData($filter);
            }
        } catch (\Exception $exception) {
            logger("Exception occurred while trying to cache (in ScorecardComponent.php)", ['Exception: ' => $exception]);
            list($this->value, $this->diff) = ['Err', null];
        } finally {
            if ($analytics['source'] !== 'Cache') {
                $analytics['completed_at'] = time();
                $this->scorecard->analytics()->create($analytics);
            }
        }
    }

    public function render()
    {
        return view('chimera::livewire.scorecard');
    }
}
