<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class AreaHierarchy extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];
    public $translatable = ['name'];
}
