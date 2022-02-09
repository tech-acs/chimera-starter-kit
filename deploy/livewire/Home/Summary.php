<?php

namespace App\Http\Livewire\Home;

use App\Services\IndicatorFactory;
use App\Services\QueryBuilder;
use Carbon\Carbon;
use Livewire\Component;

class Summary extends Component
{
    public string $connection;
    public array $subIndicators;
    public array $caseStats = [];
    public array $selectedIndicators = [];
    public string $title;
    public array $dates = [];
    public string $lastUpdated;
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
    ];

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
        $sql = "SELECT modified_time FROM cspro_jobs ORDER BY modified_time DESC LIMIT 1";
        $result = (new QueryBuilder($this->connection))->get($sql)->first();
        return is_null($result) ? '-' : Carbon::parse($result->modified_time)->format('D M d, Y g:i A');
    }

    public function mount()
    {
        $metadata = config("chimera.dictionaries.{$this->connection}");
        $this->colors = config("chimera.dictionaries.{$this->connection}.colors", $this->colors);

        $this->dates['start'] = Carbon::parse($metadata['start_date']);
        $this->dates['end'] = Carbon::parse($metadata['end_date'] . '23:59:59');
        $this->dates['progress'] = $this->makeProgressStatement($this->dates['start'], $this->dates['end'], now());
        $this->lastUpdated = $this->getLastUpdated();
        $this->title = $metadata['title'] ?? 'Title';
        $this->color = $metadata['color'] ?? 'text-black';

        foreach ($this->subIndicators as $metadata) {
            $indicatorInstance = IndicatorFactory::make($metadata['connection'], $metadata['indicator']);
            if (($metadata['type'] ?? null) === 'case-stats') {
                $this->caseStats = $indicatorInstance->getData([]);
            } else {
                $this->selectedIndicators[$metadata['label'] ?? $metadata['title']] = $indicatorInstance->getData([]);
            }
        }
    }

    public function render()
    {
        return view('livewire.summary');
    }
}
