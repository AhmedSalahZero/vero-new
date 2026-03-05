<?php

namespace App\Models;

use App\Formatter\Select2Formatter;
use App\Helpers\HAuth;

use App\Models\NonBankingService\ConsumerfinanceProduct;
use App\Models\NonBankingService\LeasingCategory;
use App\Models\NonBankingService\MicrofinanceProduct;
use App\Models\PropertyManagement\Category;
use App\Models\PropertyManagement\Nature;
use App\Models\PropertyManagement\Ownership;
use App\Models\PropertyManagement\Property;
use App\Models\PropertyManagement\PropertyType;
use App\Models\PropertyManagement\Tenant;
use App\Models\PropertyManagement\UsageStatus;
use App\NotificationSetting;
use App\OdooSetting;
use App\Traits\HasBasicStoreRequest;
use App\Traits\StaticBoot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @mixin IdeHelperCompany
 */
class Company extends Model implements HasMedia
{
    use
        StaticBoot,
        InteractsWithMedia ,
        Notifiable,
        HasBasicStoreRequest,
        HasFactory;
    protected $guarded = [];
    protected $connection ='mysql';
    public function getIdentifier():int
    {
        return $this->{$this->getRouteKeyName()};
    }
    public function getId()
    {
        return $this->getIdentifier();
    }
    protected $casts = ['name' => 'array','generate_labeling_code_fields'=>'array','labeling_print_headers'=>'array'];
    public function users():BelongsToMany
    {
        return $this->belongsToMany(User::class, 'companies_users');
    }
    public function subCompanies():HasMany
    {
        return $this->hasMany(Company::class, 'sub_of');
    }
    // public function branches()
    // {
    //     return $this->hasMany(Branch::class);
    // }
    // public function getBranchesWithSectionsAttribute()
    // {
    //     $branches = [];
    //     foreach ($this->branches as $branch) {
    //         @count($branch->sections) == 0 ?: array_push($branches, $branch);
    //     }


    //     return $branches;
    // }

    public function exportableModelFields($modelName)
    {
        return $this->hasOne(CustomizedFieldsExportation::class)->where('model_name', $modelName);
    }

    public function getName(): string
    {
        return $this->name[App()->getLocale()];
    }
    public function logs()
    {
        return $this->hasMany(Log::class, 'company_id', 'id');
    }
    public function isCachingNow()
    {
        return $this->is_caching_now;
    }
    public function getMainFunctionalCurrency():string
    {
        return strtoupper($this->main_functional_currency) ?: __('Main Functional Currency');
    }
    public function getLabelingHeight()
    {
        return $this->label_height ?: 100 ;
    }
    public function getLabelingHorizontalPadding()
    {
        return $this->getLabelingWidth() *0 ;
    }
    public function getLabelingVerticalPadding()
    {
        return $this->getLabelingHeight() * 0.05 ;
    }
    public function getLabelingMarginBottom()
    {
        return .2;
    }

    public function getLabelingWidth()
    {
        // logo (clients)
        // qrcode
        // code

        return $this->label_width ?: 100 ;
    }
    public function getFirstLabelingLogo()
    {
        $logo = $this->labeling_logo_1 ;
        return $logo && file_exists('storage/'.$logo) ? asset('storage/'.$logo) : null ;
    }
    public function getSecondLabelingLogo()
    {
        $logo = $this->labeling_logo_2 ;
        return $logo && file_exists('storage/'.$logo) ? asset('storage/'.$logo) : null ;
    }
    public function getThirdLabelingLogo()
    {
        $logo = $this->labeling_logo_3 ;
        return $logo && file_exists('storage/'.$logo) ? asset('storage/'.$logo) : null ;
    }
    public function getStampLabelingLogo()
    {
        $logo = $this->labeling_stamp ;
        return $logo && file_exists('storage/'.$logo) ? asset('storage/'.$logo) : null ;
    }
    public function notificationSetting()
    {
        return $this->hasOne(NotificationSetting::class, 'company_id', 'id');
    }
    public function odooSetting()
    {
        return $this->hasOne(OdooSetting::class, 'company_id', 'id');
    }
    
    public function getCustomerComingDuesInvoicesNotificationsDays():int
    {
        $notificationSetting = $this->notificationSetting ;
        return  $notificationSetting  ? $notificationSetting->getCustomerComingDuesInvoicesNotificationsDays() : NotificationSetting::CUSTOMER_COMING_DUES_INVOICES_NOTIFICATIONS_DAYS ;
    }
    public function getSupplierComingDuesInvoicesNotificationsDays():int
    {
        $notificationSetting = $this->notificationSetting ;
        return  $notificationSetting  ? $notificationSetting->getSupplierComingDuesInvoicesNotificationsDays() : NotificationSetting::SUPPLIER_COMING_DUES_INVOICES_NOTIFICATIONS_DAYS ;
    }
    public function getCustomerPastDuesInvoicesNotificationsDays():int
    {
        $notificationSetting = $this->notificationSetting ;
        return  $notificationSetting  ? $notificationSetting->getCustomerPastDuesInvoicesNotificationsDays() : NotificationSetting::CUSTOMER_PAST_DUES_INVOICES_NOTIFICATIONS_DAYS ;
    }
    public function getSupplierPastDuesInvoicesNotificationsDays():int
    {
        $notificationSetting = $this->notificationSetting ;
        return  $notificationSetting  ? $notificationSetting->getSupplierPastDuesInvoicesNotificationsDays() : NotificationSetting::SUPPLIER_PAST_DUES_INVOICES_NOTIFICATIONS_DAYS ;
    }
    public function getChequesInSafeNotificationDays():int
    {
        $notificationSetting = $this->notificationSetting ;
        return  $notificationSetting  ? $notificationSetting->getChequesInSafeNotificationsDays() : NotificationSetting::CHEQUES_IN_SAFE_NOTIFICATIONS_DAYS ;
    }
    public function getComingReceivableChequesNotificationDays()
    {
        $notificationSetting = $this->notificationSetting ;
        return  $notificationSetting  ? $notificationSetting->getComingReceivableChequesNotificationsDays() : NotificationSetting::COMING_RECEIVABLE_CHEQUES_NOTIFICATIONS_DAYS ;
    }
    public function getComingPayableChequeNotificationDays()
    {
        $notificationSetting = $this->notificationSetting ;
        return  $notificationSetting  ? $notificationSetting->getComingPayableChequeNotificationDays() : NotificationSetting::COMING_RECEIVABLE_CHEQUES_NOTIFICATIONS_DAYS ;
    }
    public function letterOfGuaranteeIssuances()
    {
        return $this->hasMany(LetterOfGuaranteeIssuance::class, 'company_id', 'id')->orderByRaw("case status when 'cancelled' then 2 else 1 end , renewal_date asc ");
    }
    public function letterOfCreditIssuances()
    {
        return $this->hasMany(LetterOfCreditIssuance::class, 'company_id', 'id');
    }
    public function openingBalance()
    {
        return $this->hasOne(OpeningBalance::class, 'company_id');
    }
    public function customerOpeningBalance()
    {
        return $this->hasOne(CustomerOpeningBalance::class, 'company_id');
    }
    public function supplierOpeningBalance()
    {
        return $this->hasOne(SupplierOpeningBalance::class, 'company_id');
    }
   
