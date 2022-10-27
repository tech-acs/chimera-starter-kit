<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Translatable\HasTranslations;

class Indicator extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];
    public $translatable = ['title', 'description', 'help'];

    public function pages()
    {
        return $this->belongsToMany(Page::class)
            ->withPivot('rank');
    }

    protected function permissionName(): Attribute
    {
        return new Attribute(
            get: fn () => str($this->slug)->replace('.', ':')->toString(),
        );
    }

    protected function component(): Attribute
    {
        return new Attribute(
            get: fn () => $this->slug,
        );
    }

    public function getQuestionnaire()
    {
        return Questionnaire::where('name', $this->questionnaire)->first();
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
    }
}
