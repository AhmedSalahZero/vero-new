<?php

namespace App\Models;

use App\Models\OpeningBalance;
use App\Models\OutgoingTransfer;
use App\Services\Api\CashExpenseOdooService;
use App\Services\Api\OdooPayment;
use App\Traits\Models\HasCreditStatements;
use App\Traits\Models\HasForeignExchangeGainOrLoss;
use App\Traits\Models\HasNonCustomerOrSupplier;
use App\Traits\Models\HasReviewedBy;
use App\Traits\Models\HasUserComment;
use App\Traits\Models\IsMoney;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CashExpense extends Model
{
	
	use IsMoney ,HasForeignExchangeGainOrLoss,HasCreditStatements,HasReviewedBy,HasUserComment,HasNonCustomerOrSupplier;
	const CASH_PAYMENT  = 'cash_payment';
	const PAYABLE_CHEQUE  = 'payable_cheque';
	const OUTGOING_TRANSFER  = 'outgoing-transfer';
	
	public static function generateComment(self $cashExpense,string $lang)
	{
		if($cashExpense->isPayableCheque()){
			return __('Payable Cheque To Pay [:expenseName - :expenseNameName] [ :chequeNumber ]',['expenseName'=>$cashExpense->getExpenseCategoryName(),'expenseNameName'=>$cashExpense->getExpenseName(),'chequeNumber'=>Request('cheque_number')],$lang) ;
		}
		if($cashExpense->isCashPayment()){
			return __('Cash Payment To Pay [:expenseName - :expenseNameName]',['expenseName'=>$cashExpense->getExpenseCategoryName(),'expenseNameName'=>$cashExpense->getExpenseName()],$lang) ;
		}
		if($cashExpense->isOutgoingTransfer()){
			if($cashExpense->isOutgoingTransferBankCharges()){
				return  __('To Pay [:expenseName - :expenseNameName]',['expenseName'=>$cashExpense->getExpenseCategoryName(),'expenseNameName'=>$cashExpense->getExpenseName()],$lang) ;
			}
			return __('Outgoing Transfer To Pay [:expenseName - :expenseNameName]',['expenseName'=>$cashExpense->getExpenseCategoryName(),'expenseNameName'=>$cashExpense->getExpenseName()],$lang) ;
		}
	}
	protected static function booted()
	{
		self::creating(function (self $cashExpense): void {
			$cashExpense->comment_en = self::generateComment($cashExpense,'en');
			$cashExpense->comment_ar = self::generateComment($cashExpense,'ar');
		});
		
	}

	public function getPartnerOdooId()
	{
		return $this->partner ? $this->partner->odoo_id : null ;
	}
	public function getAccountTypeId()
	{
	
		if($this->isOutgoingTransfer()){
			return $this->outgoingTransfer->getAccountTypeId();
		}
		if($this->isPayableCheque()){
			return $this->payableCheque->getAccountTypeId();
		}
		return null;
		// throw new \Exception('Custom Exception .. getAccountTypeId .. This Method Is Only For Outgoing Transfer Or Payable Cheque');
	}
	public function getAccountNumber()
	{
		if($this->isOutgoingTransfer()){
			return $this->outgoingTransfer->getAccountNumber();
		}
		if($this->isPayableCheque()){
			return $this->payableCheque->getAccountNumber();
		}
		return null ;
		// throw new \Exception('Custom Exception .. getAccountNumber .. This Method Is Only For Outgoing Transfer Or Payable Cheque');
	}public function getFinancialInstitutionId()
	{
		if($this->isOutgoingTransfer()){
			return $this->getOutgoingTransferDeliveryBankId();
		}
		if($this->isPayableCheque()){
			return $this->getPayableChequePaymentBankId();
		}
		throw new \Exception('Custom Exception .. getFinancialInstitutionId .. This Method Is Only For Outgoing Transfer Or Payable Cheque');
	}
	public static function getAllTypes()
	{
		return [
			self::CASH_PAYMENT,
			self::PAYABLE_CHEQUE,
			self::OUTGOING_TRANSFER,
		];
	}
	
    protected $guarded = ['id'];
    
	
    public function isCashPayment()
    {
        return $this->getType() ==self::CASH_PAYMENT;
    }
	public function isPayableCheque()
    {
        return $this->getType() ==self::PAYABLE_CHEQUE;
    }
  
    public function isOutgoingTransfer()
    {
        return $this->getType() ==self::OUTGOING_TRANSFER;
    }

    public function getPaymentDate()
    {
        return $this->payment_date;
    }
    public function getCashPaymentBranchId()
    {
		$cashPayment = $this->cashPayment ;
        return  $cashPayment ? $cashPayment->getDeliveryBranchId() :0;
    }
	public function getBranchId():int
    {
		return $this->getCashPaymentBranchId();
    }
    public function getPaidAmount()
    {
        return  $this->paid_amount?:0 ;
    }
	public function getAmount()
	{
		return $this->getPaidAmount();
	}
	
	public function getPayableChequeDueDate(){
		return $this->payableCheque ? $this->payableCheque->getDueDate(): null;
	}
	public function getOutgoingTransferDueDate(){
		return $this->outgoingTransfer ? $this->outgoingTransfer->actualPaymentDate(): null;
	}
	public function getPayableChequeNumber()
	{
		return $this->payableCheque ? $this->payableCheque->getChequeNumber():null;
	}
	public function getPaidAmountFormatted()
    {
        return number_format($this->getPaidAmount()) ;
    }
   
	public function getCurrency()
	{
		return $this->currency;
	}
	
	public function getCurrencyFormatted()
	{
		return strtoupper($this->currency);
	}
	public function getCurrencyToPaymentCurrencyFormatted()
	{
		$currency = $this->getCurrency();
		$paymentCurrency = $this->getPaymentCurrency();
		if($currency == $paymentCurrency || is_null($paymentCurrency)){
			return $this->getCurrencyFormatted();
		}
		return $this->getCurrencyFormatted().'/'.$this->getPaymentCurrencyFormatted();
		
	}
	public function getPaymentCurrency()
	{
		return $this->getCurrency();
	}
	
	public function getPaymentCurrencyFormatted()
	{
		return strtoupper($this->getPaymentCurrency());
	}
	
	public function getExchangeRate()
	{
		
		return $this->exchange_rate?:1;
	}

	public function getExpenseCategoryName():string
	{
		return $this->cashExpenseCategoryName && $this->cashExpenseCategoryName->cashExpenseCategory ? $this->cashExpenseCategoryName->cashExpenseCategory->getName() : __('N/A') ;
	}
	public function getExpenseName()
	{
		return  $this->cashExpenseCategoryName ? $this->cashExpenseCategoryName->getName() : __('N/A');
	}
	public function getPaymentBankName()
	{
		return '-';
	}


    public function getCashPaymentReceiptNumber()
    {
		$cashPayment = $this->cashPayment;
        return $cashPayment ? $cashPayment->getReceiptNumber() :  null ;
    }

  
	public function getNumber()
	{
		if($this->isPayableCheque()){
			return $this->payableCheque->getChequeNumber();
		}
		if($this->isCashPayment()){
			return $this->getCashPaymentReceiptNumber();
		}
		if($this->isOutgoingTransfer()){
			return $this->getOutgoingTransferAccountNumber();
		}
		
	}
	

	
	public function getBankName()
	{
		if($this->isCashPayment()){
			return $this->getCashPaymentBranchName();
		}
		if($this->isPayableCheque()){
			return $this->payableCheque->getDeliveryBankName();
		}
		if($this->isOutgoingTransfer()){
			return $this->getOutgoingTransferDeliveryBankName(app()->getLocale());
		}
		
	}
	
	public function outgoingTransfer()
	{
		return $this->hasOne(OutgoingTransfer::class,'cash_expense_id','id');
	}

    public function getOutgoingTransferDeliveryBankId()
    {
		$outgoingTransfer = $this->outgoingTransfer ;
        return $outgoingTransfer ? $outgoingTransfer->getDeliveryBankId() : 0 ;
    }
	public function outgoingTransferDeliveryBank():?FinancialInstitution
	{
		/**
		 * @var OutgoingTransfer $outgoingTransfer 
		 */
		$outgoingTransfer = $this->outgoingTransfer ;
		
		return $outgoingTransfer ? $outgoingTransfer->deliveryBank : null ;
	}
	public function getOutgoingTransferDeliveryBankName()
	{
		/**
		 * @var OutgoingTransfer $outgoingTransfer 
		 */
		$outgoingTransfer = $this->outgoingTransfer ;
        return $outgoingTransfer ? $outgoingTransfer->getDeliveryBankName() : __('N/A') ;
	}
	
	
    public function getPaymentDateFormatted()
    {
        $date = $this->getPaymentDate() ;
        if($date) {
            return Carbon::make($date)->format('d-m-Y');
        }
    }
	public function setPaymentDateAttribute($value)
	{
		if(is_object($value)){
			return $value ;
		}
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['payment_date'] = $value;
			return  ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];
		$this->attributes['payment_date'] = $year.'-'.$month.'-'.$day;
		
	}
	
	public static function getUniqueBanks( $banks):array{
		$uniqueBanksIds = [];
		foreach($banks as $bankId){
				$uniqueBanksIds[$bankId] = $bankId;
		}
		return $uniqueBanksIds; 
	}
	
	public function cashPaymentDeliveryBranch()
	{
		/**
		 * @var CashPayment $cashPayment
		 */
		$cashPayment = $this->cashPayment;
		return $cashPayment ? $cashPayment->deliveryBranch : null ;
	}
	public function getCashPaymentBranchName()
	{
		$cashPayment = $this->cashPayment;

		return $cashPayment ? $cashPayment->getDeliveryBranchName() : null ;
	}
	
	public function getPayableChequeDeliveryDate()
	{
		$payable = $this->payableCheque;
		return $payable ? $payable->getPaymentDate() : null;
	}
	public function getPayableChequeDeliveryDateFormattedForDatePicker()
	{
		$date = $this->getPayableChequeDeliveryDate();
		return $date ? Carbon::make($date)->format('m/d/Y') : null;
	}
	public function getChequeDeliveryDateFormatted()
	{
		$date = $this->getPayableChequeDeliveryDate();
		return $date ? Carbon::make($date)->format('d-m-Y') : null;
	}

	
	public function getPayableChequePaymentBankId()
	{
		$payableCheque = $this->payableCheque ;
		return $payableCheque ? $payableCheque->getDeliveryBankId() : null ;
	}
	public function getPayableChequeAccountType()
	{
		$payableCheque = $this->payableCheque ;
		return $payableCheque ? $payableCheque->getAccountType() : null ;
	}
	
	
	public function cashPayment()
	{
		return $this->hasOne(CashPayment::class,'cash_expense_id','id');
	}
	
	public function payableCheque()
	{
		return $this->hasOne(PayableCheque::class,'cash_expense_id','id');
	}

	public function getTotalWithholdAmount():float 
	{
		return $this->total_withhold_amount ?: 0 ;
	}
	public function getOutgoingTransferAccountTypeId(){
		$outgoingTransfer = $this->outgoingTransfer;
		return $outgoingTransfer ? $outgoingTransfer->getAccountTypeId() : 0 ;
	}
	public function getOutgoingTransferAccountTypeName(){
		$outgoingTransfer = $this->outgoingTransfer;
		return $outgoingTransfer ? $outgoingTransfer->getAccountTypeName() : 0 ;
	}
	public function getOutgoingTransferAccountNumber(){
		$outgoingTransfer = $this->outgoingTransfer;
		return $outgoingTransfer ? $outgoingTransfer->getAccountNumber() : 0 ;
	}
	
	public function getPayableChequeAccountTypeId(){
		$payableCheque = $this->payableCheque;

		return $payableCheque ? $payableCheque->getAccountTypeId() : 0 ;
	}
	public function getPayableChequeAccountTypeName(){
		$payableCheque = $this->payableCheque;
		return $payableCheque ? $payableCheque->getAccountTypeName() : 0 ;
	}
	public function getPayableChequeAccountNumber(){
		$payableCheque = $this->payableCheque;
		return $payableCheque ? $payableCheque->getAccountNumber() : 0 ;
	}
	

	public function cleanOverdraftCreditBankStatement()
	{
		return $this->hasOne(CleanOverdraftBankStatement::class,'cash_expense_id','id');
	}
	public function fullySecuredOverdraftCreditBankStatement()
	{
		return $this->hasOne(FullySecuredOverdraftBankStatement::class,'cash_expense_id','id');
	}
	public function overdraftAgainstCommercialPaperCreditBankStatement()
	{
		return $this->hasOne(OverdraftAgainstCommercialPaperBankStatement::class,'cash_expense_id','id');
	}
	public function overdraftAgainstAssignmentOfContractCreditBankStatement()
	{
		return $this->hasOne(OverdraftAgainstAssignmentOfContractBankStatement::class,'cash_expense_id','id');
	}
	public function cashInSafeCreditStatement()
	{
		return $this->hasOne(CashInSafeStatement::class,'cash_expense_id','id');
	}
	public function currentAccountCreditBankStatement()
	{
		return $this->hasOne(CurrentAccountBankStatement::class,'cash_expense_id','id');
	}
	
	public function openingBalance()
	{
		return $this->belongsTo(OpeningBalance::class,'opening_balance_id');
	}
	public function isOpenBalance()
	{
		return $this->opening_balance_id !== null ;
	}
	
	// public function isDownPayment()
	// {
	// 	return $this->getMoneyType() == 'down-payment';
	// }
	// public function getMoneyType()
	// {
	// 	return $this->money_type; 
	// }
	public function getMoneyTypeFormatted()
	{
		$moneyType = $this->getMoneyType();
		if($moneyType == 'money-payment'){
			$moneyType = 'invoice-settlement';
		}
		return camelizeWithSpace($moneyType) ;
	}
	// public function getContractId()
	// {
	// 	return $this->contract_id;
	// }
	public function getCurrentStatement()
	{
		if($this->cleanOverdraftCreditBankStatement){
			return $this->cleanOverdraftCreditBankStatement;
		}	
		if($this->fullySecuredOverdraftCreditBankStatement){
			return $this->fullySecuredOverdraftCreditBankStatement;
		}
		if($this->overdraftAgainstCommercialPaperCreditBankStatement){
			return $this->overdraftAgainstCommercialPaperCreditBankStatement;
		}	
		if($this->overdraftAgainstAssignmentOfContractCreditBankStatement){
			return $this->overdraftAgainstAssignmentOfContractCreditBankStatement;
		}
		if($this->cashInSafeCreditStatement){
			return $this->cashInSafeCreditStatement ;
		}
		if($this->currentAccountCreditBankStatement){
			return $this->currentAccountCreditBankStatement ;
		}
	}
	public function deleteRelations()
	{
		$this->unlinkNonCustomerOrSupplierOdooExpense();
		
		// $company= $this->company;
		// $journalEntryId = $this->journal_entry_id;
		
		if ($this->account_bank_statement_line_id) {
            $OdooPaymentService = new OdooPayment($this->company);
            $OdooPaymentService->unlinkBankCollection($this->account_bank_statement_line_id);
        }
		
		// if($company->hasOdooIntegrationCredentials() && $journalEntryId){
		// 			$cashExpenseOdooService = new CashExpenseOdooService($company);
		// 			$cashExpenseOdooService->unlink($journalEntryId);
		// }
		$oldType = $this->getType();
		$oldTypeRelationName = dashesToCamelCase($oldType);
		$this->$oldTypeRelationName ? $this->$oldTypeRelationName->delete() : null;
		$this->contracts()->detach();
		// $this->cashPayment ? $this->cashPayment->delete():null; 
		$currentStatement = $this->getCurrentStatement() ;
		if($currentStatement){
			$currentStatement->delete();
		}
	}
	/**
	 * * دا عباره عن التاريخ اللي هنستخدمة في ال
	 * * statements 
	 * * سواء بانك او كاش الخ
	 */
	public function getStatementDate()
	{
		if($this->isPayableCheque()){
			return $this->getPayableChequeDueDate();
		}
		if($this->isOutgoingTransfer()){
			return $this->getOutgoingTransferDueDate();
		}
		return $this->getPaymentDate();
	}
		
	public function cashExpenseCategoryName():BelongsTo
	{
		return $this->belongsTo(CashExpenseCategoryName::class,'cash_expense_category_name_id','id');
	}	
	public function getExpenseCategoryId():int
	{
		return $this->cashExpenseCategoryName && $this->cashExpenseCategoryName->cashExpenseCategory ? $this->cashExpenseCategoryName->cashExpenseCategory->id : 0;
	}
	public function getCashExpenseCategoryNameId()
	{
		return $this->cash_expense_category_name_id ;
	}
	public function contracts()
	{
		return $this->belongsToMany(Contract::class ,'cash_expense_contract','cash_expense_id','contract_id')
		->withTimestamps()
		->withPivot(['amount'])
		;
	}

	public static function getCashOutForExpenseCategoriesAtDates(array &$result  ,$foreignExchangeRates,$mainFunctionalCurrency , string $moneyType,string $dateFieldName , int $companyId, string $startDate , string $endDate , string $currentWeekYear , int $contractId = null , ?string $chequeStatus = null) 
	{
		$subTableName = (new self)->getTable();
		$mainTableName = [
			MoneyPayment::OUTGOING_TRANSFER => (new OutgoingTransfer())->getTable(),
			MoneyPayment::CASH_PAYMENT =>(new CashPayment())->getTable(),
			MoneyPayment::PAYABLE_CHEQUE => (new PayableCheque())->getTable()
		][$moneyType];
		$columnNames = $contractId ? 'cash_expense_categories.name as category_name , cash_expense_category_names.name as expense_name ,sum(amount) as paid_amount, currency,payment_date'  :'cash_expense_categories.name as category_name , cash_expense_category_names.name as expense_name ,sum(paid_amount) as paid_amount, currency,payment_date'; 
		$expensesWithPaidAmount = DB::table($mainTableName)
						// ->where($subTableName.'.currency',$currency)
						->where('type',$moneyType)
						->where($subTableName.'.company_id',$companyId)
						->whereBetween($dateFieldName,[$startDate,$endDate])
						->join($subTableName,$subTableName.'.id','=',$mainTableName.'.cash_expense_id')
						->join('cash_expense_category_names',$subTableName.'.cash_expense_category_name_id','=','cash_expense_category_names.id')
						->join('cash_expense_categories','cash_expense_category_id','=','cash_expense_categories.id')
						->when($chequeStatus , function(Builder $builder) use ($chequeStatus){
							$builder->where('payable_cheques.status',$chequeStatus);
						})
						->when($contractId,function($query) use ($contractId) {
						  	$query->join('cash_expense_contract','cash_expense_contract.cash_expense_id','=','cash_expenses.id')
							->where('contract_id',$contractId);
						})
						->groupBy('cash_expense_category_name_id')
						->selectRaw($columnNames)->get();
				
		foreach($expensesWithPaidAmount as $expenseWithPaidAmount){
			
				$currentCurrency = $expenseWithPaidAmount->currency;
				$paymentDate = $expenseWithPaidAmount->payment_date;
				$exchangeRate = ForeignExchangeRate::getExchangeRateForCurrencyAndClosestDate($currentCurrency,$mainFunctionalCurrency,$paymentDate,$companyId,$foreignExchangeRates);
				
			$categoryName = $expenseWithPaidAmount->category_name;
			$expenseName = $expenseWithPaidAmount->expense_name;
			$currentPaidAmount = $expenseWithPaidAmount->paid_amount *$exchangeRate;
			$result['cash_expenses'][$categoryName][$expenseName]['weeks'][$currentWeekYear] = isset($result['cash_expenses'][$categoryName][$expenseName]['weeks'][$currentWeekYear]) ? $result['cash_expenses'][$categoryName][$expenseName]['weeks'][$currentWeekYear] + $currentPaidAmount :  $currentPaidAmount;
			$result['cash_expenses'][$categoryName][$expenseName]['total'] = isset($result['cash_expenses'][$categoryName][$expenseName]['total']) ? $result['cash_expenses'][$categoryName][$expenseName]['total']  + $currentPaidAmount : $currentPaidAmount;
			$currentTotal = $currentPaidAmount;
			$result['cash_expenses'][$categoryName]['total'][$currentWeekYear] = isset($result['cash_expenses'][$categoryName]['total'][$currentWeekYear]) ? $result['cash_expenses'][$categoryName]['total'][$currentWeekYear] +  $currentTotal : $currentTotal ;
		}
	
	
	}
	public static function getProjectionOtherCashOut(array &$result ,Company $company , int $cashflowReportId,int $isContract ):void
	{
		$key = __('Projected Other Cash Out Items') ;
		$items = CashProjection::where('company_id',$company->id)->where('is_contract',$isContract)->where('cashflow_report_id',$cashflowReportId)->where('type','out')->get();
			foreach($items as $item){
				$invoiceNumber = $item->name ; 
				foreach($item->amounts as $currentWeekYear => $value){
					$result['cash_expenses'][$key][$invoiceNumber]['weeks'][$currentWeekYear] = isset($result['cash_expenses'][$key][$invoiceNumber]['weeks'][$currentWeekYear]) ? $result['cash_expenses'][$key][$invoiceNumber]['weeks'][$currentWeekYear] + $value :  $value;
					$result['cash_expenses'][$key][$invoiceNumber]['total'] = isset($result['cash_expenses'][$key][$invoiceNumber]['total']) ? $result['cash_expenses'][$key][$invoiceNumber]['total']  + $value : $value;
					$currentTotal = $value;
					$result['cash_expenses'][$key]['total'][$currentWeekYear] = isset($result['cash_expenses'][$key]['total'][$currentWeekYear]) ? $result['cash_expenses'][$key]['total'][$currentWeekYear] +  $currentTotal : $currentTotal ;
						
				}
			}
	}
	
	
	public function isOutgoingTransferBankCharges():bool
	{
	
		if(!$this->isOutgoingTransfer()){
			return false ; 
		}
		return (
			$this->outgoingTransfer && $this->outgoingTransfer->isBankCharges() )
	         ||
			 Request()->boolean('is_bank_charges');
	}
	public function saveAllocations(array $contracts)
	{
		if(count($contracts)){
			foreach($contracts as $contractArr){
				$currentContractId = $contractArr['contract_id'] ?? null ;
			
				$currentAmount = number_unformat($contractArr['amount'] ?? 0) ;
				if($currentContractId && $currentAmount > 0){
					$this->contracts()->attach(
						$currentContractId,
						['amount'=>$currentAmount],
					);
				}
				
			} 
			
		}
	}
	public function getBankAccountJournalId():int
	{
		$financialInstitution = $this->getFinancialInstitution();
		
		return $financialInstitution->getJournalIdForAccount($this->getAccountTypeId(),$this->getAccountNumber());
	}
	public function getBankAccountOdooId():int
	{
		$financialInstitution = $this->getFinancialInstitution();
		
		return $financialInstitution->getOdooIdForAccount($this->getAccountTypeId(),$this->getAccountNumber());
	}
	public function formatAnalysisDistribution():array 
	{
		$amount = $this->getAmount();
		$result = [];
		foreach($this->contracts as $contract){
			$projectAccountId = $contract->project_account_id  ;
			$pivotAmount = $contract->pivot->amount ;
			$pivotPercentage = $pivotAmount / $amount *100 ;
			if($projectAccountId){
				$result[strval($projectAccountId)] =(float)$pivotPercentage; 
			}
		}
		if(count($result) == 1){
			$result["-0"] = 0.0;
		}
		return $result;
	}
	public function getCashBranchJournalId()
	{
		$cashPayment = $this->cashPayment;

		return $cashPayment ? $cashPayment->getBankJournalId() : null ;
	}
	public function getCashBranchOdooId()
	{
		$cashPayment = $this->cashPayment;

		return $cashPayment ? $cashPayment->getBankOdooId() : null ;
	}
	
	public function getOdooReferenceNames():array
	{
		$references = [];
		$i = 0;
		foreach([
			'odoo_reference'
		] as $columnName ){
			if($this->{$columnName}){
				$i ++;
				$references[] = $i .'-'.$this->{$columnName};
			}
		}
		return $references ;
	}
	public function fullyIntegratedWithOdoo()
	{
		return count($this->getOdooReferenceNames());
	}
	public function isChequeAndNotCustomerOrSupplier()
	{
		return $this->isChequeOrChequePayment() && (!in_array($this->getPartnerType(),['is_customer','is_supplier']));
	}
	public function getOdooIdWithRefOfTransaction():array 
	{
		$cashExpenseCategoryName = $this->cashExpenseCategoryName;
	
		return [
			'id'=>$cashExpenseCategoryName->getOdooId(),
			'ref'=>__('Cash Expense Payable Cheque')
		];
	}
	public function getDate()
    {
        return $this->getPaymentDate();
    }
	public function getPartnerType()
	{
		return null;
	}
	public function getDeliveryDate()
	{
		return $this->getDate();
	}public function isInvoiceSettlementWithDownPayment()
	{
		return false;
	}
	 public function getCustomerOrSupplier():string
    {
        return 'supplier';
    }
}
