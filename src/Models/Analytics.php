<?php

namespace Uneca\Chimera\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Analytics extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['started_at' => 'datetime'];
    public $timestamps = false;

    public function analyzable()
    {
        return $this->morphTo();
    }

    protected function startedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::createFromTimestamp($value),
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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

    /*protected static function booted(): void
    {
        static::creating(function (Analytics $analytics) {
            $queryTimeInSeconds = Carbon::createFromTimestamp($analytics->completed_at)->diffInSeconds($analytics->started_at);
            if ($queryTimeInSeconds < config('chimera.long_query_time')) {
                return false;
            }
            return true;
        });
    }*/
}
