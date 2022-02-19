<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Indicator extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    protected function permissionName(): Attribute
    {
        return new Attribute(
            get: fn () => $this->slug,
        );
    }

    protected function component(): Attribute
    {
        return new Attribute(
            get: fn () => $this->attributes['connection'] . '.' . str()->kebab($this->name),
        );
    }

    protected static function booted()
    {
        static::creating(function ($page) {
            $className = Str::of($page->name)->afterLast('/')->kebab();
            $page->slug = Str::of($page->name)->beforeLast('/')->append('.', $className);
        });
    }
}
