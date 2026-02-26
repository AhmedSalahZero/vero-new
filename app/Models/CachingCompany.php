<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CachingCompany extends Model
{
    protected $guarded =  ['id'] ; 
    public $timestamps = false ; 
    protected $table = 'caching_company';
    
}
