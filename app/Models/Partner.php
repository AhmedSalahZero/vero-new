<?php

namespace App\Models;

use App\Http\Controllers\CompanyController;
use App\Services\Api\OdooService;
use App\Traits\HasBasicStoreRequest;
use App\Traits\HasCreatedAt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Partner extends Model
{
	const PARTNERS = 'partners';
	const CUSTOMERS = 'customers';
	const SUPPLIERS = 'suppliers';
	const EMPLOYEES = 'employees';
	const SHAREHOLDERS = 'shareholders';
	const SUBSIDIARY_COMPANIES = 'subsidiary-companies';
	const OTHER_PARTNERS = 'other-partners';
	const TAXES = 'taxes'; 
	use HasCreatedAt,HasBasicStoreRequest;
    protected $dates = [
    ];
	public function contracts()
	{
		return $this->hasMany(Contract::class,'partner_id','id');
	}

    protected $guarded = [];


    /**
     * The table associated with the model.
     *
     * @var string
     */
	public function getId(){
		return $this->id ;
	}
	public function getOdooId():?int
	{
		return $this->odoo_id;
	}
	public function getName()
	{
		return $this->name ;
	}
	public function getCustomerName()
	{
		return $this->getName();
	}
	public function scopeOnlyCompany(Builder $query,$companyId){
		return $query->where('company_id',$companyId);
	}
	public function scopeOnlyThatHaveContracts(Builder $query){
		return $query->has('contracts');
	}
	public function scopeOnlyHasInvoicesWithCurrency(Builder $query,string $currencyName){
		return $query->whereHas('SupplierInvoice',function(Builder $builder) use ($currencyName){
			$builder->where('currency',$currencyName);
		});
	}
	public function scopeOnlyForCompany(Builder $query,$companyId){
		return $query->where('company_id',$companyId);
	}
	public function scopeOnlyCustomers(Builder $query){
		return $query->where(function($q){
			$q->where('is_customer',1);
		});
	}
	public function scopeOnlyCustomersOrOtherPartners(Builder $query){
		return $query->where(function($q){
			$q->where('is_customer',1)->orWhere('is_other_partner',1);
		});
	}
	public function scopeOnlySuppliers(Builder $query){
		return $query->where(function($q){
			$q->where('is_supplier',1);
		});
	}
	public function getTypeFormatted(string $partnerType):string
	{

		return [
			'is_customer'=>__('Customer'),
			'is_supplier'=>__('Supplier'),
			'is_employee'=>__('Employee'),
			'is_tax'=>__('Taxes'),
			'is_shareholders'=>__('Shareholders'),
			'is_subsidiary_company'=>__('Subsidiary Company'),
			'is_other_partner'=>__('Other Partner'),
		][$partnerType];
	}
	public function scopeOnlyEmployees(Builder $query){
		return $query->where(function($q){
			$q->where('is_employee',1);
		});
	}
	public function scopeOnlyTaxes(Builder $query){
		return $query->where(function($q){
			$q->where('is_tax',1);
		});
	}
	public function scopeOnlyShareholders(Builder $query){
		return $query->where(function($q){
			$q->where('is_shareholders',1);
		});
	}
	public function scopeOnlySubsidiaryCompanies(Builder $query){
		return $query->where(function($q){
			$q->where('is_subsidiary_company',1);
		});
	}
	public function scopeOnlyOtherPartners(Builder $query){
		return $query->where(function($q){
			$q->where('is_other_partner',1);
		});
	}

	public function isCustomer()
	{
		return $this->is_customer == 1 ;
	}
	
	public function isSupplier()
	{
		return $this->is_supplier == 1 ;
	}

	public function isEmployee()
	{
		return $this->is_employee == 1 ;
	}
	public function isTax()
	{
		return $this->is_tax == 1 ;
	}
	public function isSubsidiaryCompany()
	{
		return $this->is_subsidiary_company == 1 ;
	}
	public function isShareholder()
	{
		return $this->is_shareholder == 1 ;
	}
	public function isOtherPartner()
	{
		return $this->is_other_partner == 1 ;
	}
	public function CustomerInvoice()
	{
		return $this->hasMany(CustomerInvoice::class,'customer_id','id');
	}
	public function SupplierInvoice()
	{
		return $this->hasMany(SupplierInvoice::class,'supplier_id','id');
	}
	public function updateNamesInAllTables(array $columnNames , string $oldPartnerName,string $newPartnerName, int $companyId , array $additionalWhere = [])
	{
		$tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
		foreach($tables as $tableName){
			foreach($columnNames as $columnName){
				if(Schema::hasColumn($tableName,$columnName)){
					if($tableName == 'sales_gathering'){
						continue;
					}
					$query = DB::table($tableName)->where('company_id',$companyId)
					->where($columnName,$oldPartnerName);
			
					if($tableName == 'money_received' || $tableName == 'money_payments'){
						
						$query->where($additionalWhere[0] ,$additionalWhere[1]  , $additionalWhere[2] );
					}
					$query->update([$columnName=>$newPartnerName])
					;
				}
			}
			
		}
	}
	public static function getPartnerFromName(string $name , int $companyId):?self
	{
		return self::where('name',$name)->where('company_id',$companyId)->first();
	}
	public function getCustomerType()
	{
		foreach($this->toArray() as $columnName => $colValue){
			if(in_array($columnName,array_keys(getAllPartnerTypesForCustomers())) && $colValue == 1){
				return $columnName;
			}
		}
		throw new \Exception('Custom Exception .. No Available Partner Type');
	}
	public function getSupplierType()
	{
		foreach($this->toArray() as $columnName => $colValue){
			if(in_array($columnName,array_keys(getAllPartnerTypesForSuppliers())) && $colValue == 1){
				return $columnName;
			}
		}
		throw new \Exception('Custom Exception .. No Available Partner Type');
	}
	public static function getCustomersForCompany(int $companyId){
		return Partner::where('company_id',$companyId)
		->where('is_customer',1)->orderBy('name')->pluck('name','id');
	} 
	
	public static function getSuppliersForCompany(int $companyId){
		return self::where('company_id',$companyId)->where('is_supplier',1)->orderBy('name')->pluck('name','id');
		
	} 
	public  static function getSuppliersForCompanyFormattedForSelect(Company $company)
	{
		return self::where('company_id', $company->id)->where('is_supplier',1)->orderBy('name','asc')->get()->formattedForSelect(true, 'getId', 'getName');
	}
	public static function findByOdooId(int $id,int $companyId){
		return self::where('odoo_id',$id)->where('is_tax',0)->where('company_id',$companyId)->first();
	}
	public static function findByName(string $name,int $companyId){
		return self::where('name',$name)->where('is_tax',0)->where('company_id',$companyId)->first();
	}
	public static function handlePartnerForOdoo($odooPartnerId ,$odooPartnerName,$isCustomer,$isSupplier ,$isEmployee,$isOtherPartner,$companyId  ):int
	{
			$partner = Partner::findByOdooId($odooPartnerId,$companyId);
			// if(is_null($partner)){
			// 	// $partner = Partner::findByName($odooPartnerName,$companyId);
			// 	// if($partner){
			// 		// $oldIsCustomer = $partner->is_customer;
			// 		// $oldIsSupplier = $partner->is_supplier;
			// 		// $oldIsEmployee = $partner->is_employee;
			// 		// $oldIsOtherPartner = $partner->is_other_partner;
			// 		$partner->update([
			// 			'odoo_id'=>$odooPartnerId,
			// 			'is_customer'=>$isCustomer,
			// 			'is_supplier'=>$isSupplier,
			// 			'is_employee'=>$isEmployee,
			// 			'is_other_partner'=>$isOtherPartner,	
						
			// 			// 'is_customer'=>$oldIsCustomer?:$isCustomer,
			// 			// 'is_supplier'=>$oldIsSupplier?:$isSupplier,
			// 			// 'is_employee'=>$oldIsEmployee?:$isEmployee,
			// 			// 'is_other_partner'=>$oldIsOtherPartner?:$isOtherPartner,
			// 		]);
			// 		return $partner->id;
			// 	// }
			// }
			if(is_null($partner)){
				$partner = Partner::createNewForOdoo($odooPartnerId,$odooPartnerName,$companyId,$isCustomer,$isSupplier,$isEmployee,$isOtherPartner);
			}else{
				$partner->update([
					'name'=>$odooPartnerName,
					'odoo_id'=>$odooPartnerId,
					'is_customer'=>$isCustomer,
					'is_supplier'=>$isSupplier ,
					'is_employee'=>$isEmployee,
					'is_other_partner'=>$isOtherPartner,
				]);
			}
			// if($isSupplier){
			// 	$partner->update([
			// 		'is_supplier'=>1 ,
			// 		'odoo_id'=>$odooPartnerId,
			// 		'name'=>$odooPartnerName
			// 	]);
			// }
			// if($isCustomer){
			// 	$partner->update([
			// 		'is_customer'=>1 ,
			// 		'odoo_id'=>$odooPartnerId,
			// 		'name'=>$odooPartnerName
			// 	]);
			// }
			// if($isEmployee){
			// 	$partner->update([
			// 		'is_employee'=>1 ,
			// 		'odoo_id'=>$odooPartnerId,
			// 		'name'=>$odooPartnerName
			// 	]);
			// }
			// if($isOtherPartner){
			// 	$partner->update([
			// 		'is_other_partner'=>1 ,
			// 		'odoo_id'=>$odooPartnerId,
			// 		'name'=>$odooPartnerName
			// 	]);
			// }
			return $partner->id ;
	}
	public static function createNewForOdoo(int $id,string $partnerName,int $companyId,int $isCustomer,int $isSupplier,int $isEmployee,int $isOtherPartner){
		
		/**
		 * @var Company $company 
		 */
		$partner = Partner::create([
			'odoo_id'=>$id ,
			'is_customer'=>$isCustomer ,
			'is_supplier'=>$isSupplier,
			'is_employee'=>$isEmployee,
			'is_other_partner'=>$isOtherPartner,
			'company_id'=>$companyId ,
			'name'=>$partnerName
		]);
		return $partner;
	}
	public function dueToChartOfAccountNumberCode()
	{
		if(is_null($this->due_to_chart_of_account_number_odoo_code)){
			throw new \Exception('Due To Chart Of Account Number Not Found .. Please Add It From Other Odoo Setting Form');
		}
		return  $this->due_to_chart_of_account_number_odoo_code;
	}
		public function dueToChartOfAccountNumberId()
	{
		if(!$this->due_to_chart_of_account_number_odoo_id){
			throw new \Exception('Due To Chart Of Account Number Not Found .. Please Add It From Other Odoo Setting Form');
		}
		return  $this->due_to_chart_of_account_number_odoo_id;
	}
		public function dueFromChartOfAccountNumberCode()
	{
		if(!$this->due_from_chart_of_account_number_odoo_code){
			throw new \Exception('Due To Chart Of Account Number Not Found .. Please Add It From Other Odoo Setting Form');
		}
		return  $this->due_from_chart_of_account_number_odoo_code;
	}
		public function dueFromChartOfAccountNumberId()
	{
		if(is_null($this->due_to_chart_of_account_number_odoo_id)){
			throw new \Exception('Due To Chart Of Account Number Not Found .. Please Add It From Other Odoo Setting Form');
		}
		return  $this->due_to_chart_of_account_number_odoo_id;
	}
	public static function getTaxesNames():array 
	{
		return  [
			'vat_taxes_code'=>'VAT Taxes',
			'credit_withhold_taxes_code'=>'Credit Withhold Taxes',
			'salary_taxes_code'=>'Salary Taxes',
			'social_insurance_code' => 'Social Insurance',
			'income_taxes_code'=>'Income Taxes',
			'real_estate_taxes_code'=>'Real Estate Taxes',
			'stamp_duty_taxes_code'=>'Stamp Duty Taxes',
			'other_taxes_code'=>'Other Taxes',
			'takaful_code'=>'Takaful Contribution Tax',
			'tax_for_victims_code'=>'Tax for the Support of Victims Fund'
		];;
	}
	public static function handleTaxesColumnsToPartnerTable(Company $company)
	{
		foreach(self::getTaxesNames() as $name){
			$row = Partner::where('company_id',$company->id)->where('is_tax',1)->where('name',$name)->first();
			$data = [
				'name'=>$name ,
				'is_tax'=>1 ,
				'is_customer'=>0,
				'is_supplier'=>0 ,
				'company_id'=>$company->id,
			];
			if($row){
					$row->update($data);
			}else{
				Partner::create($data);
			}
		}
	}
	public function syncAccounts(Request $request,Company $company)
	{
		if(!$company->hasOdooIntegrationCredentials()){
			return ;
		}
		$odooService = new OdooService($company);
			$code = $request->get('due_from_chart_of_account_number_odoo_code') ;
			$this->due_from_chart_of_account_number_odoo_code = $code;
			$journal = $odooService->fetchData('account.account',['code','name'],[[['code','=',$code]]]);
			$odooId = $journal[0]['id'] ?? null ;
			if($odooId){
				$this->due_from_chart_of_account_number_odoo_id  = $odooId;
			}
			$code = $request->get('due_to_chart_of_account_number_odoo_code') ;
			$this->due_to_chart_of_account_number_odoo_code = $code;
			$journal = $odooService->fetchData('account.account',['code','name'],[[['code','=',$code]]]);
			$odooId = $journal[0]['id'] ?? null ;
			if($odooId){
				$this->due_to_chart_of_account_number_odoo_id  = $odooId;
			}
			$this->save();
			
	}
	
	
}
