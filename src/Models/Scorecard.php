<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Scorecard extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];
    public $translatable = ['title'];

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

        /*static::created(function ($indicator) {
            Permission::create(['guard_name' => 'web', 'name' => $indicator->permission_name]);
        });*/
    }
}
