<?php

namespace Uneca\Chimera\Components;

use Illuminate\View\Component;
use Uneca\Chimera\Services\QueryBuilder;

class CaseStats extends Component
{
    public $questionnaire;
    public $stats;

    public function getCollection(array $filter)
    {
        $l = (new QueryBuilder($this->questionnaire->name, false))
            ->select([
                "COUNT(*) AS total",
                "SUM(CASE WHEN cases.partial_save_mode IS NULL THEN 1 ELSE 0 END) AS complete",
                "SUM(CASE WHEN cases.partial_save_mode IS NULL THEN 0 ELSE 1 END) AS partial",
                "COUNT(*) - COUNT(DISTINCT `key`) AS duplicate"
            ])
            ->from([])
            ->get()
            ->first();
        $info = [
            'total' => 'NA', 'complete' => 'NA', 'partial' => 'NA', 'duplicate' => 'NA'
        ];
        if (!is_null($l)) {
            $info['total'] = number_format($l->total, 0, '', ',');
            $info['complete'] = number_format($l->complete, 0, '', ',');
            $info['partial'] = number_format($l->partial, 0, '', ',');
            $info['duplicate'] = number_format($l->duplicate, 0, '', ',');
        }
        return $info;
    }

    public function __construct($questionnaire)
    {
        $this->questionnaire = $questionnaire;
        $this->stats = $this->getCollection([]);
    }

    public function render()
    {
        return view('chimera::components.case-stats');
    }
}
