<?php

namespace App\Models\Trading;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperGovernorate
 */
class Governorate extends Model
{
    use HasFactory;
    
    protected $connection =TRADING_CONNECTION_NAME;
    
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
