<?php

namespace Uneca\Chimera\Components;

use Uneca\Chimera\Models\Questionnaire;
use Uneca\Chimera\Services\QueryBuilder;
use Carbon\Carbon;
use Illuminate\View\Component;

class Summary extends Component
{
    public Questionnaire $questionnaire;
    public string $title;
    public array $dates = [];
    public string $lastUpdated;

    public function __construct(Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;
        $this->dates['start'] = $questionnaire->start_date;
        $this->dates['end'] = $questionnaire->end_date;
        $this->dates['progress'] = $this->makeProgressStatement($this->dates['start'], $this->dates['end'], now());
        $this->lastUpdated = $this->getLastUpdated();
        $this->title = $questionnaire->title;
        $this->color = 'text-black';
    }

    private function makeProgressStatement(Carbon $s, Carbon $e, Carbon $now)
    {
        if ($now->isBefore($s)) {
            $progress = __(':diff days ago', ['diff' => $now->diffInDays($s)]);
        } elseif ($now->isBetween($s, $e)) {
            $progress = __('Day :sofar of :total', ['sofar' => $now->diffInDays($s) + 1, 'total' => $s->diffInDays($e) + 1]);
        } elseif ($now->isAfter($e)) {
            $progress = __('Ended :diff days ago', ['diff' => $now->diffInDays($e)]);
        } else {
            // Jan 21 to Jan 30
            $progress = $s->format('M d') . " to " . $e->format('M d');
        }
        return $progress;
    }

    private function getLastUpdated()
    {
        $result = (new QueryBuilder($this->questionnaire->name))
            ->get("SELECT modified_time FROM cspro_jobs ORDER BY modified_time DESC LIMIT 1")
            ->first();
        return is_null($result) ? '-' : Carbon::parse($result->modified_time)->locale(app()->getLocale())->isoFormat('llll');
    }

    public function render()
    {
        return view('chimera::components.summary');
    }
}
