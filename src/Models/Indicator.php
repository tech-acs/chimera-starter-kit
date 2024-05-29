<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Translatable\HasTranslations;
use Uneca\Chimera\Traits\HasDashboardEntityCommonalities;

class Indicator extends Model
{
    use HasFactory;
    use HasTranslations;
    use HasDashboardEntityCommonalities;

    protected $guarded = ['id'];
    public $translatable = ['title', 'description', 'help'];
    public $permissionSuffix = ':indicator';
    protected $casts = [
        'data' => 'array',
        'layout' => 'array'
    ];

    public function pages()
    {
        return $this->belongsToMany(Page::class)
            ->withPivot('rank')
            ->withTimestamps();
    }

    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'analyzable')
            ->orderBy('completed_at');
    }

    protected function component(): Attribute
    {
        return new Attribute(
            get: fn () => $this->slug,
        );
    }

    public function scopeOfTag(Builder $query, $tag)
    {
        return $query->where('tag', $tag);
    }

    public function scopeUntagged(Builder $query)
    {
        return $query->where('tag', null);
    }
}
