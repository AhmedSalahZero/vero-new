<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $governorate_id
 * @property string $name_en
 * @property string $name_ar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PropertyManagement\Governorate $governorate
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\City newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\City newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\City query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\City whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\City whereGovernorateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\City whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\City whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\City whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\City whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class City extends Model
{
    use HasFactory;
    
    protected $connection = PROPERTY_MANAGEMENT_CONNECTION_NAME;
    
    protected $fillable = [
        'governorate_id',
        'name_en',
        'name_ar',
        'company_id',
    ];
    
    /**
     * Get localized name
     */
    public function getName(): string
    {
        $locale = app()->getLocale();
        $nameKey = 'name_' . $locale;
        
        return $this[$nameKey] ?? $this['name_en'] ?? '';
    }
    
    /**
     * Get the governorate this city belongs to
     */
    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }
    
    
}
