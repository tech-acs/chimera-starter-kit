<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Translatable\HasTranslations;

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
        return $this->morphMany(Analytics::class, 'analyzable')->orderBy('completed_at');
    }

    public function getScorecardsAttribute()
    {
        return Scorecard::published()
            ->where('data_source', $this->name)
            ->orderBy('rank')
            ->get()
            ->filter(function ($scorecard) {
                return Gate::allows($scorecard->permission_name);
            });
    }

    public function getFeaturedIndicatorsAttribute()
    {
        return Indicator::where('data_source', $this->name)
            ->where('is_featured', true)
            ->orderBy('updated_at')
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

    public function test()
    {
        $result = collect([]);
        $result->add($this->testCanConnect());
        return $result;
    }
}
