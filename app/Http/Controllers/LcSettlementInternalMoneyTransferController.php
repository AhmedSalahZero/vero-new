<?php
namespace App\Http\Controllers;
use App\Models\AccountType;
use App\Models\Bank;
use App\Models\Company;
use App\Models\FinancialInstitution;
use App\Models\LcSettlementInternalMoneyTransfer;
use App\Models\LetterOfCreditIssuance;
use App\Traits\GeneralFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LcSettlementInternalMoneyTransferController
{
    use GeneralFunctions;
    protected function applyFilter(Request $request,Collection $collection):Collection{
		if(!count($collection)){
			return $collection;
		}
		$searchFieldName = $request->get('field');
		$dateFieldName =  'created_at' ; // change it 
		// $dateFieldName = $searchFieldName === 'balance_date' ? 'balance_date' : 'created_at'; 
		$from = $request->get('from');
		$to = $request->get('to');
		$value = $request->query('value');
		$collection = $collection
		->when($request->has('value'),function($collection) use ($request,$value,$searchFieldName){
			return $collection->filter(function($moneyReceived) use ($value,$searchFieldName){
				$currentValue = $moneyReceived->{$searchFieldName} ;
				if($searchFieldName == 'bank_id'){
					$currentValue = $moneyReceived->getBankName() ;  
				}
				return false !== stristr($currentValue , $value);
			});
		})
		->when($request->get('from') , function($collection) use($dateFieldName,$from){
			return $collection->where($dateFieldName,'>=',$from);
		})
		->when($request->get('to') , function($collection) use($dateFieldName,$to){
			return $collection->where($dateFieldName,'<=',$to);
		})
		->sortByDesc('id')->values();
		
		return $collection;
	}
	public function index(Company $company,Request $request)
	{
		
		$numberOfMonthsBetweenEndDateAndStartDate = 18 ;
		$currentType = $request->get('active',LcSettlementInternalMoneyTransfer::BANK_TO_LETTER_OF_CREDIT);
		
		$filterDates = [];
		foreach(LcSettlementInternalMoneyTransfer::getAllTypes() as $type){
			$startDate = $request->has('startDate') ? $request->input('startDate.'.$type) : now()->subMonths($numberOfMonthsBetweenEndDateAndStartDate)->format('Y-m-d');
			$endDate = $request->has('endDate') ? $request->input('endDate.'.$type) : now()->format('Y-m-d');
			
			$filterDates[$type] = [
				'startDate'=>$startDate,
				'endDate'=>$endDate
			];
		}
		
		
		 
		  /**
		 * * start of bank to safe internal money transfer 
		 */
		
		$bankToSafeStartDate = $filterDates[LcSettlementInternalMoneyTransfer::BANK_TO_LETTER_OF_CREDIT]['startDate'] ?? null ;
		$bankToSafeEndDate = $filterDates[LcSettlementInternalMoneyTransfer::BANK_TO_LETTER_OF_CREDIT]['endDate'] ?? null ;
		$bankToLcSettlementInternalMoneyTransfers = $company->bankToLcSettlementInternalMoneyTransfers ;
		$bankToLcSettlementInternalMoneyTransfers =  $bankToLcSettlementInternalMoneyTransfers->filterByTransferDate($bankToSafeStartDate,$bankToSafeEndDate) ;
		$bankToLcSettlementInternalMoneyTransfers =  $currentType == LcSettlementInternalMoneyTransfer::BANK_TO_LETTER_OF_CREDIT ? $this->applyFilter($request,$bankToLcSettlementInternalMoneyTransfers):$bankToLcSettlementInternalMoneyTransfers ;

		/**
		 * * end of bank to safe internal money transfer 
		 */
		 
		
		 $searchFields = [
			LcSettlementInternalMoneyTransfer::BANK_TO_LETTER_OF_CREDIT=>[
				'transfer_date'=>__('Transfer Date')
			],
		];
	
		$models = [
			LcSettlementInternalMoneyTransfer::BANK_TO_LETTER_OF_CREDIT =>$bankToLcSettlementInternalMoneyTransfers ,
		];

        return view('lc-settlement-internal-money-transfer.index', [
			'company'=>$company,
			'searchFields'=>$searchFields,
			'models'=>$models,
			'filterDates'=>$filterDates
		]);
    }
	public function create(Company $company)
	{
        return view('lc-settlement-internal-money-transfer.bank-to-letter-of-credit-form',$this->getCommonViewVars($company));
    }
	public function getCommonViewVars(Company $company,$model = null)
	{
		$banks = Bank::pluck('view_name','id');
		// $selectedBranches =  Branch::getBranchesForCurrentCompany($company->id) ;
		$financialInstitutionBanks = FinancialInstitution::onlyForCompany($company->id)->onlyBanks()->get();
		$accountTypes = AccountType::onlyCurrentAccount()->get();
		return [
			'banks'=>$banks,
			// 'selectedBranches'=>$selectedBranches,
			'financialInstitutionBanks'=>$financialInstitutionBanks,
			'accountTypes'=>$accountTypes,
			'model'=>$model
		];
	}
	
	public function store(Company $company   , Request $request){
	
		\DB::enableQueryLog();
		$type = LcSettlementInternalMoneyTransfer::BANK_TO_LETTER_OF_CREDIT;
		$internalMoneyTransfer = new LcSettlementInternalMoneyTransfer ;
		$companyId = $company->id ;
		/**
		 * @var LetterOfCreditIssuance $letterOfCreditIssuance
		 */
		$letterOfCreditIssuance = LetterOfCreditIssuance::find($request->get('to_letter_of_credit_issuance_id'));
		$letterOfCreditFacilityId = $letterOfCreditIssuance->getLcFacilityId();
		$lcFacilityLimit = $letterOfCreditIssuance->getLcFacilityLimit();
		$supplierName = $letterOfCreditIssuance->getSupplierName();
		$lcType = $letterOfCreditIssuance->getLcType();
		$transactionName =   $letterOfCreditIssuance->getTransactionName();
		$transferDate = $request->get('transfer_date') ;
		// $receivingDate = Carbon::make($transferDate)->addDay($request->get('transfer_days',0))->format('Y-m-d');
		$transferAmount = $request->get('amount') ;
		$internalMoneyTransfer->type = LcSettlementInternalMoneyTransfer::BANK_TO_LETTER_OF_CREDIT;
		$internalMoneyTransfer->storeBasicForm($request);
		$fromFinancialInstitutionId = $request->get('from_bank_id');
		// $toFinancialInstitutionId = $request->get('to_bank_id');
		$fromAccountTypeId = $request->get('from_account_type_id');
		// $toAccountTypeId = $request->get('to_account_type_id');
		$fromAccountNumber = $request->get('from_account_number');
		// $toAccountNumber = $request->get('to_account_number');
		// $toBranchId = $request->get('to_branch_id');
		// $fromBranchId = $request->get('from_branch_id');
		// $currencyName = $request->get('currency');	
		$fromAccountType = AccountType::find($fromAccountTypeId);
		// $toAccountType = AccountType::find($toAccountTypeId);
	
		if($type === LcSettlementInternalMoneyTransfer::BANK_TO_LETTER_OF_CREDIT ){
			$commentEn = __('Internal Transfer [ :supplierName ] [ :lcType ] Transaction Name [ :transactionName ]' ,['supplierName'=>$supplierName ,'lcType'=>$lcType,'transactionName'=>$transactionName],'en');
			$commentAr = __('Internal Transfer [ :supplierName ] [ :lcType ] Transaction Name [ :transactionName ]' ,['supplierName'=>$supplierName ,'lcType'=>$lcType,'transactionName'=>$transactionName],'ar');
			$internalMoneyTransfer->handleBankToLetterOfCreditTransfer(  $companyId ,$letterOfCreditFacilityId,$lcFacilityLimit,  $fromAccountType ,  $fromAccountNumber ,  $fromFinancialInstitutionId ,  $letterOfCreditIssuance ,  $transferDate , $transferAmount,$commentEn , $commentAr);
		}
	
		
		$activeTab = $type ; 
		
		return redirect()->route('lc-settlement-internal-money-transfers.index',['company'=>$company->id,'active'=>$activeTab])->with('success',__('Data Store Successfully'));
		
	}

	public function edit(Company $company,LcSettlementInternalMoneyTransfer $lcSettlementInternalTransfer)
	{

        return view('lc-settlement-internal-money-transfer.bank-to-letter-of-credit-form' ,$this->getCommonViewVars($company,$lcSettlementInternalTransfer));
    }
	
	public function update(Company $company, Request $request , LcSettlementInternalMoneyTransfer $lcSettlementInternalTransfer){
		
		$lcSettlementInternalTransfer->deleteRelations();
		$lcSettlementInternalTransfer->delete();
		$type = LcSettlementInternalMoneyTransfer::BANK_TO_LETTER_OF_CREDIT;
		$this->store($company,$request);
		$activeTab = $type ;
		return redirect()->route('lc-settlement-internal-money-transfers.index',['company'=>$company->id,'active'=>$activeTab])->with('success',__('Item Has Been Updated Successfully'));
	}
	
	public function destroy(Company $company , LcSettlementInternalMoneyTransfer $lcSettlementInternalTransfer)
	{
		$lcSettlementInternalTransfer->deleteRelations();
		
		$lcSettlementInternalTransfer->delete();
		return redirect()->back()->with('success',__('Item Has Been Delete Successfully'));
	}
	
}