    public function contracts():HasMany
    {
        return $this->hasMany(Contract::class, 'company_id', 'id');
    }
    public function lcSettlementInternalMoneyTransfers()
    {
        return $this->hasMany(LcSettlementInternalMoneyTransfer::class, 'company_id', 'id');
    }
    public function internalMoneyTransfers()
    {
        return $this->hasMany(InternalMoneyTransfer::class, 'company_id', 'id');
    }
    public function bankToBankInternalMoneyTransfers()
    {
        return $this->internalMoneyTransfers()->where('type', InternalMoneyTransfer::BANK_TO_BANK);
    }
    public function safeToBankInternalMoneyTransfers()
    {
        return $this->internalMoneyTransfers()->where('type', InternalMoneyTransfer::SAFE_TO_BANK);
    }
    public function bankToSafeInternalMoneyTransfers()
    {
        return $this->internalMoneyTransfers()->where('type', InternalMoneyTransfer::BANK_TO_SAFE);
    }
    public function safeToSafeInternalMoneyTransfers()
    {
        return $this->internalMoneyTransfers()->where('type', InternalMoneyTransfer::SAFE_TO_SAFE);
    }
    public function bankToLcSettlementInternalMoneyTransfers()
    {
        return $this->lcSettlementInternalMoneyTransfers()->where('type', LcSettlementInternalMoneyTransfer::BANK_TO_LETTER_OF_CREDIT);
    }
    
    
    
    
    
    
    public function buyOrSellCurrencies()
    {
        return $this->hasMany(BuyOrSellCurrency::class, 'company_id', 'id');
    }
    public function bankToBankBuyOrSellCurrencies()
    {
        return $this->buyOrSellCurrencies()->where('type', BuyOrSellCurrency::BANK_TO_BANK);
    }
    public function safeToBankBuyOrSellCurrencies()
    {
        return $this->buyOrSellCurrencies()->where('type', BuyOrSellCurrency::SAFE_TO_BANK);
    }
    public function bankToSafeBuyOrSellCurrencies()
    {
        return $this->buyOrSellCurrencies()->where('type', BuyOrSellCurrency::BANK_TO_SAFE);
    }
    public function safeToSafeBuyOrSellCurrencies()
    {
        return $this->buyOrSellCurrencies()->where('type', BuyOrSellCurrency::SAFE_TO_SAFE);
    }
    
    
    public function getHeadOfficeId()
    {
        return DB::table('branch')->where('company_id', $this->id)->orderByRaw('created_at asc')->first()->id;
    }
    public function cleanOverdrafts()
    {
        return $this->hasMany(CleanOverdraft::class, 'company_id', 'id');
    }
    public function fullySecuredOverdrafts()
    {
        return $this->hasMany(FullySecuredOverdraft::class, 'company_id', 'id');
    }
    public function overdraftAgainstCommercialPapers()
    {
        return $this->hasMany(OverdraftAgainstCommercialPaper::class, 'company_id', 'id');
    }
    public function overdraftAgainstAssignmentOfContracts()
    {
        return $this->hasMany(OverdraftAgainstAssignmentOfContract::class, 'company_id', 'id');
    }
    public function financialInstitutions()
    {
        return $this->hasMany(FinancialInstitution::class, 'company_id', 'id')
        ->join('banks', 'banks.id', '=', 'financial_institutions.bank_id')
        ->selectRaw('financial_institutions.* , banks.name_en as bank_name')
        ->orderBy('bank_name');
    }
    public function getNotificationsBasedOnType($type):Collection
    {
        return $this->notifications->where('data.tap_type', $type);
    }
    public function cashExpenses()
    {
        return $this->hasMany(CashExpense::class, 'company_id', 'id');
    }
    public function getCashExpenseCashPayments(?string $startDate = null, ?string $endDate = null):Collection
    {
        return $this->cashExpenses->where('type', CashExpense::CASH_PAYMENT)->whereNull('opening_balance_id')->filterByPaymentDate($startDate, $endDate)->sortByDesc('payment_date') ;
    }
    public function getCashExpenseOutgoingTransfer(?string $startDate = null, ?string $endDate = null):Collection
    {
        return $this->cashExpenses->where('type', CashExpense::OUTGOING_TRANSFER)->filterByPaymentDate($startDate, $endDate)->sortByDesc('payment_date') ;
    }
    public function getCashExpensePayableCheques(?string $startDate = null, ?string $endDate = null):Collection
    {
        return $this->cashExpenses->where('type', CashExpense::PAYABLE_CHEQUE)->filterByPaymentDate($startDate, $endDate)->sortByDesc('payment_date')->filter(function (CashExpense $cashExpense) {
            $payableCheque = $cashExpense->payableCheque ;
            return $payableCheque && in_array($payableCheque->getStatus(), [PayableCheque::PENDING,PayableCheque::PAID]) ;
        })->values();
    }
    
    /**
     * * For Money Payments
     */
      
    public function moneyPayments()
    {
        return $this->hasMany(MoneyPayment::class, 'company_id', 'id')->with(['settlements'])
        // ->where('company_id',getCurrentCompanyId())
        ;
    }
    
    public function getMoneyPaymentCashPayments(?string $startDate = null, ?string $endDate = null,$activeTab = null):HasMany
    {
        return $this->moneyPayments()->whereNull('advanced_opening_balance_id')->where('type', MoneyPayment::CASH_PAYMENT)
		->whereNull('opening_balance_id')->filterByDeliveryDate($startDate, $endDate)
		->when($activeTab == MoneyPayment::CASH_PAYMENT, function ($query) {
			$searchFieldName = Request('field');
			$value = Request('value');
			$from = Request('from');
			$to = Request('to');
			// $dateFieldName = ($searchFieldName === 'due_date') ? 'due_date' : 'delivery_date';
			$query->when($searchFieldName == 'partner_name',function() use ($query,$value){
				$query->whereHas('partner',function($query) use ($value){
					$query->where('name', 'like', '%'.$value.'%');
				});
			})
			->when($searchFieldName == 'delivery_date',function() use ($query,$from,$to){
				$query->whereBetween('delivery_date', [$from, $to]);
			})
			->when($searchFieldName == 'delivery_branch_name',function() use ($query,$value){
				$query->whereHas('cashPayment.deliveryBranch',function($query) use ($value){
					$query->where('name', 'like', '%'.$value.'%');
				});
			})
			->when($searchFieldName == 'currency',function() use ($query,$value){
				$query->where('currency', 'like', '%'.$value.'%');
			})
			->when($searchFieldName == 'payment_currency',function() use ($query,$value){
				$query->where('payment_currency', 'like', '%'.$value.'%');
			})
			->when($searchFieldName == 'receipt_number',function() use ($query,$value){
				$query->whereHas('cashPayment',function($query) use ($value){
					$query->where('receipt_number', 'like', '%'.$value.'%');
				});
			});
		})
		->with([
			'partner:id,name',
			'cashPayment.deliveryBranch:id,name',
		
		])
		->orderByDesc('delivery_date');
		

    }
	// public function getMoneyPaymentCashPayments(?string $startDate = null, ?string $endDate = null):Collection
    // {
    //     return $this->moneyPayments->whereNull('advanced_opening_balance_id')->where('type', MoneyPayment::CASH_PAYMENT)->whereNull('opening_balance_id')->filterByDeliveryDate($startDate, $endDate)->sortByDesc('delivery_date') ;
    // }
	
