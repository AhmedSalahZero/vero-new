<?php

namespace App\Models\Trading;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperCity
 */
class City extends Model
{
    use HasFactory;
    
    protected $connection =TRADING_CONNECTION_NAME;
    
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
