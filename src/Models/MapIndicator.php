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

class MapIndicator extends Model
{
    use HasFactory;
    use HasTranslations;
    use HasDashboardEntityCommonalities;

    protected $guarded = ['id'];
    public $translatable = ['title', 'description'];
    public $permissionSuffix = ':map-indicator';

    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'analyzable')->orderBy('completed_at');
    }

    protected function fullyQualifiedClassname(): Attribute
    {
        return new Attribute(
            get: fn () => "App\\MapIndicators\\" . str($this->name)->replace('/', '\\')->toString(),
        );
    }

    /*protected function permissionName(): Attribute
    {
        return new Attribute(
            get: fn () => str($this->slug)
                ->replace('.', ':')
                ->append(':map-indicator')
                ->toString(),
        );
    }

    public function getDataSource()
    {
        return DataSource::where('name', $this->data_source)->first();
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeOfDataSource(Builder $query, string $dataSource)
    {
        return $query->where('data_source', $dataSource);
    }

    protected static function booted()
    {
        static::creating(function ($page) {
            $className = Str::of($page->name)->afterLast('/')->kebab();
            if (Str::contains($page->name, '/')) {
                $path = Str::of($page->name)
                    ->beforeLast('/')
                    ->explode('/')
                    ->map(fn ($x) => Str::of($x)->kebab())
                    ->join('.');
                $page->slug = $path . '.' . $className;
            } else {
                $page->slug = (string)$className;
            }
        });

        static::created(function ($indicator) {
            Permission::create(['guard_name' => 'web', 'name' => $indicator->permission_name]);
        });
        static::deleted(function ($indicator) {
            Permission::whereName($indicator->permission_name)->delete();
        });
    }*/
}
