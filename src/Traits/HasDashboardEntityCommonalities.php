<?php

namespace Uneca\Chimera\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Uneca\Chimera\Models\DataSource;

trait HasDashboardEntityCommonalities
{
    protected function permissionName(): Attribute
    {
        return new Attribute(
            get: fn () => str($this->slug)
                ->replace('.', ':')
                ->append($this->permissionSuffix)
                ->toString(),
        );
    }

    public function getDataSource(): DataSource
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
        static::creating(function ($model) {
            $className = Str::of($model->name)->afterLast('/')->kebab();
            if (Str::contains($model->name, '/')) {
                $path = Str::of($model->name)
                    ->beforeLast('/')
                    ->explode('/')
                    ->map(fn ($x) => Str::of($x)->kebab())
                    ->join('.');
                $model->slug = $path . '.' . $className;
            } else {
                $model->slug = (string)$className;
            }
        });

        static::created(function ($indicator) {
            Permission::create(['guard_name' => 'web', 'name' => $indicator->permission_name]);
        });
        static::deleted(function ($indicator) {
            Permission::whereName($indicator->permission_name)->delete();
        });
    }
}
