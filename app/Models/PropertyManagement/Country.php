<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name_en
 * @property string $name_ar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyManagement\Governorate> $governorates
 * @property-read int|null $governorates_count
 * @property-read bool|null $governorates_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Country query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Country whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Country whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Country whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Country whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Country whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Country extends Model
{
    use HasFactory;
    
    protected $connection = PROPERTY_MANAGEMENT_CONNECTION_NAME;
    
    protected $fillable = [
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
     * Get governorates for this country
     */
    public function governorates(): HasMany
    {
        return $this->hasMany(Governorate::class, 'country_id', 'id');
    }
    
   
}
