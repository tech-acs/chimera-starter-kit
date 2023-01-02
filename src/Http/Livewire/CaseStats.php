<?php

namespace Uneca\Chimera\Http\Livewire;

use Livewire\Component;
use Uneca\Chimera\Models\Questionnaire;
use Uneca\Chimera\Services\BreakoutQueryBuilder;

class CaseStats extends Component
{
    public $questionnaire;
    public $stats = [];

    public function mount(Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;
        //$this->stats = $this->getData([]);
    }

    public function setStats()
    {
        $this->stats = $this->getData([]);
    }

    public function getData(array $filter)
    {
        //sleep(3);
        $l = (new BreakoutQueryBuilder($this->questionnaire->name, false))
            ->select([
                "COUNT(*) AS total",
                "SUM(CASE WHEN cases.partial_save_mode IS NULL THEN 1 ELSE 0 END) AS complete",
                "SUM(CASE WHEN cases.partial_save_mode IS NULL THEN 0 ELSE 1 END) AS partial",
                "COUNT(*) - COUNT(DISTINCT `key`) AS duplicate"
            ])
            ->from([])
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
