<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];
    public $translatable = ['title', 'description'];

    public function indicators()
    {
        return $this->hasMany(Indicator::class);
    }

    public function scopePublished($query)
    {
        return $query->wherePublished(true);
    }

    protected static function booted()
    {
        static::creating(function ($page) {
            $page->slug = Str::slug($page->title);
        });
    }
}
