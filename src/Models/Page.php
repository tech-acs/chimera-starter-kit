<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];
    public $translatable = ['title', 'description'];

    public function indicators()
    {
        return $this->belongsToMany(Indicator::class)
            ->withPivot('rank')
            ->orderByPivot('rank')
            ->withTimestamps();
    }

    protected function permissionName(): Attribute
    {
        return new Attribute(
            get: fn () => str($this->slug)->replace('.', ':')->toString(),
        );
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

        static::created(function ($page) {
            Permission::create(['guard_name' => 'web', 'name' => $page->permission_name]);
        });
        static::deleted(function ($page) {
            Permission::whereName($page->permission_name)->delete();
        });
    }
}
