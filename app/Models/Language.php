<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StaticBoot;

class Language extends Model
{
    use SoftDeletes,StaticBoot;
    public $table = "languages";
    protected $guarded = [];
}
