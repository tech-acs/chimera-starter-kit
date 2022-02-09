<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function indicators()
    {
        return $this->hasMany(Indicator::class);
    }

    protected static function booted()
    {
        static::creating(function ($page) {
            $page->slug = Str::slug($page->title);
        });
    }
}
