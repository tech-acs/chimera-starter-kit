<?php

namespace Uneca\Chimera\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class IndicatorAnalytics extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function indicator()
    {
        return $this->belongsTo(Indicator::class);
    }

    public function queryTime(): string
    {
        return Carbon::createFromTimestamp($this->completed_at)
            ->diffForHumans(
                Carbon::createFromTimestamp($this->started_at),
                CarbonInterface::DIFF_ABSOLUTE,
                true,
                3
            );
    }
}
