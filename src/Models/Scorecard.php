<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Translatable\HasTranslations;

class Scorecard extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];
    public $translatable = ['title'];

    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'analyzable')->orderBy('completed_at');
    }

    protected function permissionName(): Attribute
    {
        return new Attribute(
            get: fn () => str($this->slug)
                ->replace('.', ':')
                ->append(':scorecard')
                ->toString(),
        );
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeOfQuestionnaire(Builder $query, $questionnaire)
    {
        return $query->where('questionnaire', $questionnaire);
    }

    protected static function booted()
    {
        static::creating(function ($scorecard) {
            $className = Str::of($scorecard->name)->afterLast('/')->kebab();
            if (Str::contains($scorecard->name, '/')) {
                $path = Str::of($scorecard->name)
                    ->beforeLast('/')
                    ->explode('/')
                    ->map(fn ($x) => Str::of($x)->kebab())
                    ->join('.');
                $scorecard->slug = $path . '.' . $className;
            } else {
                $scorecard->slug = (string)$className;
            }
        });

        static::created(function ($scorecard) {
            Permission::create(['guard_name' => 'web', 'name' => $scorecard->permission_name]);
        });
        static::deleted(function ($scorecard) {
            Permission::whereName($scorecard->permission_name)->delete();
        });
    }
}
