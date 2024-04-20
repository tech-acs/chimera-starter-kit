<?php

namespace Uneca\Chimera\Components;

use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\BreakoutQueryBuilder;
use Carbon\Carbon;
use Illuminate\View\Component;

class Summary extends Component
{
    public DataSource $dataSource;
    public string $title;
    public array $dates = [];
    public string $lastUpdated;
    public ?Area $area = null;

    public function __construct(DataSource $dataSource)
    {
        $this->dataSource = $dataSource;
        $this->dates['start'] = $this->dataSource->start_date;
        $this->dates['end'] = $this->dataSource->end_date;
        $this->dates['progress'] = $this->makeProgressStatement($this->dates['start'], $this->dates['end'], Carbon::today());
        $this->lastUpdated = $this->getLastUpdated();
        $this->title = $this->dataSource->title;
        $this->color = 'text-black';
        $areaRestrictions = auth()->user()->areaRestrictions;
        if ($areaRestrictions->isNotEmpty()) {
            $this->area = (new AreaTree())->getArea($areaRestrictions->first()->path);
        }
    }

    private function makeProgressStatement(Carbon $s, Carbon $e, Carbon $now)
    {
        if ($now->isBefore($s)) {
            $progress = __(':diff days to go', ['diff' => (int)$now->diffInDays($s, absolute: true)]);
        } elseif ($now->isBetween($s, $e)) {
            $progress = __('Day :sofar of :total', ['sofar' => (int)$now->diffInDays($s, absolute: true) + 1, 'total' => (int)$s->diffInDays($e, absolute: true) + 1]);
        } elseif ($now->isAfter($e)) {
            $progress = __('Ended :diff days ago', ['diff' => (int)$now->diffInDays($e, absolute: true)]);
        } else {
            $progress = $s->format('M d') . " to " . $e->format('M d'); // E.g. Jan 21 to Feb 08
        }
        return $progress;
    }

    private function getLastUpdated()
    {
        $result = (new BreakoutQueryBuilder($this->dataSource->name))
            ->get("SELECT modified_time FROM cspro_jobs ORDER BY modified_time DESC LIMIT 1")
            ->first();
        return is_null($result) ? '-' : Carbon::parse($result->modified_time)->locale(app()->getLocale())->isoFormat('llll');
    }

    public function render()
    {
        return view('chimera::components.summary');
    }
}
