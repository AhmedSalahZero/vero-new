<?php

namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyMarketValue extends Model
{
    use HasFactory, CompanyScope;

    protected $connection = 'property_management';

    protected $fillable = [
        'property_id',
        'value',
        'date',
        'company_id',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Get the property that owns the market value.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
	public function setDateAttribute($value)
	{
		$this->attributes['date'] = $value ? formatDateFromMonthPicker($value) : null;
	}
	public function getDateFormattedForVueDatePicker():array
	{
		return $this->date ? formatDateForVueDatePicker($this->date) : [];
	}
	public function getValue():float
	{
		return $this->value ?: 0;
	}
	
}
