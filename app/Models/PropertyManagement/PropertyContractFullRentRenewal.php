<?php

namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperPropertyContractFullRentRenewal
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
    /**
     * @param Study $study
     * @param string $columnName rent_revenues or rent_collections
     * @param array &$formattedResult
     * @return array
     */
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
