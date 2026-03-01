<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperCountry
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
