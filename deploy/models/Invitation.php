<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use HasFactory;
    //use SoftDeletes;

    protected $guarded = [];
    public $timestamps = false;
    protected $casts = [
        'generated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function getIsExpiredAttribute()
    {
        return $this->expires_at->isPast();
    }

    public function getStatusAttribute()
    {
        return $this->expires_at->isPast() ? "Expired" : "Expires in " . $this->expires_at->diffInHours() . ' ' . Str::plural('hour', $this->expires_at->diffInHours());
    }
}
