<?php

namespace Uneca\Chimera\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Uneca\Chimera\Models\Questionnaire;
use Uneca\Chimera\Services\BreakoutQueryBuilder;
use Uneca\Chimera\Services\CaseStatsCaching;
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
        $analytics = ['user_id' => auth()->id(), 'source' => 'Cache', 'level' => empty($filter) ? null : (count($filter) - 1), 'started_at' => time(), 'completed_at' => null];
        $this->dataTimestamp = Carbon::now();
        try {
            if (config('chimera.cache.enabled')) {
                $caching = new CaseStatsCaching($this->questionnaire, $filter);
                $this->dataTimestamp = $caching->getTimestamp();
                $this->stats = Cache::tags($caching->tags())
                    ->remember($caching->key, config('chimera.cache.ttl'), function () use ($caching, &$analytics) {
                        $caching->stamp();
                        $this->dataTimestamp = Carbon::now();
                        $analytics['source'] = 'Caching';
                        return $this->getData($caching->filter);
                    });
            } else {
                $analytics['source'] = 'Not caching';
                $this->stats = $this->getData($filter);
            }
        } catch (\Exception $exception) {
            logger("Exception occurred while trying to cache (in CaseStats.php)", ['Exception: ' => $exception]);
            $this->stats = [];
        } finally {
            if ($analytics['source'] !== 'Cache') {
                $analytics['completed_at'] = time();
                $this->questionnaire->analytics()->create($analytics);
            }
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
        $sql = "
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN cases.partial_save_mode IS NULL THEN 1 ELSE 0 END) AS complete,
                SUM(CASE WHEN cases.partial_save_mode IS NULL THEN 0 ELSE 1 END) AS partial,
                COUNT(*) - COUNT(DISTINCT `key`) AS duplicate
            FROM cases
            WHERE
        ";
        $l = DB::connection($this->questionnaire->name)
            ->select($sql . implode(' AND ', array_merge(["cases.key != ''", "cases.deleted = 0"], $whereConditions)))[0];
        $info = ['total' => 'NA', 'complete' => 'NA', 'partial' => 'NA', 'duplicate' => 'NA'];
        if (!is_null($l)) {
            $info['total'] = $l->total;
            $info['complete'] = $l->complete;
            $info['partial'] = $l->partial;
            $info['duplicate'] = $l->duplicate;
        }
        return $info;
    }

    public function render()
    {
        return view('chimera::livewire.case-stats');
    }
}
