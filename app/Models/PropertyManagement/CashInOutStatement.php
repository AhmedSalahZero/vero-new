<?php

namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property array<array-key, mixed>|null $monthly_equity_injection
 * @property array<array-key, mixed>|null $monthly_working_capital_injection
 * @property array<array-key, mixed>|null $monthly_cash_and_banks
 * @property int $study_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashInOutStatement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashInOutStatement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashInOutStatement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashInOutStatement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashInOutStatement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashInOutStatement whereMonthlyCashAndBanks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashInOutStatement whereMonthlyEquityInjection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashInOutStatement whereMonthlyWorkingCapitalInjection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashInOutStatement whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\CashInOutStatement whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CashInOutStatement extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =PROPERTY_MANAGEMENT_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
                'monthly_cash_and_banks'=>'array',
                'monthly_working_capital_injection'=>'array',
                'monthly_equity_injection'=>'array',
    ];

}
