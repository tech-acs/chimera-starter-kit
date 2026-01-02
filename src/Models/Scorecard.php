<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Translatable\HasTranslations;
use Uneca\Chimera\Traits\HasDashboardEntityCommonalities;

class Scorecard extends Model
{
    use HasFactory;
    use HasTranslations;
    use HasDashboardEntityCommonalities;

    protected $guarded = ['id'];
    public $translatable = ['title'];
    public $permissionSuffix = ':scorecard';

    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'analyzable')->orderBy('started_at');
    }
}
