<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $country_id
 * @property string $name_en
 * @property string $name_ar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyManagement\City> $cities
 * @property-read int|null $cities_count
 * @property-read bool|null $cities_exists
 * @property-read \App\Models\PropertyManagement\Country $country
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Governorate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Governorate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Governorate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Governorate whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Governorate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Governorate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Governorate whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Governorate whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Governorate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Governorate extends Model
{
    use HasFactory;
    
    protected $connection = PROPERTY_MANAGEMENT_CONNECTION_NAME;
    
    protected $fillable = [
        'country_id',
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
     * Get the country this governorate belongs to
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    
    /**
     * Get cities for this governorate
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'governorate_id', 'id')->orderBy('name_en');
    }
    
 
}
