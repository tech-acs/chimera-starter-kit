<?php

namespace Uneca\Chimera\Models;

use Spatie\Permission\Traits\HasRoles;
use Uneca\Chimera\Services\AreaTree;

class User extends \App\Models\User
{
    use HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'is_suspended'
    ];

    public function usageStats()
    {
        return $this->hasMany(UsageStat::class);
    }

    public function areaRestrictions()
    {
        return $this->hasMany(AreaRestriction::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function indicatorAnalytics()
    {
        return $this->hasMany(Analytics::class);
    }

    public function reports()
    {
        return $this->belongsToMany(Report::class)->withTimestamps();
    }

    public function areaRestrictionAsFilter()
    {
        $areaTree = new AreaTree();
        return $this->areaRestrictions->mapWithKeys(function ($areaRestriction) use ($areaTree) {
            return [$areaTree->hierarchies[$areaRestriction->level] => $areaRestriction->path];
        })->all();
    }

    public function areaRestrictionAsString()
    {
        $areaTree = new AreaTree();
        $restrictionAsString = $this->areaRestrictions->mapWithKeys(function ($areaRestriction) use ($areaTree) {
            $area = $areaTree->getArea($areaRestriction->path);
            return [$areaTree->hierarchies[$areaRestriction->level] => $area?->name];
        })->mapWithKeys(fn ($areaName, $levelName) => ["$areaName $levelName"])->join(', ');
        return empty($restrictionAsString) ? "No restriction (national)" : $restrictionAsString;
    }
}
