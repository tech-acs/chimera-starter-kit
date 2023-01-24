<?php

namespace Uneca\Chimera\Http\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Models\Questionnaire;
use Uneca\Chimera\Services\BreakoutQueryBuilder;
use Uneca\Chimera\Services\QueryFragmentFactory;

class CaseStats extends Component
{
    public Questionnaire $questionnaire;
    public array $stats = [];
    public Carbon $dataTimestamp;

    public function mount(Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;
    }

    public function setStats()
    {
        $user = auth()->user();
        $filter = $user->areaRestrictionAsFilter();;
        $this->dataTimestamp = Carbon::now();
        try {
            if (config('chimera.cache.enabled')) {
                $key = 'casestat|' . $this->questionnaire->name . implode('-', array_filter($filter));
                $tags = [$this->questionnaire->name, 'casestats'];
                $this->dataTimestamp = Cache::tags(['timestamps'])->get("$key|timestamp", Carbon::now());
                //logger($caching->key, ['Is cached?' => Cache::tags($caching->tags())->has($caching->key)]);
                $this->stats = Cache::tags($tags)
                    ->remember($key, config('chimera.cache.ttl'), function () use ($key, $filter) {
                        Cache::tags(['timestamps'])->put("$key|timestamp", Carbon::now());
                        $this->dataTimestamp = Carbon::now();
                        return $this->getData($filter);
                    });
            } else {
                $this->stats = $this->getData($filter);
            }
        } catch (\Exception $exception) {
            logger("Exception occurred while trying to cache (in CaseStats.php)", ['Exception: ' => $exception]);
            $this->stats = $this->getData($filter);
        }
    }

    public function getData(array $filter)
    {
        $queryFragmentFactory = QueryFragmentFactory::make($this->questionnaire->name);
        if (is_null($queryFragmentFactory)) {
            $whereConditions = [];
        } else {
            list(, $whereConditions) = $queryFragmentFactory->getSqlFragments($filter);
        }
        $l = (new BreakoutQueryBuilder($this->questionnaire->name, false))
            ->select([
                "COUNT(*) AS total",
                "SUM(CASE WHEN cases.partial_save_mode IS NULL THEN 1 ELSE 0 END) AS complete",
                "SUM(CASE WHEN cases.partial_save_mode IS NULL THEN 0 ELSE 1 END) AS partial",
                "COUNT(*) - COUNT(DISTINCT `key`) AS duplicate"
            ])
            ->from([])
            ->where($whereConditions)
            ->get()
            ->first();
        $info = ['total' => 'NA', 'complete' => 'NA', 'partial' => 'NA', 'duplicate' => 'NA'];
        if (!is_null($l)) {
            $nFormatter = new \NumberFormatter(app()->getLocale(), \NumberFormatter::TYPE_INT32);
            $info['total'] = $nFormatter->format($l->total);
            $info['complete'] = $nFormatter->format($l->complete);
            $info['partial'] = $nFormatter->format($l->partial);
            $info['duplicate'] = $nFormatter->format($l->duplicate);
        }
        return $info;
    }

    public function render()
    {
        return view('chimera::livewire.case-stats');
    }
}
