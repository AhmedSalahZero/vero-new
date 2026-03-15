<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $model_name
 * @property string $section_name
 * @property string|null $field
 * @property array<array-key, mixed>|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ToolTipData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ToolTipData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ToolTipData query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ToolTipData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ToolTipData whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ToolTipData whereField($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ToolTipData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ToolTipData whereModelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ToolTipData whereSectionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ToolTipData whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ToolTipData extends Model
{
    
    protected $guarded = [];

    
    protected $casts = [
        'data' => 'array',
    ];
}
