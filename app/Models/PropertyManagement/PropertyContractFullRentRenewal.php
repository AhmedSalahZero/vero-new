<?php

namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int $study_id
 * @property int $property_id
 * @property int|null $contract_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\PropertyManagement\Contract|null $contract
 * @property-read \App\Models\PropertyManagement\Property|null $property
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyContractFullRentRenewal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyContractFullRentRenewal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyContractFullRentRenewal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyContractFullRentRenewal whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyContractFullRentRenewal whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyContractFullRentRenewal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyContractFullRentRenewal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyContractFullRentRenewal wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyContractFullRentRenewal whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyContractFullRentRenewal whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PropertyContractFullRentRenewal extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $table ='property_contract_full_rent_renewals';
    protected $connection =PROPERTY_MANAGEMENT_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
    
    ];
    public function property():BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }
    public function study():BelongsTo
    {
        return $this->belongsTo(Study::class, 'study_id', 'id');
    }
    public function contract():BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'id');
    }
  
    public static function getFullRentCoveragesAmounts(Study $study):array
    {
        $propertyContractFullRentCoverages = self::with(['property','contract'])->where('study_id', $study->id)->get();
        foreach (['rent_revenues','rent_collections'] as $columnName) {
			$formattedResult = [];
			$currentStatementReportClass = $columnName == 'rent_revenues' ? IncomeStatementReport::class : CashflowStatementReport::class;
            $incomeStatementColumnName = $columnName == 'rent_revenues' ? 'full_coverage_rent_revenues' : 'full_coverage_rent_collections';
            foreach ($propertyContractFullRentCoverages as $propertyContractFullRentCoverage) {
                $contract = $propertyContractFullRentCoverage->contract;
                if ($contract) {
                    $rowResult = $contract->{$columnName}?:[];
                    foreach ($rowResult as $dateAsString => $val) {
                        $dateAsString = Carbon::make($dateAsString)->format('Y-m-01');
                        $dateAsIndex = $study->getDateIndexFromString($dateAsString);
                        if (is_null($dateAsIndex)) {
                            continue;
                        }
                        $formattedResult[$dateAsIndex] = isset($formattedResult[$dateAsIndex]) ? $formattedResult[$dateAsIndex] + $val : $val;
                    }
                }
            }
			if($currentStatementReportClass == CashflowStatementReport::class){
				$study->storeInCashFlowStatementReport( [$incomeStatementColumnName=> json_encode($formattedResult)]);
			}else{
				$study->storeInIncomeStatementReport( [$incomeStatementColumnName=> json_encode($formattedResult)]);
			}
        }
        
        return $formattedResult;
        
    }
}
