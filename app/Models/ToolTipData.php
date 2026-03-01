<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperToolTipData
 */
class ToolTipData extends Model
{
    
    protected $guarded = [];

    
    protected $casts = [
        'data' => 'array',
    ];
}
