<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Uneca\Chimera\Traits\HasDashboardEntityCommonalities;
use Uneca\Chimera\Traits\HasLevelDiscrimination;

class Gauge extends Model
{
    use HasDashboardEntityCommonalities;
    use HasFactory;
    use HasLevelDiscrimination;
    use HasTranslations;

    protected $guarded = ['id'];

    public $translatable = ['title', 'subtitle'];

    public $permissionSuffix = ':gauge';

    protected $with = ['inapplicableLevels'];

    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'analyzable')->orderBy('started_at');
    }
}
