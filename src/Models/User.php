<?php

namespace Uneca\Chimera\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

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
        $areaRestriction = $this->areaRestrictions()->first();
        if ($areaRestriction) {
            return $areaRestriction->toFilter();
        } else {
            return [];
        }
    }
}
