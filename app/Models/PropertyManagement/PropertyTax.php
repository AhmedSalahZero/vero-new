<?php

namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyTax extends Model
{
    use CompanyScope, HasFactory;
    
    protected $connection = 'property_management';
    
    protected $table = 'property_taxes';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'tax_rate' => 'decimal:2',
        'date' => 'date',
    ];
    
    /**
     * Get the property that owns the tax.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
    
    public function getTaxRate(): float
    {
        return $this->tax_rate ?: 0;
    }
    
    public function getDate(): string
    {
        return $this->date ? $this->date->format('Y-m-d') : '';
    }
	public function setDateAttribute($value)
	{
		$this->attributes['date'] = $value ? formatDateFromMonthPicker($value) : null;
	}
	 
	public function getDateFormattedForVueDatePicker():array
	{
		return $this->date ? formatDateForVueDatePicker($this->date) : [];
	}
}
