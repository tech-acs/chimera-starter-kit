<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
//use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    /*public function defaultProfilePhotoUrl()
    {
        $avatar = new InitialAvatar();
        return $avatar->name($this->name)
            ->size(96) // 48 * 2
            ->background('#EBF4FF')
            ->color('#7F9CF5')
            ->generate()
            ->encode('data-url');
    }*/

    public function usageStats()
    {
        return $this->hasMany(UsageStat::class);
    }

    public function areaRestrictions()
    {
        return $this->hasMany(AreaRestriction::class);
    }

    public function areaFilter($connection)
    {
        $areaRestriction = $this->areaRestrictions()->where('connection', $connection)->first();
        if ($areaRestriction) {
            return $areaRestriction->toFilter();
        } else {
            return [];
        }
    }
}
