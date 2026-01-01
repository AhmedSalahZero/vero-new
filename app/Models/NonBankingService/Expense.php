<?php

namespace App\Models\NonBankingService;

use App\Helpers\HStr;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Expense extends Model
{
    use BelongsToStudy,BelongsToCompany;
    protected $guarded = ['id'];
    protected $connection ='non_banking_service';
    protected $casts = [
        'monthly_repeating_amounts'=>'array',
        'expense_as_percentages'=>'array',
        'payload'=>'array',
        'custom_collection_policy'=>'array',
        'revenue_stream_type'=>'array',
        'stream_category_ids'=>'array',
        'total_vat'=>'array',
        'total_after_vat'=>'array',
        'payment_amounts'=>'array',
        'collection_statements'=>'array',
        'net_payments_after_withhold'=>'array',
        'withhold_payments'=>'array',
        'withhold_amounts'=>'array',
		'position_ids'=>'array',
		'increase_rates'=>'array',
		'withhold_statements'=>'array',
    ];
        
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    public function model()
    {
        $modelName = '\App\Models\\'.$this->model_name ;
        return $this->belongsTo($modelName, 'model_id', 'id');
        
    }
    // public function getName()
    // {
    // 	return $this->name ;
    // }
    public function getExpenseNameId()
    {
        return $this->expense_name_id ;
    }
    public function getCategoryName()
    {
        return $this->category_name ;
    }
    public function getStartDateAsIndex()
    {
        return $this->start_date;
    }
    public function getStartDateFormatted()
    {
        return app('dateIndexWithDate')[$this->start_date];
    }
    public function getStartDateYearAndMonth()
    {
        $studyStartDate = $this->getStartDateFormatted() ;
        if (is_null($studyStartDate)) {
            return now()->format('Y-m');
        }
        return Carbon::make($studyStartDate)->format('Y-m');
    }
    public function getEndDateAsIndex()
    {
        return $this->end_date;
    }
    public function getEndDateFormatted()
    {
        return !is_null($this->end_date) ? app('dateIndexWithDate')[$this->end_date] : null;
    }
    public function getEndDateYearAndMonth()
    {
        $date = $this->getEndDateFormatted() ;
        if (is_null($date)) {
            return now()->format('Y-m');
        }
        return Carbon::make($date)->format('Y-m');
    }
    public function getMonthlyAmount()
    {
        return $this->monthly_amount ?: 0 ;
    }
    public function getPaymentTerm()
    {
        return $this->payment_terms ;
    }
    public function getVatRate()
    {
        return $this->vat_rate ?: 0;
    }
    public function getWithholdTaxRate()
    {
        return $this->withhold_tax_rate?:0;
    }
    public function getIncreaseRateAtYearIndex($yearIndex)
    {
        return $this->increase_rates[$yearIndex-1] ?? 0;
    }
    public function getIncreaseInterval()
    {
        return $this->increase_interval ;
    }
    public function getPayloadAtDate(string $date)
    {
        
        return $this->payload[$date] ?? 0 ;
    }

    public function getRevenueStreamTypes():array
    {
        return (array)$this->revenue_stream_type ;
    }
    public function getMonthlyPercentage()
    {
        return $this->monthly_percentage ?:0;
    }
    public function getMonthlyCostOfUnit()
    {
        return $this->monthly_cost_of_unit ?:0;
    }
    public function getDepartment()
    {
        // this must be multiple
        return '';
    }
    public function getEmployee()
    {
        // this must be multiple
        return '';
    }
    public function getInterval()
    {
        return $this->interval ;
    }
    public function getAllocationBaseOne()
    {
        return $this->allocation_base_1 ;
    }
    public function getAllocationBaseTwo()
    {
        return $this->allocation_base_2;
    }

    public function getAllocationBaseThree()
    {
        return $this->allocation_base_3;
    }
    public function getConditionalTo()
    {
        return $this->conditional_to ;
    }
    public function getConditionalValueA()
    {
        return $this->conditional_value_a ;
    }
    public function getConditionalValueB()
    {
        return $this->conditional_value_b ;
    }
    public function getPaymentRate(int $rateIndex)
    {
        return array_values($this->custom_collection_policy ?? [])[$rateIndex] ?? 0 ;
    }
    public function getPaymentRateAtDueInDays($rateIndex)
    {
        return array_keys($this->custom_collection_policy ?? [])[$rateIndex] ?? 0 ;
    }
    public function isDeductible()
    {
        return $this->is_deductible;
    }
    public function getAmount()
    {
        return $this->amount ?: 0 ;
    }
    public function getExpenseCategory()
    {
        return $this->expense_category ;
    }
    public function getPercentageOf()
    {
        return $this->percentage_of;
    }
    public function getStreamCategoryIds():array
    {
        return (array)$this->stream_category_ids;
    }
    // public function position()
    // {
    //     return $this->belongsTo(Position::class, 'position_id', 'id');
    // }
    public function getPositionIds():array
    {
		return $this->position_ids ?: [];
    }
	public function getDepartmentIds():array 
	{
		$departmentIds = [];
		foreach($this->getPositionIds() as $positionId){
			$position = Position::find($positionId);
			if($position && $position->department){
				$departmentId = $position->department->id ;
				$departmentIds[$departmentId] = $departmentId;
			}
		}
		return array_values($departmentIds);
	}
    public function getAmortizationMonths():int
    {
        return $this->amortization_months?:12;
    }
    public static function getExpensePerContract(array $revenueStreamType, array $categoryIds, int $studyId, string $columnName , $debug= false ):array
    {
        $selectedRevenueStreamTypes = [];
        $hasLeasing = in_array('has_leasing', $revenueStreamType) ;
        $hasIjara = in_array('has_ijara_mortgage', $revenueStreamType) ;
        $hasReverseFactoring = in_array('has_reverse_factoring', $revenueStreamType) ;
        $hasPortfolioMortgage = in_array('has_portfolio_mortgage', $revenueStreamType) ;
        $hasDirectFactoring = in_array('has_direct_factoring', $revenueStreamType) ;
        $hasMicrofinance = in_array('has_micro_finance', $revenueStreamType) ;
        $hasConsumerfinance = in_array('has_consumer_finance', $revenueStreamType) ;
        
   //     $revenueStreamTypesWheres = [];
		
        if ($hasLeasing) {
            $selectedRevenueStreamTypes[] = Study::LEASING;
        }
        if ($hasIjara) {
            $selectedRevenueStreamTypes[] = Study::IJARA;
        }
        if ($hasReverseFactoring) {
            $selectedRevenueStreamTypes[] = Study::REVERSE_FACTORING;
        }
        if ($hasPortfolioMortgage) {
            $selectedRevenueStreamTypes[] = Study::PORTFOLIO_MORTGAGE;
        }
        if ($hasDirectFactoring) {
            $selectedRevenueStreamTypes[] = Study::DIRECT_FACTORING;
        }  if ($hasMicrofinance) {
            $selectedRevenueStreamTypes[] = Study::MICROFINANCE;
        }if ($hasConsumerfinance) {
            $selectedRevenueStreamTypes[] = Study::CONSUMER_FINANCE;
        }
		if(!count($selectedRevenueStreamTypes)){
			return [
				'result'=>[],
				'selectedRevenueStreamTypes'=>[]
			];
		}
	        $resultArr = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('revenue_contracts')
            ->where('study_id', $studyId)
            ->when(count($categoryIds), function (Builder $builder) use ($categoryIds) {
                $builder->whereIn('category_id', $categoryIds);
            })
            ->whereIn('revenue_type',$selectedRevenueStreamTypes)->pluck($columnName)->map(function ($item) {
                return (array)json_decode($item);
            })->toArray();
		
        return [
            'result'=>$resultArr ,
            'selectedRevenueStreamTypes'=>$selectedRevenueStreamTypes
        ];
    }
	public function getStartDateType()
	{
		return $this->start_date_type;
	}
	public static function getColumnMapping():array 
	{
		return [
            'one_time_expense'=>'payload',
            'percentage_of_sales'=>'total_after_vat',
            'fixed_monthly_repeating_amount'=>'monthly_repeating_amounts',
            'cost_per_unit'=>'monthly_repeating_amounts',
            'expense_per_employee'=>'monthly_repeating_amounts',
        ];
	}
	public function getMicrofinanceAllocation()
	{
		return $this->microfinance_allocation;
	}
		public function getDueDays():array 
	{
		$collections = (array)$this->custom_collection_policy;
		$result = [];
		foreach($collections as $dueDay => $rate){
			$result[] = $dueDay;
		}
		return $result;
	}
	public function getRates():array 
	{
		$collections = (array)$this->custom_collection_policy;
		$result = [];
		foreach($collections as $dueDay => $rate){
			$result[] = $rate;
		}
		return $result;
	}
	public function getStartDateAsString(Study $study)
	{
		return $study->getDateFromDateIndex($this->getStartDateAsIndex());
	}
	public function getEndDateAsString(Study $study)
	{
		return $study->getDateFromDateIndex($this->getEndDateAsIndex());
	}
	public static function generateRow($expense,Study $study,bool $isOneTimeExpense , string $expenseType,array $revenueCategoriesPerRevenue,array $expenseNamesPerCategories , array $positionPerDepartments)
	{
		 /**
		  * @var Expense $expense
		  */
		return [
			'fixed_monthly_repeating_amount'=>[
				 'id'=>$expense ? $expense->id : 0,
                    'expense_category'=>$expense ? $expense->expense_category : '',
                    'expense_name_id'=>$expense ? $expense->expense_name_id : '',
					'filteredExpenseNamesOptions'=>$expense ? ($expenseNamesPerCategories[$expense->expense_category]??[])  : [],
					'amount'=>$expense? $expense->amount:null,//null is important as default // for monthly repeating and one time expense only
					'increase_rates'=>$expense? $expense->increase_rates:0, // for monthly repeating only
					'start_date'=>$expense ? formatDateForVueDatePicker($expense->getStartDateAsString($study)) : formatDateForVueDatePicker($study->getStudyStartDate()),
					 'end_date'=>$expense && !$isOneTimeExpense ? formatDateForVueDatePicker($expense->getEndDateAsString($study)) : formatDateForVueDatePicker($study->getEndDate()),
					 'vat_rate'=>$expense? $expense->vat_rate:0, // for monthly repeating only
					'withhold_tax_rate'=>$expense? $expense->withhold_tax_rate:0, // for monthly repeating only
					 'payment_terms'=>$expense ? $expense->payment_terms : 'cash',
                    'due_days'=>$expense ? $expense->getDueDays() : [],
                    'payment_rate'=>$expense ? $expense->getRates() : [],
					
			],
			'percentage_of_sales'=>[
					 'id'=>$expense ? $expense->id : 0,
                    'expense_category'=>$expense ? $expense->expense_category : '',
                    'expense_name_id'=>$expense ? $expense->expense_name_id : '',
					'filteredExpenseNamesOptions'=>$expense ? ($expenseNamesPerCategories[$expense->expense_category]??[])  : [],
					'percentage_of'=>$expense? $expense->percentage_of:null,
					'revenue_stream_type'=>$expense? $expense->revenue_stream_type:[],
					'stream_category_ids'=>$expense? $expense->stream_category_ids:[], 
					'filteredRevenueCategoriesOptions'=>$expense  ?  getOnlyFilterOptions($revenueCategoriesPerRevenue , $expense->revenue_stream_type?:[] ) : [],
					'monthly_percentage'=>$expense? $expense->monthly_percentage:0, 
					'start_date'=>$expense ? formatDateForVueDatePicker($expense->getStartDateAsString($study)) : formatDateForVueDatePicker($study->getStudyStartDate()),
					 'end_date'=>$expense && !$isOneTimeExpense ? formatDateForVueDatePicker($expense->getEndDateAsString($study)) : formatDateForVueDatePicker($study->getEndDate()),
					 'vat_rate'=>$expense? $expense->vat_rate:0, // for monthly repeating only
					'withhold_tax_rate'=>$expense? $expense->withhold_tax_rate:0, // for monthly repeating only
					 'payment_terms'=>$expense ? $expense->payment_terms : 'cash',
                    'due_days'=>$expense ? $expense->getDueDays() : [],
                    'payment_rate'=>$expense ? $expense->getRates() : [],
					
			],
			'cost_per_unit'=>[
					 'id'=>$expense ? $expense->id : 0,
                    'expense_category'=>$expense ? $expense->expense_category : '',
                    'expense_name_id'=>$expense ? $expense->expense_name_id : '',
					'filteredExpenseNamesOptions'=>$expense ? ($expenseNamesPerCategories[$expense->expense_category]??[])  : [],
					'revenue_stream_type'=>$expense? $expense->revenue_stream_type:[],
					'stream_category_ids'=>$expense? $expense->stream_category_ids:[], 
					'filteredRevenueCategoriesOptions'=>$expense  ?  getOnlyFilterOptions($revenueCategoriesPerRevenue , $expense->revenue_stream_type?:[] ) : [],
					'monthly_cost_of_unit'=>$expense? $expense->monthly_cost_of_unit:null, 
					'start_date'=>$expense ? formatDateForVueDatePicker($expense->getStartDateAsString($study)) : formatDateForVueDatePicker($study->getStudyStartDate()),
					 'end_date'=>$expense && !$isOneTimeExpense ? formatDateForVueDatePicker($expense->getEndDateAsString($study)) : formatDateForVueDatePicker($study->getEndDate()),
					 'vat_rate'=>$expense? $expense->vat_rate:0, // for monthly repeating only
					'withhold_tax_rate'=>$expense? $expense->withhold_tax_rate:0, // for monthly repeating only
					 'payment_terms'=>$expense ? $expense->payment_terms : 'cash',
                    'due_days'=>$expense ? $expense->getDueDays() : [],
                    'payment_rate'=>$expense ? $expense->getRates() : [],
					
			],
			'one_time_expense'=>[
					 'id'=>$expense ? $expense->id : 0,
                    'expense_category'=>$expense ? $expense->expense_category : '',
                    'expense_name_id'=>$expense ? $expense->expense_name_id : '',
					'filteredExpenseNamesOptions'=>$expense ? ($expenseNamesPerCategories[$expense->expense_category]??[])  : [],
					'amount'=>$expense? $expense->amount:null, 
					'amortization_months'=>$expense? $expense->amortization_months:12, 
					'start_date'=>$expense ? formatDateForVueDatePicker($expense->getStartDateAsString($study)) : formatDateForVueDatePicker($study->getStudyStartDate()),
					 'vat_rate'=>$expense? $expense->vat_rate:0, // for monthly repeating only
					'withhold_tax_rate'=>$expense? $expense->withhold_tax_rate:0, // for monthly repeating only
					 'payment_terms'=>$expense ? $expense->payment_terms : 'cash',
                    'due_days'=>$expense ? $expense->getDueDays() : [],
                    'payment_rate'=>$expense ? $expense->getRates() : [],
			],
			'expense_per_employee'=>[
					 'id'=>$expense ? $expense->id : 0,
                    'expense_category'=>$expense ? $expense->expense_category : '',
                    'expense_name_id'=>$expense ? $expense->expense_name_id : '',
					'filteredExpenseNamesOptions'=>$expense ? ($expenseNamesPerCategories[$expense->expense_category]??[])  : [],
					'department_ids'=>$positionIds = $expense? $expense->getDepartmentIds():[],
					'position_ids'=>$expense? $expense->position_ids:[], 
					'filteredPositionsOptions'=>$expense  ?  getOnlyFilterOptions($positionPerDepartments , $positionIds ) : [],
					'monthly_cost_of_unit'=>$expense? $expense->monthly_cost_of_unit:null, 
					'start_date'=>$expense ? formatDateForVueDatePicker($expense->getStartDateAsString($study)) : formatDateForVueDatePicker($study->getStudyStartDate()),
					 'end_date'=>$expense && !$isOneTimeExpense ? formatDateForVueDatePicker($expense->getEndDateAsString($study)) : formatDateForVueDatePicker($study->getEndDate()),
					 'vat_rate'=>$expense? $expense->vat_rate:0, // for monthly repeating only
					'withhold_tax_rate'=>$expense? $expense->withhold_tax_rate:0, // for monthly repeating only
					 'payment_terms'=>$expense ? $expense->payment_terms : 'cash',
                    'due_days'=>$expense ? $expense->getDueDays() : [],
                    'payment_rate'=>$expense ? $expense->getRates() : [],
					
			],
			][$expenseType];
	
	}
}
