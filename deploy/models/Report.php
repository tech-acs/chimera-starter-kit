<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Translatable\HasTranslations;

class Report extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];
    public $translatable = ['title', 'description'];

    protected $dates = ['last_generated_at'];
    protected $appends = ['permission_name'];

    protected function permissionName(): Attribute
    {
        return new Attribute(
            get: fn () => str($this->slug)
                ->replace('.', ':')
                ->append(':report')
                ->toString(),
        );
    }

    protected function blueprintInstance(): Attribute
    {
        return new Attribute(
            get: function () {
                $blueprintClass = "App\Reports\\" . str($this->name)->replace('/', '\\');
                return new $blueprintClass($this);
            },
        );
    }

    public function getQuestionnaire()
    {
        return Questionnaire::where('name', $this->questionnaire)->first();
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeDueThisHour($query)
    {
        return $query->whereTime('schedule', now()->format('H:00:00'));
    }

    protected static function booted()
    {
        static::creating(function ($report) {
            $className = Str::of($report->name)->afterLast('/')->kebab();
            if (Str::contains($report->name, '/')) {
                $path = Str::of($report->name)
                    ->beforeLast('/')
                    ->explode('/')
                    ->map(fn ($x) => Str::of($x)->kebab())
                    ->join('.');
                $report->slug = $path . '.' . $className;
            } else {
                $report->slug = (string)$className;
            }
        });

        static::created(function ($report) {
            Permission::create(['guard_name' => 'web', 'name' => $report->permission_name]);
        });
        static::deleted(function ($report) {
            Permission::whereName($report->permission_name)->delete();
        });
    }
}