    public function getMoneyPaymentOutgoingTransfer(?string $startDate = null, ?string $endDate = null,$activeTab = null):HasMany
    {
        return $this->moneyPayments()->whereNull('advanced_opening_balance_id')->where('type', MoneyPayment::OUTGOING_TRANSFER)->filterByDeliveryDate($startDate, $endDate)
		->when($activeTab == MoneyPayment::OUTGOING_TRANSFER, function ($query) {
			$searchFieldName = Request('field');
			$value = Request('value');
			$from = Request('from');
			$to = Request('to');
			$query->when($searchFieldName == 'partner_name',function() use ($query,$value){
				$query->whereHas('partner',function($query) use ($value){
					$query->where('name', 'like', '%'.$value.'%');
				});
			})
			->when($searchFieldName == 'delivery_date',function() use ($query,$from,$to){
				$query->whereBetween('delivery_date', [$from, $to]);
			})
			->when($searchFieldName == 'payment_bank_name',function() use ($query,$value){
				$query->whereHas('outgoingTransfer.deliveryBank.bank',function($query) use ($value){
					$query->where(function($query) use ($value){
						$query->where('name_en', 'like', '%'.$value.'%')
						->orWhere('name_ar', 'like', '%'.$value.'%');
					});
				});
			})
			->when($searchFieldName == 'currency',function() use ($query,$value){
				$query->where('currency', 'like', '%'.$value.'%');
			})
			->when($searchFieldName == 'payment_currency',function() use ($query,$value){
				$query->where('payment_currency', 'like', '%'.$value.'%');
			})
			->when($searchFieldName == 'account_number',function() use ($query,$value){
				$query->whereHas('outgoingTransfer',function($query) use ($value){
					$query->where('account_number', 'like', '%'.$value.'%');
				});
			});
			
		}) 
		->with([
			'partner:id,name',
			'outgoingTransfer.deliveryBank.bank:id,view_name',
		])
		->orderByDesc('delivery_date') ;
    }
	// public function getMoneyPaymentOutgoingTransfer(?string $startDate = null, ?string $endDate = null):Collection
    // {
    //     return $this->moneyPayments->whereNull('advanced_opening_balance_id')->where('type', MoneyPayment::OUTGOING_TRANSFER)->filterByDeliveryDate($startDate, $endDate)->sortByDesc('delivery_date') ;
    // }
    // public function getMoneyPaymentPayableCheques(?string $startDate = null, ?string $endDate = null):Collection
    // {
    //     return $this->moneyPayments->whereNull('advanced_opening_balance_id')->where('type', MoneyPayment::PAYABLE_CHEQUE)->filterByDeliveryDate($startDate, $endDate)->filter(function (MoneyPayment $moneyPayment) {
    //         $payableCheque = $moneyPayment->payableCheque ;
    //         return $payableCheque && in_array($payableCheque->getStatus(), [PayableCheque::PENDING,PayableCheque::PAID]) ;
    //     })->sortByDesc('delivery_date')->values();
    // }
	public function getMoneyPaymentPayableCheques(?string $startDate = null, ?string $endDate = null,$activeTab = null):HasMany
{
    return $this->moneyPayments() // لاحظ الأقواس هنا للتعامل مع قاعدة البيانات مباشرة
        ->whereNull('advanced_opening_balance_id')
        ->where('type', MoneyPayment::PAYABLE_CHEQUE)
        ->filterByDeliveryDate($startDate, $endDate) // تأكد أن هذا الـ Scope يعمل على Query Builder
        ->whereHas('payableCheque', function ($query) {
            $query->whereIn('status', [PayableCheque::PENDING, PayableCheque::PAID]);
        })
		->when($activeTab == MoneyPayment::PAYABLE_CHEQUE, function ($query) {
			$searchFieldName = Request('field');
			$value = Request('value');
			$from = Request('from');
			$to = Request('to');
			$query->when($searchFieldName == 'partner_name',function() use ($query,$value){
				$query->whereHas('partner',function($query) use ($value){
					$query->where('name', 'like', '%'.$value.'%');
				});
			})
			->when($searchFieldName == 'delivery_date',function() use ($query,$from,$to){
				$query->whereBetween('delivery_date', [$from, $to]);
			})
			->when($searchFieldName == 'due_date',function() use ($query,$from,$to){
				$query->whereHas('payableCheque',function($query) use ($from,$to){
					$query->whereBetween('due_date', [$from, $to]);
				});
			})
			->when($searchFieldName == 'payment_bank_name',function() use ($query,$value){
				$query->whereHas('payableCheque.deliveryBank.bank',function($query) use ($value){
					$query->where(function($query) use ($value){
						$query->where('name_en', 'like', '%'.$value.'%')
						->orWhere('name_ar', 'like', '%'.$value.'%');
					});
				});
			})
			->when($searchFieldName == 'cheque_number',function() use ($query,$value){
				$query->whereHas('payableCheque',function($query) use ($value){
					$query->where('cheque_number', 'like', '%'.$value.'%');
				});
			})
			// ->when($searchFieldName == 'cheque_status',function() use ($query,$value){
			// 	$query->whereHas('payableCheque',function($query) use ($value){
			// 		$query->where('status', 'like', '%'.$value.'%');
			// 	});
			// })
			->when($searchFieldName == 'currency',function() use ($query,$value){
				$query->where('currency', 'like', '%'.$value.'%');
			})
			->when($searchFieldName == 'payment_currency',function() use ($query,$value){
				$query->where('payment_currency', 'like', '%'.$value.'%');
			})
			->when($searchFieldName == 'receipt_number',function() use ($query,$value){
				$query->whereHas('cashPayment',function($query) use ($value){
					$query->where('receipt_number', 'like', '%'.$value.'%');
				});
			});
		})
		->with([
			'partner:id,name',
			'payableCheque.deliveryBank.bank:id,view_name',
			// 'payableCheque.accountType',
		])
		// ->with(['payableCheque.deliveryBank', 'payableCheque.accountType', 'partner'])
        ->orderByDesc('delivery_date');
}

    
    public function mediumTermLoans()
    {
        return $this->hasMany(MediumTermLoan::class, 'company_id', 'id');
    }
    public function systems()
    {
        return $this->hasMany(CompanySystem::class, 'company_id', 'id');
    }
    public function hasSystem(string $systemName)
    {
        return in_array($systemName, $this->getSystemsNames());
    }
    public function getSystemsNames():array
    {
        return $this->systems->pluck('system_name')->toArray();
    }
    public function hasIncomeStatementPlanning():bool
    {
        return in_array(INCOME_STATEMENT_PLANNING, $this->getSystemsNames())
        || (auth()->check() && auth()->user()->isSuperAdmin());
        // return $this->system == 'cash-vero' || $this->system == 'both' || (auth()->check() && auth()->user()->isSuperAdmin());
    }
    public function hasNonBanking():bool
    {
        return in_array(NON_BANKING_SERVICE, $this->getSystemsNames())
        || (auth()->check() && auth()->user()->isSuperAdmin());
    }
    public function hasPropertyManagement():bool
    {
        return in_array(PROPERTY_MANAGEMENT, $this->getSystemsNames())
        || (auth()->check() && auth()->user()->isSuperAdmin());
    }
	public function hasTrading():bool
    {
        return in_array(TRADING, $this->getSystemsNames())
        || (auth()->check() && auth()->user()->isSuperAdmin());
    }
    public function hasVero():bool
    {
        return in_array(VERO, $this->getSystemsNames())
        || (auth()->check() && auth()->user()->isSuperAdmin());
    }public function hasCashvero():bool
    {
        // return true;
        return in_array(CASH_VERO, $this->getSystemsNames())
        || (auth()->check() && auth()->user()->isSuperAdmin());
    }
    public function syncPermissionForAllUser(array $systemsToPreserve, array $newSystemsToBeAdded):void
    {
        $permissionsNamesToBePreserve = array_column(HAuth::getPermissions($systemsToPreserve), 'name');
        $permissionsNamesToBeAdded = array_column(HAuth::getPermissions($newSystemsToBeAdded), 'name');
        foreach ($this->users as $user) {
            $currentUserPermissions = array_values(array_intersect($user->permissions->pluck('name')->toArray(), $permissionsNamesToBePreserve));
            $permissions = array_merge($currentUserPermissions, $permissionsNamesToBeAdded);
            $user->syncPermissions($permissions);
        }
    }
    public function partners()
    {
        return $this->hasMany(Partner::class, 'company_id', 'id')->orderBy('name');
    }
    public function customers()
    {
        return $this->hasMany(Partner::class, 'company_id', 'id')->where('is_customer', 1)->orderBy('name');
    }
    public function suppliers()
    {
        return $this->hasMany(Partner::class, 'company_id', 'id')->where('is_supplier', 1)->orderBy('name');
    }
    public function employees()
    {
        return $this->hasMany(Partner::class, 'company_id', 'id')->where('is_employee', 1)->orderBy('name');
    }
    public function taxes()
    {
        return $this->hasMany(Partner::class, 'company_id', 'id')->where('is_tax', 1)->orderBy('name');
    }
    public function shareholders()
    {
        return $this->hasMany(Partner::class, 'company_id', 'id')->where('is_shareholder', 1)->orderBy('name');
    }
    public function subsidiaryCompanies()
    {
        return $this->hasMany(Partner::class, 'company_id', 'id')->where('is_subsidiary_company', 1)->orderBy('name');
    }
    public function otherPartners()
    {
        return $this->hasMany(Partner::class, 'company_id', 'id')->where('is_other_partner', 1)->orderBy('name');
    }
    public function businessSectors()
    {
        return $this->hasMany(CashVeroBusinessSector::class, 'company_id', 'id')->orderBy('name');
    }
    public function salesChannels()
    {
        return $this->hasMany(CashVeroSalesChannel::class, 'company_id', 'id')->orderBy('name');
    }
    public function salesPersons()
    {
        return $this->hasMany(CashVeroSalesPerson::class, 'company_id', 'id')->orderBy('name');
    }
    public function businessUnits()
    {
        return $this->hasMany(CashVeroBusinessUnit::class, 'company_id', 'id')->orderBy('name');
    }
    public function branches()
    {
        return $this->hasMany(CashVeroBranch::class, 'company_id', 'id')->orderBy('name');
    }
    public function financialInstitutionsBanks():Collection
    {
        return $this->financialInstitutions->where('type', 'bank') ;
    }
    public function financialInstitutionsLeasingCompanies():Collection
    {
        return $this->financialInstitutions->where('type', 'leasing_companies') ;
    }
    public function financialInstitutionsFactoringCompanies():Collection
    {
        return $this->financialInstitutions->where('type', 'factoring_companies') ;
    }
    public function financialInstitutionsMortgageCompanies():Collection
    {
        return $this->financialInstitutions->where('type', 'mortgage_companies') ;
    }
	public function getReceivedChequesInSafe(?string $startDate = null, ?string $endDate = null, $activeTab = null):HasMany
    {
	
        return $this->moneyReceived()->whereNull('advanced_opening_balance_id')
		
		->when($activeTab == MoneyReceived::CHEQUE, function ( $query) {
			$searchFieldName = Request('field');
			$value = Request('value');
			$from = Request('from');
			$to = Request('to');
			$query->when($searchFieldName == 'partner_name',function() use ($query,$value){
				$query->whereHas('partner',function($query) use ($value){
					$query->where('name', 'like', '%'.$value.'%');
				});
			});
			$query->when($searchFieldName == 'receiving_date',function() use ($query,$from,$to){
				$query->whereBetween('receiving_date', [$from, $to]);
			});
			$query->when($searchFieldName == 'cheque_number',function() use ($query,$value){
				$query->whereHas('cheque',function($query) use ($value){
					$query->where('cheque_number', 'like', '%'.$value.'%');
				});
			});
			$query->when($searchFieldName == 'drawee_bank_name',function() use ($query,$value){
				$query->whereHas('cheque.draweeBank',function($query) use ($value){
					$query->where(function($query) use ($value){
						$query->where('name_en', 'like', '%'.$value.'%')
						->orWhere('name_ar', 'like', '%'.$value.'%');
					});
				});
			});
			$query->when($searchFieldName == 'due_date',function() use ($query,$from,$to){
				$query->whereHas('cheque',function($query) use ($from,$to){
					$query->whereBetween('due_date', [$from, $to]);
				});
			});
			// $query->when($searchFieldName == 'cheque_status',function() use ($query,$value){
			// 	$query->whereHas('cheque',function($query) use ($value){
			// 		$query->where('status', $value);
			// 	});
			// });
			$query->when($searchFieldName == 'received_amount',function() use ($query,$value){
				$query->where('received_amount', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'currency',function() use ($query,$value){
				$query->where('currency', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'receiving_currency',function() use ($query,$value){
				$query->where('receiving_currency', 'like', '%'.$value.'%');
			});
		
		})
		->whereHas('cheque',function($query) {
			$query->where('status', Cheque::IN_SAFE);
		})
		->filterByReceivingDate($startDate, $endDate)
		->orderByDesc('receiving_date')
		->with([
			'partner:id,name',
			'cheque.draweeBank:id,name_en,name_ar',
			'cheque.accountType:id,name_en,name_ar',
			'cheque.moneyReceived:id,receiving_date',
		])
		;
		// ->filter(function (MoneyReceived $moneyReceived) {
        //     $cheque = $moneyReceived->cheque ;
        //     return $cheque && in_array($cheque->getStatus(), [Cheque::IN_SAFE]) ;
        // })   
    }
	
    // public function getReceivedChequesInSafe(?string $startDate = null, ?string $endDate = null):Collection
    // {
    //     return $this->moneyReceived->whereNull('advanced_opening_balance_id')->where('type', MoneyReceived::CHEQUE)->filterByReceivingDate($startDate, $endDate)->filter(function (MoneyReceived $moneyReceived) {
    //         $cheque = $moneyReceived->cheque ;
    //         return $cheque && in_array($cheque->getStatus(), [Cheque::IN_SAFE]) ;
    //     })->values();
        
    // }
    /**
     * * هي الشيكات اللي اترفضت ورجعتها الخزنة تاني وليكن مثلا بسبب ان حساب العميل مفيهوش فلوس حاليا
     */
    public function getReceivedRejectedChequesInSafe(?string $startDate = null, ?string $endDate = null,$activeTab = null):HasMany
    {
        return $this->moneyReceived()->whereNull('advanced_opening_balance_id')->where('type', MoneyReceived::CHEQUE)
		->whereHas('cheque',function($query) {
			$query->where('status', Cheque::REJECTED);
		})
		->when($activeTab == MoneyReceived::CHEQUE_REJECTED, function ($query) {
			$searchFieldName = Request('field');
			$value = Request('value');
			$from = Request('from');
			$to = Request('to');
			$query->when($searchFieldName == 'partner_name',function() use ($query,$value){
				$query->whereHas('partner',function($query) use ($value){
					$query->where('name', 'like', '%'.$value.'%');
				});
			});
			$query->when($searchFieldName == 'receiving_date',function() use ($query,$from,$to){
				$query->whereBetween('receiving_date', [$from, $to]);
			});
			$query->when($searchFieldName == 'cheque_number',function() use ($query,$value){
				$query->whereHas('cheque',function($query) use ($value){
					$query->where('cheque_number', 'like', '%'.$value.'%');
				});
			});
			$query->when($searchFieldName == 'drawee_bank_name',function() use ($query,$value){
				$query->whereHas('cheque.draweeBank',function($query) use ($value){
					$query->where(function($query) use ($value){
						$query->where('name_en', 'like', '%'.$value.'%')
						->orWhere('name_ar', 'like', '%'.$value.'%');
					});
				});
			});
			$query->when($searchFieldName == 'due_date',function() use ($query,$from,$to){
				$query->whereHas('cheque',function($query) use ($from,$to){
					$query->whereBetween('due_date', [$from, $to]);
				});
			});
			$query->when($searchFieldName == 'currency',function() use ($query,$value){
				$query->where('currency', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'receiving_currency',function() use ($query,$value){
				$query->where('receiving_currency', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'account_number',function() use ($query,$value){
				$query->whereHas('cheque',function($query) use ($value){
					$query->where('account_number', 'like', '%'.$value.'%');
				});
			});
		})
		->filterByReceivingDate($startDate, $endDate)
		->orderByDesc('receiving_date')
		->with(['partner:id,name', 'cheque.draweeBank:id,name_en,name_ar', 'cheque.accountType:id,name_en,name_ar', 'cheque.moneyReceived:id,receiving_date']);
    }
    
    public function getCollectedCheques(?string $startDate = null, ?string $endDate = null,$activeTab = null):HasMany
    {
        return $this->moneyReceived()->whereNull('advanced_opening_balance_id')->where('type', MoneyReceived::CHEQUE)
            ->filterByReceivingDate($startDate, $endDate)
           ->whereHas('cheque',function($query) use ($activeTab){
			$query->where('status', Cheque::COLLECTED);
		})
		->when($activeTab == MoneyReceived::CHEQUE_COLLECTED, function ($query) {
			$searchFieldName = Request('field');
			$value = Request('value');
			$from = Request('from');
			$to = Request('to');
			$query->when($searchFieldName == 'partner_name',function() use ($query,$value){
				$query->whereHas('partner',function($query) use ($value){
					$query->where('name', 'like', '%'.$value.'%');
				});
			});
			$query->when($searchFieldName == 'receiving_date',function() use ($query,$from,$to){
				$query->whereBetween('receiving_date', [$from, $to]);
			});
			$query->when($searchFieldName == 'cheque_number',function() use ($query,$value){
				$query->whereHas('cheque',function($query) use ($value){
					$query->where('cheque_number', 'like', '%'.$value.'%');
				});
			});
			$query->when($searchFieldName == 'drawee_bank_name',function() use ($query,$value){
				$query->whereHas('cheque.draweeBank',function($query) use ($value){
					$query->where(function($query) use ($value){
						$query->where('name_en', 'like', '%'.$value.'%')
						->orWhere('name_ar', 'like', '%'.$value.'%');
					});
				});
			});
			$query->when($searchFieldName == 'due_date',function() use ($query,$from,$to){
				$query->whereHas('cheque',function($query) use ($from,$to){
					$query->whereBetween('due_date', [$from, $to]);
				});
			});
			$query->when($searchFieldName == 'currency',function() use ($query,$value){
				$query->where('currency', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'receiving_currency',function() use ($query,$value){
				$query->where('receiving_currency', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'account_number',function() use ($query,$value){
				$query->whereHas('cheque',function($query) use ($value){
					$query->where('account_number', 'like', '%'.$value.'%');
				});
			});
			
		})
		->orderByDesc('receiving_date')
		->with(['partner:id,name', 'cheque.draweeBank:id,name_en,name_ar', 'cheque.accountType:id,name_en,name_ar', 'cheque.moneyReceived:id,receiving_date']);
    }
    
    public function getReceivedChequesUnderCollection(?string $startDate = null, ?string $endDate = null,$activeTab = null):HasMany
    {
        return $this->moneyReceived()->whereNull('advanced_opening_balance_id')->where('type', MoneyReceived::CHEQUE)
            ->filterByReceivingDate($startDate, $endDate)
           ->whereHas('cheque',function($query) use ($activeTab){
			$query->where('status', Cheque::UNDER_COLLECTION);
		})
		->when($activeTab == MoneyReceived::CHEQUE_UNDER_COLLECTION, function ($query) {
			$searchFieldName = Request('field');
			$value = Request('value');
			$from = Request('from');
			$to = Request('to');
			$query->when($searchFieldName == 'partner_name',function() use ($query,$value){
				$query->whereHas('partner',function($query) use ($value){
					$query->where('name', 'like', '%'.$value.'%');
				});
			});
			$query->when($searchFieldName == 'receiving_date',function() use ($query,$from,$to){
				$query->whereBetween('receiving_date', [$from, $to]);
			});
			$query->when($searchFieldName == 'drawee_bank_name',function() use ($query,$value){
				$query->whereHas('cheque.draweeBank',function($query) use ($value){
					$query->where(function($query) use ($value){
						$query->where('name_en', 'like', '%'.$value.'%')
						->orWhere('name_ar', 'like', '%'.$value.'%');
					});
				});
			});
			$query->when($searchFieldName == 'due_date',function() use ($query,$from,$to){
				$query->whereHas('cheque',function($query) use ($from,$to){
					$query->whereBetween('due_date', [$from, $to]);
				});
			});
			$query->when($searchFieldName == 'cheque_number',function() use ($query,$value){
				$query->whereHas('cheque',function($query) use ($value){
					$query->where('cheque_number', 'like', '%'.$value.'%');
				});
			});
			$query->when($searchFieldName == 'currency',function() use ($query,$value){
				$query->where('currency', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'receiving_currency',function() use ($query,$value){
				$query->where('receiving_currency', 'like', '%'.$value.'%');
			});
		})
		->filterByReceivingDate($startDate, $endDate)
		->orderByDesc('receiving_date')
            ->with(['partner:id,name', 'cheque.draweeBank:id,name_en,name_ar', 'cheque.accountType:id,name_en,name_ar', 'cheque.moneyReceived:id,receiving_date']);
			
			// ->filter(function (MoneyReceived $moneyReceived) {
            //     $cheque = $moneyReceived->cheque;
            //     return $cheque && in_array($cheque->getStatus(), [Cheque::UNDER_COLLECTION]);
            // })->values();
			// // ->with(['partner:id,name', 'cheque.draweeBank:id,name_en,name_ar', 'cheque.accountType:id,name_en,name_ar', 'cheque.moneyReceived:id,receiving_date'])
    }
    public function getReceivedCashesInSafe(?string $startDate = null, ?string $endDate = null , $activeTab = null):HasMany
    {
        return $this->moneyReceived()->whereNull('advanced_opening_balance_id')->whereNull('advanced_opening_balance_id')->where('type', MoneyReceived::CASH_IN_SAFE)->whereNull('opening_balance_id')
		->when($activeTab == MoneyReceived::CASH_IN_SAFE, function ($query) {
			$searchFieldName = Request('field');
			$value = Request('value');
			$from = Request('from');
			$to = Request('to');
			
			$query->when($searchFieldName == 'partner_name',function() use ($query,$value){
				$query->whereHas('partner',function($query) use ($value){
					$query->where('name', 'like', '%'.$value.'%');
				});
			})
			->when($searchFieldName == 'receiving_date',function() use ($query,$from,$to){
				$query->whereBetween('receiving_date', [$from, $to]);
			});
			$query->when($searchFieldName == 'receiving_branch_name',function() use ($query,$value){
				$query->whereHas('cashInSafe.receivingBranch',function($query) use ($value){
					$query->where('name', 'like', '%'.$value.'%');
				});
			});
			$query->when($searchFieldName == 'received_amount',function() use ($query,$value){
				$query->where('received_amount', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'currency',function() use ($query,$value){
				$query->where('currency', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'receiving_currency',function() use ($query,$value){
				$query->where('receiving_currency', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'receipt_number',function() use ($query,$value){
				$query->whereHas('cashInSafe',function($query) use ($value){
					$query->where('receipt_number', 'like', '%'.$value.'%');
				});
			});
		})
		->filterByReceivingDate($startDate, $endDate)
		->orderByDesc('receiving_date')
		->with([
			'partner:id,name',
			'cashInSafe.receivingBranch:id,name'
		])
		;
    }
	// public function getReceivedCashesInSafe(?string $startDate = null, ?string $endDate = null):Collection
    // {
    //     return $this->moneyReceived->whereNull('advanced_opening_balance_id')->whereNull('advanced_opening_balance_id')->where('type', MoneyReceived::CASH_IN_SAFE)->whereNull('opening_balance_id')->filterByReceivingDate($startDate, $endDate) ;
    // }
    public function getReceivedCashesInBank(?string $startDate = null, ?string $endDate = null, $activeTab = null):HasMany
    {
        return $this->moneyReceived()->whereNull('advanced_opening_balance_id')->where('type', MoneyReceived::CASH_IN_BANK)
		->when($activeTab == MoneyReceived::CASH_IN_BANK, function ($query) {
			$searchFieldName = Request('field');
			$value = Request('value');
			$from = Request('from');
			$to = Request('to');
			
			$query->when($searchFieldName == 'partner_name',function() use ($query,$value){
				$query->whereHas('partner',function($query) use ($value){
					$query->where('name', 'like', '%'.$value.'%');
				});
			})
			->when($searchFieldName == 'receiving_date',function() use ($query,$from,$to){
				$query->whereBetween('receiving_date', [$from, $to]);
			});
			$query->when($searchFieldName == 'receiving_bank_name',function() use ($query,$value){
				$query->whereHas('cashInBank.receivingBank.bank',function($query) use ($value){
					$query->where(function($query) use ($value){
						$query->where('name_en', 'like', '%'.$value.'%')
						->orWhere('name_ar', 'like', '%'.$value.'%');
					});
				});
			});
			$query->when($searchFieldName == 'received_amount',function() use ($query,$value){
				$query->where('received_amount', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'currency',function() use ($query,$value){
				$query->where('currency', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'receiving_currency',function() use ($query,$value){
				$query->where('receiving_currency', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'account_number',function() use ($query,$value){
				$query->whereHas('cashInBank',function($query) use ($value){
					$query->where('account_number', 'like', '%'.$value.'%');
				});
			});
		})
		->filterByReceivingDate($startDate, $endDate)
		->orderByDesc('receiving_date')
		->with([
			'partner:id,name',
			'cashInBank.receivingBank.bank:id,view_name',
			'cashInBank.accountType:id,name_en,name_ar',
		])
		;
    }
    public function getReceivedTransfer(?string $startDate = null, ?string $endDate = null, $activeTab = null):HasMany
    {
        return $this->moneyReceived()->whereNull('advanced_opening_balance_id')->where('type', MoneyReceived::INCOMING_TRANSFER)
		->when($activeTab == MoneyReceived::INCOMING_TRANSFER, function ($query) {
			$searchFieldName = Request('field');
			$value = Request('value');
			$from = Request('from');
			$to = Request('to');
			$query->when($searchFieldName == 'partner_name',function() use ($query,$value){
				$query->whereHas('partner',function($query) use ($value){
					$query->where('name', 'like', '%'.$value.'%');
				});
			})
			->when($searchFieldName == 'receiving_date',function() use ($query,$from,$to){
				$query->whereBetween('receiving_date', [$from, $to]);
			});
			$query->when($searchFieldName == 'receiving_bank_name',function() use ($query,$value){
				$query->whereHas('incomingTransfer.receivingBank.bank',function($query) use ($value){
					$query->where(function($query) use ($value){
						$query->where('name_en', 'like', '%'.$value.'%')
						->orWhere('name_ar', 'like', '%'.$value.'%');
					});
				});
			});
			$query->when($searchFieldName == 'received_amount',function() use ($query,$value){
				$query->where('received_amount', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'currency',function() use ($query,$value){
				$query->where('currency', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'receiving_currency',function() use ($query,$value){
				$query->where('receiving_currency', 'like', '%'.$value.'%');
			});
			$query->when($searchFieldName == 'account_number',function() use ($query,$value){
				$query->whereHas('incomingTransfer',function($query) use ($value){
					$query->where('account_number', 'like', '%'.$value.'%');
				});
			});
		})
		->filterByReceivingDate($startDate, $endDate)
		->orderByDesc('receiving_date')
		->with([
			'partner:id,name',
			'incomingTransfer.receivingBank.bank:id,view_name',
			'incomingTransfer.accountType:id,name_en,name_ar',
		])
		;
    }
	// public function getReceivedTransfer(?string $startDate = null, ?string $endDate = null):Collection
    // {
    //     return $this->moneyReceived->whereNull('advanced_opening_balance_id')->where('type', MoneyReceived::INCOMING_TRANSFER)->filterByReceivingDate($startDate, $endDate) ;
    // }
    public function moneyReceived()
    {
        return $this->hasMany(MoneyReceived::class, 'company_id', 'id')->with(['settlements']);
        // ->where('company_id',getCurrentCompanyId())
        ;
    }
    public function deductions()
    {
        return $this->hasMany(Deduction::class, 'company_id', 'id');
    }
    
    public function getOdooDBUrl()
    {
        return $this->odoo_db_url;
    }
    public function getOdooDBName()
    {
        return $this->odoo_db_name;
    }
   
    public function hasOdooIntegrationCredentials():bool
    {
		$user =auth()->user();
		/**
		 * @var User $user
		 */
        return $this->getOdooDBUrl() && $this->getOdooDBName() && $user->getOdooDBUserName() && $user->getOdooDBPassword();
    }
    
    public function lastUploadFileNames()
    {
        return $this->hasMany(LastUploadFileName::class, 'company_id');
    }
    public function addNewFileUploadFileNameFor(string $fileName, string $modelName):LastUploadFileName
    {
        return $this->lastUploadFileNames()->create([
            'name'=>$fileName,
            'status'=>LastUploadFileName::CURRENT,
            'company_id'=>$this->id,
            'model_name'=>$modelName,
        ]);
    }
    public function updateLastUploadFileNameStatus(string $modelName)
    {
        return $this->lastUploadFileNames->where('status', LastUploadFileName::CURRENT)
        ->where('model_name', $modelName)
        ->last()->update([
            'status'=>LastUploadFileName::SUCCESS
        ]);
        
    }
    public function getCurrentLastFileNameForModel(string $modelName)
    {
        $lastFile = $this->lastUploadFileNames->where('status', LastUploadFileName::CURRENT)
        ->where('model_name', $modelName)
        ->last();
        return $lastFile ? $lastFile->name : __('N/A');
    }
    public function getSuccessLastFileNameForModel(string $modelName)
    {
        $lastFile = $this->lastUploadFileNames->where('status', LastUploadFileName::SUCCESS)
        ->where('model_name', $modelName)
        ->last();
        return $lastFile ? $lastFile->name : __('N/A');
    }
    public function hasLastSuccessfullyUploadFileForModel(string $modelName)
    {
        return  $this->lastUploadFileNames()->where('status', LastUploadFileName::SUCCESS)
        ->where('model_name', $modelName)
        ->exists();
    }
    public function hasLastCurrentUploadFileForModel(string $modelName)
    {
        return  $this->lastUploadFileNames()->where('status', LastUploadFileName::CURRENT)
        ->where('model_name', $modelName)
        ->exists();
    }
    
    public function deleteAllOldLastUploadFileNamesFor(string $modelName, string $status):void
    {
        $this->lastUploadFileNames->where('model_name', $modelName)
        ->where('status', $status)
        ->each(function ($lastUploadFileName) {
            $lastUploadFileName->delete();
        });
    }

    public function leasingCategories()
    {
        return $this->hasMany(LeasingCategory::class, 'company_id', 'id');
    }
    public function getLeasingCategoriesFormattedForSelect():array
    {
        return $this->leasingCategories->pluck('title', 'id')->map(function ($title, $id) {
            return [
                'title'=>$title ,
                'id'=>$id
            ];
        })->values()->toArray();
        // return (new Select2Formatter)->formatForAssocArr($this->leasingCategories->pluck('title','id')->toArray());
    }
    public function existingBranches()
    {
        $isNonBanking = hasMiddleware('isNonBankingService') ;
        if ($isNonBanking) {
            return $this->hasMany(\App\Models\NonBankingService\ExistingBranch::class, 'company_id', 'id');
        }
       
        
    }
    public function getExistingBranchesFormattedForSelect():array
    {
        return (new Select2Formatter)->formatForAssocArr($this->existingBranches->where('is_active', 1)->pluck('title', 'id')->toArray());
    }
    
    public function microfinanceProducts()
    {
        return $this->hasMany(MicrofinanceProduct::class, 'company_id', 'id');
    }
    public function getMicrofinanceProductsFormattedForSelect():array
    {
    	return (new Select2Formatter)->formatForAssocArr($this->microfinanceProducts->where('is_active',1)->pluck('title','id')->toArray());
    }
    public function getActiveMicrofinanceProducts()
    {
        return $this->microfinanceProducts->where('is_active', 1) ;
    }
    public function consumerfinanceProducts()
    {
        return $this->hasMany(ConsumerfinanceProduct::class, 'company_id', 'id');
    }
    public function activeConsumerfinanceProducts()
    {
        return $this->hasMany(ConsumerfinanceProduct::class, 'company_id', 'id')->where('is_active', 1);
    }
    public function getConsumerfinanceProductsFormattedForSelect():array
    {
        return (new Select2Formatter)->formatForAssocArr($this->consumerfinanceProducts->pluck('title', 'id')->toArray());
    }
    public function studies():HasMany
    {
        $isNonBanking = hasMiddleware('isNonBankingService') ;
        $isPropertyManagement = hasMiddleware('isPropertyManagement') ;
        $isTrading = hasMiddleware('isTrading') ;
        if ($isNonBanking) {
            return $this->hasMany(\App\Models\NonBankingService\Study::class, 'company_id', 'id');
        }
        if ($isPropertyManagement) {
            return $this->hasMany(\App\Models\PropertyManagement\Study::class, 'company_id', 'id');
        }
		if ($isTrading) {
            return $this->hasMany(\App\Models\Trading\Study::class, 'company_id', 'id');
        }
    }
    public function financialPlanningStudies():HasMany
    {
        return $this->hasMany(\App\Models\FinancialPlanning\Study::class, 'company_id', 'id');
    }
    public function getMainPlanningBasesForSelector():array
    {
        $mainColumns = ['customer_name'=>__('Customer Name') , 'sales_channel'=>__('Sales Channel') , 'business_sector'=>__('Business Sector') , 'branch'=>__('Branch')  ,'principle'=>__('Principle') , 'zone'=>__('Zone') ,'sales_person'=>__('Sales Person')];
        
        $hasVero = $this->hasVero() ;
        $mainPlanning = [];
        if ($hasVero) {
            $hasProductItem = hasExport(['product_item'], $this->id);
            if ($hasProductItem) {
                $mainPlanning[] = [
                    'value'=>'product_item',
                    'title'=>__('Product / Service')
                ];
            } else {
                $mainPlanning[] = [
                    'value'=>'product_or_service',
                    'title'=>__('Product / Service')
                ];
            }
            foreach ($mainColumns as $columnName => $title) {
                if (hasExport([$columnName], $this->id)) {
                    $mainPlanning[] = [
                        'title'=>$title,
                        'value'=>$columnName
                    ];
                }
            }
            
            
            
        } else {
            foreach (array_merge(['product_or_service'=>__('Product / Service')], $mainColumns) as $columnName=>$title) {
                $mainPlanning[] = [
                    'title'=>$title,
                    'value'=>$columnName
                ];
            }
        }
        return $mainPlanning;
    }

    public function departments()
    {
        $isNonBanking = hasMiddleware('isNonBankingService') ;
        $isPropertyManagement = hasMiddleware('isPropertyManagement') ;
		$isTrading = hasMiddleware('isTrading') ;
        if ($isNonBanking) {
            return $this->hasMany(\App\Models\NonBankingService\Department::class, 'company_id', 'id');
        }
        if ($isPropertyManagement) {
            return $this->hasMany(\App\Models\PropertyManagement\Department::class, 'company_id', 'id');
        }
		if ($isTrading) {
            return $this->hasMany(\App\Models\Trading\Department::class, 'company_id', 'id');
        }
    }
    public function generalDepartments()
    {
        $isNonBanking = hasMiddleware('isNonBankingService') ;
        $isPropertyManagement = hasMiddleware('isPropertyManagement') ;
		$isTrading = hasMiddleware('isTrading') ;
        if ($isNonBanking) {
            return $this->hasMany(\App\Models\NonBankingService\Department::class, 'company_id', 'id')->where('type', \App\Models\NonBankingService\Department::GENERAL);
        }
        if ($isPropertyManagement) {
            return $this->hasMany(\App\Models\PropertyManagement\Department::class, 'company_id', 'id')->where('type', \App\Models\PropertyManagement\Department::GENERAL);
        }
		if ($isTrading) {
            return $this->hasMany(\App\Models\Trading\Department::class, 'company_id', 'id')->where('type', \App\Models\Trading\Department::GENERAL);
        }
    }
    
    public function microfinanceDepartments()
    {
        return $this->hasMany(\App\Models\NonBankingService\Department::class, 'company_id', 'id')->where('type', \App\Models\NonBankingService\Department::MICROFINANCE);
    }
    
    public function expenseNames()
    {
        $isNonBanking = hasMiddleware('isNonBankingService') ;
        $isPropertyManagement = hasMiddleware('isPropertyManagement') ;
		$isTrading = hasMiddleware('isTrading') ;
        if ($isNonBanking) {
            return $this->hasMany(\App\Models\NonBankingService\ExpenseName::class, 'company_id', 'id');
        }
        if ($isPropertyManagement) {
            return $this->hasMany(\App\Models\PropertyManagement\ExpenseName::class, 'company_id', 'id');
        }
		if ($isTrading) {
            return $this->hasMany(\App\Models\Trading\ExpenseName::class, 'company_id', 'id');
        }
      
    }
    
    public function expenseNamesFor(string $type, int $companyId)
    {
        $isNonBanking = hasMiddleware('isNonBankingService') ;
        $isPropertyManagement = hasMiddleware('isPropertyManagement') ;
		$isTrading = hasMiddleware('isTrading') ;
        if ($isNonBanking) {
            return \App\Models\NonBankingService\ExpenseName::where('expense_type', $type)->where('company_id', $companyId)->get();
        }
        if ($isPropertyManagement) {
            return \App\Models\PropertyManagement\ExpenseName::where('expense_type', $type)->where('company_id', $companyId)->get();
        }
		if ($isTrading) {
            return \App\Models\Trading\ExpenseName::where('expense_type', $type)->where('company_id', $companyId)->get();
        }
    }
    public function fixedAssetNames()
    {
        $isNonBanking = hasMiddleware('isNonBankingService') ;
        $isPropertyManagement = hasMiddleware('isPropertyManagement') ;
		$isTrading = hasMiddleware('isTrading') ;
        if ($isNonBanking) {
            return $this->hasMany(\App\Models\NonBankingService\FixedAssetName::class, 'company_id', 'id');
        }
        if ($isPropertyManagement) {
            return $this->hasMany(\App\Models\PropertyManagement\FixedAssetName::class, 'company_id', 'id');
        }
		if ($isTrading) {
            return $this->hasMany(\App\Models\Trading\FixedAssetName::class, 'company_id', 'id');
        }
    }
    
    public function cashflowReports():HasMany
    {
        return $this->hasMany(CashflowReport::class, 'company_id', 'id');
    }
    public function cashProjects()
    {
        return $this->hasMany(CashProjection::class)->where('cashflow_report_id', 0);
    }
    public function resetCashflowReport()
    {
        DB::table('weekly_cashflow_custom_due_invoices')
        ->where('cashflow_report_id', 0)
        ->where('company_id', $this->id)->delete();
        
        DB::table('weekly_cashflow_custom_past_due_schedules')
        ->where('cashflow_report_id', 0)
        ->where('company_id', $this->id)
        ->delete();

        DB::table('cash_projections')
        ->where('cashflow_report_id', '=', 0)->where('company_id', $this->id)->delete();
    }
    public function odooApprovedExpenses():HasMany
    {
        return $this->hasMany(OdooExpense::class, 'company_id', 'id');
    }
    public function getOdooId():?int
    {
        return $this->odoo_id ;
    }
    public function getIntegrationStartDate():?string
    {
        return $this->odoo_integration_start_date?:'2025-01-01';
    }
    public function withinIntegrationDate(string $date)
    {
        $odooIntegrationStartDate = $this->getIntegrationStartDate();
        return Carbon::make($odooIntegrationStartDate)->lessThanOrEqualTo(Carbon::make($date));
    }
    public function interestRevenuesAccounts():HasMany
    {
        return $this->hasMany(InterestRevenueAccount::class, 'company_id', 'id');
    }
    /**
     * * لو مفيش القسم بتاع ال
     * * microfinance
     * * هنضيفه للشركة دي ودا بيحصل في اول مرة يدخل علي الصفحه دي
     */
    public function syncMicrofinanceDepartments():void
    {
        $isExist  = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('departments')->where('type', \App\Models\NonBankingService\Department::MICROFINANCE)->where('company_id', $this->id)->count();
        if ($isExist) {
            return ;
        }
        DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('departments')->insert([
            'name'=>'Microfinance Branch',
            'type'=>\App\Models\NonBankingService\Department::MICROFINANCE,
            'company_id'=>$this->id
        ]);
    }
    public function letterOfCreditFacilities()
    {
        return $this->hasMany(LetterOfCreditFacility::class, 'company_id', 'id');
    }public function letterOfGuaranteeFacilities()
    {
        return $this->hasMany(LetterOfGuaranteeFacility::class, 'company_id', 'id');
    }
    public function hasAtLeastOneOfEachMainModels():bool
    {
        $numberOfGeneralDepartments = $this->generalDepartments->count();
        $numberOfExpenseNames = $this->expenseNames->count();
        $numberOfFixedAssetNames = $this->fixedAssetNames->count();
        return $numberOfGeneralDepartments && $numberOfExpenseNames && $numberOfFixedAssetNames;
    
        
    }
    public function hasMicrofinanceProducts()
    {
        return $this->microfinanceProducts->count();
    }
    
    public function hasAtLeastOneExistingBranch()
    {
        return $this->existingBranches->count();
    }
    public function properties():HasMany
    {
        return $this->hasMany(Property::class, 'company_id', 'id');
    }
    public function propertyNatures():HasMany
    {
        return $this->hasMany(Nature::class, 'company_id', 'id');
    }
    public function propertyCategories():HasMany
    {
        return $this->hasMany(Category::class, 'company_id', 'id')->orderBy('name');
    }
    public function getPropertyNaturesFormatted():array
    {
        $results = [];
        foreach ($this->propertyNatures as $nature) {
            $results[] = [
                'title'=>$nature->getName() ,
                'id'=>$nature->id
            ];
        }
        return $results;
    }
    public function getCategoriesFormatted():array
    {
        $results = [];
        foreach ($this->propertyCategories as $category) {
            $results[] = [
                'title'=>$category->getName() ,
                'id'=>$category->id,
				'types'=>$category->types->map(function($type){
					return [
						'title'=>$type->getName() ,
						'id'=>$type->id
					];
				})->toArray()
            ];
        }
        return $results;
    }
    public function propertyOwnerships():HasMany
    {
        return $this->hasMany(Ownership::class, 'company_id', 'id');
    }
    public function propertyUsageStatues():HasMany
    {
        return $this->hasMany(UsageStatus::class, 'company_id', 'id');
    }
    public function getOwnershipsFormatted():array
    {
        $results = [];
        foreach ($this->propertyOwnerships as $ownership) {
            $results[] = [
                'title'=>$ownership->getName() ,
                'id'=>$ownership->id
            ];
        }
        return $results;
    }		public function getPropertyUsageStatuesFormatted():array
    {
        $results = [];
        foreach ($this->propertyUsageStatues as $usageStatus) {
            $results[] = [
                'title'=>$usageStatus->getName() ,
                'id'=>$usageStatus->id
            ];
        }
        return $results;
    }
    public function getUnitOfMeasurements():array
    {
        return [
            // [
            //     'id'=>'cm2',
            //     'title'=>__('cm^2')
            // ],
            [
                'id'=>'sqm',
                'title'=>__('Sqm')
            ],
            [
                'id'=>'feddan',
                'title'=>__('Feddan')
            ]
        ];
    }
    public function propertyTypes():HasMany
    {
        return $this->hasMany(PropertyType::class, 'company_id', 'id')->orderBy('name');
    }
    public function getTypesFormatted():array
    {
        $results = [];
        foreach ($this->propertyTypes as $type) {
            $results[] = [
                'title'=>$type->getName() ,
                'id'=>$type->id
            ];
        }
        return $results;
    }
	public function getPropertyCategoriesFormatted():array
	{
		return $this->propertyCategories->map(function($category){
			return [
				'title'=>$category->getName() ,
				'id'=>$category->id,
				'types'=>$category->types->map(function($type){
					return [
						'title'=>$type->getName() ,
						'id'=>$type->id
					];
				})->toArray()
			];
		})->toArray();
	}
	public function tenants():HasMany
	{
		return $this->hasMany(Tenant::class, 'company_id', 'id');
	}
}
