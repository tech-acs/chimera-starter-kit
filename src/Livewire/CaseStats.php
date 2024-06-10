<?php

namespace Uneca\Chimera\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Uneca\Chimera\Enums\DataStatus;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Services\BreakoutQueryBuilder;
use Uneca\Chimera\Traits\AreaResolver;
use Uneca\Chimera\Traits\Cachable;

class CaseStats extends Component
{
    use Cachable;
    use AreaResolver;

    public DataSource $dataSource;
    public Collection $stats;
    public Carbon $dataTimestamp;

    public function mount(DataSource $dataSource)
    {
        $this->dataTimestamp = Carbon::now();
        $this->dataSource = $dataSource;
        list($this->filterPath,) = $this->areaResolver();
        $this->checkData();
    }

    public function placeholder()
    {
        return view('chimera::livewire.placeholders.case-stats');
    }

    public function cacheKey(): string
    {
        return implode(':', ['case-stats', $this->dataSource->id, $this->filterPath]);
    }

    public function getData(string $filterPath): Collection
    {
        try {
            $l = (new BreakoutQueryBuilder($this->dataSource->name, $filterPath, excludePartials: false))
                ->select([
                    "COUNT(*) AS total",
                    "SUM(CASE WHEN cases.partial_save_mode IS NULL THEN 1 ELSE 0 END) AS complete",
                    "SUM(CASE WHEN cases.partial_save_mode IS NULL THEN 0 ELSE 1 END) AS partial",
                    "COUNT(*) - COUNT(DISTINCT cases.`key`) AS duplicate"
                ])
                ->from([])
                ->get()
                ->first();
            $info = ['total' => 'NA', 'complete' => 'NA', 'partial' => 'NA', 'duplicate' => 'NA'];
            if (!is_null($l)) {
                $info['total'] = $l->total;
                $info['complete'] = $l->complete;
                $info['partial'] = $l->partial;
                $info['duplicate'] = $l->duplicate;
            }
            return collect($info);
        } catch (\Exception $exception) {
            logger('Exception in CaseStats:', ['exception' => $exception->getMessage()]);
            return collect();
        }
    }

    public function setPropertiesFromData(): void
    {
        list($this->dataTimestamp, $this->stats) = Cache::get($this->cacheKey());
        $this->dataStatus = $this->stats->isEmpty() ?
            DataStatus::EMPTY :
            DataStatus::RENDERABLE;
    }

    public function render()
    {
        return view('chimera::livewire.case-stats');
    }
}
