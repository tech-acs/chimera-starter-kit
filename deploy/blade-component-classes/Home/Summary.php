<?php

namespace App\View\Components\Home;

use App\Models\Questionnaire;
use App\Services\QueryBuilder;
use Carbon\Carbon;
use Illuminate\View\Component;

class Summary extends Component
{
    public Questionnaire $questionnaire;
    public string $title;
    public array $dates = [];
    public string $lastUpdated;

    /*public array $caseStats = [];
    public array $colors = [
        'bg-red-500',
        'bg-yellow-500',
        'bg-green-500',
        'bg-blue-500',
        'bg-pink-500',
        'bg-purple-500',
        'bg-red-800',
        'bg-yellow-800',
        'bg-green-800',
        'bg-blue-800',
        'bg-purple-800',
        'bg-pink-800',
    ];*/

    public function __construct(Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;
        $this->dates['start'] = $questionnaire->start_date;
        $this->dates['end'] = $questionnaire->end_date;
        $this->dates['progress'] = $this->makeProgressStatement($this->dates['start'], $this->dates['end'], now());
        $this->lastUpdated = $this->getLastUpdated();
        $this->title = $questionnaire->title;
        $this->color = 'text-black';

        /*$indicatorInstance = IndicatorFactory::make($questionnaire->name, 'case-stats');
        $this->caseStats = $indicatorInstance->getData([]);*/

        /*foreach ($this->subIndicators as $metadata) {
            $indicatorInstance = IndicatorFactory::make($metadata['connection'], $metadata['indicator']);
            if (($metadata['type'] ?? null) === 'case-stats') {
                $this->caseStats = $indicatorInstance->getData([]);
            } else {
                $this->selectedIndicators[$metadata['label'] ?? $metadata['title']] = $indicatorInstance->getData([]);
            }
        }*/
    }

    private function makeProgressStatement(Carbon $s, Carbon $e, Carbon $now)
    {
        if ($now->isBefore($s)) {
            $progress = $now->diffInDays($s) . " days to go";
        } elseif ($now->isBetween($s, $e)) {
            $progress = "Day " . ($now->diffInDays($s) + 1) . " of " . ($s->diffInDays($e) + 1);
        } elseif ($now->isAfter($e)) {
            $progress = "Ended " . $now->diffInDays($e) . " days ago";
        } else {
            $progress = $s->format('M d') . " to " . $e->format('M d');
        }
        return $progress;
    }

    private function getLastUpdated()
    {
        $result = (new QueryBuilder($this->questionnaire->name))
            ->get("SELECT modified_time FROM cspro_jobs ORDER BY modified_time DESC LIMIT 1")
            ->first();
        return is_null($result) ? '-' : Carbon::parse($result->modified_time)->format('D M d, Y g:i A');
    }

    public function render()
    {
        return view('components.home.summary');
    }
}
