<?php

use App\Http\Controllers\Analysis\SalesGathering\SalesBreakdownAgainstAnalysisReport;
use App\Http\Controllers\BalanceSheetController;
use App\Http\Controllers\CashFlowStatementController;
use App\Http\Controllers\DeleteAllRowsFromCaching;
use App\Http\Controllers\DeleteMultiRowsFromCaching;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FilterMainTypeBasedOnDatesController;
use App\Http\Controllers\FinancialStatementController;
use App\Http\Controllers\getUploadPercentage;
use App\Http\Controllers\Helpers\DeleteSingleRecordController;
use App\Http\Controllers\Helpers\EditTableCellsController;
use App\Http\Controllers\Helpers\getEditFormController;
use App\Http\Controllers\Helpers\HelpersController;
use App\Http\Controllers\Helpers\UpdateBasedOnGlobalController;
use App\Http\Controllers\Helpers\UpdateCitiesBasedOnCountryController;
use App\Http\Controllers\IncomeStatementController;
use App\Http\Controllers\QuickPricingCalculatorController;
use App\Http\Controllers\RemoveCompanycontroller;
use App\Http\Controllers\RemoveUsercontroller;
use App\Http\Controllers\RevenueBusinessLineController;
use App\Http\Controllers\RoutesDefinition;
use App\Http\Controllers\SalesGatheringTestController;
use App\Models\Company;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware([])->group(function () {
    // Route::any('FreeUserSubscription', 'UserController@freeSubscription')->name('free.user.subscription');
    Auth::routes();
    
    Route::group(
        [
            'prefix' => LaravelLocalization::setLocale(),
            'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'auth', 'checkIfAccountExpired']
        ],
        function () {
            Route::get('sharable-links/quick-pricing-calculator/{pricingPlanId}', [QuickPricingCalculatorController::class, 'create']);
            
            
            Route::post('get-net-sales-for-type/', [SalesBreakdownAgainstAnalysisReport::class, 'getNetSalesValueSum'])->name('get.net.sales.modal.for.type');
            Route::post('getTopAndBottomsForDashboard', [SalesBreakdownAgainstAnalysisReport::class, 'topAndBottomsForDashboard'])->name('getTopAndBottomsForDashboard');

            Route::post('remove-user', [RemoveUsercontroller::class, '__invoke'])->name('remove.user');
            Route::post('remove-company', [RemoveCompanycontroller::class, '__invoke'])->name('remove.company');
            Route::get('/client', function () {
                return view('client_view.supplier_invoices.form');
            });

            Route::resource('section', 'SectionController');
            Route::resource('companySection', 'CompanyController');
            // Route::resource('user', 'UserController');
            Route::get('user/create/{company?}', 'UserController@create')->middleware('isCashManagement')->name('user.create');
            Route::get('user/all/{company?}', 'UserController@index')->middleware('isCashManagement')->name('user.index');
            Route::post('user/{company?}', 'UserController@store')->middleware('isCashManagement')->name('user.store');
            Route::get('user/{user}/edit/{company?}', 'UserController@edit')->middleware('isCashManagement')->name('user.edit');
            Route::put('user/{user}/{company?}', 'UserController@update')->middleware('isCashManagement')->name('user.update');
            Route::delete('user/{user}/{company?}', 'UserController@destroy')->middleware('isCashManagement')->name('user.destroy');
            Route::resource('toolTipData', 'ToolTipDataController');

            
            
            
            Route::group(['prefix' => 'roles-permissions/', 'as' => 'roles.permissions.'], function () {
                // Route::get('/index/{company?}', 'RolesAndPermissionsController@index')->name('index');
                // Route::get('/create/{company?}', 'RolesAndPermissionsController@create')->name('create');
                // Route::post('/store/{company?}', 'RolesAndPermissionsController@store')->name('store');
                Route::get('/edit/{company?}', 'RolesAndPermissionsController@edit')->middleware(['isCashManagement'])->name('edit');
                Route::post('/update/{company?}', 'RolesAndPermissionsController@update')->name('update');
            });

            Route::get('update-users-based-on-company-and-role', 'UserController@getUsersBasedOnCompanyAndRole')->name('update.users.based.on.company.and.role');
            Route::get('render-permission-html-for-user', 'UserController@renderPermissionForUser')->name('render.permissions.html.for.user');
            Route::group(['prefix' => 'user-permissions/{user}/', 'as' => 'user.permissions.'], function () {
                Route::get('/index', 'UsersAndPermissionsController@index')->name('index');
                Route::get('/create', 'UsersAndPermissionsController@create')->name('create');
                Route::post('/store', 'UsersAndPermissionsController@store')->name('store');
                Route::get('/edit/{company?}', 'UsersAndPermissionsController@edit')->middleware('isCashManagement')->name('edit');
                Route::post('/update', 'UsersAndPermissionsController@update')->name('update');
            });
            Route::get('toolTipSectionsFields/{id}', 'ToolTipDataController@sectionFields')->name('section.fields');
            Route::get('logs', 'LogController@show')->name('admin.show.logs');
            Route::get('logs/{user}', 'LogController@showDetail')->name('admin.show.logs.detail');
            //########### Client View ############
            Route::get('/', 'HomeController@index')->name('home');

            Route::prefix('{company}')->group(function () {
                
            
                Route::get('fixed-payments-at-end-and-beginning', 'Loans2Controller@viewFixedAntEndAndBeginning')->name('fixed.loan.fixed.at.end.and.beginning');
                Route::get('variable-payment-loan', 'Loans2Controller@viewVariable')->name('variable.loan');
                Route::resource('/loan2', 'Loans2Controller')->names([
                    'index' => 'loans2.index',
                    'create' => 'loan2.create',
                    'store' => 'loan2.store',
                    'show' => 'loan2.show',
                    'edit' => 'loan2.edit',
                    'update' => 'loan2.update',
                    'destroy' => 'loan2.destroy',
                ])->except('create');
                Route::post('calculate-fixed-at-end-and-beginning', 'Loans2Controller@calculateFixedAtEndAndBeginning')->name('calculate.fixed.at.end.and.beginning');
                Route::post('calculate-variable-at-end-and-beginning', 'Loans2Controller@calculateVariableAtEndAndBeginning')->name('calculate.variable.at.end.and.beginning');
                Route::post('save-fixed-at-end', 'SaveFixedAtEndController@__invoke')->name('save.fixed.at.end');
                Route::post('save-loan-dates', 'SaveLoanDatesController@__invoke')->name('save.loan.dates');
                Route::get('fixed-payments-at-end', 'Loans2Controller@create')->name('fixed.loan.fixed.at.end');
                Route::get('calculate-loan-amount', 'Loans2Controller@create')->name('calc.loan.amount');
                Route::get('calculate-interest-rate', 'Loans2Controller@create')->name('calc.interest.percentage');
                Route::get('fixed-payments-at-beginning', 'Loans2Controller@create')->name('fixed.loan.fixed.at.beginning');
                Route::get('variable-payments', 'Loans2Controller@create')->name('variable.payments');
                
                
                //cash vero roles and permissions
                // Route::group(['prefix'=>'cash-vero-permissions'],function(){
                // 	Route::get('create','CashVeroPermissionsController@create')->name('cashvero.permissions.create');
                // 	Route::post('store','CashVeroPermissionsController@store')->name('cashvero.permissions.store');
                // });
            
                    
                Route::get('update-currency-account-based-on-currency/{financialInstitution}', 'UpdateCurrentAccountBasedOnCurrencyController@index')->name('update.current.account.based.on.currency');
                Route::post('save-labeling-data', 'CompanyController@saveLabelingData')->name('save.labeling.item');

                Route::post('get-type-based-on-dates', [FilterMainTypeBasedOnDatesController::class, '__invoke'])->name('get.type.based.on.dates');

                Route::get('income-statement', [IncomeStatementController::class, 'view'])->name('admin.view.income.statement');
                Route::get('income-statement/create', [IncomeStatementController::class, 'create'])->name('admin.create.income.statement');
                Route::get('income-statement-report/{incomeStatement}/edit', [IncomeStatementController::class, 'editItems']);
                Route::post('income-statement/{incomeStatement}/update', [IncomeStatementController::class, 'update'])->name('admin.update.income.statement');
                Route::post('income-statement/store', [IncomeStatementController::class, 'store'])->name('admin.store.income.statement');
                Route::get('export-income-statement', 'IncomeStatementController@export')->name('admin.export.income.statement');
                Route::get('get-income-statement', 'IncomeStatementController@paginate')->name('admin.get.income.statement');
                
                Route::get('income-statement/{incomeStatement}/actual-report', [IncomeStatementController::class, 'createReport'])->name('admin.create.income.statement.actual.report');
                Route::get('income-statement/{incomeStatement}/forecast-report', [IncomeStatementController::class, 'createReport'])->name('admin.create.income.statement.forecast.report');
                Route::get('income-statement/{incomeStatement}/adjusted-report', [IncomeStatementController::class, 'createReport'])->name('admin.create.income.statement.adjusted.report');
                Route::get('income-statement/{incomeStatement}/modified-report', [IncomeStatementController::class, 'createReport'])->name('admin.create.income.statement.modified.report');

                Route::post('income-statement-report/update', [IncomeStatementController::class, 'updateReport'])->name('admin.update.income.statement.report');
                Route::post('income-statement-report/delete', [IncomeStatementController::class, 'deleteReport'])->name('admin.destroy.income.statement.report');
                Route::post('income-statement/storeReport', [IncomeStatementController::class, 'storeReport'])->name('admin.store.income.statement.report');
                Route::post('export-income-statement-report-excel/{incomeStatementId}/{reportType}', 'IncomeStatementController@exportReport')->name('admin.export.income.statement.report');
                Route::post('export-income-statement-report-pdf/{incomeStatementId}/{reportType}', 'IncomeStatementController@exportReportAsPdf')->name('admin.export.income.statement.report.pdf');
                Route::post('get-income-statement-report/{incomeStatement}', 'IncomeStatementController@paginateReport')->name('admin.get.income.statement.report');
                Route::get('/expense-dashboard', 'Analysis\SalesGathering\ExpenseAgainstAnalysisReport@viewDashboard')->name('view.expense.analysis.dashboard');
                // balance sheet

                Route::get('balance-sheet', [BalanceSheetController::class, 'view'])->name('admin.view.balance.sheet');
                Route::get('balance-sheet/create', [BalanceSheetController::class, 'create'])->name('admin.create.balance.sheet');
                Route::get('balance-sheet-report/{balanceSheet}/edit', [BalanceSheetController::class, 'editItems']);
                Route::post('balance-sheet/{balanceSheet}/update', [BalanceSheetController::class, 'update'])->name('admin.update.balance.sheet');
                Route::post('balance-sheet/store', [BalanceSheetController::class, 'store'])->name('admin.store.balance.sheet');
                Route::get('export-balance-sheet', 'BalanceSheetController@export')->name('admin.export.balance.sheet');
                Route::get('get-balance-sheet', 'BalanceSheetController@paginate')->name('admin.get.balance.sheet');
                Route::get('balance-sheet/{balanceSheet}/actual-report', [BalanceSheetController::class, 'createReport'])->name('admin.create.balance.sheet.actual.report');

                // actual.report the first segment represent type so do not change it
                Route::get('balance-sheet/{balanceSheet}/actual-report', [BalanceSheetController::class, 'createReport'])->name('admin.create.balance.sheet.actual.report');

                // forecast.report the first segment represent type so do not change it
                Route::get('balance-sheet/{balanceSheet}/forecast-report', [BalanceSheetController::class, 'createReport'])->name('admin.create.balance.sheet.forecast.report');
                // adjusted.report the first segment represent type so do not change it

                Route::get('balance-sheet/{balanceSheet}/adjusted-report', [BalanceSheetController::class, 'createReport'])->name('admin.create.balance.sheet.adjusted.report');

                Route::get('balance-sheet/{balanceSheet}/modified-report', [BalanceSheetController::class, 'createReport'])->name('admin.create.balance.sheet.modified.report');

                Route::post('balance-sheet-report/update', [BalanceSheetController::class, 'updateReport'])->name('admin.update.balance.sheet.report');
                Route::post('balance-sheet-report/delete', [BalanceSheetController::class, 'deleteReport'])->name('admin.destroy.balance.sheet.report');
                Route::post('balance-sheet/storeReport', [BalanceSheetController::class, 'storeReport'])->name('admin.store.balance.sheet.report');
                Route::post('export-balance-sheet-report', 'BalanceSheetController@exportReport')->name('admin.export.balance.sheet.report');
                Route::post('get-balance-sheet-report/{balanceSheet}', 'BalanceSheetController@paginateReport')->name('admin.get.balance.sheet.report');

                // cash flow statement

                Route::get('cash-flow-statement', [CashFlowStatementController::class, 'view'])->name('admin.view.cash.flow.statement');
                Route::get('cash-flow-statement/create', [CashFlowStatementController::class, 'create'])->name('admin.create.cash.flow.statement');
                Route::get('cash-flow-statement-report/{cashFlowStatement}/edit', [CashFlowStatementController::class, 'editItems']);
                Route::post('cash-flow-statement/{cashFlowStatement}/update', [CashFlowStatementController::class, 'update'])->name('admin.update.cash.flow.statement');
                Route::post('cash-flow-statement/store', [CashFlowStatementController::class, 'store'])->name('admin.store.cash.flow.statement');
                Route::get('export-cash-flow-statement', 'CashFlowStatementController@export')->name('admin.export.cash.flow.statement');
                Route::get('get-cash-flow-statement', 'CashFlowStatementController@paginate')->name('admin.get.cash.flow.statement');
                Route::get('cash-flow-statement/{cashFlowStatement}/actual-report', [CashFlowStatementController::class, 'createReport'])->name('admin.create.cash.flow.statement.actual.report');

                Route::get('cash-and-banks/{cashFlowStatement}/{reportType}', 'CashFlowStatementController@createReport')->name('admin.show-cash-and-banks');
                Route::post('store-cash-and-banks', 'CashFlowStatementController@storeCashAndBanks')->name('admin.store-cash-and-banks');
                // actual.report the first segment represent type so do not change it
                Route::get('cash-flow-statement/{cashFlowStatement}/actual-report', [CashFlowStatementController::class, 'createReport'])->name('admin.create.cash.flow.statement.actual.report');

                // forecast.report the first segment represent type so do not change it
                Route::get('cash-flow-statement/{cashFlowStatement}/forecast-report', [CashFlowStatementController::class, 'createReport'])->name('admin.create.cash.flow.statement.forecast.report');
                // adjusted.report the first segment represent type so do not change it

                Route::get('cash-flow-statement/{cashFlowStatement}/adjusted-report', [CashFlowStatementController::class, 'createReport'])->name('admin.create.cash.flow.statement.adjusted.report');

                Route::get('cash-flow-statement/{cashFlowStatement}/modified-report', [CashFlowStatementController::class, 'createReport'])->name('admin.create.cash.flow.statement.modified.report');

                Route::post('cash-flow-statement-report/update', [CashFlowStatementController::class, 'updateReport'])->name('admin.update.cash.flow.statement.report');
                Route::post('cash-flow-statement-report/delete', [CashFlowStatementController::class, 'deleteReport'])->name('admin.destroy.cash.flow.statement.report');
                Route::post('cash-flow-statement/storeReport', [CashFlowStatementController::class, 'storeReport'])->name('admin.store.cash.flow.statement.report');
                Route::post('export-cash-flow-statement-report', 'CashFlowStatementController@exportReport')->name('admin.export.cash.flow.statement.report');
                Route::post('get-cash-flow-statement-report/{cashFlowStatement}', 'CashFlowStatementController@paginateReport')->name('admin.get.cash.flow.statement.report');

                // excel for financial statement
                Route::get('download-excel-template-for-actual/{incomeStatement}', [FinancialStatementController::class, 'downloadExcelTemplateForActual'])->name('admin.export.excel.template');
                Route::any('salesGatheringImport/last-upload-failed/{model}', 'SalesGatheringTestController@lastUploadFailed')->name('last.upload.failed');
                // Route::delete('delete-from-gathering','SalesGatheringTestController@deleteFromTo')->name('delete.export.from.to');
                Route::post('import-excel-template-for-actual/{incomeStatement}', [FinancialStatementController::class, 'importExcelTemplateForActual'])->name('admin.import.excel.template');
                Route::get('update-financial-statement-date', [FinancialStatementController::class, 'updateDate'])->name('admin.update.financial.statement.date');
                Route::delete('update-financial-statement-duration-type', [FinancialStatementController::class, 'updateDurationType'])->name('admin.update.financial.statement.duration.type');
                Route::get('financial-statement', [FinancialStatementController::class, 'view'])->name('admin.view.financial.statement');
                Route::get('financial-statement/create', [FinancialStatementController::class, 'create'])->name('admin.create.financial.statement');
                Route::get('financial-statement/{financialStatement}/edit', [FinancialStatementController::class, 'edit']);
                Route::post('financial-statement/{financialStatement}/update', [FinancialStatementController::class, 'update'])->name('admin.update.financial.statement');
                Route::post('financial-statement/store', [FinancialStatementController::class, 'store'])->name('admin.store.financial.statement');
                Route::get('export-financial-statement', 'FinancialStatementController@export')->name('admin.export.financial.statement');

                Route::get('get-financial-statement', 'FinancialStatementController@paginate')->name('admin.get.financial.statement');
                Route::get('financial-statement/{financialStatement}/report', [FinancialStatementController::class, 'createReport'])->name('admin.create.financial.statement.report');
                Route::post('financial-statement-report/update', [FinancialStatementController::class, 'updateReport'])->name('admin.update.financial.statement.report');
                Route::post('financial-statement-report/delete', [FinancialStatementController::class, 'deleteReport'])->name('admin.destroy.financial.statement.report');
                Route::post('financial-statement/storeReport', [FinancialStatementController::class, 'storeReport'])->name('admin.store.financial.statement.report');
                Route::post('export-financial-statement-report', 'FinancialStatementController@exportReport')->name('admin.export.financial.statement.report');
                Route::post('get-financial-statement-report/{financialStatement}', 'FinancialStatementController@paginateReport')->name('admin.get.financial.statement.report');

                Route::get('expense-form/create', [ExpenseController::class, 'create'])->name('admin.create.expense');
                Route::post('expense-form/store', [ExpenseController::class, 'store'])->name('admin.store.expense');
                
                
                

                Route::post('edit-table-cell', [EditTableCellsController::class, '__invoke'])->name('admin.edit.table.cell');

                //Ajax
                Route::post('get/ZoneZonesData/', 'Analysis\SalesGathering\ZoneAgainstAnalysisReport@ZonesData')->name('get.zones.data');
                Route::get('get/viewData/', 'Analysis\SalesGathering\ZoneAgainstAnalysisReport@dataView')->name('get.view.data');
                Route::get('checkIfJobFinished/{modelName}', 'SalesGatheringTestController@activeJob')->name('active.job');
                Route::get('filter-column-based-on-another', 'FilterColumnBasedOnAnotherColumnController@filter')->name('filter.column.based.on.another.column');

                Route::get('/redirect', 'HomeController@redirectFun')->name('home.redirect');
                //########### Dashboard ############
                Route::get('/companyGroup', 'HomeController@companyGroup')->name('company.group');
                Route::any('Admin_Company', 'CompanyController@adminCompany')->name('admin.company');
                Route::any('Edit_Admin_Company/{companySection}', 'CompanyController@editAdminCompany')->name('edit.admin.company');

                //########### Dashboards Links ############
                Route::prefix('/dashboard')->group(function () {
                    Route::any('/', 'HomeController@dashboard')->name('dashboard');
                    Route::any('/income-statement-revenue-dashboard', 'HomeController@incomeStatementDashboard')->name('income.statement.dashboard');
                    Route::get('/HomePage', 'HomeController@welcomePage')->name('viewHomePage');
                    Route::any('/breakdown', 'HomeController@dashboardBreakdownAnalysis')->name('dashboard.breakdown');
                    Route::get('ajax-refresh-limits-chart', 'CustomerInvoiceDashboardController@refreshBankMovementChart')->name('refresh.chart.limits.data') ; // ajax request
                    Route::any('/income-statement-breakdown-dashboard/{reportType}/{incomeStatement?}', 'HomeController@dashboardBreakdownIncomeStatementAnalysis')->name('dashboard.breakdown.incomeStatement');
                    Route::any('/balance-sheet-breakdown-dashboard/{reportType}/{balanceSheet?}', 'HomeController@dashboardBreakdownBalanceSheetAnalysis')->name('dashboard.breakdown.balanceSheet');
                    Route::any('/cash-flow-statement-breakdown-dashboard/{reportType}/{cashFlowStatement?}', 'HomeController@dashboardBreakdownCashFlowStatementAnalysis')->name('dashboard.breakdown.cashFlowStatement');
                    Route::any('/customers', 'HomeController@dashboardCustomers')->name('dashboard.customers');
                    Route::any('/salesPerson', 'HomeController@dashboardSalesPerson')->name('dashboard.salesPerson');
                    Route::any('/salesDiscount', 'HomeController@dashboardSalesDiscount')->name('dashboard.salesDiscount');
                    Route::any('/intervalComparing', 'HomeController@dashboardIntervalComparing')->name('dashboard.intervalComparing');
                    Route::any('/incomeStatementIntervalComparing/{subItemType?}', 'HomeController@dashboardIncomeStatementIntervalComparing')->name('dashboard.intervalComparing.incomeStatement');
                    Route::any('/variousIncomeStatementComparing/{subItemType}', 'HomeController@dashboardIncomeStatementVariousComparing')->name('dashboard.various.incomeStatement');
                });

                //########### Import Routs ############
                // Route::any('inventoryStatementImport', 'InventoryStatementTestController@import')->name('inventoryStatementImport');
                // Route::get('inventoryStatement/insertToMainTable', 'InventoryStatementTestController@insertToMainTable')->name('inventoryStatementTest.insertToMainTable');
                Route::any('salesGatheringImport/{model}', 'SalesGatheringTestController@import')->name('salesGatheringImport');
                Route::get('SalesGathering/insertToMainTable/{modelName}', 'SalesGatheringTestController@insertToMainTable')->name('salesGatheringTest.insertToMainTable');

                //########### Export Routes ############
                Route::get('salesGathering/export/{model}', 'SalesGatheringController@export')->name('salesGathering.export');
                // type excel or pdf
                Route::get('/export-labeling-items/{type}', 'SalesGatheringController@exportLabelingItems')->name('export.labeling.item');
                Route::get('/print-labeling-items-qrcode/{fromIndex}/{toIndex}', 'SalesGatheringController@printLabelingItemsQrcode')->name('print.labeling.item.qrcode');
                Route::post('/print-by-headers', 'SalesGatheringController@printLabelingByCustomHeaders')->name('print.custom.header');

                // ->parameters(['name-of-route'=> inventoryStatement [dependancies injection of model]])

                //########### test table for uploading ############
                // Route::resource('inventoryStatementTest', 'InventoryStatementTestController')
                // 	->only(['edit', 'update', 'destroy']);
                Route::resource('salesGatheringTest', 'SalesGatheringTestController')
                    ->only(['edit', 'update', 'destroy']);

                //########### Sections Resources ############

                Route::resource('inventoryStatement', 'InventoryStatementController');
                Route::resource('salesGathering', 'SalesGatheringController');

                Route::get('uploading/{model}/{loanId?}', 'SalesGatheringController@index')->name('view.uploading');

                //###########  (TRUNCATE) ############
                Route::get('Truncate/{model}', 'DeletingClass@truncate')->name('truncate');
                Route::get('delete-all-labeling', 'SalesGatheringController@deleteAllLabelingItemsWithColumns')->name('delete.all.labeling.items.with.columns');
                Route::delete('DeleteMultipleRows/{model}', 'DeletingClass@multipleRowsDeleting')->name('multipleRowsDelete');
                Route::delete('delete-model', [DeleteSingleRecordController::class, '__invoke'])->name('delete.model');

                //########### Inventory Links ############
                Route::prefix('/Inventory')->group(function () {
                    Route::get('/EndBalanceAnalysis/View', 'Analysis\Inventory\EndBalanceAnalysisReport@index')->name('end.balance.analysis');
                    Route::post('/EndBalanceAnalysis/Result', 'Analysis\Inventory\EndBalanceAnalysisReport@result')->name('end.balance.analysis.result');
                });

                Route::delete('delete-multi-rows', [HelpersController::class, 'deleteMulti'])->name('delete.multi');
                Route::post('store-new-model', [HelpersController::class, 'storeNewModal'])->name('admin.store.new.modal');
                /**
                 * * non banking services
                 */
                Route::group(['prefix'=>NON_BANKING_SERVICE_URL_PREFIX,'middleware'=>'isNonBankingService'], function () {
                    Route::get('fixed-payments-at-end/{study}', 'Loans2Controller@create')->name('non.banking.fixed.loan.fixed.at.end');
                    Route::get('fixed-payments-at-beginning/{study}', 'Loans2Controller@create')->name('non.banking.fixed.loan.fixed.at.beginning');
                    Route::get('calculate-loan-amount/{study}', 'Loans2Controller@create')->name('non.banking.calc.loan.amount');
                    Route::get('calculate-interest-rate/{study}', 'Loans2Controller@create')->name('non.banking.calc.interest.percentage');
                    Route::get('variable-payments/{study}', 'Loans2Controller@create')->name('non.banking.variable.payments');
                    Route::group(['namespace'=>'NonBankingServices'], function () {
                        
                        route::get('study', 'StudyController@index')->name('view.study');
                        route::get('study/create', 'StudyController@create')->name('create.study');
                        Route::get('study/{study}/edit', 'StudyController@edit')->name('edit.study');
                        Route::put('study/{study}/update', 'StudyController@update')->name('update.study');
                        route::post('study', 'StudyController@store')->name('store.non.banking.services');
                        route::delete('study/{study}/delete', 'StudyController@destroy')->name('study.destroy');
                        Route::post('/copy/{study}', 'CopyStudyController@index')->name('copy.study');
                    
                        // route::get('leasing-categories','LeasingCategoriesController@index')->name('view.leasing.categories');
                        route::get('leasing-products/create', 'LeasingCategoriesController@create')->name('create.leasing.categories');
                        route::post('leasing-products/create', 'LeasingCategoriesController@store')->name('store.leasing.categories');
                    
                    
                        route::get('existing-branches/create', 'ExistingBranchesController@create')->name('create.existing.branches');
                        route::post('existing-branches/create', 'ExistingBranchesController@store')->name('store.existing.branches');
                    
                    
                        route::get('departments', 'DepartmentController@index')->name('view.departments');
                        route::get('departments/create/{type}', 'DepartmentController@create')->name('create.departments');
                        route::post('departments/create/{type}', 'DepartmentController@store')->name('store.departments');
                        route::get('departments/{department}/edit/{type}', 'DepartmentController@edit')->name('edit.departments');
                        route::put('departments/{department}/update/{type}', 'DepartmentController@update')->name('update.departments');
                        route::delete('departments/{department}/destroy', 'DepartmentController@destroy')->name('departments.destroy');
                    
                        // route::get('microfinance-departments/create','MicrofinanceDepartmentController@create')->name('create.microfinance-departments');
                        // route::post('microfinance-departments/create', 'MicrofinanceDepartmentController@store')->name('store.microfinance-departments');
                        // route::get('microfinance-departments/{microfinanceDepartment}/edit', 'MicrofinanceDepartmentController@edit')->name('edit.microfinance-departments');
                        // route::put('microfinance-departments/{microfinanceDepartment}/update', 'MicrofinanceDepartmentController@update')->name('update.microfinance-departments');
                    
                        route::get('expense-names', 'ExpenseController@index')->name('view.expense.names');
                        route::get('expense-names/create', 'ExpenseController@create')->name('create.expense.names');
                        route::post('expense-names/create', 'ExpenseController@store')->name('store.expense.names');
                        route::get('expense-names/{expenseType}/edit', 'ExpenseController@edit')->name('edit.expense.names');
                        route::put('expense-names/{expenseType}/update', 'ExpenseController@update')->name('update.expense.names');
                        route::delete('expense-names/{expenseType}/destroy', 'ExpenseController@destroy')->name('expense.names.destroy');
                    
                    
                        route::get('fixed-assets-names', 'FixedAssetController@index')->name('view.fixed.asset.names');
                        route::get('fixed-assets-names/create', 'FixedAssetController@create')->name('create.fixed.asset.names');
                        route::post('fixed-assets-names/create', 'FixedAssetController@store')->name('store.fixed.asset.names');
                        route::get('fixed-assets-names/{fixedAssetName}/edit', 'FixedAssetController@edit')->name('edit.fixed.asset.names');
                        route::put('fixed-assets-names/{fixedAssetName}/update', 'FixedAssetController@update')->name('update.fixed.asset.names');
                        route::delete('fixed-assets-names/{fixedAssetName}/destroy', 'FixedAssetController@destroy')->name('fixed.asset.names.destroy');
                    
                    
                        route::post('consolidations', 'ConsolidationController@create')->name('view.consolidations');
                        route::post('consolidations', 'ConsolidationController@store')->name('store.consolidations');
						
						 route::get('consolidation-income-statement/{consolidation}', 'ConsolidationIncomeStatementController@index')->name('view.non.banking.consolidation.income.statement');
						 
                        route::get('expense-per-employees/{study}/create', 'ExpensePerEmployeeController@create')->name('create.expense.per.employees');
                    
                        // route::get('leasing-categories/edit/{leasingCategory}','LeasingCategoriesController@edit')->name('edit.leasing.categories');
                        // route::delete('leasing-categories/destroy/{leasingCategory}','LeasingCategoriesController@destroy')->name('destroy.leasing.categories');

                        route::get('microfinance-products/create', 'MicrofinanceProductsController@create')->name('create.microfinance.products');
                        route::post('microfinance-products/create', 'MicrofinanceProductsController@store')->name('store.microfinance.products');
                    
                        route::get('consumerfinance-products/create', 'ConsumerfinanceProductsController@create')->name('create.consumerfinance.products');
                        route::post('consumerfinance-products/create', 'ConsumerfinanceProductsController@store')->name('store.consumerfinance.products');
                    
                        /**
                         * * Start General Assumption
                         */
                        Route::group(['prefix'=>'study/{study}'], function () {
                            /**
                             * * General Assumption
                             */
                        
                            route::get('general-and-reserve-assumption', 'GeneralAndReservationAssumptionController@create')->name('create.general.assumption');
                            route::post('general-and-reserve-assumption', 'GeneralAndReservationAssumptionController@store')->name('store.general.assumption');
                        
                            // route::get('microfinance-branches-assumption', 'MicrofinanceBranchAssumptionsController@create')->name('create.microfinance.branches.assumption');
                            // route::post('microfinance-branches-assumption', 'MicrofinanceBranchAssumptionsController@store')->name('store.microfinance.branches.assumption');
                        
                            /**
                             * * End General Assumption
                             */
                    
                            /**
                             * * Start Leasing Revenue Streams Breakdown
                             */
                            route::get('revenue-streams-breakdown/leasing', 'LeasingController@create')->name('create.leasing.revenue.stream.breakdown');
							route::get('leasing-fetch-old-data','LeasingController@getOldData');
                            route::post('revenue-streams-breakdown/leasing', 'LeasingController@store')->name('store.leasing.revenue.stream.breakdown');
                            /**
                             * * End Leasing Revenue Streams Breakdown
                             */
                        
                         
                            /**
                            * * Start Direct Factoring Revenue Streams Breakdown
                            */
                            route::get('revenue-streams-breakdown/direct-factoring', 'DirectFactoringController@create')->name('create.direct.factoring.revenue.stream.breakdown');
                            route::post('revenue-streams-breakdown/direct-factoring', 'DirectFactoringController@store')->name('store.direct.factoring.revenue.stream.breakdown');
                        	route::get('direct-factoring-fetch-old-data','DirectFactoringController@getOldData');
                            // route::get('revenue-streams-breakdown/direct-factoring-vue','VueDirectFactoringController@create')->name('create.direct.factoring.revenue.stream.breakdown.vue');
                            // route::post('revenue-streams-breakdown/direct-factoring-vue','VueDirectFactoringController@store')->name('store.direct.factoring.revenue.stream.breakdown.vue');
                        
                            /**
                             * * End Direct Factoring Revenue Streams Breakdown
                             */
                        
                         
                         
                            /**
                             * * Start Reverse Factoring Revenue Streams Breakdown
                             */
                            route::get('revenue-streams-breakdown/reverse-factoring', 'ReverseFactoringController@create')->name('create.reverse.factoring.revenue.stream.breakdown');
                            route::post('revenue-streams-breakdown/reverse-factoring', 'ReverseFactoringController@store')->name('store.reverse.factoring.revenue.stream.breakdown');
							route::get('reverse-factoring-fetch-old-data','ReverseFactoringController@getOldData');
                            /**
                             * * End Reverse Factoring Revenue Streams Breakdown
                             */
                        
                         
                            /**
                             * * Start Ijara Mortgage Revenue Streams Breakdown
                             */
                            route::get('revenue-streams-breakdown/ijara', 'IjaraMortgageController@create')->name('create.ijara.mortgage.revenue.stream.breakdown');
                            route::post('revenue-streams-breakdown/ijara', 'IjaraMortgageController@store')->name('store.ijara.mortgage.revenue.stream.breakdown');
                        
                            route::get('securitization', 'SecuritizationController@create')->name('create.securitization');
                            route::post('securitization', 'SecuritizationController@store')->name('store.securitization');
                        
                        
                            route::get('consumer-finance', 'ConsumerFinanceController@create')->name('create.consumer.finance');
                            route::post('consumer-finance', 'ConsumerFinanceController@store')->name('store.consumer.finance');
                            
                            route::get('microfinance/all-branches/{branch_id?}', 'AllBranchesMicrofinanceControllerController@create')->name('create.all-branches.microfinance');
                            route::post('microfinance/all-branches/{branch_id?}', 'AllBranchesMicrofinanceControllerController@store')->name('store.all-branches.microfinance');
                        
                            route::get('get-decrease-rate-based-on-flat-rate', 'AllBranchesMicrofinanceControllerController@getDecreaseRateBasedOnFlatRate'); // ajax ;

                        
                            // route::get('microfinance/by-branches', 'ByBranchesMicrofinanceControllerController@create')->name('create.by-branches.microfinance');
                            // route::post('microfinance/by-branches', 'ByBranchesMicrofinanceControllerController@store')->name('store.by-branches.microfinance');
                        
                            route::get('microfinance/planning-by-branch', 'ByBranchesMicrofinanceControllerController@create')->name('create.by-branch.microfinance');
                            // route::post('microfinance/allocate-by-branch', 'ByBranchesMicrofinanceControllerController@store')->name('store.by-branch.microfinance');
                        
                        
                            route::get('microfinance/new-branches', 'NewBranchesMicrofinanceControllerController@create')->name('create.new-branches.microfinance');
                            route::post('microfinance/new-branches', 'NewBranchesMicrofinanceControllerController@store')->name('store.new-branches.microfinance');
                        
                            route::get('microfinance/loans', 'MicrofinanceLoanController@create')->name('create.loan.microfinance');
                            route::post('microfinance/loans', 'MicrofinanceLoanController@store')->name('store.loan.microfinance');
                            route::get('microfinance/loan-report/{branchId}', 'MicrofinanceLoanReportController@create')->name('view.loan.report.microfinance');
                        
                        
                            route::get('microfinance-products-mix', 'MicrofinanceProductMixControllerController@create')->name('create.microfinance.product.mix');
                            route::post('microfinance-products-mix', 'MicrofinanceProductMixControllerController@store')->name('store.microfinance.product.mix');
                        
                            // route::get('revenue-streams-breakdown/microfinance', 'MicrofinanceRevenueStreamBreakdownController@create')->name('create.microfinance.revenue.stream.breakdown');
                            // route::post('revenue-streams-breakdown/microfinance', 'MicrofinanceRevenueStreamBreakdownController@store')->name('store.microfinance.revenue.stream.breakdown');
                        
                            /**
                             * * End Ijara Mortgage Revenue Streams Breakdown
                             */
                        
                            /**
                            * * Start Portfolio Mortgage Revenue Streams Breakdown
                            */
                            route::get('revenue-streams-breakdown/portfolio-mortgage', 'PortfolioMortgageController@create')->name('create.portfolio.mortgage.revenue.stream.breakdown');
                            route::post('revenue-streams-breakdown/portfolio-mortgage', 'PortfolioMortgageController@store')->name('store.portfolio.mortgage.revenue.stream.breakdown');
                            Route::get('add-new-portfolio-mortgage-category', 'PortfolioMortgageController@addNewCategory')->name('add.new.portfolio.mortgage.category');
                            Route::get('delete-portfolio-mortgage-category/{portfolioMortgageCategory}', 'PortfolioMortgageController@deleteCategory')->name('delete.portfolio.mortgage.category');
                            /**
                             * * End Portfolio Mortgage Revenue Streams Breakdown
                             */
                        
                            route::get('dashboard', 'DashboardController@view')->name('view.results.dashboard');
                            route::get('dashboard-with-sensitivity', 'CashInOutFlowController@view')->name('view.results.dashboard.with.sensitivity');
                         
                            Route::get('cash-in-out-flow', 'CashInOutFlowController@view')->name('cash.in.out.flow.result');
                            Route::post('save-manual-equity-injection', 'CashInOutFlowController@saveManualEquityInjection')->name('save.manual.equity.injection');
                            Route::get('balance-sheet', 'BalanceSheetController@view')->name('balance.sheet.result');
                         
                            route::post('recalculate-spread-rates-sensitivity', 'RecalculateSpreadRateSensitivityController@recalculate')->name('calculate.spread.rate.sensitivity');
                            route::get('income-statement', 'IncomeStatementController@index')->name('view.non.banking.forecast.income.statement');
							route::get('previous-years-income-statement', 'IncomeStatementController@viewPreviousTwoYearsIncomeStatement')->name('view.previous.non.banking.forecast.income.statement');
							route::post('previous-years-income-statement', 'IncomeStatementController@storePreviousTwoYearsIncomeStatement')->name('store.previous.non.banking.forecast.income.statement');

                       
                            route::get('valuation', 'ValuationController@index')->name('view.non.banking.valuation');
                            route::get('expense-statement-reports', 'ExpenseStatementReportController@index')->name('view.expense.statement.reports');
                            route::post('expense-statement-reports', 'ExpenseStatementReportController@result')->name('result.expense.statement.reports');
                        
                        
            
                            /**
                             * * Non Banking Expenses
                             */
                            route::get('expenses', 'ExpensesController@create')->name('create.expenses');
                            route::get('expenses-fetch-old-data', 'ExpensesController@expensesGetVueOldData')->name('fetch.expenses.old.data');
                            route::post('expenses', 'ExpensesController@store')->name('store.expenses');
                            route::get('expense-name-from-category', 'ExpensesController@getExpenseNamesForCategory')->name('get.expense.name.for.category');
                            route::get('expense-name-from-category-only-employees', 'ExpensesController@getExpenseNamesForCategoryOnlyEmployees')->name('get.expense.name.for.category.only.in.employee');
                            route::get('expense-name-from-category-only-branch', 'ExpensesController@getExpenseNamesForCategoryOnlyBranches')->name('get.expense.name.for.category.only.in.branch');
                            route::get('fixed-assets/ffe', 'FfeFixedAssetsController@create')->name('create.ffe.fixed.assets');
                            route::post('fixed-assets/ffe', 'FfeFixedAssetsController@store')->name('store.ffe.fixed.assets');
                            route::get('fixed-assets/ffe/funding-structure', 'FfeFixedAssetsController@createFundingStructure')->name('create.ffe.funding.structure.fixed.assets');
                            route::post('fixed-assets/ffe/funding-structure', 'FfeFixedAssetsController@storeFunding')->name('store.ffe.funding.structure.fixed.assets');
                        
                            route::get('fixed-assets/new-branches', 'NewBranchFixedAssetsController@create')->name('create.new.branch.fixed.assets');
                        
                            route::post('fixed-assets/new-branches', 'NewBranchFixedAssetsController@store')->name('store.new.branch.fixed.assets');
                        
                            route::get('fixed-assets/per-employee', 'PerEmployeeFixedAssetsController@create')->name('create.per.employee.fixed.assets');
                            route::post('fixed-assets/per-employee', 'PerEmployeeFixedAssetsController@store')->name('store.per.employee.fixed.assets');
                        
                            // route::get('fixed-assets/employee', 'EmployeeFixedAssetsController@create')->name('create.per.employee.fixed.assets');
                            // route::post('fixed-assets/employee', 'EmployeeFixedAssetsController@store')->name('store.per.employee.fixed.assets');
                            route::post('fixed-assets/per-employee/funding-structure', 'NewBranchFixedAssetsController@storeFunding')->name('store.per.employee.funding.structure.fixed.assets');
                        
                        
                            route::post('departments', 'ManpowerExpensesController@storeDepartmentPositions')->name('store.department.positions.for.non.banking');
                            route::get('manpower', 'ManpowerExpensesController@create')->name('view.manpower.for.non.banking');
                            route::post('manpower', 'ManpowerExpensesController@store')->name('store.manpower.for.non.banking');
                        
                        
                            route::get('opening-balances', 'OpeningBalancesController@create')->name('view.opening.balances.for.non.banking');
                            route::post('opening-balances', 'OpeningBalancesController@store')->name('store.opening.balances.for.non.banking');
                        
                        
                            // route::get('delete/{position}/manpower','ManpowerExpensesController@deleteSinglePosition')->name('delete.single.position.for.non.banking');
                            // route::get('delete-department/{department}/manpower','ManpowerExpensesController@deleteSingleDepartment')->name('delete.single.department.for.non.banking');
                            route::get('get-positions-based-on-department', 'ManpowerExpensesController@getPositionsBasedOnDepartment'); // ajax ;
                            route::get('get-stream-category-based-on-revenue-stream-id', 'AjaxController@getStreamCategoryBasedOnRevenueStream');
                        
                            // Route::post('get-stream-category-based-on-revenue-stream','AjaxController@getStreamCategoryBasedOnRevenueStream');
                            Route::get('get-positions-based-on-departments', 'AjaxController@getPositionsBasedOnDepartments');
                        
                            /**
                             * * End expenses table
                             */
                        
                        
                        });
                        /**
                         * * Study Info
                        */
                    
                        
                         
                         
                    });
                    
                    
                });
                
                /**
                 * * Budget
                 */
                Route::group(['prefix'=>FINANCIAL_PLANNING_URL_PREFIX,'namespace'=>'FinancialPlanning','middleware'=>'isFinancialPlanning'], function () {
                    /**
                     * * Study Info
                     */
                    route::get('study', 'StudyController@index')->name('view.financial.planning.study');
                    route::get('study/create', 'StudyController@create')->name('create.financial.planning.study');
                    Route::get('study/{study}/edit', 'StudyController@edit')->name('edit.financial.planning.study');
                    Route::put('study/{study}/update', 'StudyController@update')->name('update.financial.planning.study');
                    route::post('study', 'StudyController@store')->name('store.financial.planning.study');
                    route::delete('study/{study}/delete', 'StudyController@destroy')->name('study.financial.planning.destroy');
                    
                    Route::group(['prefix'=>'study/{study}'], function () {
                        
                
                    
                        
                        route::get('income-statement/forecast', 'IncomeStatementController@index')->name('view.financial.planning.income.statement');
                        route::post('income-statement/forecast', 'IncomeStatementController@store')->name('store.financial.planning.income.statement');
            
                         
                        
                        route::get('cost-expenses', 'CostExpensesController@create')->name('view.cost.expenses');
                        route::post('cost-expenses', 'CostExpensesController@store')->name('store.cost.expenses');
                        route::post('{expenseType}/departments', 'ManpowerExpensesController@storeDepartmentPositions')->name('store.department.positions');
                        route::get('{expenseType}/manpower', 'ManpowerExpensesController@create')->name('view.manpower');
                        route::post('{expenseType}/manpower', 'ManpowerExpensesController@store')->name('store.manpower');
                        route::get('delete/{position}/manpower', 'ManpowerExpensesController@deleteSinglePosition')->name('delete.single.position');
                        route::get('delete-department/{department}/manpower', 'ManpowerExpensesController@deleteSingleDepartment')->name('delete.single.department');
                        
                         
                        /**
                        * * Start Direct Factoring Revenue Streams Breakdown
                        */
                        // route::get('revenue-streams-breakdown/direct-factoring','DirectFactoringController@create')->name('create.direct.factoring.revenue.stream.breakdown');
                        // route::post('revenue-streams-breakdown/direct-factoring','DirectFactoringController@store')->name('store.direct.factoring.revenue.stream.breakdown');
                        /**
                         * * End Direct Factoring Revenue Streams Breakdown
                         */
                        
                         
                         
                        /**
                         * * Start Reverse Factoring Revenue Streams Breakdown
                         */
                        // route::get('revenue-streams-breakdown/reverse-factoring','ReverseFactoringController@create')->name('create.reverse.factoring.revenue.stream.breakdown');
                        // route::post('revenue-streams-breakdown/reverse-factoring','ReverseFactoringController@store')->name('store.reverse.factoring.revenue.stream.breakdown');
                        /**
                         * * End Reverse Factoring Revenue Streams Breakdown
                         */
                        
                         
                        /**
                         * * Start Ijara Mortgage Revenue Streams Breakdown
                         */
                        // route::get('revenue-streams-breakdown/ijara-mortgage','IjaraMortgageController@create')->name('create.ijara.mortgage.revenue.stream.breakdown');
                        // route::post('revenue-streams-breakdown/ijara-mortgage','IjaraMortgageController@store')->name('store.ijara.mortgage.revenue.stream.breakdown');
                        /**
                         * * End Ijara Mortgage Revenue Streams Breakdown
                         */
                        
                        /**
                        * * Start Portfolio Mortgage Revenue Streams Breakdown
                        */
                        // route::get('revenue-streams-breakdown/portfolio-mortgage','PortfolioMortgageController@create')->name('create.portfolio.mortgage.revenue.stream.breakdown');
                        // route::post('revenue-streams-breakdown/portfolio-mortgage','PortfolioMortgageController@store')->name('store.portfolio.mortgage.revenue.stream.breakdown');
                        /**
                         * * End Portfolio Mortgage Revenue Streams Breakdown
                         */
                        
                        //  route::get('dashboard','DashboardController@view')->name('view.results.dashboard');
                        
                        
                        
                        
                        /**
                        * * Start expenses tables
                        */
                        
                        // route::get('expenses','ExpensesController@create')->name('create.expenses');
                        // route::post('expenses','ExpensesController@store')->name('store.expenses');
                        
                        
                        // Route::post('get-stream-category-based-on-revenue-stream','AjaxController@getStreamCategoryBasedOnRevenueStream');
                        
                        /**
                         * * End expenses table
                         */
                        
                         
                         
                    });
                    
                    
                });
                

                // bank certificate of deposit
                Route::middleware('isCashManagement')->group(function () {

                    /**
                 * * Start Of Financial Institution Routes
                 */

                    Route::get('financial-institutions', 'FinancialInstitutionController@index')->name('view.financial.institutions');
                    Route::get('financial-institutions/create/{model?}', 'FinancialInstitutionController@create')->name('create.financial.institutions');
                    Route::post('financial-institutions/create', 'FinancialInstitutionController@store')->name('store.financial.institutions');
                    Route::get('financial-institutions/edit/{financialInstitution}', 'FinancialInstitutionController@edit')->name('edit.financial.institutions');
                    Route::put('financial-institutions/update/{financialInstitution}', 'FinancialInstitutionController@update')->name('update.financial.institutions');
                    Route::delete('financial-institutions/delete/{financialInstitution}', 'FinancialInstitutionController@destroy')->name('delete.financial.institutions');

                    Route::get('get-financial-institution-accounts-number-based-on-currency/{financialInstitution}/{currency}', 'FinancialInstitutionController@getAccountNumbersBasedOnCurrency');

                    Route::get('financial-institutions', 'FinancialInstitutionController@index')->name('view.financial.institutions');
                    Route::get('financial-institutions/create/{model?}', 'FinancialInstitutionController@create')->name('create.financial.institutions');
                    Route::post('financial-institutions/create', 'FinancialInstitutionController@store')->name('store.financial.institutions');
                    Route::get('financial-institutions/edit/{financialInstitution}', 'FinancialInstitutionController@edit')->name('edit.financial.institutions');
                    Route::put('financial-institutions/update/{financialInstitution}', 'FinancialInstitutionController@update')->name('update.financial.institutions');
                    Route::delete('financial-institutions/delete/{financialInstitution}', 'FinancialInstitutionController@destroy')->name('delete.financial.institutions');

                    Route::get('financial-institutions/{financialInstitution}/add-account', 'FinancialInstitutionController@addAccount')->name('financial.institution.add.account');
                    Route::post('financial-institutions/{financialInstitution}/add-account', 'FinancialInstitutionController@storeAccount')->name('financial.institution.store.account');
                    Route::get('financial-institution-accounts/edit/{financialInstitutionAccount}', 'FinancialInstitutionAccountController@edit')->name('edit.financial.institutions.account');
                    Route::put('financial-institution-accounts/update/{financialInstitution}/{financialInstitutionAccount}', 'FinancialInstitutionAccountController@update')->name('update.financial.institutions.account');
                    Route::delete('financial-institution-accounts/delete/{financialInstitutionAccount}', 'FinancialInstitutionAccountController@destroy')->name('delete.financial.institutions.account');
                    Route::put('financial-institution-accounts/lock-or-unlock/{financialInstitutionAccount}', 'FinancialInstitutionAccountController@lockOrUnlock')->name('lock.or.unlock.financial.institutions.account');

                    /**
                     * * Bank Accounts
                     * * لعرض الاكونتات الخاصة بالعميل في بنك معين او مؤسسة مالية
                     */
                    Route::get('financial-institutions/{financialInstitution}/bank-accounts', 'FinancialInstitutionController@viewAllAccounts')->name('view.all.bank.accounts');

                    Route::post('add-new-partner', 'AddNewCustomerController@addNew')->name('add.new.partner');
                    Route::post('add-new-partner/{type}', 'AddNewCustomerController@addNew2')->name('add.new.partner.type');
                    Route::resource('opening-balance', 'OpeningBalancesController');
                    Route::resource('customers-opening-balance', 'CustomerOpeningBalancesController');
                    Route::resource('suppliers-opening-balance', 'SupplierOpeningBalancesController');
                
                 
                    Route::group(['prefix'=>'general-settings'], function () {
                        Route::get('partners', 'PartnersController@index')->name('partners.index');
                        Route::get('partners/create', 'PartnersController@create')->name('partners.create');
                        Route::post('partners/store', 'PartnersController@store')->name('partners.store');
                        Route::get('partners/{partner}/edit', 'PartnersController@edit')->name('partners.edit');
                        Route::put('partners/{partner}/update', 'PartnersController@update')->name('partners.update');
                        Route::delete('partners/{partner}/delete', 'PartnersController@destroy')->name('partners.destroy');
                    
                        Route::get('customers', 'CustomersController@index')->name('customers.index');
                        Route::get('customers/create', 'CustomersController@create')->name('customers.create');
                        Route::post('customers/store', 'CustomersController@store')->name('customers.store');
                        Route::get('customers/{supplier}/edit', 'CustomersController@edit')->name('customers.edit');
                        Route::put('customers/{supplier}/update', 'CustomersController@update')->name('customers.update');
                        Route::delete('customers/{supplier}/delete', 'CustomersController@destroy')->name('customers.destroy');
                    
                    
                        Route::get('suppliers', 'SuppliersController@index')->name('suppliers.index');
                        Route::get('suppliers/create', 'SuppliersController@create')->name('suppliers.create');
                        Route::post('suppliers/store', 'SuppliersController@store')->name('suppliers.store');
                        Route::get('suppliers/{supplier}/edit', 'SuppliersController@edit')->name('suppliers.edit');
                        Route::put('suppliers/{supplier}/update', 'SuppliersController@update')->name('suppliers.update');
                        Route::delete('suppliers/{supplier}/delete', 'SuppliersController@destroy')->name('suppliers.destroy');
                    
                    
                    
                        Route::get('shareholders', 'ShareholdersController@index')->name('shareholders.index');
                        Route::get('shareholders/create', 'ShareholdersController@create')->name('shareholders.create');
                        Route::post('shareholders/store', 'ShareholdersController@store')->name('shareholders.store');
                        Route::get('shareholders/{shareholder}/edit', 'ShareholdersController@edit')->name('shareholders.edit');
                        Route::put('shareholders/{shareholder}/update', 'ShareholdersController@update')->name('shareholders.update');
                        Route::delete('shareholders/{shareholder}/delete', 'ShareholdersController@destroy')->name('shareholders.destroy');
                    
                        Route::get('employees', 'EmployeesController@index')->name('employees.index');
                        Route::get('employees/create', 'EmployeesController@create')->name('employees.create');
                        Route::post('employees/store', 'EmployeesController@store')->name('employees.store');
                        Route::get('employees/{employee}/edit', 'EmployeesController@edit')->name('employees.edit');
                        Route::put('employees/{employee}/update', 'EmployeesController@update')->name('employees.update');
                        Route::delete('employees/{employee}/delete', 'EmployeesController@destroy')->name('employees.destroy');
                    
                        Route::get('subsidiary-companies', 'SubsidiaryCompaniesController@index')->name('subsidiary.companies.index');
                        Route::get('subsidiary-companies/create', 'SubsidiaryCompaniesController@create')->name('subsidiary.companies.create');
                        Route::post('subsidiary-companies/store', 'SubsidiaryCompaniesController@store')->name('subsidiary.companies.store');
                        Route::get('subsidiary-companies/{subsidiaryCompany}/edit', 'SubsidiaryCompaniesController@edit')->name('subsidiary.companies.edit');
                        Route::put('subsidiary-companies/{subsidiaryCompany}/update', 'SubsidiaryCompaniesController@update')->name('subsidiary.companies.update');
                        Route::delete('subsidiary-companies/{subsidiaryCompany}/delete', 'SubsidiaryCompaniesController@destroy')->name('subsidiary.companies.destroy');
                    
                        Route::get('taxes', 'TaxesController@index')->name('taxes.index');
                        Route::get('taxes/create', 'TaxesController@create')->name('taxes.create');
                        Route::post('taxes/store', 'TaxesController@store')->name('taxes.store');
                        Route::get('taxes/{employee}/edit', 'TaxesController@edit')->name('taxes.edit');
                        Route::put('taxes/{employee}/update', 'TaxesController@update')->name('taxes.update');
                        Route::delete('taxes/{employee}/delete', 'TaxesController@destroy')->name('taxes.destroy');
                    
                    
                    
                        Route::get('other-partners', 'OtherPartnersController@index')->name('other.partners.index');
                        Route::get('other-partners/create', 'OtherPartnersController@create')->name('other.partners.create');
                        Route::post('other-partners/store', 'OtherPartnersController@store')->name('other.partners.store');
                        Route::get('other-partners/{otherPartner}/edit', 'OtherPartnersController@edit')->name('other.partners.edit');
                        Route::put('other-partners/{otherPartner}/update', 'OtherPartnersController@update')->name('other.partners.update');
                        Route::delete('other-partners/{otherPartner}/delete', 'OtherPartnersController@destroy')->name('other.partners.destroy');
                    
                        Route::get('business-sectors', 'BusinessSectorsController@index')->name('business.sectors.index');
                        Route::get('business-sectors/create', 'BusinessSectorsController@create')->name('business.sectors.create');
                        Route::post('business-sectors/store', 'BusinessSectorsController@store')->name('business.sectors.store');
                        Route::get('business-sectors/{businessSector}/edit', 'BusinessSectorsController@edit')->name('business.sectors.edit');
                        Route::put('business-sectors/{businessSector}/update', 'BusinessSectorsController@update')->name('business.sectors.update');
                        Route::delete('business-sectors/{businessSector}/delete', 'BusinessSectorsController@destroy')->name('business.sectors.destroy');
                    
                    
                        Route::get('business-units', 'BusinessUnitsController@index')->name('business.units.index');
                        Route::get('business-units/create', 'BusinessUnitsController@create')->name('business.units.create');
                        Route::post('business-units/store', 'BusinessUnitsController@store')->name('business.units.store');
                        Route::get('business-units/{businessUnit}/edit', 'BusinessUnitsController@edit')->name('business.units.edit');
                        Route::put('business-units/{businessUnit}/update', 'BusinessUnitsController@update')->name('business.units.update');
                        Route::delete('business-units/{businessUnit}/delete', 'BusinessUnitsController@destroy')->name('business.units.destroy');
                    
                    
                        Route::get('sales-channels', 'SalesChannelsController@index')->name('sales.channels.index');
                        Route::get('sales-channels/create', 'SalesChannelsController@create')->name('sales.channels.create');
                        Route::post('sales-channels/store', 'SalesChannelsController@store')->name('sales.channels.store');
                        Route::get('sales-channels/{salesChannel}/edit', 'SalesChannelsController@edit')->name('sales.channels.edit');
                        Route::put('sales-channels/{salesChannel}/update', 'SalesChannelsController@update')->name('sales.channels.update');
                        Route::delete('sales-channels/{salesChannel}/delete', 'SalesChannelsController@destroy')->name('sales.channels.destroy');
                    
                    
                    
                                        
                        Route::get('sales-persons', 'SalesPersonsController@index')->name('sales.persons.index');
                        Route::get('sales-persons/create', 'SalesPersonsController@create')->name('sales.persons.create');
                        Route::post('sales-persons/store', 'SalesPersonsController@store')->name('sales.persons.store');
                        Route::get('sales-persons/{salesPerson}/edit', 'SalesPersonsController@edit')->name('sales.persons.edit');
                        Route::put('sales-persons/{salesPerson}/update', 'SalesPersonsController@update')->name('sales.persons.update');
                        Route::delete('sales-persons/{salesPerson}/delete', 'SalesPersonsController@destroy')->name('sales.persons.destroy');
                    
                    
                    
                        Route::get('branches', 'BranchesController@index')->name('branches.index');
                        Route::get('branches/create', 'BranchesController@create')->name('branches.create');
                        Route::post('branches/store', 'BranchesController@store')->name('branches.store');
                        Route::get('branches/{branch}/edit', 'BranchesController@edit')->name('branches.edit');
                        Route::put('branches/{branch}/update', 'BranchesController@update')->name('branches.update');
                        Route::delete('branches/{branch}/delete', 'BranchesController@destroy')->name('branches.destroy');
                        Route::get('get-branches-from-currency', 'BranchesController@getBranchesForCurrency')->name('get.branch.based.on.currency');
                    
                    
                        Route::get('deductions', 'DeductionsController@index')->name('deductions.index');
                        Route::get('deductions/create', 'DeductionsController@create')->name('deductions.create');
                        Route::post('deductions/store', 'DeductionsController@store')->name('deductions.store');
                        Route::get('deductions/{deduction}/edit', 'DeductionsController@edit')->name('deductions.edit');
                        Route::put('deductions/{deduction}/update', 'DeductionsController@update')->name('deductions.update');
                        Route::delete('deductions/{deduction}/delete', 'DeductionsController@destroy')->name('deductions.destroy');
                    
                    
                    });
                 
                 
                 
                 
                    Route::get('lc-settlement-internal-money-transfers', 'LcSettlementInternalMoneyTransferController@index')->name('lc-settlement-internal-money-transfers.index');
                    Route::get('lc-settlement-internal-money-transfers/create', 'LcSettlementInternalMoneyTransferController@create')->name('lc-settlement-internal-money-transfers.create');
                    Route::post('lc-settlement-internal-money-transfers/store', 'LcSettlementInternalMoneyTransferController@store')->name('lc-settlement-internal-money-transfers.store');
                    Route::get('lc-settlement-internal-money-transfers/{lc_settlement_internal_transfer}/edit', 'LcSettlementInternalMoneyTransferController@edit')->name('lc-settlement-internal-money-transfers.edit');
                    Route::put('lc-settlement-internal-money-transfers/{lc_settlement_internal_transfer}/update', 'LcSettlementInternalMoneyTransferController@update')->name('lc-settlement-internal-money-transfers.update');
                    Route::delete('lc-settlement-internal-money-transfers/{lc_settlement_internal_transfer}/delete', 'LcSettlementInternalMoneyTransferController@destroy')->name('lc-settlement-internal-money-transfers.destroy');
                 
                 
                 
                 
                 
                 
                 
                 
                    Route::get('internal-money-transfers', 'InternalMoneyTransferController@index')->name('internal-money-transfers.index');
                    Route::get('internal-money-transfers/{type}/create', 'InternalMoneyTransferController@create')->name('internal-money-transfers.create');
                    Route::post('internal-money-transfers/{type}/store', 'InternalMoneyTransferController@store')->name('internal-money-transfers.store');
                    Route::get('internal-money-transfers/{type}/{internal_money_transfer}/edit', 'InternalMoneyTransferController@edit')->name('internal-money-transfers.edit');
                    Route::put('internal-money-transfers/{type}/{internal_money_transfer}/update', 'InternalMoneyTransferController@update')->name('internal-money-transfers.update');
                    Route::delete('internal-money-transfers/{type}/{internal_money_transfer}/delete', 'InternalMoneyTransferController@destroy')->name('internal-money-transfers.destroy');
                 
                 
                    Route::get('buy-or-sell-currencies', 'BuyOrSellCurrenciesController@index')->name('buy-or-sell-currencies.index');
                    Route::get('buy-or-sell-currencies/create', 'BuyOrSellCurrenciesController@create')->name('buy-or-sell-currencies.create');
                    Route::post('buy-or-sell-currencies/store', 'BuyOrSellCurrenciesController@store')->name('buy-or-sell-currencies.store');
                    Route::get('buy-or-sell-currencies/{buy_or_sell_currency}/edit', 'BuyOrSellCurrenciesController@edit')->name('buy-or-sell-currencies.edit');
                    Route::put('buy-or-sell-currencies/{buy_or_sell_currency}/update', 'BuyOrSellCurrenciesController@update')->name('buy-or-sell-currencies.update');
                    Route::delete('buy-or-sell-currencies/{buy_or_sell_currency}/delete', 'BuyOrSellCurrenciesController@destroy')->name('buy-or-sell-currencies.destroy');
                 
                 
                 
        
                 
                 
                    //  Route::get('internal-money-');
                 
                    //  Route::resource('contracts', 'ContractsController');
                    Route::post('store-po-allocation', 'ContractsController@storePoAllocations')->name('store.po.allocations');
                    Route::get('contracts/{type}', 'ContractsController@index')->name('contracts.index');
                    Route::get('contracts/create/{type}', 'ContractsController@create')->name('contracts.create');
                    Route::post('contracts/{type}', 'ContractsController@store')->name('contracts.store');
                    Route::get('contracts/{contract}/edit/{type}', 'ContractsController@edit')->name('contracts.edit');
                    Route::put('contracts/{contract}/{type}', 'ContractsController@update')->name('contracts.update');
                    Route::delete('contracts/{contract}/{type}', 'ContractsController@destroy')->name('contracts.destroy');
                    Route::get('get-contracts-for-customer-or-supplier', 'ContractsController@getContractsForCustomerOrSupplier')->name('get.contracts.for.customer.or.supplier');
                    Route::get('generate-contract-code/{type}', 'ContractsController@generateRandomCode')->name('generate.unique.rondom.contract.code');
                    Route::get('financial-institutions/js-update-contracts-based-on-customer', 'ContractsController@updateContractsBasedOnCustomer')->name('update.contracts.based.on.customer');
                    Route::get('financial-institutions/js-update-sales-orders-based-on-contract', 'ContractsController@updateSalesOrdersBasedOnContract')->name('update.sales.orders.based.on.contract');
                    Route::get('financial-institutions/js-update-purchase-orders-based-on-contract', 'ContractsController@updatePurchaseOrdersBasedOnContract')->name('update.purchase.orders.based.on.contract');
                    Route::get('financial-institutions/get-lc-issuance-based-of-financial-institution', 'FinancialInstitutionController@getLcIssuanceBasedOnFinancialInstitution')->name('update.lc.issuance.based.on.financial.institution');
                 
                 
                    //
                 
                    Route::get('expense-category', 'CashExpenseCategoryController@index')->name('cash.expense.category.index');
                    Route::get('expense-category/create', 'CashExpenseCategoryController@create')->name('cash.expense.category.create');
                    Route::post('expense-category', 'CashExpenseCategoryController@store')->name('cash.expense.category.store');
                    Route::get('expense-category/{cashExpenseCategory}/edit', 'CashExpenseCategoryController@edit')->name('cash.expense.category.edit');
                    Route::put('expense-category/{cashExpenseCategory}', 'CashExpenseCategoryController@update')->name('cash.expense.category.update');
                    Route::delete('expense-category/{cashExpenseCategory}', 'CashExpenseCategoryController@destroy')->name('cash.expense.category.destroy');
                    Route::get('update-expense-category-name-based-on-expense-category-category', 'CashExpenseCategoryController@updateExpenseCategoryNameBasedOnCategory')->name('update.expense.category.name.based.on.category');
                    //
                    Route::get('notifications/{type}', 'NotificationsController@index')->name('view.notifications');
                    Route::resource('notifications-settings', 'NotificationSettingsController');
                    Route::resource('odoo-settings', 'OdooSettingController');
                    Route::get('mark-notifications-as-read', 'NotificationSettingsController@markAsRead')->name('mark.notifications.as.read');

                    Route::get('adjust-due-dates/{modelId}/{modelType}', 'AdjustedDueDateHistoriesController@index')->name('adjust.due.dates');
                    Route::post('adjust-due-dates/{modelId}/{modelType}', 'AdjustedDueDateHistoriesController@store')->name('store.adjust.due.dates');
                    Route::get('adjust-due-dates/edit/{modelId}/{modelType}/{dueDateHistory}', 'AdjustedDueDateHistoriesController@edit')->name('edit.adjust.due.dates');
                    Route::patch('adjust-due-dates/edit/{modelId}/{modelType}/{dueDateHistory}', 'AdjustedDueDateHistoriesController@update')->name('update.adjust.due.dates');
                    Route::delete('delete-adjust-due-dates/edit/{modelId}/{modelType}/{dueDateHistory}', 'AdjustedDueDateHistoriesController@destroy')->name('delete.adjust.due.dates');
                 
                 
                 
                    //  Route::get('adjust-due-dates/{modelId}/{modelType}', 'AdjustedDueDateHistoriesController@index')->name('adjust.due.dates');
                    //  Route::post('adjust-due-dates/{modelId}/{modelType}', 'AdjustedDueDateHistoriesController@store')->name('store.adjust.due.dates');
                    //  Route::get('adjust-due-dates/edit/{modelId}/{modelType}/{dueDateHistory}', 'AdjustedDueDateHistoriesController@edit')->name('edit.adjust.due.dates');
                    Route::patch('invoice-deductions/edit/{modelId}/{modelType}', 'InvoiceDeductionsController@update')->name('update.invoice.deductions');
                    //  Route::delete('delete-invoice-deductions/edit/{modelId}/{modelType}', 'InvoiceDeductionsController@destroy')->name('delete.invoice.deductions');
                 

                 

                    Route::get('foreign-exchange-rate', 'ForeignExchangeRateController@index')->name('view.foreign.exchange.rate');
                    Route::post('foreign-exchange-rate', 'ForeignExchangeRateController@store')->name('store.foreign.exchange.rate');
                    Route::get('foreign-exchange-rate/edit/{foreignExchangeRate}', 'ForeignExchangeRateController@edit')->name('edit.foreign.exchange.rate');
                    Route::patch('foreign-exchange-rate/edit/{foreignExchangeRate}', 'ForeignExchangeRateController@update')->name('update.foreign.exchange.rate');
                    Route::delete('delete-foreign-exchange-rate/edit/{foreignExchangeRate}', 'ForeignExchangeRateController@destroy')->name('delete.foreign.exchange.rate');



                    Route::get('financial-institutions/{financialInstitution}/full-secured-overdraft', 'FullySecuredOverdraftController@index')->name('view.fully.secured.overdraft');
                    Route::get('financial-institutions/{financialInstitution}/full-secured-overdraft/create', 'FullySecuredOverdraftController@create')->name('create.fully.secured.overdraft');
                    Route::post('financial-institutions/{financialInstitution}/full-secured-overdraft/create', 'FullySecuredOverdraftController@store')->name('store.fully.secured.overdraft');
                    Route::get('financial-institutions/{financialInstitution}/full-secured-overdraft/edit/{fullySecuredOverdraft}', 'FullySecuredOverdraftController@edit')->name('edit.fully.secured.overdraft');
                    Route::put('financial-institutions/{financialInstitution}/full-secured-overdraft/update/{fullySecuredOverdraft}', 'FullySecuredOverdraftController@update')->name('update.fully.secured.overdraft');
                    Route::delete('financial-institutions/{financialInstitution}/full-secured-overdraft/delete/{fullySecuredOverdraft}', 'FullySecuredOverdraftController@destroy')->name('delete.fully.secured.overdraft');
                
                    Route::post('financial-institutions/{financialInstitution}/fully-secured-overdraft/apply-rate/{fullySecuredOverdraft}', 'FullySecuredOverdraftController@applyRate')->name('fully-secured-overdraft-apply.rates');
                    Route::post('financial-institutions/{financialInstitution}/fully-secured-overdraft/edit-rates/{rate}', 'FullySecuredOverdraftController@editRate')->name('fully-secured-overdraft-edit-rates');
                    Route::get('financial-institutions/{financialInstitution}/fully-secured-overdraft/delete-rates/{rate}', 'FullySecuredOverdraftController@deleteRate')->name('fully-secured-overdraft-delete-rate');
                 
                 
                 
                 
                    Route::get('financial-institutions/{financialInstitution}/clean-overdraft', 'CleanOverdraftController@index')->name('view.clean.overdraft');
                    Route::get('financial-institutions/{financialInstitution}/clean-overdraft/create', 'CleanOverdraftController@create')->name('create.clean.overdraft');
                    Route::post('financial-institutions/{financialInstitution}/clean-overdraft/create', 'CleanOverdraftController@store')->name('store.clean.overdraft');
                    Route::get('financial-institutions/{financialInstitution}/clean-overdraft/edit/{cleanOverdraft}', 'CleanOverdraftController@edit')->name('edit.clean.overdraft');
                    Route::put('financial-institutions/{financialInstitution}/clean-overdraft/update/{cleanOverdraft}', 'CleanOverdraftController@update')->name('update.clean.overdraft');
                    Route::delete('financial-institutions/{financialInstitution}/clean-overdraft/delete/{cleanOverdraft}', 'CleanOverdraftController@destroy')->name('delete.clean.overdraft');
                    Route::post('financial-institutions/{financialInstitution}/clean-overdraft/apply-rate/{cleanOverdraft}', 'CleanOverdraftController@applyRate')->name('clean-overdraft-apply.rates');
                    Route::post('financial-institutions/{financialInstitution}/clean-overdraft/edit-rates/{rate}', 'CleanOverdraftController@editRate')->name('clean-overdraft-edit-rates');
                    Route::get('financial-institutions/{financialInstitution}/clean-overdraft/delete-rates/{rate}', 'CleanOverdraftController@deleteRate')->name('clean-overdraft-delete-rate');
                 
                 
                    Route::get('financial-institutions/{financialInstitution}/medium-term-loan', 'MediumTermLoanController@index')->name('loans.index');
                    Route::get('financial-institutions/{financialInstitution}/medium-term-loan/create', 'MediumTermLoanController@create')->name('loans.create');
                    Route::post('financial-institutions/{financialInstitution}/medium-term-loan/store', 'MediumTermLoanController@store')->name('loans.store');
                    Route::get('financial-institutions/{financialInstitution}/medium-term-loan/{mediumTermLoan}/edit', 'MediumTermLoanController@edit')->name('loans.edit');
                    Route::put('financial-institutions/{financialInstitution}/medium-term-loan/{mediumTermLoan}/update', 'MediumTermLoanController@update')->name('loans.update');
                    Route::delete('financial-institutions/{financialInstitution}/medium-term-loan/{mediumTermLoan}/delete', 'MediumTermLoanController@destroy')->name('loans.destroy');
                 
                 
                
                 
                 
                    Route::get('loan-schedule-settlement/{loanSchedule}', 'MediumTermLoanController@viewLoanScheduleSettlement')->name('view.loan.schedule.settlements');
                    Route::post('loan-schedule-settlements/{loanSchedule}', 'MediumTermLoanController@storeLoanScheduleSettlement')->name('store.loan.schedule.settlements');
                    Route::get('edit-loan-schedule-settlement/{loanScheduleSettlement}', 'MediumTermLoanController@editLoanScheduleSettlement')->name('edit.loan.schedule.settlements');
                    Route::patch('loan-schedule-settlements/{loanScheduleSettlement}', 'MediumTermLoanController@updateLoanScheduleSettlement')->name('update.loan.schedule.settlements');
                    Route::delete('delete-loan-schedule-settlement/{loanScheduleSettlement}', 'MediumTermLoanController@deleteLoanScheduleSettlement')->name('delete.loan.schedule.settlements');
                    Route::get('medium-term-loan-report', 'MediumTermLoanController@refreshReport')->name('refresh.medium.term.loan.report'); // ajax
                    Route::get('get-medium-term-loan-for-financial-institution', 'MediumTermLoanController@getMediumTermLoanForFinancialInstitution')->name('get.medium.term.loan.for.financial.institution');
                 

                    Route::get('financial-institutions/{financialInstitution}/overdraft-against-commercial-paper', 'OverdraftAgainstCommercialPaperController@index')->name('view.overdraft.against.commercial.paper');
                    Route::get('financial-institutions/{financialInstitution}/overdraft-against-commercial-paper/create', 'OverdraftAgainstCommercialPaperController@create')->name('create.overdraft.against.commercial.paper');
                    Route::post('financial-institutions/{financialInstitution}/overdraft-against-commercial-paper/create', 'OverdraftAgainstCommercialPaperController@store')->name('store.overdraft.against.commercial.paper');
                    Route::get('financial-institutions/{financialInstitution}/overdraft-against-commercial-paper/edit/{overdraftAgainstCommercialPaper}', 'OverdraftAgainstCommercialPaperController@edit')->name('edit.overdraft.against.commercial.paper');
                    Route::put('financial-institutions/{financialInstitution}/overdraft-against-commercial-paper/update/{overdraftAgainstCommercialPaper}', 'OverdraftAgainstCommercialPaperController@update')->name('update.overdraft.against.commercial.paper');
                    Route::delete('financial-institutions/{financialInstitution}/overdraft-against-commercial-paper/delete/{overdraftAgainstCommercialPaper}', 'OverdraftAgainstCommercialPaperController@destroy')->name('delete.overdraft.against.commercial.paper');
                 
                    Route::post('financial-institutions/{financialInstitution}/overdraft-against-commercial-paper/apply-rate/{overdraftAgainstCommercialPaper}', 'OverdraftAgainstCommercialPaperController@applyRate')->name('overdraft-against-commercial-paper-apply.rates');
                    Route::post('financial-institutions/{financialInstitution}/overdraft-against-commercial-paper/edit-rates/{rate}', 'OverdraftAgainstCommercialPaperController@editRate')->name('overdraft-against-commercial-paper-edit-rates');
                    Route::get('financial-institutions/{financialInstitution}/overdraft-against-commercial-paper/delete-rates/{rate}', 'OverdraftAgainstCommercialPaperController@deleteRate')->name('overdraft-against-commercial-paper-delete-rate');
                 
                 
                 
                    Route::get('financial-institutions/{financialInstitution}/overdraft-against-assignment-of-contract', 'OverdraftAgainstAssignmentOfContractController@index')->name('view.overdraft.against.assignment.of.contract');
                    Route::get('financial-institutions/{financialInstitution}/overdraft-against-assignment-of-contract/create', 'OverdraftAgainstAssignmentOfContractController@create')->name('create.overdraft.against.assignment.of.contract');
                    Route::post('financial-institutions/{financialInstitution}/overdraft-against-assignment-of-contract/create', 'OverdraftAgainstAssignmentOfContractController@store')->name('store.overdraft.against.assignment.of.contract');
                    Route::get('financial-institutions/{financialInstitution}/overdraft-against-assignment-of-contract/edit/{odAgainstAssignmentOfContract}', 'OverdraftAgainstAssignmentOfContractController@edit')->name('edit.overdraft.against.assignment.of.contract');
                    Route::put('financial-institutions/{financialInstitution}/overdraft-against-assignment-of-contract/update/{odAgainstAssignmentOfContract}', 'OverdraftAgainstAssignmentOfContractController@update')->name('update.overdraft.against.assignment.of.contract');
                    Route::delete('financial-institutions/{financialInstitution}/overdraft-against-assignment-of-contract/delete/{odAgainstAssignmentOfContract}', 'OverdraftAgainstAssignmentOfContractController@destroy')->name('delete.overdraft.against.assignment.of.contract');
                 
                    Route::post('financial-institutions/{financialInstitution}/overdraft-against-assignment-of-contract/lending-information/{odAgainstAssignmentOfContract}', 'OverdraftAgainstAssignmentOfContractController@applyLendingInformation')->name('lending.information.apply.for.against.assignment.of.contract');
                    Route::post('financial-institutions/{financialInstitution}/overdraft-against-assignment-of-contract/edit-lending-information/{lendingInformation}', 'OverdraftAgainstAssignmentOfContractController@editLendingInformation')->name('lending.information.edit.for.against.assignment.of.contract');
                    Route::get('financial-institutions/{financialInstitution}/overdraft-against-assignment-of-contract/delete-lending-information/{lendingInformation}', 'OverdraftAgainstAssignmentOfContractController@deleteLendingInformation')->name('lending.information.delete.for.against.assignment.of.contract');
                    Route::get('financial-institutions/{financialInstitution}/overdraft-against-assignment-of-contract/apply-against-lending/{lendingInformation}', 'OverdraftAgainstAssignmentOfContractController@applyAgainstLending')->name('apply.against.lending');
                    Route::put('contract/{contract}/{type}/mark-as-finished', 'ContractsController@markAsFinished')->name('contract.mark.as.finished');
                    Route::put('contract/{contract}/{type}/mark-as-running-and-against', 'ContractsController@markAsRunningAndAgainst')->name('contract.mark.as.running.and.against');
                 
                 
                    Route::post('financial-institutions/{financialInstitution}/overdraft-against-assignment-of-contract/apply-rate/{odAgainstAssignmentOfContract}', 'OverdraftAgainstAssignmentOfContractController@applyRate')->name('overdraft-against-assignment-of-contract-apply.rates');
                    Route::post('financial-institutions/{financialInstitution}/overdraft-against-assignment-of-contract/edit-rates/{rate}', 'OverdraftAgainstAssignmentOfContractController@editRate')->name('overdraft-against-assignment-of-contract-edit-rates');
                    Route::get('financial-institutions/{financialInstitution}/overdraft-against-assignment-of-contract/delete-rates/{rate}', 'OverdraftAgainstAssignmentOfContractController@deleteRate')->name('overdraft-against-assignment-of-contract-delete-rate');
                 
                    /**
                     * * start certificates of deposit
                     */

                    Route::get('financial-institutions/{financialInstitution}/certificates-of-deposit', 'CertificatesOfDepositsController@index')->name('view.certificates.of.deposit');
                    Route::get('financial-institutions/{financialInstitution}/certificates-of-deposit/create', 'CertificatesOfDepositsController@create')->name('create.certificates.of.deposit');
                    Route::post('financial-institutions/{financialInstitution}/certificates-of-deposit/create', 'CertificatesOfDepositsController@store')->name('store.certificates.of.deposit');
                    Route::get('financial-institutions/{financialInstitution}/certificates-of-deposit/edit/{certificatesOfDeposit}', 'CertificatesOfDepositsController@edit')->name('edit.certificates.of.deposit');
                    Route::put('financial-institutions/{financialInstitution}/certificates-of-deposit/update/{certificatesOfDeposit}', 'CertificatesOfDepositsController@update')->name('update.certificates.of.deposit');
                    Route::delete('financial-institutions/{financialInstitution}/certificates-of-deposit/delete/{certificatesOfDeposit}', 'CertificatesOfDepositsController@destroy')->name('delete.certificates.of.deposit');
                    Route::post('financial-institutions/{financialInstitution}/certificates-of-deposit/apply-deposit/{certificatesOfDeposit}', 'CertificatesOfDepositsController@applyDeposit')->name('apply.deposit.to.certificate.of.deposit');
                    Route::post('financial-institutions/{financialInstitution}/certificates-of-deposit/apply-break/{certificatesOfDeposit}', 'CertificatesOfDepositsController@applyBreak')->name('apply.break.to.certificate.of.deposit');
                    Route::post('financial-institutions/{financialInstitution}/certificates-of-deposit/reverse-deposit/{certificatesOfDeposit}', 'CertificatesOfDepositsController@reverseDeposit')->name('reverse.deposit.to.certificate.of.deposit');
                    Route::post('financial-institutions/{financialInstitution}/certificates-of-deposit/reverse-broken/{certificatesOfDeposit}', 'CertificatesOfDepositsController@reverseBroken')->name('reverse.broken.to.certificate.of.deposit');
                    
                    Route::post('financial-institutions/{financialInstitution}/certificates-of-deposit/apply-period-interest/{certificatesOfDeposit}', 'CertificatesOfDepositsController@applyPeriodInterest')->name('apply.period.interest.to.certificates.of.deposit');
                    Route::get('financial-institutions/{financialInstitution}/certificates-of-deposit/view-period-interests/{certificatesOfDeposit}', 'CertificatesOfDepositsController@viewPeriodInterest')->name('view.period.interest.to.certificates.of.deposit');
                    Route::delete('financial-institutions/{financialInstitution}/certificates-of-deposit/delete-period-interests/{certificatesOfDeposit}/{currentAccountBankStatement}', 'CertificatesOfDepositsController@deletePeriodInterest')->name('delete.period.interest.to.certificates.of.deposit');
                     
                    /**
                     * * end certificates of deposit
                     */





                    /**
                     * * start time of deposit
                     */

                    Route::get('financial-institutions/{financialInstitution}/time-of-deposit', 'TimeOfDepositsController@index')->name('view.time.of.deposit');
                    Route::get('financial-institutions/{financialInstitution}/time-of-deposit/create', 'TimeOfDepositsController@create')->name('create.time.of.deposit');
                    Route::post('financial-institutions/{financialInstitution}/time-of-deposit/create', 'TimeOfDepositsController@store')->name('store.time.of.deposit');
                    Route::get('financial-institutions/{financialInstitution}/time-of-deposit/edit/{timeOfDeposit}', 'TimeOfDepositsController@edit')->name('edit.time.of.deposit');
                    Route::put('financial-institutions/{financialInstitution}/time-of-deposit/update/{timeOfDeposit}', 'TimeOfDepositsController@update')->name('update.time.of.deposit');
                    Route::delete('financial-institutions/{financialInstitution}/time-of-deposit/delete/{timeOfDeposit}', 'TimeOfDepositsController@destroy')->name('delete.time.of.deposit');
                    Route::post('financial-institutions/{financialInstitution}/time-of-deposit/apply-deposit/{timeOfDeposit}', 'TimeOfDepositsController@applyDeposit')->name('apply.deposit.to.time.of.deposit');
                    Route::post('financial-institutions/{financialInstitution}/time-of-deposit/apply-period-interest/{timeOfDeposit}', 'TimeOfDepositsController@applyPeriodInterest')->name('apply.period.interest.to.time.of.deposit');
                    Route::get('financial-institutions/{financialInstitution}/time-of-deposit/view-period-interests/{timeOfDeposit}', 'TimeOfDepositsController@viewPeriodInterest')->name('view.period.interest.to.time.of.deposit');
                    Route::delete('financial-institutions/{financialInstitution}/time-of-deposit/delete-period-interests/{timeOfDeposit}/{currentAccountBankStatement}', 'TimeOfDepositsController@deletePeriodInterest')->name('delete.period.interest.to.time.of.deposit');
                    Route::post('financial-institutions/{financialInstitution}/time-of-deposit/apply-break/{timeOfDeposit}', 'TimeOfDepositsController@applyBreak')->name('apply.break.to.time.of.deposit');
                    Route::post('financial-institutions/{financialInstitution}/time-of-deposit/reverse-deposit/{timeOfDeposit}', 'TimeOfDepositsController@reverseDeposit')->name('reverse.deposit.to.time.of.deposit');
                    Route::post('financial-institutions/{financialInstitution}/time-of-deposit/reverse-broken/{timeOfDeposit}', 'TimeOfDepositsController@reverseBroken')->name('reverse.broken.to.time.of.deposit');

                     
                     
                     
                    Route::get('time-of-deposit-renewal-date/{timeOfDeposit}', 'TimeOfDepositRenewalDateController@index')->name('time.of.deposit.renewal.date');
                    Route::post('time-of-deposit-renewal-date/{timeOfDeposit}', 'TimeOfDepositRenewalDateController@store')->name('store.time.of.deposit.renewal.date');
                    Route::get('time-of-deposit-renewal-date/edit/{timeOfDeposit}/{TdRenewalDateHistory}', 'TimeOfDepositRenewalDateController@edit')->name('edit.time.of.deposit.renewal.date');
                    Route::patch('time-of-deposit-renewal-date/edit/{timeOfDeposit}/{TdRenewalDateHistory}', 'TimeOfDepositRenewalDateController@update')->name('update.time.of.deposit.renewal.date');
                    Route::delete('delete-time-of-deposit-renewal-date/{timeOfDeposit}/{TdRenewalDateHistory}', 'TimeOfDepositRenewalDateController@destroy')->name('delete.time.of.deposit.renewal.date');
                     
                     
                    /**
                     * * end time of deposit
                     */




                    Route::get('financial-institutions/{financialInstitution}/letter-of-guarantee-facility', 'LetterOfGuaranteeFacilityController@index')->name('view.letter.of.guarantee.facility');
                    Route::get('financial-institutions/{financialInstitution}/letter-of-guarantee-facility/create', 'LetterOfGuaranteeFacilityController@create')->name('create.letter.of.guarantee.facility');
                    Route::post('financial-institutions/{financialInstitution}/letter-of-guarantee-facility/create', 'LetterOfGuaranteeFacilityController@store')->name('store.letter.of.guarantee.facility');
                    Route::get('financial-institutions/{financialInstitution}/letter-of-guarantee-facility/edit/{letterOfGuaranteeFacility}', 'LetterOfGuaranteeFacilityController@edit')->name('edit.letter.of.guarantee.facility');
                    Route::put('financial-institutions/{financialInstitution}/letter-of-guarantee-facility/update/{letterOfGuaranteeFacility}', 'LetterOfGuaranteeFacilityController@update')->name('update.letter.of.guarantee.facility');
                    Route::delete('financial-institutions/{financialInstitution}/letter-of-guarantee-facility/delete/{letterOfGuaranteeFacility}', 'LetterOfGuaranteeFacilityController@destroy')->name('delete.letter.of.guarantee.facility');
                    Route::get('financial-institutions/update-outstanding-balance-and-limits', 'LetterOfGuaranteeFacilityController@updateOutstandingBalanceAndLimits')->name('update.letter.of.guarantee.outstanding.balance.and.limit');
                    Route::get('get-lg-facility-based-on-financial-institution', 'LetterOfGuaranteeFacilityController@getLgFacilityBasedOnFinancialInstitution')->name('get.lg.facility.based.on.financial.institution');
                    Route::get('letter-of-guarantee-issuance', 'LetterOfGuaranteeIssuanceController@index')->name('view.letter.of.guarantee.issuance');
                    Route::get('letter-of-guarantee-issuance/create/{source}', 'LetterOfGuaranteeIssuanceController@create')->name('create.letter.of.guarantee.issuance');
                    Route::post('letter-of-guarantee-issuance/create/{source}', 'LetterOfGuaranteeIssuanceController@store')->name('store.letter.of.guarantee.issuance');
                    Route::get('letter-of-guarantee-issuance/edit/{letterOfGuaranteeIssuance}/{source}', 'LetterOfGuaranteeIssuanceController@edit')->name('edit.letter.of.guarantee.issuance');
                    Route::put('letter-of-guarantee-issuance/update/{letterOfGuaranteeIssuance}/{source}', 'LetterOfGuaranteeIssuanceController@update')->name('update.letter.of.guarantee.issuance');
                    Route::delete('letter-of-guarantee-issuance/delete/{letterOfGuaranteeIssuance}/{source}', 'LetterOfGuaranteeIssuanceController@destroy')->name('delete.letter.of.guarantee.issuance');
                    Route::post('letter-of-guarantee-issuance/cancel/{letterOfGuaranteeIssuance}/{source}', 'LetterOfGuaranteeIssuanceController@cancel')->name('cancel.letter.of.guarantee.issuance');
                    Route::post('letter-of-guarantee-issuance/apply-amount-to-be-decreased/{letterOfGuaranteeIssuance}/{source}', 'LetterOfGuaranteeIssuanceController@applyAmountToBeDecreased')->name('advanced.lg.payment.apply.amount.to.be.decreased');
                    Route::post('letter-of-guarantee-issuance/edit-amount-to-be-decreased/{lgAdvancedPaymentHistory}/{source}', 'LetterOfGuaranteeIssuanceController@editAmountToBeDecreased')->name('advanced.lg.payment.edit.amount.to.be.decreased');
                    Route::get('letter-of-guarantee-issuance/delete-advanced-payment/{lgAdvancedPaymentHistory}', 'LetterOfGuaranteeIssuanceController@deleteAdvancedPayment')->name('delete.lg.advanced.payment');
                    Route::post('letter-of-guarantee-issuance/back-to-running/{letterOfGuaranteeIssuance}/{source}', 'LetterOfGuaranteeIssuanceController@backToRunningStatus')->name('back.to.running.letter.of.guarantee.issuance');
                    
                                     
                    Route::get('letter-of-guarantee-issuance-renewal-date/{letterOfGuaranteeIssuance}', 'LetterOfGuaranteeIssuanceRenewalDateController@index')->name('letter.of.issuance.renewal.date');
                    Route::post('letter-of-guarantee-issuance-renewal-date/{letterOfGuaranteeIssuance}', 'LetterOfGuaranteeIssuanceRenewalDateController@store')->name('store.letter.of.issuance.renewal.date');
                    Route::get('letter-of-guarantee-issuance-renewal-date/edit/{letterOfGuaranteeIssuance}/{LgRenewalDateHistory}', 'LetterOfGuaranteeIssuanceRenewalDateController@edit')->name('edit.letter.of.issuance.renewal.date');
                    Route::patch('letter-of-guarantee-issuance-renewal-date/edit/{letterOfGuaranteeIssuance}/{LgRenewalDateHistory}', 'LetterOfGuaranteeIssuanceRenewalDateController@update')->name('update.letter.of.issuance.renewal.date');
                    Route::delete('delete-letter-of-guarantee-issuance-renewal-date/{letterOfGuaranteeIssuance}/{LgRenewalDateHistory}', 'LetterOfGuaranteeIssuanceRenewalDateController@destroy')->name('delete.letter.of.issuance.renewal.date');
                 
                
                    
                    // letter of credit issuance
                    
                    Route::get('letter-of-credit-issuance', 'LetterOfCreditIssuanceController@index')->name('view.letter.of.credit.issuance');
                    Route::get('letter-of-credit-issuance/create/{source}', 'LetterOfCreditIssuanceController@create')->name('create.letter.of.credit.issuance');
                    Route::post('letter-of-credit-issuance/create/{source}', 'LetterOfCreditIssuanceController@store')->name('store.letter.of.credit.issuance');
                    Route::get('letter-of-credit-issuance/edit/{letterOfCreditIssuance}/{source}', 'LetterOfCreditIssuanceController@edit')->name('edit.letter.of.credit.issuance');
                    Route::put('letter-of-credit-issuance/update/{letterOfCreditIssuance}/{source}', 'LetterOfCreditIssuanceController@update')->name('update.letter.of.credit.issuance');
                    Route::delete('letter-of-credit-issuance/delete/{letterOfCreditIssuance}/{source}', 'LetterOfCreditIssuanceController@destroy')->name('delete.letter.of.credit.issuance');
                    Route::post('letter-of-credit-issuance/cancel/{letterOfCreditIssuance}/{source}', 'LetterOfCreditIssuanceController@markAsPaid')->name('make.letter.of.credit.issuance.as.paid');
                    // Route::post('letter-of-credit-issuance/apply-amount-to-be-decreased/{letterOfCreditIssuance}/{source}', 'LetterOfCreditIssuanceController@applyAmountToBeDecreased')->name('advanced.lc.payment.apply.amount.to.be.decreased');
                    // Route::post('letter-of-credit-issuance/edit-amount-to-be-decreased/{lcAdvancedPaymentHistory}/{source}', 'LetterOfCreditIssuanceController@editAmountToBeDecreased')->name('advanced.lc.payment.edit.amount.to.be.decreased');
                    // Route::get('letter-of-credit-issuance/delete-advanced-payment/{lcAdvancedPaymentHistory}', 'LetterOfCreditIssuanceController@deleteAdvancedPayment')->name('delete.lc.advanced.payment');
                    Route::post('letter-of-credit-issuance/back-to-running/{letterOfCreditIssuance}/{source}', 'LetterOfCreditIssuanceController@backToRunningStatus')->name('back.to.running.letter.of.credit.issuance');
                    Route::get('financial-institutions/update-outstanding-balance-and-limits-for-lc', 'LetterOfCreditFacilityController@updateOutstandingBalanceAndLimits')->name('update.letter.of.credit.outstanding.balance.and.limit');
                    Route::get('get-lc-facility-based-on-financial-institution', 'LetterOfCreditFacilityController@getLcFacilityBasedOnFinancialInstitution')->name('get.lc.facility.based.on.financial.institution');

                    Route::post('letter-of-credit-issuance/apply-expense/{letterOfCreditIssuance}', 'LetterOfCreditIssuanceController@applyExpense')->name('apply.lc.issuance.expense');
                    Route::post('letter-of-credit-issuance/update-expense/{expense}', 'LetterOfCreditIssuanceController@updateExpense')->name('update.lc.issuance.expense');
                    Route::get('letter-of-credit-issuance/delete-expense/{expense}', 'LetterOfCreditIssuanceController@deleteExpense')->name('delete.lc.issuance.expense');
                    Route::get('letter-of-credit-issuance-remaining-balance', 'LetterOfCreditIssuanceController@getRemainingBalance')->name('get.remaining.balance.lc.issuance');
                    
                    // end letter of credit issuance
                    
                    Route::get('financial-institutions/{financialInstitution}/letter-of-credit-facility', 'LetterOfCreditFacilityController@index')->name('view.letter.of.credit.facility');
                    Route::get('financial-institutions/{financialInstitution}/letter-of-credit-facility/create', 'LetterOfCreditFacilityController@create')->name('create.letter.of.credit.facility');
                    Route::post('financial-institutions/{financialInstitution}/letter-of-credit-facility/create', 'LetterOfCreditFacilityController@store')->name('store.letter.of.credit.facility');
                    Route::get('financial-institutions/{financialInstitution}/letter-of-credit-facility/edit/{letterOfCreditFacility}', 'LetterOfCreditFacilityController@edit')->name('edit.letter.of.credit.facility');
                    Route::put('financial-institutions/{financialInstitution}/letter-of-credit-facility/update/{letterOfCreditFacility}', 'LetterOfCreditFacilityController@update')->name('update.letter.of.credit.facility');
                    Route::delete('financial-institutions/{financialInstitution}/letter-of-credit-facility/delete/{letterOfCreditFacility}', 'LetterOfCreditFacilityController@destroy')->name('delete.letter.of.credit.facility');

                    
                    
                    
                    
                    Route::get('aging-analysis/{modelType}', 'AgingController@index')->name('view.aging.analysis');
                    Route::post('aging-analysis/{modelType}', 'AgingController@result')->name('result.aging.analysis');
                    
                    
                    
                    
                    Route::get('effectiveness-index-report/collection', 'CollectionEffectivenessIndexController@index')->name('view.collections.effectiveness.index');
                    Route::post('effectiveness-index-report/collection', 'CollectionEffectivenessIndexController@result')->name('result.collections.effectiveness.index');


                    Route::get('safe-statement', 'SafeStatementController@index')->name('view.safe.statement');
                    Route::post('safe-statement', 'SafeStatementController@result')->name('result.safe.statement');

                    Route::get('cash-expense-statement', 'CashExpenseStatementController@index')->name('view.cash.expense.statement');
                    Route::post('cash-expense-statement', 'CashExpenseStatementController@result')->name('result.cash.expense.statement');
                    
                    Route::get('partners-statement', 'PartnersStatementController@index')->name('view.partners.statement');
                    Route::post('partners-statement', 'PartnersStatementController@result')->name('result.partners.statement');
                    
                    
                    Route::get('show-bank-statement', 'BankStatementController@index')->name('view.bank.statement');
                    Route::get('bank-statement', 'BankStatementController@result')->name('result.bank.statement');
                    
                    Route::post('update-commission-fees', 'BankStatementController@updateCommissionFees')->name('update.commission.fees');
                    Route::post('update-bank-statement-row-fees', 'BankStatementController@updateBankStatementRow')->name('update.bank.statement.debit.or.credit');
                    
                    Route::get('show-lg-by-beneficiary-name-report', 'LgByBeneficiaryNameReportController@index')->name('view.lg.by.beneficiary.name.report');
                    Route::get('lg-by-beneficiary-name-report', 'LgByBeneficiaryNameReportController@result')->name('result.lg.by.beneficiary.name.report');
                    
                    Route::get('show-lg-by-bank-name-report', 'LgByBankNameReportController@index')->name('view.lg.by.bank.name.report');
                    Route::get('lg-by-bank-name-report', 'LgByBankNameReportController@result')->name('result.lg.by.bank.name.report');
                    
                    Route::get('lg-lc-bank-statement', 'LGLCSBanktatementController@index')->name('view.lg.lc.bank.statement');
                    Route::post('lg-lc-bank-statement', 'LGLCSBanktatementController@result')->name('result.lg.lc.bank.statement');
                    Route::get('get-lg-lc-types', 'LGLCSBanktatementController@getLgOrLcType')->name('get.lc.or.lg.types');

                    Route::get('customer-balances/{modelType}', 'BalancesController@index')->name('view.balances');
                    Route::get('/cashvero-dashboard/cash', 'CustomerInvoiceDashboardController@viewCashDashboard')->name('view.customer.invoice.dashboard.cash');

                    Route::get('/cashvero-dashboard/forecast', 'CustomerInvoiceDashboardController@viewForecastDashboard')->name('view.customer.invoice.dashboard.forecast');
                    Route::get('/cashvero-dashboard/lglc', 'CustomerInvoiceDashboardController@viewLGLCDashboard')->name('view.lglc.dashboard');
                    // Route::get('/cashvero-dashboard-update-lg-dashboard','CustomerInvoiceDashboardController@updateLgDashboard')->name('update.lg.table.and.charts');
                    Route::get('/customer-balances/invoices-report/{partnerId}/{currency}/{modelType}', 'CustomerInvoiceDashboardController@showInvoiceReport')->name('view.invoice.report');
                    Route::get('/customer-balances/invoices-statement-report/{partnerId}/{currency}/{modelType}', 'CustomerInvoiceDashboardController@showInvoiceStatementReport')->name('view.invoice.statement.report');
                    Route::get('/customer-balances/total-net-balance-details/{currency}/{modelType}', 'BalancesController@showTotalNetBalanceDetailsReport')->name('show.total.net.balance.in');
                    // Route::get('collection-effectiveness-index-report',[]);
                    Route::get('get-contract-name-for-customer-or-supplier', 'getProjectsForCustomerOrSupplierController@handle')->name('get.projects.for.customer.or.supplier');
                    Route::get('get-po-or-so-for-contract', 'getPoOrSoFromContractController@handle')->name('get.po.or.so.from.contract');
                    Route::get('cashflow-report', 'CashFlowReportController@index')->name('view.cashflow.report');
                    Route::get('cashflow-report-result/{returnResultAsArray?}/{cashflowReport?}', 'CashFlowReportController@result')->name('result.cashflow.report');
                    Route::delete('delete-cashflow-report/{cashflowReport}', 'CashFlowReportController@destroy')->name('delete.cashflow.report');
                    Route::get('contract-cashflow-report', 'ContractCashFlowReportController@index')->name('view.contract.cashflow.report');
                    Route::get('contract-cashflow-report-result/{returnResultAsArray?}/{cashflowReport?}', 'ContractCashFlowReportController@result')->name('result.contract.cashflow.report');
                    

                    Route::get('withdrawals-settlements-report', 'WithdrawalsSettlementReportController@index')->name('view.withdrawals.settlement.report');
                    Route::post('withdrawals-settlements-report', 'WithdrawalsSettlementReportController@result')->name('result.withdrawals.settlement.report');

                    Route::get('refresh-withdrawal-dues-report', 'WithdrawalsSettlementReportController@refreshReport')->name('refresh.withdrawal.report'); // ajax

                    
                    Route::get('down-payment-contracts/{partnerId}/{modelType}/{currency}', 'DownPaymentContractsController@viewContractsWithDownPayments')->name('view.contracts.down.payments');
                    Route::get('down-payment-contracts-settlements/{downPaymentId}/{modelType}', 'DownPaymentContractsController@downPaymentSettlements')->name('view.down.payment.settlement');
                    Route::post('store-down-payment-settlement/{downPaymentId}/{partnerId}/{modelType}', 'DownPaymentContractsController@storeDownPaymentSettlement')->name('store.down.payment.settlement');
                    
                    Route::post('read-odoo-invoices', 'ReadOdooInvoices@handle')->name('read-odoo-invoices');
                    Route::post('read-odoo-contracts', 'ReadOdooContracts@handle')->name('read-odoo-contracts');
                    Route::post('read-odoo-partners', 'ReadOdooPartners@handle')->name('read-odoo-partners');
                    Route::post('send-odoo-collection-or-payments', 'SendOdooCollectionOrPayment@handle')->name('send-odoo-collection-or-payments');
                    Route::post('read-expenses', 'ReadOdooExpense@handle')->name('read-odoo-expenses');
                    Route::get('allocate-expense/{cashExpense}', 'CashExpenseController@viewAllocation')->name('cash.expense.allocate');
                    Route::put('allocate-expense/{cashExpense}', 'CashExpenseController@postAllocation')->name('allocate.odoo.cash.expense');
                    
                    Route::get('money-received', 'MoneyReceivedController@index')->name('view.money.receive');
                    Route::post('resend-odoo-money/{moneyReceived}', 'MoneyReceivedController@resendToOdoo')->name('resend.with.odoo');
                    Route::get('money-received/create/{model?}', 'MoneyReceivedController@create')->name('create.money.receive');
                    Route::post('money-received/create', 'MoneyReceivedController@store')->name('store.money.receive');
                    Route::get('money-received/edit/{moneyReceived}', 'MoneyReceivedController@edit')->name('edit.money.receive');
                    Route::put('money-received/update/{moneyReceived}', 'MoneyReceivedController@update')->name('update.money.receive');
                    Route::delete('money-received/delete/{moneyReceived}', 'MoneyReceivedController@destroy')->name('delete.money.receive');
                    Route::get('money-received/get-invoice-numbers/{customer_name}/{currency?}', 'MoneyReceivedController@getInvoiceNumber'); // ajax request
                    Route::get('money-received/get-account-numbers-based-on-account-type/{accountType}/{currency}/{financialInstitutionId}', 'MoneyReceivedController@getAccountNumbersForAccountType'); // ajax request
                    Route::get('money-received/get-account-ids-based-on-account-type/{accountType}/{currency}/{financialInstitutionId}', 'MoneyReceivedController@getAccountIdsForAccountType'); // ajax request
                    Route::get('money-received/get-net-balance-based-on-account-number', 'MoneyReceivedController@updateNetBalanceBasedOnAccountNumber')->name('update.balance.and.net.balance.based.on.account.number');
                    Route::get('money-received/get-net-balance-based-on-account-id-by-ajax/{accountType}/{accountId}/{financialInstitutionId}', 'MoneyReceivedController@updateNetBalanceBasedOnAccountIdByAjax')->name('update.balance.and.net.balance.based.on.account.id.ajax');
                    Route::get('money-received/get-account-amount-based-on-account-id/{accountType}/{accountId}/{financialInstitutionId}', 'MoneyReceivedController@getAccountAmountForAccountId')->name('get.account.amount.based.on.account.id'); // ajax request
                    Route::get('get-customers-based-on-currency/{currencyName}', 'MoneyReceivedController@getCustomersBasedOnCurrency');
                    Route::get('get-partners-based-on-type/{currencyName}', 'MoneyReceivedController@getPartnersBasedOnCurrency');
                    Route::get('get-beneficiary-name-from-lg-issuance-based-on-currency', 'LetterOfGuaranteeIssuanceController@getBeneficiaryNameByCurrency')->name('get.beneficiary.name.by.currency');
                    Route::get('get-bank-name-from-lg-issuance-based-on-currency', 'LetterOfGuaranteeIssuanceController@getBankNameByCurrency')->name('get.bank.name.by.currency');
                    Route::post('confirmed-reviewed/{model}', 'MoneyReceivedController@markAsConfirmed')->name('confirmed.review');
                    
                    Route::get('money-received', 'MoneyReceivedController@index')->name('view.money.receive');
                    Route::get('money-received/create/{model?}', 'MoneyReceivedController@create')->name('create.money.receive');
                    Route::post('money-received/create', 'MoneyReceivedController@store')->name('store.money.receive');
                    Route::get('money-received/edit/{moneyReceived}', 'MoneyReceivedController@edit')->name('edit.money.receive');
                    Route::put('money-received/update/{moneyReceived}', 'MoneyReceivedController@update')->name('update.money.receive');
                    Route::delete('money-received/delete/{moneyReceived}', 'MoneyReceivedController@destroy')->name('delete.money.receive');
                    Route::get('money-received/get-invoice-numbers/{customer_name}/{currency?}', 'MoneyReceivedController@getInvoiceNumber'); // ajax request
                    Route::get('money-received/get-account-numbers-based-on-account-type/{accountType}/{currency}/{financialInstitutionId}', 'MoneyReceivedController@getAccountNumbersForAccountType'); // ajax request
                    Route::get('get-interest-rate-for-financial-institution-id', 'FinancialInstitutionController@getInterestRateForFinancialInstitution')->name('get.interest.rate.for.financial.institution.id');

                    // money payments
                    
                    Route::get('money-payment', 'MoneyPaymentController@index')->name('view.money.payment');
                    Route::get('money-payment/create/{model?}', 'MoneyPaymentController@create')->name('create.money.payment');
                    Route::post('money-payment/create', 'MoneyPaymentController@store')->name('store.money.payment');
                    Route::get('money-payment/edit/{moneyPayment}', 'MoneyPaymentController@edit')->name('edit.money.payment');
                    Route::put('money-payment/update/{moneyPayment}', 'MoneyPaymentController@update')->name('update.money.payment');
                    Route::delete('money-payment/delete/{moneyPayment}', 'MoneyPaymentController@destroy')->name('delete.money.payment');
                    Route::get('money-payment/get-invoice-numbers/{supplier_name}/{currency?}', 'MoneyPaymentController@getInvoiceNumber'); // ajax request
                    Route::get('money-payment/get-account-numbers-based-on-account-type/{accountType}/{currency}/{financialInstitutionId}', 'MoneyPaymentController@getAccountNumbersForAccountType'); // ajax request
                    Route::post('mark-payable-cheques-as-paid', 'MoneyPaymentController@markChequesAsPaid')->name('payable.cheque.mark.as.paid');
                    Route::post('mark-outgoing-transfer-as-paid', 'MoneyPaymentController@markOutgoingTransfersAsPaid')->name('outgoing.transfer.mark.as.paid');
                    Route::get('get-supplier-invoices', 'SupplierInvoicesController@getSupplierInvoicesForSupplier')->name('get.supplier.invoices');
                    Route::get('get-suppliers-based-on-currency/{currencyName}', 'MoneyPaymentController@getSuppliersBasedOnCurrency');
                    Route::get('get-current-end-balance-of-current-account', 'MoneyPaymentController@getCashInSafeStatementEndBalance')->name('get.current.end.balance.of.cash.in.safe.statement');
                    // cash expense
                    Route::get('get-exchange-rate-for-date-and-currencies', 'ForeignExchangeRateController@getExchangeRate');
                    
                    Route::get('odoo-approved-expenses', 'OdooExpensesController@index')->name('odoo-expenses.index');
                    // Route::get('odoo-approved-expenses/create','OdooExpensesController@create')->name('odoo-expenses.create');
                    Route::post('odoo-approved-expenses/mark-as-paid', 'OdooExpensesController@markAsPaid')->name('odoo-expenses.mark.as.paid');
                    // Route::get('odoo-approved-expenses/{odooExpense}/edit','OdooExpensesController@edit')->name('odoo-expenses.edit');
                    // Route::put('odoo-approved-expenses/{odooExpense}/update','OdooExpensesController@update')->name('odoo-expenses.update');
                    Route::delete('odoo-approved-expenses/{odooExpense}/delete', 'OdooExpensesController@destroy')->name('odoo-expenses.destroy');
                    
                    Route::get('cash-expense', 'CashExpenseController@index')->name('view.cash.expense');
                    Route::get('cash-expense/create/{model?}', 'CashExpenseController@create')->name('create.cash.expense');
                    Route::post('cash-expense/create', 'CashExpenseController@store')->name('store.cash.expense');
                    Route::get('cash-expense/edit/{cashExpense}', 'CashExpenseController@edit')->name('edit.cash.expense');
                   
                    Route::put('cash-expense/update/{cashExpense}', 'CashExpenseController@update')->name('update.cash.expense');
                    Route::delete('cash-expense/delete/{cashExpense}', 'CashExpenseController@destroy')->name('delete.cash.expense');
                    Route::get('cash-expense/get-account-numbers-based-on-account-type/{accountType}/{currency}/{financialInstitutionId}', 'CashExpenseController@getAccountNumbersForAccountType'); // ajax request
                    Route::post('cash-expense-mark-payable-cheques-as-paid', 'CashExpenseController@markChequesAsPaid')->name('cash.expense.payable.cheque.mark.as.paid');
                    Route::post('cash-expense-mark-outgoing-transfer-as-paid', 'CashExpenseController@markOutgoingTransfersAsPaid')->name('cash.expense.outgoing.transfer.mark.as.paid');

                    
                    Route::post('adjust-customer-due-invoices', 'CashFlowReportController@adjustCustomerDueInvoices')->name('adjust.customer.dues.invoices');
                    Route::post('adjust-loan-past-due-installments', 'CashFlowReportController@adjustLoanPastDueInstallments')->name('adjust.loan.past.dues.installments');
                    Route::post('save-projections', 'CashFlowReportController@saveProjection')->name('save.projection');
                    // Route::post('adjust-loan-past-due-installments','CashFlowReportController@storeProjection')->name('store.projection');
                    
                    // Route::get('unapplied-amounts/{partnerId}/{modelType}', 'UnappliedAmountController@index')->name('view.settlement.by.unapplied.amounts');
                    // Route::get('unapplied-amounts/create/{customerInvoiceId}/{modelType}', 'UnappliedAmountController@create')->name('create.settlement.by.unapplied.amounts');
                    // Route::post('unapplied-amounts/create/{modelType}', 'UnappliedAmountController@store')->name('store.settlement.by.unapplied.amounts');
                    // Route::put('unapplied-amounts/update/{modelType}/{unappliedAmountId}/{settlementId}', 'UnappliedAmountController@update')->name('update.settlement.by.unapplied.amounts');
                    // Route::get('unapplied-amounts/edit/{invoice_number}/{settlementId}/{modelType}', 'UnappliedAmountController@edit')->name('edit.settlement.by.unapplied.amounts');
                });

                /**
                 * * End Of Financial Institution Routes
                 *
                 */

                Route::resource('sharing-links', 'SharingLinkController');
                // Route::
                // Route::get('shareable-paginate', 'SharingLinkController@paginate')->name('admin.get.sharing.links');
                // Route::get('export-shareable-link', 'SharingLinkController@export')->name('admin.export.sharing.link');



                Route::post('edit-table-cell', [EditTableCellsController::class, '__invoke'])->name('admin.edit.table.cell');
                Route::delete('delete-revenue-business-line/{revenueBusinessLine}', [RevenueBusinessLineController::class, 'deleteRevenueBusinessLine'])->name('admin.delete.revenue.business.line');
                Route::delete('delete-service-category/{serviceCategory}', [RevenueBusinessLineController::class, 'deleteServiceCategory'])->name('admin.delete.service.category');
                Route::delete('delete-service-item/{serviceItem}', [RevenueBusinessLineController::class, 'deleteServiceItem'])->name('admin.delete.service.item');

                //helpers
                Route::get('get-edit-form', [getEditFormController::class, '__invoke']);
                Route::get('helpers/updateCitiesBasedOnCountry', [UpdateCitiesBasedOnCountryController::class, '__invoke']);
                Route::get('helpers/updateBasedOnGlobalController', [UpdateBasedOnGlobalController::class, '__invoke']);
                //Quick pricing calculator
                Route::get('quick-pricing-calculator', [QuickPricingCalculatorController::class, 'view'])->name('admin.view.quick.pricing.calculator');

                Route::get('quick-pricing-calculator/create/{pricingPlanId?}', [QuickPricingCalculatorController::class, 'create'])->name('admin.create.quick.pricing.calculator');

                Route::get('quick-pricing-calculator/{quickPricingCalculator}/edit', [QuickPricingCalculatorController::class, 'edit'])->name('admin.edit.quick.pricing.calculator');
                Route::post('quick-pricing-calculator/{quickPricingCalculator}/update', [QuickPricingCalculatorController::class, 'update'])->name('admin.update.quick.pricing.calculator');
                Route::post('quick-pricing-calculator/store', [QuickPricingCalculatorController::class, 'store'])->name('admin.store.quick.pricing.calculator');
                Route::get('export-quick-pricing-calculator', 'QuickPricingCalculatorController@export')->name('admin.export.quick.pricing.calculator');
                Route::get('get-quick-pricing-calculator', 'QuickPricingCalculatorController@paginate')->name('admin.get.quick.pricing.calculator');
                Route::delete('delete-quick-pricing-calculator/{quickPricingCalculator}', 'QuickPricingCalculatorController@destroy')->name('admin.delete.quick.pricing.calculator');

                //Quotation pricing calculator
                // Route::get('quotation-pricing-calculator', [QuotationPricingCalculatorController::class, 'view'])->name('admin.view.quotation.pricing.calculator');
                // Route::get('quotation-pricing-calculator/create', [QuotationPricingCalculatorController::class, 'create'])->name('admin.create.quotation.pricing.calculator');
                // Route::get('quotation-pricing-calculator/{quotationPricingCalculator}/edit', [QuotationPricingCalculatorController::class, 'edit'])->name('admin.edit.quotation.pricing.calculator');
                // Route::post('quotation-pricing-calculator/{quotationPricingCalculator}/update', [QuotationPricingCalculatorController::class, 'update'])->name('admin.update.quotation.pricing.calculator');
                // Route::post('quotation-pricing-calculator/store', [QuotationPricingCalculatorController::class, 'store'])->name('admin.store.quotation.pricing.calculator');
                // Route::get('export-quotation-pricing-calculator', 'QuotationPricingCalculatorController@export')->name('admin.export.quotation.pricing.calculator');
                // Route::get('get-quotation-pricing-calculator', 'QuotationPricingCalculatorController@paginate')->name('admin.get.quotation.pricing.calculator');

                Route::resource('pricing-expenses', 'PricingExpensesController');
                Route::resource('positions', 'PositionsController');
                Route::resource('pricing-plans', 'PricingPlansController');

                //Revenue Business Line
                Route::get('get-revenue-business-line', 'RevenueBusinessLineController@paginate')->name('admin.get.revenue-business-line');
                Route::get('get-revenue-business-line/create', 'RevenueBusinessLineController@create')->name('admin.create.revenue-business-line');
                Route::post('get-revenue-business-line/create', 'RevenueBusinessLineController@store')->name('admin.store.revenue-business-line');
                Route::get('export-revenue-business-line', 'RevenueBusinessLineController@export')->name('admin.export.revenue-business-line');
                Route::resource('revenue-business', 'RevenueBusinessLineController')->names([
                    'index' => 'admin.view.revenue.business.line',
                ]);
                Route::get('revenue-business-edit/{revenueBusinessLine}/{serviceCategory?}/{serviceItem?}', 'RevenueBusinessLineController@editForm')->name('admin.edit.revenue');
                Route::post('admin.update.revenue-business', 'RevenueBusinessLineController@updateForm')->name('admin.update.revenue');

                Route::post('send-cheques-to-collection', 'MoneyReceivedController@sendToCollection')->name('cheque.send.to.collection');
                Route::get('send-cheques-to-safe/{moneyReceived}', 'MoneyReceivedController@sendToSafe')->name('cheque.send.to.safe');
                Route::post('send-cheques-to-collection/{moneyReceived}', 'MoneyReceivedController@applyCollection')->name('cheque.apply.collection');
                Route::get('send-cheques-to-rejected-safe/{moneyReceived}', 'MoneyReceivedController@sendToSafeAsRejected')->name('cheque.send.to.rejected.safe');
                Route::get('send-cheques-to-under-collection-safe/{moneyReceived}', 'MoneyReceivedController@sendToUnderCollection')->name('cheque.send.to.under.collection');
                Route::get('down-payments/get-contracts-for-customer-with-start-and-end-date', 'MoneyReceivedController@getContractsForCustomerWithStartAndEndDate')->name('get.contracts.for.customer.with.start.and.end.date'); // ajax request
                Route::get('down-payments/get-contracts-for-customer', 'MoneyReceivedController@getContractsForCustomer')->name('get.contracts.for.customer'); // ajax request
                Route::get('down-payments/get-contracts-for-supplier', 'MoneyPaymentController@getContractsForSupplier')->name('get.contracts.for.supplier'); // ajax request
                Route::get('down-payments/get-sales-orders-for-contract/{contract_id}/{currency?}', 'MoneyReceivedController@getSalesOrdersForContract'); // ajax request
                Route::get('down-payments/get-purchases-orders-for-contract/{contract_id}/{currency?}', 'MoneyPaymentController@getSalesOrdersForContract'); // ajax request
                Route::post('update-payable-cheques/{moneyPayment}/{payableCheque}', 'MoneyPaymentController@updateOpeningPayableCheque')->name('update.opening.payable.cheque');
                Route::get('/filter-labeling-items', 'SalesGatheringController@filterLabelingItems')->name('filter.labeling.item');
                Route::get('/create-labeling-items', 'DynamicItemsController@createLabelingItems')->name('create.labeling.items');
                Route::get('/create-labeling-form', 'DynamicItemsController@createLabelingForm')->name('create.labeling.form');
                Route::get('/create-labeling-items/building-label', 'DynamicItemsController@showBuildingLabel')->name('show.building.label');
                Route::get('/create-labeling-items/ff&e-label', 'DynamicItemsController@showffeLabel')->name('show.ffe.label');
                Route::post('/create-labeling-items', 'DynamicItemsController@storeItemsCount')->name('add.count.dynamic.items');
                Route::post('store-new', 'DynamicItemsController@storeNewModal')->name('admin.store.new.modal.dynamic');

                Route::post('/store-dynamic-items', 'DynamicItemsController@storeSubItems')->name('store.dynamic.items.names');
                Route::get('/create-item/{model}', 'SalesGatheringTestController@createModel')->name('create.sales.form');
                Route::post('/create-item/{model}', 'SalesGatheringTestController@storeModel')->name('admin.store.analysis');
                Route::post('/close-period-action', 'ClosePeriodController@execute')->name('store.close.period');

                Route::get('/create-item/{model}/edit/{modelId}', 'SalesGatheringTestController@editModel')->name('edit.sales.form');
                Route::post('/create-item/{model}/update/{modelId}', 'SalesGatheringTestController@updateModel')->name('admin.update.analysis');

                Route::resource('sharing-links', 'SharingLinkController');

                Route::prefix('/SalesGathering')->group(function () {
                    Route::get('SalesTrendAnalysis', 'AnalysisReports@salesAnalysisReports')->name('sales.trend.analysis');
                    Route::get('SalesTrendAnalysis2', 'AnalysisReports@salesAnalysisReports2')->name('sales.trend.analysis2');
                    Route::get('SalesExportAnalysis', 'AnalysisReports@exportAnalysisReports')->name('sales.export.analysis');
                    Route::get('SalesExpenseAnalysis', 'AnalysisReports@expenseAnalysisReports')->name('sales.expense.analysis');
                    Route::get('SalesBreakdownAnalysis', 'AnalysisReports@salesAnalysisReports')->name('sales.breakdown.analysis');

                    //########### Average Prices Post Link ############
                    Route::post('/AveragePrices/Result', 'Analysis\SalesGathering\AveragePricesReport@result')->name('averagePrices.result');
                    //########### Breakdown Post Link ############
                    Route::post('/SalesBreakdownAnalysis/Result', 'Analysis\SalesGathering\SalesBreakdownAgainstAnalysisReport@salesBreakdownAnalysisResult')->name('salesBreakdown.analysis.result');
                    Route::post('/SalesDiscountSalesBreakdownAnalysis/Result', 'Analysis\SalesGathering\SalesBreakdownAgainstAnalysisReport@discountsSalesBreakdownAnalysisResult')->name('salesBreakdown.salesDiscounts.analysis.result');
                    //########### Two Dimensional Breakdown Post Link ############
                    Route::post('/TwoDimensionalBreakdown', 'Analysis\SalesGathering\TwodimensionalSalesBreakdownAgainstAnalysisReport@result')->name('TwoDimensionalBreakdown.result');
                    Route::post('/DiscountsAnalysisResult', 'Analysis\SalesGathering\DiscountsAnalysisReport@result')->name('discounts.analysis.result');

                    //########### Two Dimensional Breakdown Ranking Post Link ############
                    Route::post('/TwoDimensionalBreakdownRanking', 'Analysis\SalesGathering\TwodimensionalSalesBreakdownAgainstRankingAnalysisReport@result')->name('TwoDimensionalBreakdownRanking.result');
                    Route::post('/DiscountsRankingAnalysisResult', 'Analysis\SalesGathering\DiscountsRankingAnalysisReport@result')->name('discounts.Ranking.analysis.result');

                    // Providers Two Dimensional Breakdown
                    Route::post('/ProvidersTwoDimensionalBreakdown', 'Analysis\SalesGathering\ProvidersTwodimensionalSalesBreakdownAgainstAnalysisReport@result')->name('ProvidersTwoDimensionalBreakdown.result');
                    Route::get('/get-customers-from-currencies/{modelType}', 'AgingController@getCustomersFromBusinessUnitsAndCurrencies')->name('get.customers.or.suppliers.from.business.units.currencies');
                    Route::get('/get-customers-for-settlement-of-opening-balance', 'MoneyReceivedController@getCustomersWithOpeningBalance')->name('get.customers.of.opening-balance');
                    Route::get('/get-suppliers-for-settlement-of-opening-balance', 'MoneyPaymentController@getSuppliersWithOpeningBalance')->name('get.suppliers.of.opening-balance');
                    //########### Sales Trend Analysis Links +   Average Prices +  Breakdown ############
                    // For [Zone , Sales Channels , Categories , Products , Product Items , Branches , Business Sectors ,Sales Persons]
                    Route::get('products-bundling', 'Analysis\SalesGathering\ProductsAgainstAnalysisReport@viewBundlingReport')->name('view.products.bundling');
                    Route::get('product-items-bundling', 'Analysis\SalesGathering\SKUsAgainstAnalysisReport@viewBundlingReport')->name('view.productItems.bundling');
                    $routesDefinition = (new RoutesDefinition());
                    $saleTrendRoutes = $routesDefinition->salesTrendAnalysisRoutes();
                    foreach ($saleTrendRoutes as $nameOfMainItem => $info) {
                        if (isset($info['class_path'])) {
                          

                            // Not All Reports Contains Analysis Reports
                            !isset($info['analysis_view']) ?: Route::get('/' . $nameOfMainItem . 'SalesAnalysis/View', $info['class_path'] . '@' . $info['analysis_view'])->name($info['name'] . '.sales.analysis');
                            !isset($info['analysis_result']) ?: Route::post('/' . $nameOfMainItem . 'SalesAnalysis/Result', $info['class_path'] . '@' . $info['analysis_result'])->name($info['name'] . '.sales.analysis.result');
                            Route::post('/' . $nameOfMainItem . 'AgainstAnalysis/Result', $info['class_path'] . '@' . $info['against_result'])->name($info['name'] . '.analysis.result');
                            // Against Reports
                            
                            foreach ($info['sub_items'] as $viewName => $sub_item) {
                                
                                Route::get('/' . $nameOfMainItem . 'Against' . $viewName . 'Analysis/View', $info['class_path'] . '@' . $info['against_view'])->name($info['name'] . '.' . $sub_item . '.analysis');
                            }
                            Route::post('/' . $nameOfMainItem . 'AgainstSalesDiscountAnalysis/Result', $info['class_path'] . '@' . $info['discount_result'])->name($info['name'] . '.salesDiscount.analysis.result');
                            // Average Prices Links
                            if (isset($info['avg_items'])) {
                                foreach ($info['avg_items'] as $viewName => $avg_item) {
                                    Route::get('/' . $nameOfMainItem . $viewName . 'AveragePricesView', $info['class_path'] . '@' . $info['against_view'])->name($info['name'] . '.' . $avg_item . '.averagePrices');
                                }
                            }
                        }
                        // Discounts
                        ($info['has_discount'] === false) ?: Route::get('/' . $nameOfMainItem . 'VSDiscounts/View', 'Analysis\SalesGathering\DiscountsAnalysisReport@index')->name($info['name'] . '.vs.discounts.view');
                        
                        ($info['has_break_down'] === false) ?: Route::get('/' . $nameOfMainItem . 'SalesBreakdownAnalysis/View', 'Analysis\SalesGathering\SalesBreakdownAgainstAnalysisReport@salesBreakdownAnalysisIndex')->name('salesBreakdown.' . $info['name'] . '.analysis');
                    }

                    //########### Two Dimensional Breakdown ############
                    $twoDimentionsRoutes = $routesDefinition->twoDimensionalBreakdownRoutes();
                    foreach ($twoDimentionsRoutes as $nameOfMainItem => $info) {
                        foreach ($info['sub_items'] as $viewName => $sub_item) {
                            if (isset($info['is_provider']) && $info['is_provider'] === true) {
                                Route::get('/' . $nameOfMainItem . 'VS' . $viewName . '/View', 'Analysis\SalesGathering\ProvidersTwodimensionalSalesBreakdownAgainstAnalysisReport@index')->name($info['name'] . '.vs.' . $sub_item . '.view');
                            } else {
                                Route::get('/' . $nameOfMainItem . 'VS' . $viewName . '/View', 'Analysis\SalesGathering\TwodimensionalSalesBreakdownAgainstAnalysisReport@index')->name($info['name'] . '.vs.' . $sub_item . '.view');
                            }
                        }
                    }

                    //########### Two Dimensional Ranking ############
                    $twoDimentionsRoutes = $routesDefinition->twoDimensionalRankingsRoutes();
                    foreach ($twoDimentionsRoutes as $nameOfMainItem => $info) {

                        foreach ($info['sub_items'] as $viewName => $sub_item) {
                            if (isset($info['is_provider']) && $info['is_provider'] === true) {
                            } else {
                                
                                Route::get('/' . $nameOfMainItem . 'VS' . $viewName . 'Ranking' . '/View', 'Analysis\SalesGathering\TwodimensionalSalesBreakdownAgainstRankingAnalysisReport@index')->name($info['name'] . '.vs.' . $sub_item . 'Ranking' . '.view');
                            }
                        }
                    }

                    //########### Sales Report ############
                    Route::get('/SalesReport/View', 'Analysis\SalesGathering\salesReport@index')->name('salesReport.view');
                    Route::post('/SalesReport/Result', 'Analysis\SalesGathering\salesReport@result')->name('salesReport.result');

                    // Comparing Analysis
                    Route::post('/Comparing/Result', 'Analysis\SalesGathering\IntervalsComparingReport@result')->name('intervalComparing.analysis.result');
                    Route::post('/SalesDiscountComparing/Result', 'Analysis\SalesGathering\IntervalsComparingReport@discountsComparingResult')->name('intervalComparing.salesDiscounts.analysis.result');
                    //Zones
                    Route::get('/ZonesComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.zone.analysis');
                    // Sales Channels
                    Route::get('/SalesChannelsComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.salesChannels.analysis');
                    // Customers
                    Route::get('/CustomersComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.customers.analysis');
                    // Business Sectors
                    Route::get('/BusinessSectorsComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.businessSectors.analysis');
                    Route::get('/BusinessUnitsComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.businessUnits.analysis');
                    // Branches
                    Route::get('/BranchesComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.branches.analysis');
                    // Categories
                    Route::get('/CategoriesComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.categories.analysis');
                    // Products
                    Route::get('/ProductsComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.products.analysis');
                    //Items
                    Route::get('/ProductItemsComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.Items.analysis');
                    // SalesPersons
                    Route::get('/SalesPersonsComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.salesPersons.analysis');
                    // SalesDiscount
                    Route::get('/SalesDiscountComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.salesDiscounts.analysis');
                    // Principles
                    Route::get('/PrinciplesComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.principles.analysis');
                    // service_provider_name
                    Route::get('/ServiceProvidersComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.serviceProviders.analysis');
                    // serviceProvidersType
                    Route::get('/ServiceProvidersTypeComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.serviceProvidersType.analysis');
                    // serviceProvidersBirthYear
                    Route::get('/ServiceProvidersBirthYearComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.serviceProvidersBirthYear.analysis');
                    //Countries
                    Route::get('/CountriesComparing/View', 'Analysis\SalesGathering\IntervalsComparingReport@index')->name('intervalComparing.country.analysis');
                    /////////////////////////////////////////////////////////////////////////

                    Route::get('export-analysis-reports/{firstColumn}/{secondColumn}', 'Analysis\SalesGathering\ExportAgainstAnalysisReport@index')->name('view.export.against.report');
                    Route::post('export-analysis-reports', 'Analysis\SalesGathering\ExportAgainstAnalysisReport@result')->name('result.export.against.report');
                    
                    
                    Route::get('expense-analysis-reports/{firstColumn}/{secondColumn?}/{thirdColumn?}', 'Analysis\SalesGathering\ExpenseAgainstAnalysisReport@viewOneAndTwoSelectorAndThreeSelectorAndComparing')->name('view.expense.against.report');
                    Route::post('expense-analysis-reports', 'Analysis\SalesGathering\ExpenseAgainstAnalysisReport@twoSelectorAndThreeSelectorAndComparingResult')->name('result.expense.against.report');
                    // Route::get('expense-report-result','Analysis\SalesGathering\ExpenseAgainstAnalysisReport@oneSelectorResult')->name('one.selector.expense.report.result.get');
                    Route::post('expense-report-result', 'Analysis\SalesGathering\ExpenseAgainstAnalysisReport@oneSelectorResult')->name('one.selector.expense.report.result');
                    Route::get('expense-breakdown-reports/{columnName}', 'Analysis\SalesGathering\ExpenseAgainstAnalysisReport@viewBreakdownReport')->name('view.expense.breakdown.report');
                    Route::post('expense-breakdown-report-result', 'Analysis\SalesGathering\ExpenseAgainstAnalysisReport@breakdownResult')->name('result.expense.breakdown.report');
                    Route::get('avg-min-max-expense-analysis-reports/{firstColumn}/{secondColumn?}/{thirdColumn?}', 'Analysis\SalesGathering\ExpenseAgainstAnalysisReport@viewAvgMinMaxReport')->name('view.avg.min.max.against.report');
                    Route::post('avg-min-max-expense-analysis-reports', 'Analysis\SalesGathering\ExpenseAgainstAnalysisReport@AvgMinMaxReportResult')->name('result.avg.min.max.against.report');
                    
                    
                    Route::get('interval-comparing-expense-reports/{firstColumn}', 'Analysis\SalesGathering\ExpenseAgainstAnalysisReport@viewIntervalComparingReport')->name('view.interval.comparing.report');
                    Route::post('interval-comparing-expense-reports', 'Analysis\SalesGathering\ExpenseAgainstAnalysisReport@resultIntervalComparingReport')->name('result.interval.comparing.report');
                    
                    
                    // Customers Nature
                    Route::post('/CustomersNaturesAnalysis/Result', 'Analysis\SalesGathering\CustomersNaturesAnalysisReport@result')->name('customersNatures.analysis.result');
                    Route::post('/CustomersNaturesAnalysisTwoDimensional/Result', 'Analysis\SalesGathering\CustomersNaturesAnalysisReport@twoDimensionalResult')->name('customersNatures.twoDimensional.analysis.result');
                    Route::get('/CustomersNaturesAnalysis/View', 'Analysis\SalesGathering\CustomersNaturesAnalysisReport@index')->name('customersNatures.analysis');
                    Route::get('/ZonesVsCustomersNaturesAnalysis/View', 'Analysis\SalesGathering\CustomersNaturesAnalysisReport@index')->name('zones.vs.customersNatures');
                    Route::get('/SalesChannelsVsCustomersNaturesAnalysis/View', 'Analysis\SalesGathering\CustomersNaturesAnalysisReport@index')->name('salesChannels.vs.customersNatures');
                    Route::get('/BusinessSectorsVsCustomersNaturesAnalysis/View', 'Analysis\SalesGathering\CustomersNaturesAnalysisReport@index')->name('businessSectors.vs.customersNatures');
                    Route::get('/BusinessUnitsVsCustomersNaturesAnalysis/View', 'Analysis\SalesGathering\CustomersNaturesAnalysisReport@index')->name('businessUnits.vs.customersNatures');
                    Route::get('/BranchesVsCustomersNaturesAnalysis/View', 'Analysis\SalesGathering\CustomersNaturesAnalysisReport@index')->name('branches.vs.customersNatures');
                    Route::get('/CategoriesVsCustomersNaturesAnalysis/View', 'Analysis\SalesGathering\CustomersNaturesAnalysisReport@index')->name('categories.vs.customersNatures');
                    Route::get('/ProductsServicesVsCustomersNaturesAnalysis/View', 'Analysis\SalesGathering\CustomersNaturesAnalysisReport@index')->name('products.vs.customersNatures');
                    Route::get('/ProductItemsVsCustomersNaturesAnalysis/View', 'Analysis\SalesGathering\CustomersNaturesAnalysisReport@index')->name('Items.vs.customersNatures');
                    Route::get('/CountriesVsCustomersNaturesAnalysis/View', 'Analysis\SalesGathering\CustomersNaturesAnalysisReport@index')->name('countries.vs.customersNatures');
                    /////////////////////////////////////////////////////////////////////////
                    // Sales Forecast
                    Route::any('/SalesForecast', 'SalesForecastReport@result')->name('sales.forecast');
                    Route::post('/SalesForecast/save', 'SalesForecastReport@save')->name('sales.forecast.save');
                    Route::any('/NewCategories', 'SalesForecastReport@createCategories')->name('categories.create');
                    Route::any('/NewProducts', 'SalesForecastReport@createProducts')->name('products.create');
                    Route::any('/ProductsSeasonality', 'SalesForecastReport@productsSeasonality')->name('products.seasonality');
                    Route::any('/ProductsSalesTargets', 'SalesForecastReport@productsSalesTargets')->name('products.sales.targets');
                    Route::any('/ProductsAllocations', 'SalesForecastReport@productsAllocations')->name('products.allocations');

                    // Seasonality
                    Route::get('/ModifiedSeasonality', 'SeasonalityReport@modifySeasonality')->name('modify.seasonality');
                    Route::post('/SaveModifiedSeasonality', 'SeasonalityReport@saveSeasonality')->name('save.modify.seasonality');

                    // First Allocation
                    Route::any('/Allocations', 'AllocationsReport@allocationSettings')->name('allocations');
                    Route::any('/NewProductsAllocations', 'AllocationsReport@NewProductsAllocationBase')->name('new.product.allocation.base');
                    Route::any('/ExistingProductsAllocations', 'AllocationsReport@existingProductsAllocationBase')->name('existing.products.allocations');
                    Route::any('/NewProductsSeasonality', 'AllocationsReport@NewProductsSeasonality')->name('new.product.seasonality');
                    // Second Allocation
                    Route::any('/SecondAllocations', 'SecondAllocationsReport@allocationSettings')->name('second.allocations');
                    Route::any('/SecondNewProductsAllocations', 'SecondAllocationsReport@NewProductsAllocationBase')->name('second.new.product.allocation.base');
                    Route::any('/SecondExistingProductsAllocations', 'SecondAllocationsReport@existingProductsAllocationBase')->name('second.existing.products.allocations');
                    Route::any('/SecondNewProductsSeasonality', 'SecondAllocationsReport@NewProductsSeasonality')->name('second.new.product.seasonality');
                    // Collection
                    Route::any('/Collection', 'CollectionController@collectionSettings')->name('collection.settings');
                    Route::any('/CollectionReport', 'CollectionController@collectionReport')->name('collection.report');
                    //  Summary
                    Route::any('/SummaryReport', 'SummaryController@forecastReport')->name('forecast.report');
                    Route::any('/goToSummaryReport', 'SummaryController@goToSummaryReport')->name('go.to.summary.report');
                    Route::any('/BreakdownSummaryReport', 'SummaryController@breakdownForecastReport')->name('breakdown.forecast.report');
                    Route::any('/CollectionSummaryReport', 'SummaryController@collectionForecastReport')->name('collection.forecast.report');





                    ///////////////////////////////////////////////////////////////////////////////////////////////////////
                    // Sales Forecast Quantity

                    Route::any('/SalesForecastQuantity', 'QuantitySalesForecastReport@result')->name('sales.forecast.quantity');
                    Route::post('/SalesForecastQuantity/save', 'QuantitySalesForecastReport@save')->name('sales.forecast.quantity.save');
                    Route::any('/ForecastedSalesValues', 'QuantitySalesForecastReport@forecastedSalesValues')->name('forecasted.sales.values');
                    Route::any('/NewCategoriesQuantity', 'QuantitySalesForecastReport@createCategories')->name('categories.quantity.create');
                    Route::any('/NewProductsQuantity', 'QuantitySalesForecastReport@createProducts')->name('products.quantity.create');
                    Route::any('/ProductsSeasonalityQuantity', 'QuantitySalesForecastReport@productsSeasonality')->name('products.seasonality.quantity');
                    Route::any('/ProductsSalesTargetsQuantity', 'QuantitySalesForecastReport@productsSalesTargets')->name('products.sales.targets.quantity');
                    Route::any('/ProductsAllocationsQuantity', 'QuantitySalesForecastReport@productsAllocations')->name('products.allocations.quantity');

                    // Seasonality
                    Route::get('/ModifiedSeasonalityQuantity', 'QuantitySeasonalityReport@modifySeasonality')->name('modify.seasonality.quantity');
                    Route::post('/SaveModifiedSeasonalityQuantity', 'QuantitySeasonalityReport@saveSeasonality')->name('save.modify.seasonality.quantity');

                    // First Allocation
                    Route::any('/AllocationsQuantity', 'QuantityAllocationsReport@allocationSettings')->name('allocations.quantity');
                    Route::any('/NewProductsAllocationsQuantity', 'QuantityAllocationsReport@NewProductsAllocationBase')->name('new.product.allocation.base.quantity');
                    Route::any('/ExistingProductsAllocationsQuantity', 'QuantityAllocationsReport@existingProductsAllocationBase')->name('existing.products.allocations.quantity');
                    Route::any('/NewProductsSeasonalityQuantity', 'QuantityAllocationsReport@NewProductsSeasonality')->name('new.product.seasonality.quantity');
                    // Second Allocation
                    Route::any('/SecondAllocationsQuantity', 'QuantitySecondAllocationsReport@allocationSettings')->name('second.allocations.quantity');
                    Route::any('/SecondNewProductsAllocationsQuantity', 'QuantitySecondAllocationsReport@NewProductsAllocationBase')->name('second.new.product.allocation.base.quantity');
                    Route::any('/SecondExistingProductsAllocationsQuantity', 'QuantitySecondAllocationsReport@existingProductsAllocationBase')->name('second.existing.products.allocations.quantity');
                    Route::any('/SecondNewProductsSeasonalityQuantity', 'QuantitySecondAllocationsReport@NewProductsSeasonality')->name('second.new.product.seasonality.quantity');
                    // Collection
                    Route::any('/CollectionQuantity', 'QuantityCollectionController@collectionSettings')->name('collection.settings.quantity');
                    Route::any('/CollectionReportQuantity', 'QuantityCollectionController@collectionReport')->name('collection.quantity.report');
                    //  Summary
                    Route::any('/SummaryReportQuantity', 'QuantitySummaryController@forecastReport')->name('forecast.quantity.report');
                    Route::any('/goToQuantitySummaryReport', 'QuantitySummaryController@goToSummaryReport')->name('go.to.summary.quantity.report');
                    Route::any('/BreakdownQuantitySummaryReport', 'QuantitySummaryController@breakdownForecastReport')->name('breakdown.forecast.quantity.report');
                    Route::any('/CollectionQuantitySummaryReport', 'QuantitySummaryController@collectionForecastReport')->name('collection.forecast.quantity.report');

                    /////////////////////////////////////////////////////////////////////////
                });


                //########### Exportable Fields Selection Routes ############
                Route::get('fieldsToBeExported/{model}/{view}', 'ExportTable@customizedTableField')->name('table.fields.selection.view');
                Route::post('fieldsToBeExportedSave/{model}/{modelName}', 'ExportTable@customizedTableFieldSave')->name('table.fields.selection.save');
            });
        }
    );
});

