<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Translatable\HasTranslations;
use Uneca\Chimera\Traits\HasDashboardEntityCommonalities;

class Report extends Model
{
    use HasFactory;
    use HasTranslations;
    use HasDashboardEntityCommonalities;

    protected $guarded = ['id'];
    public $translatable = ['title', 'description'];
    protected $casts = ['last_generated_at' => 'datetime'];
    protected $appends = ['permission_name'];
    public $permissionSuffix = ':report';

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function schedule(): array
    {
        $runAt = $this->run_at;
        $runEvery = $this->run_every;
        $loop = 24 / $runEvery;
        $schedule = [(int)$runAt];
        for ($i = 1; $i < $loop; $i++){
            array_push($schedule, ((int)$runAt + $i * $runEvery) % 24);
        }
        return collect($schedule)
            ->map(fn ($hour) => str($hour)->padLeft(2, '0') . ':00:00')
            ->all();
    }

    public function scheduleForHumans(): Attribute
    {
        return new Attribute(
            get: function () {
                if (! $this->enabled) {
                    return 'N/A';
                }
                return collect($this->schedule())
                    ->map(fn($time) => str($time)->beforeLast(':'))
                    ->join(', ', ' and ');
            },
        );
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }
}
