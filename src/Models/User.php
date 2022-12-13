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

    public function areaFilter()
    {
        $areaTree = new AreaTree(removeLastNLevels: 1);
        return $this->areaRestrictions->mapWithKeys(function ($areaRestriction) use ($areaTree) {
            return [$areaTree->hierarchies[$areaRestriction->level] => $areaRestriction->path];
        })->all();
    }
}