Route::delete('deleteMultiRowsFromCaching/{company}/{modelName}', [DeleteMultiRowsFromCaching::class, '__invoke'])->name('deleteMultiRowsFromCaching');
Route::get('deleteAllRowsFromCaching/{company}/{modelType}', [DeleteAllRowsFromCaching::class, '__invoke'])->name('deleteAllCaches');
Route::post('get-uploading-percentage/{companyId}/{modelName}', [getUploadPercentage::class, '__invoke']);
Route::get('{lang}/remove-company-image/{company}', function ($lang, Company $company) {
    if ($company->getFirstMedia('default')) {
        $company->getFirstMedia('default')->delete();
    }
    
    return redirect()->back()->with('success', __('Company Image Has Been Deleted Successfully'));
})->name('remove.company.image');

Route::get('getStartDateAndEndDateOfIncomeStatementForCompany', 'HomeController@getIncomeStatementStartDateAndEndDate');
Route::get('removeSessionForRedirect', function () {
    if (session()->has('redirectTo')) {
        $url = session()->get('redirectTo');
        session()->forget('redirectTo');

        return response()->json([
            'status' => true,
            'url' => $url
        ]);
    }
});
Route::domain('second.con')->group(function () {
    Route::get('salah', function () {
        return 'good';
    });
});

Route::get('eee', function () {
    $migrationOutput = Artisan::call('migrate');
    // $testOutput = Artisan::call('run:test');
    $testOutput = Artisan::call('run:sql');
    return $testOutput . $migrationOutput;
    // dd($migrationOutput);
});
