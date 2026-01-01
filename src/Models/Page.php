<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Translatable\HasTranslations;
use Uneca\Chimera\Enums\PageableTypes;

class Page extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];
    public $translatable = ['title', 'description'];

    protected $casts = ['for' => PageableTypes::class];

    public function indicators(): MorphToMany
    {
        return $this->morphedByMany(Indicator::class, 'pageable');
    }

    public function reports(): MorphToMany
    {
        return $this->morphedByMany(Report::class, 'pageable');
    }

    public function mapIndicators(): MorphToMany
    {
        return $this->morphedByMany(MapIndicator::class, 'pageable');
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

    public function scopeFor($query, $type)
    {
        return $query->whereFor($type);
    }

    public function scopeIncludeArtefactCount($query)
    {
        $query->addSelect(['artefact_count' => DB::table('pageables')
            ->selectRaw('COUNT(*)')
            ->whereColumn('page_id', 'pages.id')
        ]);
    }

    protected static function booted()
    {
        static::creating(function ($page) {
            $page->slug = Str::slug($page->title) . '-' . strtolower($page->for->name);
        });

        static::created(function ($page) {
            Permission::create(['guard_name' => 'web', 'name' => $page->permission_name]);
        });
        static::deleted(function ($page) {
            Permission::whereName($page->permission_name)->delete();
        });
    }
}
