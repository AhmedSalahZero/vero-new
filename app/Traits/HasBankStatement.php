<?php
namespace App\Traits;

use App\Models\CurrentAccountBankStatement;
use App\Models\FinancialInstitutionAccount;
use Carbon\Carbon;


trait HasBankStatement
{
	public function updateBankStatementsFromDate(string $date)
	{
		$isFinancialInstitutionAccount  = new self instanceof FinancialInstitutionAccount ; 
		$orderBy = $isFinancialInstitutionAccount ? 'date asc , id asc' : 'date asc , priority asc , id asc';
		$firstBankStatementToBeUpdated = (self::getBankStatementTableClassName())::where(self::generateForeignKeyFormModelName(),$this->id)
		
		->where('date','>=',$date)
		->orderByRaw($orderBy)
		->first();	
		
		if($firstBankStatementToBeUpdated){
			$firstBankStatementToBeUpdated->update([
				'updated_at'=>now()
			]);
		}
	}
	/**
	 * *  دا محدود بتاريخ بدايه ونهايه وبالتالي مش هيحصل حركات خارجهم
	 */
	public function handleEndOfMonthInterestForContractStatements(string $contractStartDate , string $contractEndDate , int $companyId)
	{
		$isFinancialInstitutionAccount  = new self instanceof FinancialInstitutionAccount ; 
		$foreignKeyColumnName = self::generateForeignKeyFormModelName(); // clean_overdraft_id for clean_overdrafts for example
		$fullBankStatement = self::getBankStatementTableClassName();
		
		$contractStartDateAsCarbon = Carbon::make($contractStartDate);
		
		$isLastDayOfMonth = $contractStartDateAsCarbon->isSameDay($contractStartDateAsCarbon->endOfMonth());
		
		$contractEndDateAsCarbon= Carbon::make($contractEndDate);
		
		$dates = generateDatesBetweenTwoDatesWithoutOverflow($contractStartDateAsCarbon,$contractEndDateAsCarbon) ;
		$countDates = count($dates);
		$interestText = 'interest';
		$interestTypeText = 'end_of_month';
		$fullBankStatement::where('company_id',$companyId)->where('type',$interestText)->where($foreignKeyColumnName,$this->id)->where('interest_type',$interestTypeText)->where('date','>',$contractEndDate)->delete();
		foreach($dates as $index => $dateAsString){
			if($index == 0 && $isLastDayOfMonth){
				continue;
			}
			$isLastLoop = $index == $countDates -1;
			$currentEndOfMonthDate = $isLastLoop ? Carbon::make($contractEndDate)->format('Y-m-d') : Carbon::make($dateAsString)->endOfMonth()->format('Y-m-d');
			$isExist = $fullBankStatement::where('company_id',$companyId)->where($foreignKeyColumnName,$this->id)->where('type',$interestText)->where('interest_type',$interestTypeText)->where('date',$currentEndOfMonthDate)->first();
			if(!$isExist){
				$data = [
				'company_id'=>$companyId,
				$foreignKeyColumnName=>$this->id ,
				'priority'=>1 ,
				'type'=>$interestText,
				'date'=>$currentEndOfMonthDate,
				'limit'=>$this->limit ,
				'credit'=>0 ,
				'interest_type'=>'end_of_month',
				'comment_en'=>__('End Of Month Interest'),
				'comment_ar'=>__('End Of Month Interest'),
			] ; 
			if($isFinancialInstitutionAccount){
				unset($data['priority']);
			}
			 $fullBankStatement::create($data);
			}
			
		}
	}

	
}
