<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Translatable\HasTranslations;
use Uneca\Chimera\Enums\ScorecardScope;

class DataSource extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'password' => 'encrypted',
    ];
    public $translatable = ['title'];

    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'analyzable')->orderBy('started_at');
    }

    public function getScorecardsAttribute()
    {
        return Scorecard::published()
            ->scope(ScorecardScope::Dashboard)
            ->where('data_source', $this->name)
            ->orderBy('rank')
            ->get()
            ->filter(function ($scorecard) {
                return Gate::allows($scorecard->permission_name);
            });
    }

    public function getAreaInsightsScorecardsAttribute()
    {
        return Scorecard::published()
            ->scope(ScorecardScope::AreaInsights)
            ->where('data_source', $this->name)
            ->orderBy('rank')
            ->get()
            ->filter(function ($scorecard) {
                return Gate::allows($scorecard->permission_name);
            });
    }

    public function getGaugesAttribute()
    {
        return Gauge::published()
            ->where('data_source', $this->name)
            ->orderBy('rank')
            ->get()
            ->filter(function ($gauge) {
                return Gate::allows($gauge->permission_name);
            });
    }

    public function getFeaturedIndicatorsAttribute()
    {
        return Indicator::where('data_source', $this->name)
            ->whereNotNull('featured_at')
            ->orderBy('featured_at', 'DESC')
            ->take(config('chimera.featured_indicators_per_data_source'))
            ->get();
    }

    public function scopeActive($query)
    {
        return $query->where('connection_active', true);
    }

    public function scopeShowOnHomePage($query)
    {
        return $query->where('show_on_home_page', true);
    }

    private function testCanConnect()
    {
        try {
            DB::connection($this->name)->getPdo();
            return ['passes' => true, 'message' => ''];
        } catch (\Exception $exception) {
            return ['passes' => false, 'message' => $exception->getMessage()];
        }
    }

    public function test(): Collection
    {
        $result = collect([]);
        $result->add($this->testCanConnect());
        return $result;
    }
}
