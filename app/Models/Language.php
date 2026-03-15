<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StaticBoot;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int|null $updated_by
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Language withoutTrashed()
 * @mixin \Eloquent
 */
class Language extends Model
{
    use SoftDeletes,StaticBoot;
    public $table = "languages";
    protected $guarded = [];
}
