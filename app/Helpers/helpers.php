<?php

use App\Enums\LcTypes;
use App\Enums\LgTypes;
use App\Helpers\HArr;
use App\Helpers\HHelpers;
use App\Helpers\HStr;
use App\Helpers\HVero;
use App\Http\Controllers\Analysis\SalesGathering\BranchesAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\BusinessSectorsAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\CategoriesAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\ExpenseAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\ExportAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\ProductsAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\SalesChannelsAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\SalesPersonsAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\SKUsAgainstAnalysisReport;
use App\Http\Controllers\Analysis\SalesGathering\ZoneAgainstAnalysisReport;
use App\Http\Controllers\ExportTable;
use App\Models\Branch;
use App\Models\CachingCompany;
use App\Models\CollectionSetting;
use App\Models\Company;
use App\Models\Country;
use App\Models\CustomizedFieldsExportation;
use App\Models\ExistingProductAllocationBase;
use App\Models\FinancialInstitutionAccount;
use App\Models\IncomeStatement;
use App\Models\IncomeStatementItem;
use App\Models\IncomeStatementSubItem;
use App\Models\ModifiedSeasonality;
use App\Models\NonBankingService\ExpenseName;
use App\Models\NonBankingService\Study;
use App\Models\Partner;
use App\Models\ProductSeasonality;
use App\Models\QuantityExistingProductAllocationBase;
use App\Models\QuantityModifiedSeasonality;
use App\Models\QuantityProductSeasonality;
use App\Models\QuantitySalesForecast;
use App\Models\QuantitySecondExistingProductAllocationBase;
use App\Models\SalesForecast;
use App\Models\SalesGathering;
use App\Models\SecondAllocationSetting;
use App\Models\SecondExistingProductAllocationBase;
use App\Models\SecondNewProductAllocationBase;
use App\Models\Section;
use App\Models\User;
use App\Services\Caching\CashingService;
use App\Services\IntervalSummationOperations;
use App\Traits\Intervals;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

const FINANCIAL_PLANNING_CONNECTION_NAME='financial_planning';
const PROPERTY_MANAGEMENT_CONNECTION_NAME='property_management';
const NON_BANKING_SERVICE_CONNECTION_NAME='non_banking_service';
const Customers_Against_Products_Trend_Analysis = 'Customers Against Products Trend Analysis';
const Customers_Against_Categories_Trend_Analysis = 'Customers Against Categories Trend Analysis';
const Customers_Against_Products_ITEMS_Trend_Analysis = 'Customers Against Products Items Trend Analysis';
const INVOICES = 'Invoices';
const uploadLabelingItemData ='upload labeling export data';

const exportLabelingItemData ='export labeling export data';
const deleteLabelingItemData ='delete labeling export data';
const viewLabelingItemData ='view labeling export data';

const uploadCustomerInvoiceData ='upload customer invoice analysis data';
const exportCustomerInvoiceData ='export customer invoice analysis data';
const deleteCustomerInvoiceData ='delete customer invoice analysis data';
const viewCustomerInvoiceData ='view customer invoice analysis data';

const uploadExportAnalysisData ='upload export analysis data';
const exportExportAnalysisData ='export export analysis data';
const deleteExportAnalysisData ='delete export analysis data';
const viewExportAnalysisData ='view export analysis data';

const uploadExpenseAnalysisData ='upload expense analysis data';
const exportExpenseAnalysisData ='export expense analysis data';
const deleteExpenseAnalysisData ='delete expense analysis data';
const viewExpenseAnalysisData ='view expense analysis data';

const uploadSupplierInvoiceData ='upload supplier invoice analysis data';
const exportSupplierInvoiceData ='export supplier invoice analysis data';
const deleteSupplierInvoiceData ='delete supplier invoice analysis data';
const viewSupplierInvoiceData ='view supplier invoice analysis data';

const uploadLoanScheduleData ='upload loan schedule analysis data';
const exportLoanScheduleData ='export loan schedule analysis data';
const deleteLoanScheduleData ='delete loan schedule analysis data';
const viewLoanScheduleData ='view loan schedule analysis data';



const CASH_VERO = 'cash-vero';
const NON_BANKING_SERVICE = 'non-banking-service';
const PROPERTY_MANAGEMENT = 'property-management';
const VERO = 'vero';
const EXPORT_ANALYSIS = 'export-analysis';
const EXPENSE_ANALYSIS = 'expense-analysis';
const PRICING_CALCULATOR = 'pricing-calculator';
const SALES_FORECAST = 'sales-forecast';
const INCOME_STATEMENT_PLANNING = 'income-statement-planning';
const LABELING = 'labeling';



const MAX_YEARS_COUNT = 7 ;
// const FINANCIAL_PLANNING_MAX_YEARS_COUNT = 7 ;
const quantityIdentifier = ' ( Quantity )';

const PROPERTY_MANAGEMENT_SERVICE_URL_PREFIX = 'property-managements';
const NON_BANKING_SERVICE_URL_PREFIX = 'non-banking-financial-services';
const FINANCIAL_PLANNING_URL_PREFIX = 'financial-planning';






function getHavingConditionForDeadReactivated($year)
{
    return ' having max(case when Year = ' . $year . ' then 1 else 0 end ) = 1
	and max(case when Year = ' . ($year - 1) . '  then 1 else 0 end ) = 0
	and max(case when Year = ' . ($year - 2) . ' then 1 else 0 end ) = 0
	and
	( max(case when Year = ' . ($year - 3) . ' then 1 else 0 end ) = 1 or

	(max(case when Year = ' . ($year - 3) . ' then 1 else 0 end ) = 0
	and max(case when Year = ' . ($year - 4) . ' then 1 else 0 end ) = 1)

	)


	order by total_sales desc ; ';
}

function getHavingConditionForDeadRepeating($year)
{
    return ' having max(case when Year = ' . $year . ' then 1 else 0 end ) = 1
	and max(case when Year = ' . ($year - 1) . '  then 1 else 0 end ) = 1
	and max(case when Year = ' . ($year - 2) . ' then 1 else 0 end ) = 0
	and max(case when Year = ' . ($year - 3) . ' then 1 else 0 end ) = 0
	and max(case when Year = ' . ($year - 4) . ' then 1 else 0 end ) = 1
	order by total_sales desc ; ';
}
function getYearsFromInterval($start, $end)
{
    return [
        'start_year' => explode('-', $start)[0],
        'end_year' => explode('-', $end)[0],
    ];
}

function array_unique_value(array $array, string $key)
{
    $uniqueItems = [];
    foreach ($array as $arr) {
        foreach ($arr as $ar) {
            $uniqueItems[$ar[$key]] = $ar;
        }
    }

    return $uniqueItems;
}
function getDeadRepeatingCustomersCacheNameForCompanyInYear(Company $companyId, string $year, string $month)
{
    return 'dead_repeating_reactivated_customers_for_company_' . $companyId->id . 'for_year_' . $year.'_for_month'.$month;
}

function getPeriods($interval)
{
    if ($interval == 'monthly') {
        return  [
            1 => [1],
            2 => [2],
            3 => [3],
            4 => [4],
            5 => [5],
            6 => [6],
            7 => [7],
            8 => [8],
            9 => [9],
            10 => [10],
            11 => [11],
            12 => [12],
        ];
    }

    if ($interval == 'quarterly') {
        return [
            3 => [1, 2, 3], 6 => [4, 5, 6], 9 => [7, 8, 9], 12 => [10, 11, 12]
        ];
    }
    if ($interval == 'semi-annually') {
        return [
            6 => [1, 2, 3, 4, 5, 6], 12 => [7, 8, 9, 10, 11, 12]
        ];
    }

    if ($interval == 'annually') {
        return [
            12 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
        ];
    }
}

function getLongestArray($array)
{
    $result = [];
    foreach ($array as $arr) {
        if (count($arr) > count($result)) {
            $result = $arr;
        }
    }

    return $result;
}
function arrayCountAllLongest(array $array)
{
    $longestArray = getLongestArray($array);

    $counter = 0;

    foreach ($longestArray as $arr) {
        $counter += count($arr);
    }

    return $counter;
}
function flatten(array $array)
{
    $return = [];
    array_walk_recursive($array, function ($a) use (&$return) {
        $return[] = $a;
    });

    return $return;
}
function countTotalForBranch(array $array): int
{
    $total = 0;
    foreach ($array as $arr) {
        $total += count($arr);
    }

    return $total;
}

function countSumForAllRank(array $array, $i): array
{
    $total = [
        'total' => 0,
        'values' => 0,
        'percentages' => 0
    ];
    foreach ($array as $arr) {
        if (isset($arr[$i])) {
            $total['total'] += count($arr[$i]);
            $total['values'] += array_sum(flatten($arr[$i]));
            $total['percentages'] += 0;
        }
    }

    return $total;
}
function camelize($input, $separator = '_')
{
    return str_replace($separator, '', ucwords($input, $separator));
}

if (!function_exists('lang')) {
    function lang()
    {
        return  app()->getLocale();
    }
}

// if (!function_exists('company')) {
//     function company()
//     {
//         if (Auth::check()) {
//             $company =   Auth::user()->companies()->where('type', 'single')->first();

//             $company = $company ?? Auth::user()->companies()->where('type', 'group')->first()->subCompanies()->first();
//             return  $company;
//         }
//     }
// }
if (!function_exists('setCompany')) {
    function setCompany($company_id)
    {
        if (Auth::check()) {
            $company = Company::find($company_id);

            return  $company;
        }
    }
}
if (!function_exists('exportableFields')) {
    function exportableFields($company_id, $model)
    {
        if (Auth::check()) {
            $fields = CustomizedFieldsExportation::where('model_name', $model)->where('company_id', $company_id)->first();
            return  $fields;
        }
    }
}

if (!function_exists('strip_strings')) {
    function strip_strings(string $sentence)
    {
        $removeHtml =  strip_tags($sentence);

        return str_replace(['&amp;', '&nbsp;', 'nbsp;'], '', $removeHtml);
    }
}

if (!function_exists('dateFormatting')) {
    function dateFormatting($date, $formate = 'd-m-Y')
    {
        return date($formate, strtotime($date));
    }
}
if (!function_exists('routeName')) {
    function routeName($route)
    {
        $route_array = explode('.', $route);
        $route = $route_array[0];

        return $route;
    }
}

function array_sort_multi_levels(&$array)
{
    uasort($array, function ($a, $b) {
        $sumA = 0;
        foreach ($a as $year => $data) {
            foreach ($data as $quarter => $data) {
                $sumA += $data['invoice_number'];
            }
        }

        $sumB = 0;
        foreach ($b as $year => $data) {
            foreach ($data as $quarter => $data) {
                $sumB += $data['invoice_number'];
            }
        }


        if ($sumA == $sumB) {
            return 0;
        }

        return ($sumA > $sumB) ? -1 : 1;
    });
}

function getExportableFields($companyId = null): array
{
    $company  = Company::find($companyId ?: Request()->segment(2));
    if ($company) {
        return (new ExportTable)->customizedTableField($company, 'SalesGathering', 'selected_fields');
    }

    return [];
}

function getExportableFieldsKeysAsValues($companyId)
{
    return array_keys(getExportableFields($companyId));
}
function getExportableFieldsForModel($companyId, $modelName): array
{
    $company  = Company::find($companyId ?: Request()->segment(2));
    if ($company) {
        return (new ExportTable)->customizedTableField($company, $modelName, 'selected_fields');
    }
    return [];
}
function canViewCustomersDashboard(array $exportables)
{
    return in_array('Customer Name', $exportables) || in_array('Customer Code', $exportables);
}
// 1- customers dashboard
function getNewCustomersCacheNameForCompanyInYear(Company $companyId, string $year, string $month)
{
    return 'new_customers_for_company_' . $companyId->id . '_for_year_' . $year.'_for_month'.$month;
}
function getNewCustomersCacheNameForCompanyInYearForType(Company $companyId, string $year, string $type, string $month)
{
    return 'new_customers_for_company_' . $companyId->id . '_for_year_' . $year . 'for_type_' . $type.'_'.$month;
}
function getBreakdownCacheNameForCompanyAndDatesAndType(Company $companyId, string $start_date, string $endDate, string $type)
{
    return 'breakdown_start_date'. $start_date .'end_date' . $endDate . 'company_id'. $companyId->id . 'for_type_' . $type;
}
function getBreakdownSimpleLinearRegressionCacheNameForCompanyAndDatesAndType(Company $companyId, string $start_date, string $endDate, string $type)
{
    return 'breakdown_simple_linear_regression_start_date'. $start_date .'end_date' . $endDate . 'company_id'. $companyId->id . 'for_type_' . $type ;
}
function getBreakdownSimpleLinearRegressionCacheNameFor2CompanyAndDatesAndType(Company $companyId, string $start_date, string $endDate, string $type)
{
    return 'breakdown_simple_linear_regression2_start_date'. $start_date .'end_date' . $endDate . 'company_id'. $companyId->id . 'for_type_' . $type;
}
function getBreakdownSimpleLinearRegressionDatesCacheNameForCompanyAndDatesAndType(Company $companyId, string $start_date, string $endDate, string $type)
{
    return 'breakdown_simple_linear_regression_dates_start_date'. $start_date .'end_date' . $endDate . 'company_id'. $companyId->id . 'for_type_' . $type;
}

function getTotalCustomersCacheNameForCompanyInYearForType(Company $companyId, string $year, $type, string $month)
{
    return 'total_customers_for_company_' . $companyId->id . '_for_year_' . $year . 'for_type_' . $type.'_'.$month;
}



function getRepeatingCustomersCacheNameForCompanyInYear(Company $companyId, string $year, string $month)
{
    return 'repeating_customers_for_company_' . $companyId->id . '_for_year_' . $year.'month'.$month;
}

function getRepeatingCustomersCacheNameForCompanyInYearForType(Company $companyId, string $year, string $type, string $month)
{
    return 'repeating_customers_for_company_' . $companyId->id . '_for_year_' . $year . 'for_type_' . $type .'_'.$month;
}

function getActiveCustomersCacheNameForCompanyInYear(Company $companyId, string $year, string $month)
{
    return 'active_customers_for_company_' . $companyId->id . '_for_year_' . $year.'_for_month'.$month;
}

function getActiveCustomersCacheNameForCompanyInYearForType(Company $companyId, string $year, string $type, string $month)
{
    return 'active_customers_for_company_' . $companyId->id . '_for_year_' . $year . 'for_type_' . $type.'_'.$month;
}



function getStopReactivatedCustomersCacheNameForCompanyInYear(Company $companyId, string $year, string $month)
{
    return 'stop_reactivated_customers_for_company_' . $companyId->id . '_for_year_' . $year.'_for_month'.$month;
}
function getStopReactivatedCustomersCacheNameForCompanyInYearForType(Company $companyId, string $year, string $type, string $month)
{
    return 'stop_reactivated_customers_for_company_' . $companyId->id . '_for_year_' . $year . 'for_type_' . $type.'_'.$month;
}
function getDeadReactivatedCustomersCacheNameForCompanyInYear(Company $companyId, string $year, string $month)
{
    return 'dead_reactivated_customers_for_company_' . $companyId->id . '_for_year_' . $year . '_for_month'.$month;
}

function getDeadReactiveCacheNameForCompanyInYearForType(Company $companyId, string $year, string $type, string $month)
{
    return 'dead_reactivated_customers_for_company_' . $companyId->id . '_for_year_' . $year . 'for_type_' . $type.'_'.$month;
}
// getStopRepeatingCacheNameForCompanyInYearForType
// getDeadReactiveCacheNameForCompanyInYearForType
function getStopRepeatingCustomersCacheNameForCompanyInYear(Company $companyId, string $year, string $month)
{
    return 'stop_repeating_reactivated_customers_for_company_' . $companyId->id . 'for_year_' . $year.'_for_month'.$month;
}
function getStopRepeatingCustomersCacheNameForCompanyInYearForType(Company $companyId, string $year, string $type, string $month)
{
    return 'stop_repeating_reactivated_customers_for_company_' . $companyId->id . '_for_year_' . $year . 'for_type_' . $type.'_'.$month;
}
function getStopCustomersCacheNameForCompanyInYear(Company $companyId, string $year, string $month)
{
    return 'stop_customers_for_company_' . $companyId->id . '_for_year_' . $year.'_for_month'.$month;
}

function getStopCustomersCacheNameForCompanyInYearForType(Company $companyId, string $year, string $type, string $month)
{
    return 'stop_customers_for_company_' . $companyId->id . '_for_year_' . $year . 'for_type_' . $type.'_'.$month;
}


function getDeadCustomersCacheNameForCompanyInYear(Company $companyId, string $year, string $month)
{
    return 'dead_customers_for_company_' . $companyId->id . '_for_year_' . $year.'_for_month'.$month;
}
function getDeadCustomersCacheNameForCompanyInYearForType(Company $companyId, string $year, string $type, string $month)
{
    return 'dead_customers_for_company_' . $companyId->id . '_for_year_' . $year . 'for_type_' . $type.'_'.$month;
}

function getTotalCustomersCacheNameForCompanyInYear(Company $companyId, string $year, string $month)
{
    return 'total_customers_dashboard_for_company_' . $companyId->id . '_for_year_' . $year.'and_month'.$month;
}

// intervalYearsForCompany (max date and min date in database for sales gatering)


function getIntervalYearsFormCompanyCacheNameForCompany(Company $companyId)
{
    return 'interval_years_for_company_' . $companyId->id;
}
function getExpenseIntervalYearsFormCompanyCacheNameForCompany(Company $companyId)
{
    return 'interval_expense_years_for_company_' . $companyId->id;
}
function formatChartNameForDom($chartName)
{
    return str_replace(['/', ' '], '-', $chartName);
}





function sortReportForTotals(&$report_data)
{
    (
        uasort(
            $report_data,
            function ($a, $b)  {
                if (isset($b['Total'], $a['Total'])) {
                    $a = array_sum($a['Total']);
                    $b = array_sum($b['Total']);

                    if ($a == $b) {
                        return 0;
                    }

                    return ($a > $b) ? -1 : 1;
                }

                if (!is_multi_array($a) &&  is_multi_array($b)) {
                    return 1;
                }

                if (is_multi_array($a) &&  !is_multi_array($b)) {
                    return -1;
                }

                if (isset($a['Total']) && !isset($b['Total'])) {
                    return -1;
                }

                if (!isset($a['Total']) && isset($b['Total'])) {
                    return 1;
                }



                return -1;
            }
        )
    );
}

function sortSubItems(&$sales_channel_channels_data, $type = null)
{
    if ($type == 'day_name') {
        HArr::orderByDayNameForOneDimension($sales_channel_channels_data);
        return ;
    }
    (
        uasort(
            $sales_channel_channels_data,
            function ($a, $b) {
                if (isset($a['Sales Values'], $b['Sales Values'])) {
                    $a = array_sum($a['Sales Values']);
                    $b = array_sum($b['Sales Values']);
                    if ($a == $b) {
                        return 0;
                    }
                    return ($a > $b) ? -1 : 1;
                }

                return;
            }
        )
    );
}
function sortTwoDimensionalArr(array &$arr)
{
    uasort($arr, function ($a, $b) {
        if ($a == $b) {
            return 0;
        }

        return ($a > $b) ? -1 : 1;
    });
}
function sortOneDimensionalArr(array &$arr)
{
    uasort($arr, function ($a, $b) {
        if ($a == $b) {
            return 0;
        }

        return ($a > $b) ? -1 : 1;
    });
}

function sortTwoDimensionalBaseOnKey(array &$arr, $key)
{
    uasort($arr, function ($a, $b) use ($key) {
        if ($a[$key] == $b[$key]) {
            return 0;
        }

        return ($a[$key] > $b[$key]) ? -1 : 1;
    });
}
function sortTwoDimensionalExcept(array &$arr, array $exceptKeys)
{
    uksort($arr, function ($key1, $key2) use ($exceptKeys, $arr) {
        if (!in_array($key1, $exceptKeys) && !in_array($key2, $exceptKeys)) {
            if ($arr[$key1] == $arr[$key2]) {
                return 0;
            }

            return $arr[$key1] > $arr[$key2] ? -1 : 1;
        } elseif (!in_array($key1, $exceptKeys) && in_array($key2, $exceptKeys)) {
            return -1;
        } elseif (in_array($key1, $exceptKeys) && !in_array($key2, $exceptKeys)) {
            return -1;
        } else {
            return -1;
        }
    });
}

function getTypeFor($type, $companyId, $formatted = false, $date = false, $start_date = null, $end_date = null, $tableName=null)
{
    $tableName = $tableName ? $tableName : 'sales_gathering';
    $netValueColumn = $tableName == 'expense_analysis' ? 'total_cost' : 'net_sales_value';
    if ($formatted) {
        // 2022-03-22
        // start 01-01-2021
        // end 01-01-2022

        
        return  DB::table($tableName)->where('company_id', $companyId)
            ->when($date && $start_date, function (Builder $builder) use ($start_date) {
                $builder->where('date', '>=', $start_date);
            })
            ->when($date && $end_date, function (Builder $builder) use ($end_date) {
                $builder->where('date', '<=', $end_date);
            })
            ->groupBy($type)
            ->distinct()
            ->select($type)
             ->orderByRaw('sum('.$netValueColumn.') desc')
            ->get()->pluck($type, $type)->toArray();
        ;
    } else {
        $data = DB::table('sales_gathering')->where('company_id', $companyId)
            ->when($date && $start_date, function (Builder $builder) use ($start_date) {
                $builder->where('date', '>=', $start_date);
            })
            ->when($date && $end_date, function (Builder $builder) use ($end_date) {
                $builder->where('date', '<=', $end_date);
            })
            ->groupBy($type)
            ->select($type)
             ->orderByRaw('sum('.$netValueColumn.') desc')
            ->distinct()
            ->get()->pluck($type)->toArray();

        $data = array_filter($data, function ($item) {
            return $item;
        });
        return $data;
    }
}
function getExportFor($type, $companyId, $formatted = false, $date = false, $start_date = null, $end_date = null)
{
    if ($formatted) {
        // 2022-03-22
        // start 01-01-2021
        // end 01-01-2022


        return  DB::table('export_analysis')->where('company_id', $companyId)
            ->when($date && $start_date, function (Builder $builder) use ($start_date) {
                $builder->where('purchase_order_date', '>=', $start_date);
            })
            ->when($date && $end_date, function (Builder $builder) use ($end_date) {
                $builder->where('purchase_order_date', '<=', $end_date);
            })
            ->groupBy($type)
            ->distinct()
            ->select($type)
            // ->orderByRaw('sum(net_sales_value) desc')
            // ->orderBy($type)
            ->get()->pluck($type, $type)->toArray();
        ;
    } else {
        $data = DB::table('export_analysis')->where('company_id', $companyId)
            ->when($date && $start_date, function (Builder $builder) use ($start_date) {
                $builder->where('purchase_order_date', '>=', $start_date);
            })
            ->when($date && $end_date, function (Builder $builder) use ($end_date) {
                $builder->where('purchase_order_date', '<=', $end_date);
            })
            ->groupBy($type)
            ->select($type)
            // ->orderByRaw('sum(net_sales_value) desc')
            ->distinct()
            ->get()->pluck($type)->toArray();

        $data = array_filter($data, function ($item) {
            return $item;
        });

        return $data;
    }
}

function getExpenseFor($type, $companyId, $formatted = false, $date = false, $start_date = null, $end_date = null)
{
    if ($formatted) {
        // 2022-03-22
        // start 01-01-2021
        // end 01-01-2022


        return  DB::table('expense_analysis')->where('company_id', $companyId)
            ->when($date && $start_date, function (Builder $builder) use ($start_date) {
                $builder->where('date', '>=', $start_date);
            })
            ->when($date && $end_date, function (Builder $builder) use ($end_date) {
                $builder->where('date', '<=', $end_date);
            })
            ->groupBy($type)
            ->distinct()
            ->select($type)
            // ->orderByRaw('sum(net_sales_value) desc')
            // ->orderBy($type)
            ->get()->pluck($type, $type)->toArray();
        ;
    } else {
        $data = DB::table('expense_analysis')->where('company_id', $companyId)
            ->when($date && $start_date, function (Builder $builder) use ($start_date) {
                $builder->where('date', '>=', $start_date);
            })
            ->when($date && $end_date, function (Builder $builder) use ($end_date) {
                $builder->where('date', '<=', $end_date);
            })
            ->groupBy($type)
            ->select($type)
            // ->orderByRaw('sum(net_sales_value) desc')
            ->distinct()
            ->get()->pluck($type)->toArray();

        $data = array_filter($data, function ($item) {
            return $item;
        });

        return $data;
    }
}

function getNumberOfProductsItems($companyId)
{
    return ProductSeasonality::where('company_id', $companyId)->count();
}
function canShowNewItemsProducts($companyId)
{
    return  getNumberOfProductsItems($companyId);
}

// function getProductsItems($companyId)
// {
//     return ProductSeasonality::where('company_id', $companyId)->get();
// }
// function deleteProductItemsForForecast($companyId)
// {
//     ProductSeasonality::where('company_id', $companyId)->delete();
// }
// function deleteNewProductAllocationBaseForForecast($companyId)
// {
//     NewProductAllocationBase::where('company_id', $companyId)->delete();
//     SecondNewProductAllocationBase::where('company_id', $companyId)->delete();
//     AllocationSetting::where('company_id', $companyId)->delete();
//     SecondAllocationSetting::where('company_id', $companyId)->delete();
//     ExistingProductAllocationBase::where('company_id', $companyId)->delete();
//     SecondExistingProductAllocationBase::where('company_id', $companyId)->delete();
//     ModifiedSeasonality::where('company_id', $companyId)->delete();
//     ModifiedTarget::where('company_id', $companyId)->delete();
// }

function getLargestArrayDates(array $array)
{
    if (count($array) == count($array, COUNT_RECURSIVE)) {
        $dates = [];
        foreach ($array as $date => $val) {
            if ($date) {
                try {
                    $dates[] =
                        Carbon::make($date)->format('d-M-Y');
                } catch (\Exception $e) {
                    return $dates;
                }
            } else {
                return $dates;
            }
        }

        return $dates;
    } else {
        $largestArray = getLargestArray($array);

        return getLargestArrayDates($largestArray);
    }
}
function getLargestArray($array)
{
    $largestArr = [];
    foreach ($array as $arr) {
        if (count($arr) > count($largestArr)) {
            $largestArr = $arr;
        }
    }

    return $largestArr;
}
function getDateBetween(array $dates)
{
    $smallest = null;
    $largest = null;
    if (count($dates)) {
        foreach ($dates as $type => $date) {
            if (is_array($date)) {
                foreach ($date as $d => $k) {
                    $d = Carbon::make($d);
                    if (is_null($smallest)) {
                        $smallest = $d;
                    } else {
                        if (!$d->greaterThan($smallest)) {
                            $d = $smallest;
                        }
                    }

                    if (is_null($largest)) {
                        $largest = $d;
                    } else {
                        if ($d->greaterThan($largest)) {
                            $largest = $d;
                        }
                    }
                }
            } else {
                $newDates = array_keys($dates);
                $smallest = Carbon::make($newDates[0]) ?? null;
                $largest = Carbon::make($newDates[count($newDates) - 1]) ?? null;
            }
        }



        $period = new DatePeriod(
            new DateTime($smallest->format('Y-m-d')),
            new DateInterval('P1M'),
            new DateTime($largest->format('Y-m-d'))
        );

        $per = [];
        foreach ($period as $p) {
            $per[] = $p->format('d-M-Y');
        }

        return $per;
    }


    return [];
}


function generateIdForExcelRow(int $companyId)
{
    return uniqid('company_' . $companyId) . Str::random(9) . $companyId . uniqid();
}

function getTotalUploadCacheKey($company_id, $jobId, string $modelName)
{
    return 'total_uploaded_for_company_' . $company_id . 'for_job_' . $jobId .'for_model'. $modelName;
}

function getShowCompletedTestMessageCacheKey($companyId, $modelName)
{
    return 'show_complete_test_phase_' . $companyId.$modelName;
}




function is_multi_array($arr)
{
    rsort($arr);

    return isset($arr[0]) && is_array($arr[0]);
}

function maxOptionsForOneSelector(): int
{
    // return 2 ;
    return 12;
}

function isCustomerExceptionalCase($type, $name_of_selector_label)
{
    $conditionOne = (($type == 'category') && ($name_of_selector_label == 'Customers Against Categories' ||  $name_of_selector_label == 'Categories'));
    return $conditionOne;
}

function isCustomerExceptionalForProducts($type, $name_of_selector_label)
{
    $conditionTwo = ($type == 'product_or_service' && ($name_of_selector_label == 'Customers Against Products' ||  $name_of_selector_label == 'Products'));

    return $conditionTwo;
}

function isCustomerExceptionalForProductsItems($type, $name_of_selector_label)
{
    $conditionTwo = ($type == 'product_item' && ($name_of_selector_label == 'Customers Against Products Items' ||  $name_of_selector_label == 'Product Items'));

    return $conditionTwo;
}

function orderTotalsForRanking(array &$array)
{
    (
        uasort(
            $array,
            function ($a, $b) {
                if (isset($a['total'], $b['total'])) {
                    $a = ($a['total']);

                    $b = ($b['total']);


                    if ($a == $b) {
                        return 0;
                    }

                    return ($a > $b) ? -1 : 1;
                }

                return;
            }
        )
    );
    ;


    // $data[$branchName][$rankNumber] ?? []
}


// function hasProductsItems($company)
// {
// $fields = (new ExportTable)->customizedTableField($company, 'SalesGathering', 'selected_fields');

// return (false !== $found = array_search('Product Item', $fields));
// }
function failAllocationMessage($allocation_type)
{
	$type = \App\Helpers\HVero::capitializeType($allocation_type);
    return __('Please Add New') . ' ' . $type;
}
function hasProductsItems($company)
{
    $query = 'select count(*) as has_product_item from sales_gathering where company_id = ' . $company->id . ' and product_item is not null';
    $productItems = DB::select($query);

    return $productItems[0]->has_product_item ?? 0;
}
function hasAtLeastOneOfType($company, $type)
{
    $query = 'select count(*) as has_product_item from sales_gathering where company_id = ' . $company->id . ' and ' . $type . ' is not null';
    $productItems = DB::select($query);

    return $productItems[0]->has_product_item ?? 0;
}
function count_array_values(array $array)
{
    $counter = 0;
    foreach ($array as $arr) {
        $counter += count($arr);
    }

    return $counter;
}
function countExistingTypeFor($type, $company)
{
    $query = 'select count(*) as has_product_item from sales_gathering where company_id = ' . $company->id . ' and ' . $type . ' is not null';
    $productItems = DB::select($query);

    return $productItems[0]->has_product_item ?? 0;
}




function getTypeSalesAnalysisData(Request $request, Company $company, $type)
{
    $dimension = $request->report_type;
    $report_data = [];
    $growth_rate_data = [];

    $sales_channels = is_array(json_decode(($request->sales_channels[0]))) ? json_decode(($request->sales_channels[0])) : $request->sales_channels;

    foreach ($sales_channels as $sales_channel) {
        $sales_channel = str_replace("'", "\'", $sales_channel);
        $query = "
                SELECT DATE_FORMAT(LAST_DAY(date),'%d-%m-%Y') as gr_date  , net_sales_value ," . $type . "
                FROM sales_gathering
                WHERE ( company_id = '" . $company->id . "'AND " . $type . " = '" . $sales_channel . "' AND date between '" . $request->start_date . "' and '" . $request->end_date . "')
                ORDER BY id ";
        $sales_channels_data = collect(DB::select($query))->groupBy('gr_date')->map(function ($item) {
            return $item->sum('net_sales_value');
        })->toArray();

        $interval_data_per_item = [];
        $years = [];
        if (count($sales_channels_data) > 0) {
            array_walk($sales_channels_data, function ($val, $date) use (&$years) {
                $years[] = date('Y', strtotime($date));
            });
            $years = array_unique($years);
            $report_data[$sales_channel] = $sales_channels_data;
            $interval_data_per_item[$sales_channel] = $sales_channels_data;
            $interval_data = Intervals::intervals($interval_data_per_item, $years, $request->interval);

            $report_data[$sales_channel] = $interval_data['data_intervals'][$request->interval][$sales_channel] ?? [];
			$report_data[$sales_channel]['31-01-2026'] = 100;
			$growth_rate_data[$sales_channel] = HArr::calculateGrowthRate($report_data[$sales_channel]);
        }
    }

    $final_report_data = [];
    $sales_channels_names = [];
    foreach ($sales_channels as $sales_channel) {
        $final_report_data[$sales_channel]['Sales Values'] = ($report_data[$sales_channel] ?? []);
        $final_report_data[$sales_channel]['Growth Rate %'] = ($growth_rate_data[$sales_channel] );
        $sales_channels_names[] = (str_replace(' ', '_', $sales_channel));
    }

    return $report_data;
}


function sumBasedOnQuarterNumber($array, array $quarters, $total)
{
    $result = 0;
    foreach ($array as $month => $val) {
        if (in_array($month, $quarters)) {
            $result += $val;
        }
    }

    return $result ? number_format($result / $total  * 100, 2) . ' % ' : '-';
}



function getAllColumnsTypesForCaching($companyId)
{
    $exportables = array_keys(getExportableFields($companyId));
    $cacheablesFields = [
        'country', 'branch', 'sales_person', 'customer_name', 'business_sector', 'zone', 'sales_channel', 'category', 'product_or_service', 'product_item'
    ];

    return array_intersect($exportables, $cacheablesFields);
}




function getIterableItems($array)
{
    $iterables = [];
    foreach ($array as $key => $arrVal) {
        foreach ($arrVal as $item => $val) {
            if (!isset($iterables[$item])) {
                $iterables[$item] = getTotalForThisTypeExceptDead($array, $item, 'total_sales');
            }
        }
    }
    sortTwoDimensionalArr($iterables);

    return $iterables;
}

function getTotalForSingleType($array, $key)
{
    $totals = 0;
    foreach ($array as $arr) {
        foreach ($arr as $ar) {
            $totals += $ar->{$key};
        }
    }

    return $totals;
}
function countTotalForSingleType($array)
{
    $totals = 0;
    foreach ($array as $arr) {
        foreach ($arr as $ar) {
            $totals += 1;
        }
    }

    return $totals;
}
function calcTotalsForTotalsActiveItems(array $array, $key)
{
    $totals = 0;
    foreach ($array as $arr) {
        foreach ($arr as $ar) {
            foreach ($ar as $item) {
                $totals += $item->{$key} ?? 0;
            }
        }
    }

    return $totals;
}

function countTotalsForTotalsActiveItems(array $array, $key)
{
    $totals = 0;
    foreach ($array as $arr) {
        foreach ($arr as $ar) {
            foreach ($ar as $item) {
                $totals += 1;
            }
        }
    }

    return $totals;
}


function getTotalForThisTypeExceptDead(array $array, $iterableSingleItem, $key)
{
    $total = 0;
    foreach ($array as $index => $arrayOfValues) {
        if (!in_array($index, ['Dead', 'Stop'])) {
            $items =  $arrayOfValues[$iterableSingleItem] ?? [];

            foreach ($items as $item) {
                $total += $item->{$key};
            }
        }
    }

    return $total;
}

function getTotalForThisType(array $array, $iterableSingleItem, $key)
{
    $total = 0;
    foreach ($array as $arrayOfValues) {
        $items =  $arrayOfValues[$iterableSingleItem] ?? [];
        foreach ($items as $item) {
            $total += $item->{$key};
        }
    }

    return $total;
}
function array_fill_keys_with_values(array $arr)
{
    $newArray = [];
    foreach ($arr as $a) {
        $newArray[$a] = $a;
    }

    return $newArray;
}
function countTotalForThisType(array $array, $iterableSingleItem)
{
    $total = 0;
    foreach ($array as $arrayOfValues) {
        $items =  $arrayOfValues[$iterableSingleItem] ?? [];
        foreach ($items as $item) {
            $total += 1;
        }
    }

    return $total;
}

function sum_array_of_std_objectsForSubType(array $array, $key)
{
    $sum =  0;
    foreach ($array as $arr) {
        $sum += $arr->{$key};
    }

    return $sum;
}

function count_array_of_std_objects(array $array)
{
    $counter = 0;
    foreach ($array as $arr) {
        $counter += 1;
    }

    return $counter;
}

function formatInvoiceForEachInterval(array $array, $selectedType)
{
    $finalResult = [];
    $result = [
        'product_item' => 0,
        'invoice_number' => 0
    ];

    $finalResult = [
        'product_item_avg_count' => 0,
        'invoice_count' => 0,
        'avg_invoice_value' => 0
    ];
    foreach ($array['sumForEachInterval'][$selectedType] ?? [] as $year => $data) {
        $result['product_item'] =   $result['product_item'] + $data[12]['product_item'] ;
        $result['invoice_number'] =  $result['invoice_number'] + $data[12]['invoice_number'] ;
    }
    $resultForSales = 0;
    foreach ($array['reportSalesValues'][$selectedType] ?? [] as $data => $saleValue) {
        $resultForSales += $saleValue;
    }

    $finalResult['invoice_count'] = $result['invoice_number'] ;
    $finalResult['product_item_avg_count'] = $result['invoice_number'] ? round($result['product_item'] / $result['invoice_number']) : 0;
    $finalResult['avg_invoice_value'] = $result['invoice_number'] ? number_format($resultForSales / $result['invoice_number'], 0) : 0;

    return $finalResult;
}
function getFieldsForTakeawayForType(string $type)
{
    $commonFields = ['customer_name' => __('Customers Count'), 'category' => __('Categories Count'), 'product_or_service' => __('Products/Service Count'), 'product_item' => __('Products Item Count'), 'sales_person' => __('Salesperson Count'), 'branch' => __('Branch Count'), 'invoice_count' => __('Invoices Count'), 'product_item_avg_count' => __('Avg Products Item Per Invoice'), 'avg_invoice_value' => __('Avg Invoice Values')];

    return [
        'business_sector' => array_merge($commonFields, []),
        'category' => array_merge(Arr::except($commonFields, ['category']), [
            'business_sector' => __('Business Sectors Count'),
            'sales_channel' => __('Sales Channel Count'),
            'zone' => __('Zone Count')
        ]),
        'sales_channel' => array_merge($commonFields, [
            'business_sector' => __('Business Sectors Count'),
            'zone' => __('Zone Count')
        ]),
        'branch' => array_merge($commonFields, [
            'business_sector' => __('Business Sectors Count'),
            'sales_channel' => __('Sales Channel Count'),

        ]),
        'zone' => array_merge($commonFields, [
            'sales_channel' => __('Sales Channel Count'),
        ]),
        'product_or_service' => array_merge(Arr::except($commonFields, ['category', 'product_or_service']), [
            'business_sector' => __('Business Sectors Count'),
            'sales_channel' => __('Sales Channel Count'),
            'zone' => __('Zone Count')
        ]),

        'product_item' => array_merge(Arr::except($commonFields, ['category', 'product_or_service', 'product_item']), [
            'business_sector' => __('Business Sectors Count'),
            'sales_channel' => __('Sales Channel Count'),
            'zone' => __('Zone Count')
        ])
    ][$type] ?? $commonFields;
}
function orderStdClassBy($stdObjArray, $orderKey, $direction = 'desc')
{
    (
        uasort(
            $stdObjArray,
            function ($a, $b) {
                if (isset($a->total_sales_value, $b->total_sales_value)) {
                    $a = $a->total_sales_value;
                    ;

                    $b = $b->total_sales_value;


                    if ($a == $b) {
                        return 0;
                    }

                    return ($a > $b) ? -1 : 1;
                }

                return;
            }
        )
    );

    return $stdObjArray;
}

function hasTopAndBottom($type)
{
    $allowedTypes = [
        'zone', 'product_or_service', 'product_item', 'customer_name', 'business_sector', 'category', 'sales_channel', 'sales_person', 'branch'
    ];

    return in_array($type, $allowedTypes);
}

function forecastHasBeenChanged($sales_forecast, array $newData)
{
    if (is_null($sales_forecast)) {
        return true;
    }



    foreach (['previous_1_year_sales', 'previous_year', 'previous_year_gr', 'average_last_3_years', 'target_base', 'sales_target', 'new_start', 'growth_rate', 'add_new_products', 'number_of_products', 'sales_target', 'seasonality', 'start_date'] as $index => $field) {
        if (@$newData[$field] != $sales_forecast->{$field}) {
            return true;
        }
    }

    return false;
}

function getCacheKeyForFirstAllocationReport($companyId)
{
    return 'first_allocation_report_for_company_' . $companyId;
}


function getCacheKeyForSecondAllocationReport($companyId)
{
    return 'second_allocation_report_for_company_' . $companyId;
}
function getCacheKeyForQuantityFirstAllocationReport($companyId)
{
    return 'quantity_first_allocation_report_for_company_' . $companyId;
}


function getCacheKeyForQuantitySecondAllocationReport($companyId)
{
    return 'quantity_second_allocation_report_for_company_' . $companyId;
}
function formatExistingFormNewAllocation($newAllocation)
{
    if ($newAllocation) {
        $allocationsNames = $newAllocation->new_allocation_bases_names;
        $data = $newAllocation->allocation_base_data;
        if (!$data) {
            return [];
        }
        $sums = [];
        foreach ($data as $productItem => $newData) {
            foreach ($newData as $branchName => $values) {
                $sums[$branchName] = ($sums[$branchName] ?? 0) + ($values['actual_value'] ?? 0);
            }
        }

        return $sums;
    }

    return [];
}

function formatDateVariable($dates, $start_date, $end_date)
{
    if (!$dates) {
        return [];
    }
    if (!$start_date || !$end_date) {
        return $dates;
    }
    $start_date = Carbon::make($start_date);

    $end_date = Carbon::make($end_date);
    // we will ignore day of end date
    $dayOfEndDate = $end_date->day;
    $monthOfEndDate = $end_date->month;
    $yearOfEndDate = $end_date->year;
    // get last day in month and year
    $end_date = Carbon::create($yearOfEndDate, $monthOfEndDate)->lastOfMonth()->format('Y-m-d');
    $end_date = Carbon::make($end_date);
    $filteredDates = [];
    foreach ($dates as $date) {
        $dateWithoutFormatting = $date;
        $date = Carbon::make($date);
        if (
            $date >= $start_date
            && $date <= $end_date

        ) {
            $filteredDates[] = $dateWithoutFormatting;
        }
    }

    return count($filteredDates) ? $filteredDates : $dates;
}

function getTotalsOfTotal($reportArray)
{
    $totalForEachItem = [];
    foreach ($reportArray as $itemName => $data) {
        foreach ($data as $reportKey => $valueArr) {
            if ($reportKey != 'Growth Rate %' && $reportKey != 'Total' && $itemName != 'Total' && $itemName != 'Growth Rate %') {
                $totalForEachItem[$itemName][$reportKey] = 0;

                if (isset($reportArray[$itemName][$reportKey]['Sales Values'])) {
                    $totalForEachItem[$itemName][$reportKey] += array_sum($reportArray[$itemName][$reportKey]['Sales Values']);
                }
            }
        }
    }

    $newArray = [];

    foreach ($totalForEachItem as $key => $arr) {
        uasort($arr, function ($a, $b) {
            $a = ($a);
            $b = ($b);

            if ($a == $b) {
                return 0;
            }

            return ($a > $b) ? -1 : 1;
        });

        $newArray[$key] = $arr;
    }

    return $newArray;
    // return $totalForEachItem ;
}

function getLopeItemsFromEachReport($firstReport, $secondReport)
{
    $first = [];
    $second = [];
    foreach ($secondReport as $key => $arrayOfValues) {
        foreach ($arrayOfValues as $itemName => $value) {
            $second[$itemName] = $itemName;
        }
    }
    foreach ($firstReport as $key => $arrayOfValues) {
        sortOneDimensionalArr($arrayOfValues);
        foreach ($arrayOfValues as $itemName => $value) {
            $first[$itemName] = $itemName;
        }
    }

    return array_unique(array_merge($second, $first));

    // return $data ;
}

function getMainItemsNameFromEachInterval($firstReport, $secondReport)
{
    array_sort_products($secondReport);

    $firstReportProductsItems = array_keys($firstReport);
    $secondReportProductsItems = array_keys($secondReport);

    return array_unique(array_merge($secondReportProductsItems, $firstReportProductsItems));
}
function array_sort_products(&$secondReport)
{
    uasort($secondReport, function ($a, $b) {
        //   foreach( )
		
        $a = $a['Average Value'] ?? 0;
        $b = $b['Average Value'] ?? 0;

        if ($a == $b) {
            return 0;
        }

        return ($a > $b) ? -1 : 1;
    });
}
function sum_all_array_values($array)
{
    $total = 0;
    foreach ($array as $key => $value) {
        $total += $value;
    }

    return $total;
}

function getCanReloadUploadPageCachingForCompany($companyId, $modelName)
{
    return 'can_reload_caching_page_for_company_' . $companyId.$modelName;
}

function getComparingReportForAnalysis($request, $report_data, $secondReport, $company, $dates, $view_name, $Items_names, $modelType, $forMinMaxReport = false)
{
    if ($request->report_type == 'comparing' && $secondReport == true) {
        if ($forMinMaxReport) {
            $firstReportData = $report_data;
        }
        $firstReportData['first_report']  =   $dates;
        $firstReportData['first_report_date']  =   Carbon::make($request->start_date)->format('d M Y') . ' ' . __('To') . ' ' . Carbon::make($request->end_date)->format('d M Y');
        $firstReportData['report_data'] =  $report_data;
        $request['start_date'] = $request->start_date_second;
        $request['end_date'] = $request->end_date_second;

        if ($modelType == 'product_item') {
            $secondReportDataResult = (new SKUsAgainstAnalysisReport())->result($request, $company, false);
            $type = __('Products Items');
        } elseif ($modelType == 'zone') {
            $secondReportDataResult = (new ZoneAgainstAnalysisReport())->result($request, $company, 'view', false);
            $type = __('Zones');
        } elseif ($modelType == 'sales_channel') {
            $secondReportDataResult = (new SalesChannelsAgainstAnalysisReport())->result($request, $company, 'view', false);
            $type = __('Sales Channel');
        } elseif ($modelType == 'category') {
            $secondReportDataResult = (new CategoriesAgainstAnalysisReport())->result($request, $company, 'view', false);
            $type = __('Categories');
        } elseif ($modelType == 'product_or_service') {
            $secondReportDataResult = (new ProductsAgainstAnalysisReport())->result($request, $company, 'view', false);
            $type = __('Products Or Services');
        } elseif ($modelType == 'branch') {
            $secondReportDataResult = (new BranchesAgainstAnalysisReport())->result($request, $company, false);
            $type = __('Branch');
        } elseif ($modelType == 'business_sector') {
            $secondReportDataResult = (new BusinessSectorsAgainstAnalysisReport())->result($request, $company, 'view', false);
            $type = __('Business Sector');
        } elseif ($modelType == 'sales_person') {
            $secondReportDataResult = (new SalesPersonsAgainstAnalysisReport())->result($request, $company, false);
            $type = __('Business Sector');
        } elseif (isset((new ExportTable)->customizedTableField($company, 'ExportAnalysis', 'selected_fields')[$modelType])) {
            $secondReportDataResult = (new ExportAgainstAnalysisReport())->result($request, $company, 'view', false);
            $type = __($modelType);
        } elseif (isset((new ExportTable)->customizedTableField($company, 'ExpenseAnalysis', 'selected_fields')[$modelType])) {
            if ($forMinMaxReport) {
                $secondReportDataResult = (new ExpenseAgainstAnalysisReport())->AvgMinMaxReportResult($request, $company, 'view', false);
                
            } else {
                $secondReportDataResult = (new ExpenseAgainstAnalysisReport())->twoSelectorAndThreeSelectorAndComparingResult($request, $company, 'view', false);
                
            }
            $type = __($modelType);
        } else {
            return [];
            // throw new \Exception('custom exception .. not supported type ' . $modelType);
        }

        $secondReportData = $secondReportDataResult['report_data'] ?? [];
        
        $secondReportData['full_date'] = $secondReportDataResult['full_date'] ?? [];
        $report_data = getTotalsOfTotal($report_data);
        $secondReportData['report_data'] = getTotalsOfTotal($secondReportDataResult['report_data']);
        if ($forMinMaxReport) {
            $secondReportData['report_data'] = $secondReportDataResult['report_data'];
        }
        
        $secondItemsName = getLopeItemsFromEachReport($report_data, $secondReportData['report_data']);
        $isDayNameReport=$request->get('type') == 'day_name';
        $secondItemsName = $isDayNameReport ? HArr::orderByDayNameForOneDimension($secondItemsName) : $secondItemsName;
        $secondReportData['report_data']  = addFirstReportKeysToSendReport($secondItemsName, $secondReportData['report_data']);
        $mainItems = getMainItemsNameFromEachInterval($report_data, $secondReportData['report_data']);
        $viewName = 'client_view.reports.sales_gathering_analysis.second_comparing_analysis';
        if ($forMinMaxReport) {
            $viewName = 'client_view.reports.sales_gathering_analysis.avg_comparing_analysis';
        }
        return view($viewName, compact('company', 'isDayNameReport', 'view_name', 'firstReportData', 'Items_names', 'dates', 'report_data', 'secondReportData', 'secondItemsName', 'mainItems', 'type'));
    }
}
function addFirstReportKeysToSendReport($keys, $secondReport)
{
    if (!count($secondReport)) {
        return $secondReport;
    }
    foreach ($secondReport as $key => $array) {
        foreach ($keys as $newKey) {
            !isset($array[$newKey]) ? $secondReport[$key][$newKey] = 0 : '';
        }
    }

    return $secondReport;
}



function getCurrentCompanyId()
{
    return Request()->segment(2) ?? null;
}
function getCurrentDateForFormDate($fieldName, $format = 'm/d/Y')
{
    return old($fieldName) ?: date($format);
}
function getCompanyId()
{
    //  admin.get.revenue-business-line
    return Request()->segment(2);
}

function getExportFormat()
{
    return
        [
            [
                'title' => __('Excel'),
                'value' => 'Xlsx'
            ],
            [
                'title' => __('PDF'),
                'value' => 'Dompdf'
            ]

        ];
}
function getDefaultOrderBy(): array
{
    return [
        'column' => 'created_at',
        'direction' => 'desc'
    ];
}
function getModelNamespace()
{
    return '\App\Models\\';
}

function generateDatesBetweenTwoDates(Carbon $start_date, Carbon $end_date, $method = 'addMonth', $format = 'Y-m-d', $indexedArray = true, $indexFormat = 'Y-m-d')
{
    $dates = [];
    for ($date = $start_date->copy(); $date->lte($end_date); $date->{$method}()->setTime(0, 0)) {
        if ($indexedArray) {
            $dates[] = $date->format($format);
        } else {
            $dates[$date->format($indexFormat)] = $date->format($format);
        }
    }
    return $dates;
}
/**
 * ! USE HDate::generateDatesBetweenStartDateAndDuration instead of this function
 */
function generateDatesBetweenTwoDatesWithoutOverflow(Carbon $start_date, Carbon $end_date, $method = 'addMonthNoOverflow', $format = 'Y-m-d', $indexedArray = true, $indexFormat = 'Y-m-d')
{
    $dates = [];
    for ($date = $start_date->copy(); $date->lte($end_date); $date->{$method}()->setTime(0, 0)) {
        if ($indexedArray) {
            $dates[] = $date->format($format);
        } else {
            $dates[$date->format($indexFormat)] = $date->format($format);
        }
    }
    return $dates;
}
function generateDatesBetweenTwoIndexedDates(int $startDateAsIndex, int $endDateAsIndex):array
{
    $result = [];
    for ($i =$startDateAsIndex ; $i <=$endDateAsIndex ; $i++) {
        $result[] = $i;
    }
    return $result;
}
function formatDateFromString(string $date): string
{
    if ($date) {
        return \Carbon\Carbon::make($date)->format(defaultUserDateFormat());
    }

    return __('N/A');
}
function formatDateWithoutDayFromString(string $date): string
{
    if ($date) {
        return \Carbon\Carbon::make($date)->format('M-Y');
    }

    return __('N/A');
}

function defaultUserDateFormat()
{
    return 'd-M-Y';
    // return 'Y F d';
}

function formatReportDataForDashBoard(string $incomeStatementDurationType, string $incomeStatementStartDate, $data, $start_date, $end_date)
{
    $dates = generateDatesBetweenTwoDates(Carbon::make($start_date), Carbon::make($end_date), 'addMonth');

    $newData = [];
    foreach ($data as $index => $mainItem) {
        foreach ($dates as $dateAsIndex => $dateAsString) {
            $mainItemName = $mainItem->name;
            $newData[$mainItemName]['data'][$dateAsString] = getTotalInPivotDate($incomeStatementDurationType, $incomeStatementStartDate, $mainItem->withSubItemsFor(
                $mainItem->pivot->financial_statement_able_id,
                $mainItem->pivot->sub_item_type
            )->get()->pluck('pivot'), $dateAsIndex, $dateAsString, $dates);
        }
        
        if (isset($mainItemName)) {

            $newData[$mainItemName]['sub_items'] = getSubItemsFormatted($mainItem->withSubItemsFor(
                $mainItem->pivot->financial_statement_able_id,
                $mainItem->pivot->sub_item_type
            )->get()->pluck('pivot'), $dates, $incomeStatementStartDate, $incomeStatementDurationType);
            $newData[$mainItemName]['name'] = $mainItemName;
        }
    }
    return $newData;
}
function getSubItemsFormatted($data, $dates, string $incomeStatementStartDate, string $incomeStatementDurationType): array
{
    $subItems = [];
    foreach ($data as $pivot) {
        $subItemName = $pivot->sub_item_name;
        $payload = $pivot->payload ? (array)json_decode($pivot->payload) : null;
        if ($payload) {
            $subItems[$subItemName] = array_sum_conditional($payload, $dates, $incomeStatementStartDate, $incomeStatementDurationType);
        } else {
            $subItems[$subItemName] = 0;
        }
    }

    return $subItems;
}
function yearInArray(string $date, array $dates)
{
    $year = explode('-', $date)[0];
    foreach ($dates as $newDate) {
        if (explode('-', $newDate)[0] == $year) {
            return true;
        }
        //  ;
    }

    return false;
}
function yearAndMonthInArray(string $date, array $dates)
{
    $year = explode('-', $date)[0];
    $month = explode('-', $date)[1];
    foreach ($dates as $newDate) {
        if (explode('-', $newDate)[0] == $year && $month == explode('-', $newDate)[1]) {
            return true;
        }
    }

    return false;
}
function array_sum_conditional($data, $dates, $incomeStatementStartDate, $incomeStatementDurationType)
{
    $incomeStatementStartDate = Carbon::make($incomeStatementStartDate);
    // $incomeStatementDurationType='annually';
    $total = 0;
    foreach ($data as $date => $value) {
        if ($incomeStatementDurationType == 'annually') {
            if (isset($dates[$date]) && yearInArray($dates[$date], $dates)) {
                $total += $value;
            }
        } else {
            if (array_key_exists($date, $dates)) {
                // if (yearAndMonthInArray($date, $dates)) {
                $total += $value;
            }
        }
    }

    return $total;
}
function inDurationDate(string $date, $dates, $incomeStatementDurationType)
{
    if ($incomeStatementDurationType == 'annually') {
        return yearInArray($date, $dates);
    }
    return yearAndMonthInArray($date, $dates);
}
function getTotalInPivotDate(string $incomeStatementDurationType, string $incomeStatementStartDate, $pivot, int $dateAsIndex, string $dateAsString, $dates): array
{
    // 1-1-2021

    // 2/1/2021
    $totalWithDepreciation = 0;
    $totalDepreciation = 0;
    $incomeStatementStartDate = Carbon::make($incomeStatementStartDate);

    // 2023
    if (inDurationDate($dateAsString, $dates, $incomeStatementDurationType)) {
        foreach ($pivot as $data) {
            if (!isQuantitySubItem($data->sub_item_name)) {
                // $formattedDate = explode('-', $date)[0] . '-' . explode('-', $date)[1] . '-' . sprintf('%02d', $incomeStatementStartDate->day);
                $payload = $data->payload ? (array)json_decode($data->payload) : null;
                if ($payload && isset($payload[$dateAsIndex]) && $payload[$dateAsIndex]) {
                    $totalWithDepreciation += $payload[$dateAsIndex];
                    if ($data->is_depreciation_or_amortization) {
                        $totalDepreciation += $payload[$dateAsIndex];
                    }
                }
            }
        }
    }

    return [
        'total_with_depreciation' => $totalWithDepreciation,
        'total_depreciation' => $totalDepreciation
    ];
}
// function formatDataForBreakDown($array)
// {
//     $data = [];
//     foreach($array as $key => $values){
//         foreach($values as $date => $value){
//             $data[] = [
//                 'gr_date'=>$date,
//                 'net_sales_value'=>$value ,
//                 'zone'=>$key
//             ];
//         }
//     }
//     return $data ;
// }
function get_total_for_group_by_key(array $data, string $key): array
{
    $totalWithDepreciation = 0;
    $totalDepreciation = 0;
    foreach ($data as $obj) {
        if ($obj['name'] == $key) {
            $totalWithDepreciation += array_sum(array_column($obj['data'], 'total_with_depreciation'));
            $totalDepreciation += array_sum(array_column($obj['data'], 'total_depreciation'));
        }
    }

    return [
        'total_with_depreciation' => $totalWithDepreciation,
        'total_depreciation' => $totalDepreciation
    ];
}
function format_for_chart($array)
{
    $formattedData  = [];
    foreach ($array as $key => $data) {
        if (!isQuantitySubItem($key)) {
            $formattedData[] = [
                'item' => $key,
                'Sales Value' => $data
            ];
        }
    }

    return $formattedData;
}
function getIncomeStatementForCompany(int $companyId): Collection
{
    return IncomeStatement::where('company_id', $companyId)->get();
}

function dateIsBetweenTwoDates(Carbon $date, Carbon $firstDate, Carbon $secondDate)
{
    return $date->isBetween($firstDate, $secondDate);
}
function combineTwoArrayKeys(array $firstArray, array $secondArray)
{
    $combinedArray = [];
    foreach ($firstArray as $key1 => $val1) {
        foreach ($secondArray as $key2 => $val2) {
            $combinedArray[$key1] = $key1;
            $combinedArray[$key2] = $key2;
        }
    }

    return $combinedArray;
}
function getYearsFromDate(array $data)
{
    $years = [];
    foreach ($data as $name => $values) {
        foreach ($values as $dateString => $value) {
            $year = Carbon::make($dateString)->year;
            $years[$year] = $year;
        }
    }

    return $years;
}

function sum_each_key($array)
{
    $sumForEachItem = [];
    foreach ($array as $key => $values) {
        $sumForEachItem[$key] = array_sum($values);
    }

    return $sumForEachItem;
}

function secondIntervalGreaterThanFirst(string $firstIntervalDates, string $secondIntervalDates)
{
    $secondSegmentOfFirstDate = explode('/', $firstIntervalDates)[1];
    $secondSegmentOfSecondDate = explode('/', $secondIntervalDates)[1];

    return Carbon::make($secondSegmentOfFirstDate)->greaterThan($secondSegmentOfSecondDate);
}
function getIntervalFromString(string $str): string
{
    $firstDate = explode('/', explode('#', $str)[1] ?? '')[0];
    $secondDate = explode('/', explode('#', $str)[1] ?? '')[1];

    return Carbon::make($firstDate)->format('M\'Y') . '/' . Carbon::make($secondDate)->format('M\'Y');
}
function sum_all_keys(array $items)
{
    $total = 0;

    foreach ($items as $name => $itemValue) {
        if (!isQuantitySubItem($name)) {
            $total += $itemValue;
        }
    }

    return $total;
}
function getIntervals(array $items)
{
    $firstItem = array_key_first($items);

    return count($items[$firstItem]) ? array_keys($items[$firstItem]) : [];
}
function getSubItemsNames($items)
{
    $subItems = [];
    foreach ($items as $intervalName => $item) {
        foreach ($item as $key => $val) {
            $subItems[$key][$intervalName] = $val;
        }
    }

    return $subItems;
}



function getArrayValuesFromIndex(array $array, int $index)
{
    $newArray = [];
    foreach ($array as $currentItemIndex => $item) {
        if ($currentItemIndex >= $index) {
            $newArray[$currentItemIndex] = $item;
        }
    }

    return $newArray;
}





function getDurationIntervalTypesForSelectExceptMonthly(): array
{
    return [
        [
            'value' => 'annually',
            'title' => __('Annually')
        ],
    ];
}

function getPaymentTerms(): array
{

    return [
        [
            'value' => 'customize',
            'title' => __('Customize')
        ],
        [
            'value' => 'cash',
            'title' => __('Cash')
        ],
        [
            'value' => 'quarterly',
            'title' => __('Quarterly')
        ],
        [
            'value' => 'semi-annually',
            'title' => __('Semi Annually')
        ],
        [
            'value' => 'annually',
            'title' => __('Annually')
        ],
    ];
}
// function getFfePaymentTerms(): array
// {

//     return [
//         [
//             'value' => 'customize',
//             'title' => __('Customize')
//         ],
//         [
//             'value' => 'cash',
//             'title' => __('Cash')
//         ],
  
//     ];
// }

function getReportNameFromRouteName(string $routeName): string
{
    $explodedRouteName = explode('.', $routeName);

    return $explodedRouteName[count($explodedRouteName) - 2];
}
function getDeleteSubItemsFor(string $subItem): array
{
    if ($subItem == 'forecast') {
        return getAllFinancialAbleTypes();
    } elseif ($subItem == 'actual') {
        return getAllFinancialAbleTypes(['forecast']);
    }

    return [$subItem];
}
function getAllFinancialAbleTypes(array $exclude = []): array
{
    $allTypes = ['forecast', 'actual', 'adjusted', 'modified'];
    $types = [];
    foreach ($allTypes as $type) {
        if (!in_array($type, $exclude)) {
            $types[] = $type;
        }
    }

    return $types;
}
function getAllFinancialAbleTypesFormattedForDashboard()
{
    return [
        'forecast-actual' => __('Forecast Vs Actual'),
        'forecast-adjusted' => __('Forecast Vs Adjusted'),
        'forecast-modified' => __('Forecast Vs Modified'),
        'adjusted-actual' => __('Adjusted Vs Actual'),
        'adjusted-modified' => __('Adjusted Vs Modified'),
        'modified-actual' => __('Modified Vs Actual'),
    ];
}
function getDatedOf(array $first, array $second): array
{
    $firstArrayDates = array_keys($first);
    $secondArrayDates = array_keys($second);
    $dates = array_merge($firstArrayDates, $secondArrayDates);
    $dates = array_unique($dates);
    sort($dates);

    return $dates;
}
function combineNoneZeroValuesBasedOnComingDates(array $actualDatesAsIndexAndBooleans, array $first, array $second): array
{
    $combined = [];
    $dates = getDatedOf($first, $second);

    foreach ($dates as $date) {
        $isActualValue = $actualDatesAsIndexAndBooleans[$date]  ;
        $firstVal = $first[$date] ?? 0;
        $actualVal = $second[$date] ?? 0;
        $combined[$date] = $isActualValue ? $actualVal : $firstVal;
        // if ($isActualValue) {
        //     $actualDates[] = $date;
        // }
    }
    return $combined;
}

// function getProductsItemsQuantity($companyId)
// {
//     return QuantityProductSeasonality::where('company_id', $companyId)->get();
// }
function getNumberOfProductsItemsQuantity($companyId)
{
    return QuantityProductSeasonality::where('company_id', $companyId)->count();
}
function canShowNewItemsProductsQuantity($companyId)
{
    return  getNumberOfProductsItemsQuantity($companyId);
}
function formatOptionsForSelect(Collection $items, $idFun = 'getId', $valueFun = 'getName'): array
{
    $formattedData = [];
    foreach ($items as $item) {
        $formattedData[] = [
            'value' => $item->$idFun(),
            'title' => $item->$valueFun(),
        ];
    }

    return $formattedData;
}

function formatSelects($selects, $selectedItem, $id, $value, $addNew = false, $selectAll = false): string
{
    $result = '';
    if ($addNew) {
        // $result = '<option class="add-new-item" >'. __('Add New')  .' </option>';
    } elseif ($selectAll) {
        $result = '<option>' . __('All') . '</option> ';
    } else {
        $result = '';
    }

    foreach ($selects as $select) {
        $ID = $select->{$id};
        $val = $select->{$value};

        if (
            in_array($ID, explode(',', $selectedItem))
        ) {
            $result .= "<option value='$ID' selected> $val </option> ";
        } else {
            $result .= "<option value='$ID' > $val </option> ";
        }
    }

    return $result;
}

function getExportDateTime(): string
{
    return now()->toDateTimeString();
}
function getExportUserName()
{
   
    $user = Auth()->user() ;
    return  $user ? $user->getName() : null;
}

function orderArrayByItemsKeys(array $array): array
{
    ksort($array);

    return $array;
}

function checkIfArrayAllIsAllPositive(array $array)
{
    $positiveNumbers = array_filter($array, function ($val) {
        return $val > 0;
    });

    return count($positiveNumbers) == count($array);
}

function checkIfArrayAllIsAllNegative(array $array)
{
    $negativeNumbers = array_filter($array, function ($val) {
        return $val <= 0;
    });

    return count($negativeNumbers) == count($array);
}

// function calculateIrr($annual_free_cash_array, $discount_rate, $cash_and_loans, $net_present_value, $calculatedPercentage = null, $numberOfIteration = 1)
// {
//     $yearsAndFreeCash = $annual_free_cash_array;
//     // = [
//     //     1=>-5000000 ,
//     //     2=>3000000 ,
//     //     3=>4500000,
//     //     4=>15000000 ,
//     //     5=>125000000,
//     //     // 6=>1545132872.40807
//     // ];

//     if ($numberOfIteration == 1 && (checkIfArrayAllIsAllNegative($yearsAndFreeCash) || checkIfArrayAllIsAllPositive($yearsAndFreeCash))) {
//         return 'No IRR';
//     }


//     $percentage = $calculatedPercentage ?: $discount_rate;
//     $discountFactor = [];
//     $npv = [];
//     foreach ($yearsAndFreeCash as $year => $freshCash) {
//         $discountFactor[$year] = pow(1  +  $percentage, $year);
//         $npv[$year] = $freshCash / $discountFactor[$year];
//     }

//     // if($numberOfIteration == 1){
//     //     $original_npv =array_sum($npv)+ $cash_and_loans;
//     // }
//     $npv_sum = array_sum($npv) + $cash_and_loans;

//     if ($numberOfIteration == 750000) {
//         return $calculatedPercentage;
//     }
//     // need to make $npv_sum = 0 by changing  $percentage  to get irr
//     if ($net_present_value >= 0) {
//         while ((!($npv_sum <= $net_present_value * 0.000001))) {
//             if ($npv_sum > 0) {
//                 $irr = $percentage  + 0.00001;

//                 return calculateIrr($annual_free_cash_array, $discount_rate, $cash_and_loans, $net_present_value, $irr, ++$numberOfIteration);
//             }
//         }
//     } elseif ($net_present_value < 0) {
//         while ((!($npv_sum >= $net_present_value * -0.000001))) {
//             if ($npv_sum < 0) {
//                 $irr = $percentage - 0.00001;

//                 return calculateIrr($annual_free_cash_array, $discount_rate, $cash_and_loans, $net_present_value, $irr, ++$numberOfIteration);
//             }
//         }
//     }

//     return $calculatedPercentage;
// }
function getIndexesLargerThanOrEqualIndex(array $items, string $item): array
{
    $index = array_search($item, $items);
    $newItems = array_filter($items, function ($item) use ($items, $index) {
        return array_search($item, $items) >= $index;
    });

    return count($newItems) ? array_values($newItems) : (array)$item;
}
function isActualDate(string $dateString): bool
{
    
    $year = explode('-', $dateString)[0];
    $month = explode('-', $dateString)[1];

    $now = now()->format('Y-m-d');
    $currentYear = explode('-', $now)[0];
    $currentMonth = explode('-', $now)[1];
    $date = Carbon::make(Carbon::createFromDate($year, $month, 1)->format('Y-m-d'));
    $currentDate = Carbon::make(Carbon::createFromDate($currentYear, $currentMonth, 1)->format('Y-m-d'));

    return $currentDate->greaterThan($date);
}

function getPercentageColor($val): string
{
    if ($val > 0) {
        return 'green ';
    } elseif ($val < 0) {
        return 'red ';
    }

    return '';
}

function getPercentageColorOfSubTypes($val, $type): string
{
    if (($type == 'Sales Revenue' || $type == 'Gross Profit' || $type == 'Earning Before Interest Taxes Depreciation Amortization - EBITDA' || $type == 'Earning Before Interest Taxes - EBIT' || $type == 'Earning Before Taxes - EBT' || $type == 'Net Profit') && $val >= 0
        || (($type == 'Cost Of Goods / Service Sold' || $type == 'Marketing Expenses' || $type == 'Sales Expenses' || $type == 'General Expenses' || $type == 'Finance Income/(Expenses)' || $type == 'Corporate Taxes') && $val <= 0)

    ) {
        return 'green ';
    } else {
        return 'red ';
    }
    // if ($val > 0) {
    // 	return 'green ';
    // } elseif ($val < 0) {
    // 	return 'red ';
    // }
    // return '';
}

function convertStringToClass(string $str): string
{
    $reg = " /^[\d]+|[!\"#$%&'\(\)\*\+,\.\/:;<=>\?\@\~\{\|\}\^ ]/ ";

    return preg_replace($reg, '-', $str);
}
function secondReportIsFirstInArray(string $firstReportType, string $secondReportType)
{
    return $firstReportType != 'forecast' && $secondReportType != 'modified' && $secondReportType != 'actual';
}
function getFirstSegmentInString(string $str, string $separator): string
{
    return 	explode($separator, $str)[0];
}
function getDependsMaps($financialStatementAbleId, $financialStatementAbleClass): array
{
    return $financialStatementAbleClass::find($financialStatementAbleId)->mainItems->pluck('depends_on', 'id')->toArray();
}
function getLastSegmentInRequest()
{
    return Request()->segments()[count(Request()->segments()) - 1];
}
// function getLastNonNumericSegmentInRequest()
// {
// 	return Request()->segments()[count(Request()->segments()) - 1];
// }
function getTotalPerYears(array $array)
{
    $totalPerYears = [];
    foreach ($array as $date => $valArr) {
        $year = explode('-', $date)[0];
        if (isset($totalPerYears[$year])) {
            $totalPerYears[$year] += $valArr['total_with_depreciation'];
        } else {
            $totalPerYears[$year] = $valArr['total_with_depreciation'];
        }
    }

    return $totalPerYears;
}
function getPreviousDate(?array $array, ?string $date, $datesExistsAsKeys = true)
{
	if (empty($array) || $date === null) {
        return null;
    }
    $searched = array_search($date, $datesExistsAsKeys ? array_keys($array) : $array);
    $arrayPlusOne = $datesExistsAsKeys ? @array_keys($array)[$searched - 1] : @($array)[$searched - 1];
    if ($searched !== false &&  isset($arrayPlusOne)) {
        return $datesExistsAsKeys ? array_keys($array)[$searched - 1] : ($array)[$searched - 1];
    }

    return null;
}

function formatDataForChart(array $data): array
{
    $formattedReport = [];
    if (!isset($data['Sales Revenue'])) {
        return [];
    }
    $salesRevenueData = $data['Sales Revenue'];
    $totalPerYears = getTotalPerYears($salesRevenueData['data']);
    foreach ($salesRevenueData['data'] as $date => $reportValueArr) {
        $previousDate = getPreviousDate($salesRevenueData['data'], $date);
        $previousMonthSales = $previousDate ? $salesRevenueData['data'][$previousDate]['total_with_depreciation'] : 0;
        $year = explode('-', $date)[0];
        $currentYearTotal = $totalPerYears[$year] ?? 0;
        $formattedReport[] = [
            'Sales Values' => $monthSales = $reportValueArr['total_with_depreciation'] ?? 0,
            'date' => Carbon::make($date)->format('d-M-Y'),
            'Month Sales %' => $currentYearTotal ? number_format($monthSales / $currentYearTotal * 100, 2) : 0,
            'Growth Rate %' => $previousDate && $previousMonthSales ? number_format(($monthSales - $previousMonthSales)  / $previousMonthSales * 100, 2) : 0
        ];
    }

    return $formattedReport;
}
function getArrayWhereIndexLessThanOrEqual($formattedData, $index)
{
    $data = [];
    foreach ($formattedData as $i => $value) {
        if ($i <= $index) {
            $data[] = $formattedData[$i];
        }
    }

    return $data;
}
function array_sum_key(array $array, $key)
{
    $total = 0;
    foreach ($array as $index => $arr) {
        $total += $arr[$key];
    }

    return $total;
}
function getMonthlyChartCumulative(array $formattedData): array
{
    $result = [];
    foreach ($formattedData as $index => $data) {
        $result[] = [
            'date' => $data['date'],
            'price' => array_sum_key(getArrayWhereIndexLessThanOrEqual($formattedData, $index), 'Sales Values')
        ];
    }

    return $result;
}
function extractMainItemsAndSubItemsFrom(array $array): array
{
    $mainItemsAndSubitems = [];
    foreach ($array as $mainItemName => $values) {
        foreach ($values as $reportType => $reportValues) {
            foreach ($reportValues as $subItemName => $subItemValue) {
                if (!isset($mainItemsAndSubitems[$mainItemName]) || !in_array($subItemName, $mainItemsAndSubitems[$mainItemName])) {
                    $mainItemsAndSubitems[$mainItemName][] = $subItemName;
                }
            }
        }
    }

    return $mainItemsAndSubitems;
}
function getFirstKeyReportType($arrayOfData): array
{
    $key = array_key_first($arrayOfData);

    return [
        'key' => $key,
        'reportType' => explode('#', $key)[0]
    ];
}
function getSecondKeyReportType($arrayOfData, $firstReportKey): array
{
    $key = '#';
    foreach ($arrayOfData as $index => $value) {
        if ($index != $firstReportKey) {
            $key = $index;
        }
    }

    return [
        'key' => $key,
        'reportType' => explode('#', $key)[0]
    ];
}
function strEndsWith($string, $endString)
{
    $len = strlen($endString);
    if ($len == 0) {
        return true;
    }

    return substr($string, -$len) === $endString;
}
function sumAllKeysOfData(array $array, array $keysToSum, string $date)
{
    $total = 0;
    foreach ($array as $key => $values) {
        if (
            in_array($key, $keysToSum)
        ) {
            $total += $values[$date] ?? 0;
        }
    }

    return $total;
}
function sumAllExceptQuantityOfData(array $array, string $date)
{
    $total = 0;
    foreach ($array as $key => $values) {
        //	if (!isQuantitySubItem($key)) {
        $total += $values[$date] ?? 0;
        //	}
    }

    return $total;
}
function getChartsData($chartItems, $dates, $arrayOfData, $mainItemName)
{
    $data = [];
    $firstReportTypeKey = getFirstKeyReportType($arrayOfData)['key'];
    $firstReportType = getFirstKeyReportType($arrayOfData)['reportType'];
    $secondReportTypeKey = getSecondKeyReportType($arrayOfData, $firstReportTypeKey)['key'];
    $secondReportType = getSecondKeyReportType($arrayOfData, $firstReportTypeKey)['reportType'];
    $firstTypeAccumulated = 0;
    $secondTypeAccumulated = 0;
    $subItems = $chartItems[$mainItemName] ?? [];
    foreach ($dates as $dateAsIndex => $dateAsString) {
        //barChart chart
        $data['barChart'][$mainItemName][$dateAsString][$firstReportType] = sumAllKeysOfData($arrayOfData[$firstReportTypeKey], $subItems, $dateAsIndex);
        $data['barChart'][$mainItemName][$dateAsString][$secondReportType] =  sumAllKeysOfData($arrayOfData[$secondReportTypeKey], $subItems, $dateAsIndex);
        $data['barChart'][$mainItemName][$dateAsString]['variance'] = $data['barChart'][$mainItemName][$dateAsString][$secondReportType] - $data['barChart'][$mainItemName][$dateAsString][$firstReportType];
        $data['barChart'][$mainItemName][$dateAsString]['var %'] = $data['barChart'][$mainItemName][$dateAsString][$firstReportType] ? $data['barChart'][$mainItemName][$dateAsString]['variance'] / $data['barChart'][$mainItemName][$dateAsString][$firstReportType] * 100 : 0;
        $data['barChart'][$mainItemName][$dateAsString][$secondReportType] =  sumAllKeysOfData($arrayOfData[$secondReportTypeKey], $subItems, $dateAsIndex);
        // two lines charts
        $firstTypeAccumulated +=  $data['barChart'][$mainItemName][$dateAsString][$firstReportType];
        $secondTypeAccumulated +=  $data['barChart'][$mainItemName][$dateAsString][$secondReportType];
        $data['twoLinesChart'][$mainItemName][$dateAsString][$firstReportType] = $firstTypeAccumulated;
        $data['twoLinesChart'][$mainItemName][$dateAsString][$secondReportType] = $secondTypeAccumulated;
        $data['twoLinesChart'][$mainItemName][$dateAsString]['variance'] = $data['twoLinesChart'][$mainItemName][$dateAsString][$secondReportType] - $data['twoLinesChart'][$mainItemName][$dateAsString][$firstReportType];
        $data['twoLinesChart'][$mainItemName][$dateAsString]['var %'] = $data['twoLinesChart'][$mainItemName][$dateAsString][$firstReportType] ? $data['twoLinesChart'][$mainItemName][$dateAsString]['variance'] / $data['twoLinesChart'][$mainItemName][$dateAsString][$firstReportType]  * 100 : 0;
    }
    // donut chart

    foreach ($subItems as $subItemName) {
        $data['donutChart'][$mainItemName][$firstReportType][$subItemName] = isset($arrayOfData[$firstReportTypeKey][$subItemName]) ? array_sum($arrayOfData[$firstReportTypeKey][$subItemName]) : 0;
        $data['donutChart'][$mainItemName][$secondReportType][$subItemName] = isset($arrayOfData[$secondReportTypeKey][$subItemName]) ? array_sum($arrayOfData[$secondReportTypeKey][$subItemName]) : 0;
    }

    return $data;
}
function formatDataForBarChart(array $subItemValues, $firstReportType, $secondReportType)
{
    $formattedData = [];
    foreach ($subItemValues as $date => $values) {
        $formattedData[] = ['category' => explode('-', $date)[1] . '-' . explode('-', $date)[0], 'first' => $values[$firstReportType], 'second' => $values[$secondReportType], 'third' => $values['variance']];
    }

    return $formattedData;
}
function formatDataFromTwoLinesChart(array $subItemValues)
{
    $formattedData = [];
    foreach ($subItemValues as $date => $values) {
        $formattedData[] = ['date' => $date, 'Variance' => $values['variance'], 'Var %' => $values['var %']];
    }

    return $formattedData;
}
function formatDataFromTwoLinesChart2(array $subItemValues)
{
    $formattedData = [];
    foreach ($subItemValues as $date => $values) {
        $formattedData[] = ['date' => $date, 'Accumulated Variance' => number_format($values['variance'], 2), 'Accumulated Var %' => number_format($values['var %'], 2)];
    }

    return $formattedData;
}
function removeFirstKeyAndMergeOthers(array $array)
{
    $newArray = [];
    foreach ($array as $mainTypeName => $values) {
        if ($newArray) {
            foreach ($newArray as $key => $value) {
                if ($key != 'donutChart') {
                    $newArray[$key][$mainTypeName] =  array_values($values[$key])[0];
                } else {
                    foreach ($values['donutChart'][$mainTypeName] ?? [] as $subItemName => $subItemValue) {
                        $newArray[$key][$mainTypeName][$subItemName] = $subItemValue;
                    }
                }
            }
        } else {
            $newArray = $values;
        }
    }

    return $newArray;
}
function getSubItemsForMainItemName($incomeStatement, int $financialStatementAbleItemId, string $reportType)
{
    $subItems = $incomeStatement->withSubItemsFor($financialStatementAbleItemId, $reportType)->get()->pluck('pivot.sub_item_name')->toArray();
    $subItems = array_filter($subItems, function ($subItem) {
        return !isQuantitySubItem($subItem);
    });

    return array_values($subItems);
}
function addAllSubItemForMainItemsArray(array $mainItems, $incomeStatement, $reportType)
{
    $data = [];
    foreach ($mainItems as $mainItemId => $mainItemName) {
        $data[$mainItemName] = mainItemHasSubItems($incomeStatement, $mainItemId) ? getSubItemsForMainItemName($incomeStatement, $mainItemId, $reportType) : [$mainItemName];
    }

    return $data;
}
function mainItemHasSubItems($incomeStatement, int $mainItemId): bool
{
    return $incomeStatement->withMainItemsFor($mainItemId)->first()->has_sub_items ?? false;
}
function formatForPieChart(array $array): array
{
    $formattedData = [];

    foreach ($array as $subItemName => $subItemValues) {
        foreach ($subItemValues as $date => $value) {
            $formattedData[$subItemName] = $value;
        }
    }

    return $formattedData;
}
function hideExportField($fieldName): bool
{
    $hidden  = ['local_or_export', 'sub_category', 'return_reason', 'quantity_status', 'quantity_bonus'];

    return in_array($fieldName, $hidden);
}

function formatDataForDonutChart(array $array)
{
    $formattedData = [];
    foreach ($array as $subItemName => $value) {
        $formattedData[] = [
            'name' => $subItemName,
            'value' => $value
        ];
    }

    return $formattedData;
}
function isQuantitySubItem($subItemName): bool
{
    // note that (isQuantitySubItem) is also js function (edit it also if you edited this condition)
    return strEndsWith($subItemName, quantityIdentifier) || strEndsWith($subItemName, __(quantityIdentifier));
}
function getTotalForQuantityAndValues(array $items, bool $is_sales_revenue, bool $totalForAllItems, ?string $currentSubItemName = null): array
{
    $currentSubItemName = $currentSubItemName ? $currentSubItemName . quantityIdentifier : '';
    $totals = [
        'quantity' => 0,
        'value' => 0
    ];
    foreach ($items as $subItemName => $value) {
        if ($totalForAllItems || $subItemName == $currentSubItemName) {
            if (isQuantitySubItem($subItemName) && $is_sales_revenue) {
                $totals['quantity'] += $value;
            } else {
                $totals['value'] += $value;
            }
        }
    }

    return $totals;
}
function hasQuantityRow(array $subItemsName, string $mainRowName): bool
{
    $subItems = array_keys($subItemsName);

    return in_array($mainRowName . quantityIdentifier, $subItems) || in_array($mainRowName . __(quantityIdentifier), $subItems);
}
function formatSubItemsNamesForQuantity(string $subItemName): array
{
    $subItems = [];
    $subItemNameTrimmedFromQuantityIdentifier = removeStringFromEnd($subItemName, quantityIdentifier);
    $subItemNameWithQuantityIdentifier = appendStringTo($subItemNameTrimmedFromQuantityIdentifier, quantityIdentifier);
    $subItems['value'] = $subItemNameTrimmedFromQuantityIdentifier;
    $subItems['quantity'] = $subItemNameWithQuantityIdentifier;

    return $subItems;
}
function removeStringFromEnd(string $haystack, string $needle): string
{
    $needle_length = strlen($needle);
    if (substr($haystack, -$needle_length) === $needle) {
        return substr($haystack, 0, -$needle_length);
    }

    return $haystack;
}
function appendStringTo(string $str, string $append): string
{
    return $str . $append;
}






function isActualDateInModifiedOrAdjusted($date, $subItemType, $actualDatesAsIndexAndBooleans)
{
    return ($subItemType == 'adjusted' || $subItemType == 'modified') && $actualDatesAsIndexAndBooleans[$date];
}
function isQuantity(array $options): bool
{
    return isset($options['is_quantity']) && $options['is_quantity'] != 'value';
}
function convertJsonToArray(?string $json):array
{
    return $json ? (array)json_decode($json) : [];
}

function preventUserFromForeCast()
{
    return [

        'tamer@terra-egypt.com',
        'ehab@terra-egypt.com',
        'sales@terra-egypt.com',
        'hesham.tawfik@lesdames.org',
        'yasser.fouad@lesdames.org',
        'mmahrous@gi-cg.com',
        'mkhalefa@gi-cg.com',
        'oelbakry@gi-cg.com',
        'mabdallah@jobmastergroup.com'
    ];
}

function filterPermissionForSystemName($permissions, array $systemsNames):array
{
    $result =[];
    foreach ($permissions as $permissionArr) {
        if (HArr::atLeastOneValueExistInArray($systemsNames, $permissionArr['systems'])) {
            $result[] = $permissionArr;
        }
        
    }
    return $result ;
}
function generateReportName($reportName)
{
    if ($reportName === 'product items') {
        $reportName ='products items';
    }
    if ($reportName =='products / service') {
        $reportName ='products / services';
    }

    return 'view ' . $reportName . ' report';
}
function reportNames()
{
    return  [
        'zone'=>'zone', // here
        'sales channel'=>'sales channel',
        'customers'=>'customers',
        'business sector'=>'business sector',
        'business unit'=>'business unit',
        'branch'=>'branch',
        'category'=>'category', // here
        'principle'=>'principle',
        'products / services'=>'products / services', //here
        'products / service'=>'products / service', //here
        'products items'=>'products items', // here
        'product items'=>'product items', // here
        'average prices'=>'average prices', // here
        'sales persons'=>'sales persons',
        'discount'=>'discount',
        'invoice'=>'invoice',
        'country'=>'country',
        'service provider'=>'service provider', // here

    ];
}
function str_plural($str)
{
    return Str::plural($str);
}
function searchWordInstr(array $words, string $sentence)
{
    $foundWords = [];
    foreach ($words as $word) {
        if (strpos($sentence, $word) !== false || strpos($sentence, ucwords($word)) !== false
        || strpos($sentence, Str::plural($word)) !== false
        || strpos($sentence, Str::plural(ucwords($word))) !== false


        ) {
            $foundWords[]=$word;
        }
    }

    return $foundWords;
}
function getColorForIndexes($firstValue, $secondValue, $elementIndex)
{
    if (($elementIndex == 0 ||$elementIndex==2 ||$elementIndex==6|| $elementIndex==7||$elementIndex==9||$elementIndex==11) &&  ($secondValue >= $firstValue)) {
        return 'green !important';
    } elseif ($elementIndex == 0 ||$elementIndex==2 ||$elementIndex==6|| $elementIndex==7||$elementIndex==9||$elementIndex==11) {
        return 'red !important';
    }

    if (($elementIndex == 1 ||$elementIndex==3 ||$elementIndex==4|| $elementIndex==5||$elementIndex==8||$elementIndex==10) &&  ($secondValue < $firstValue)) {
        return 'green !important';
    } elseif ($elementIndex == 1 ||$elementIndex==3 ||$elementIndex==4|| $elementIndex==5||$elementIndex==8||$elementIndex==10) {
        return 'red !important';
    }
}
function checkIfAllDates(array $dates):array
{
    $validDates = [];
    foreach ($dates as $date) {
        if (DateTime::createFromFormat('Y-m', $date) !== false) {
            $validDates[] =$date;
        }
    }

    return $validDates;
}

function number_unformat($number, $force_number = true, $dec_point = '.', $thousands_sep = ',')
{
    $isNegativeNumber = str_starts_with($number, '-');
    if ($force_number) {
        $number = preg_replace('/^[^\d]+/', '', $number);
    } elseif (preg_match('/^[^\d]+/', $number)) {
        return false;
    }
    $type = (strpos($number, $dec_point) === false) ? 'int' : 'float';
    $number = str_replace([$dec_point, $thousands_sep], ['.', ''], $number);
    settype($number, $type);
    if ($isNegativeNumber) {
        $number  = $number * -1 ;
    }
    return $number;
}
function hasUploadData($company_id)
{
    return Schema::hasTable('sales_gathering') ? SalesGathering::where('company_id', $company_id)->first() != null : false;
}
function getEndYearBasedOnDataUploaded(Company $company, int $minusFromYear = 0)
{
    $cashingService = new CashingService($company);
    $dates = $cashingService->getIntervalYearsFormCompany() ;

    $endYear = $dates['end_year'];
    $endYear = $endYear ?: now()->format('Y');
    $endYear = $endYear - $minusFromYear;
    return [
        'jan'=>$endYear . '-' . '01' . '-' . '01',
        'dec'=>isset($dates['full_end_date']) ? Carbon::make($dates['full_end_date'])->subYears($minusFromYear)->format('Y-m-d') :$endYear . '-' . '12' . '-' . '31',
    ];
}

function getEndYearBasedOnDataUploadedFromExpense(Company $company, int $minusFromYear = 0)
{
    $cashingService = new CashingService($company);
    $dates = $cashingService->getExpenseIntervalYearsFormCompany() ;

    $endYear = $dates['end_year'];
    $endYear = $endYear ?: now()->format('Y');
    $endYear = $endYear - $minusFromYear;
    return [
        'jan'=>$endYear . '-' . '01' . '-' . '01',
        'dec'=>isset($dates['full_end_date']) ? Carbon::make($dates['full_end_date'])->subYears($minusFromYear)->format('Y-m-d') :$endYear . '-' . '12' . '-' . '31',
    ];
}

function isPercentageOrRate(string $name)
{
    return
    str_contains($name, __('Sales Growth Rate'))||
    str_contains($name, __('[ % Of Sales ]'))
    ;
}
function getNameFromNumber(int $num)
{
    $numeric = ($num - 1) % 26;
    $letter = chr(65 + $numeric);
    $num2 = intval(($num - 1) / 26);
    if ($num2 > 0) {
        return getNameFromNumber($num2) . $letter;
    } else {
        return $letter;
    }

}
function validateDate($date, $format = 'Y-m-d')
{
    return $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    // return $d && $d->format($format) === $date;
    // ;
}
function formatDateForView($date)
{
    return Carbon::make($date)->format('M\'Y');
}
function formatDateForViewWithDay($date)
{
    return Carbon::make($date)->format('d M\'Y');
}
function getTypesForValues():array
{
    return [
        'fixed_monthly_repeating_amount'=>[
            'title'=>__('Fixed Monthly Repeating Amount'),
            'value'=>'fixed_monthly_repeating_amount',
        ],
        'varying_amount'=>[
            'title'=>__('Varying Amount'),
            'value'=>'varying_amount',
        ],
        'fixed_percentage_of_sales'=>[
            'title'=>__('Fixed Percentage Of Sales'),
            'value'=>'fixed_percentage_of_sales',
        ],
        'varying_percentage_of_sales'=>[
            'title'=>__('Varying Percentage Of Sales'),
            'value'=>'varying_percentage_of_sales',
        ],
        'fixed_cost_per_unit'=>[
            'title'=>__('Fixed Cost Per Unit'),
            'value'=>'fixed_cost_per_unit',
        ],
        'varying_cost_per_unit'=>[
            'title'=>__('Varying Cost Per Unit'),
            'value'=>'varying_cost_per_unit',
        ],
        'intervally_repeating_amount'=>[
            'title'=>__('Intervally Repeating Amount'),
            'value'=>'intervally_repeating_amount',
        ],
        'one_time_expense'=>[
            'title'=>__('One Time Expense'),
            'value'=>'one_time_expense',
        ],
        'expense_per_employee'=>[
            'title'=>__('Expense Per Employee'),
            'value'=>'expense_per_employee',
        ],




    ];
}
function getManpowerTypesForValuesForNonBanking():array
{
    return [
        'department'=>[
            'title'=>__('Department Manpower'),
            'value'=>'departments',
        ],
      
        'expense_per_employee'=>[
            'title'=>__('Expense Per Employee'),
            'value'=>'expense_per_employee',
        ],




    ];
}

function getTypesForValuesForNonBanking():array
{
    return [
        'fixed_monthly_repeating_amount'=>[
            'title'=>__('Fixed Monthly Amount'),
            'value'=>'fixed_monthly_repeating_amount',
        ],
      
        'percentage_of_sales'=>[
            'title'=>__('Expense As Percentage'),
            'value'=>'percentage_of_sales',
        ],
      
        'cost_per_unit'=>[
            'title'=>__('Cost Per Contract'),
            'value'=>'cost_per_unit',
        ],
      
        'one_time_expense'=>[
            'title'=>__('One Time Expense'),
            'value'=>'one_time_expense',
        ],
        'expense_per_employee'=>[
            'title'=>__('Expense Per Employee'),
            'value'=>'expense_per_employee',
        ],




    ];
}


function getTypesForValuesForPropertyManagement():array
{
    return [
        'fixed_monthly_repeating_amount'=>[
            'title'=>__('Fixed Monthly Amount'),
            'value'=>'fixed_monthly_repeating_amount',
        ],
      
        'percentage_of_sales'=>[
            'title'=>__('Expense As Percentage'),
            'value'=>'percentage_of_sales',
        ],
      
        'cost_per_unit'=>[
            'title'=>__('Cost Per Sqm'),
            'value'=>'cost_per_unit',
        ],
      
        'one_time_expense'=>[
            'title'=>__('One Time Expense'),
            'value'=>'one_time_expense',
        ],
        'expense_per_employee'=>[
            'title'=>__('Expense Per Employee'),
            'value'=>'expense_per_employee',
        ],




    ];
}

function getTypesForValuesForFinancialPlanning():array
{
    return [
        'fixed_monthly_repeating_amount'=>[
            'title'=>__('Fixed Monthly Amount'),
            'value'=>'fixed_monthly_repeating_amount',
        ],
      
        'percentage_of_sales'=>[
            'title'=>__('Expense As Percentage'),
            'value'=>'percentage_of_sales',
        ],
      
        'cost_per_unit'=>[
            'title'=>__('Cost Per Unit'),
            'value'=>'cost_per_unit',
        ],
      
        'one_time_expense'=>[
            'title'=>__('One Time Expense'),
            'value'=>'one_time_expense',
        ],
        // 'expense_per_employee'=>[
        //     'title'=>__('Expense Per Employee'),
        //     'value'=>'expense_per_employee',
        // ],




    ];
}
function twoArrayIsEqualValues(array $firstItems, array $secondItems)
{
    if (count($firstItems) != count($secondItems)) {
        return false ;
    }
    foreach ($firstItems as $date =>$val) {
        $secondVal = $secondItems[$date] ?? 0;
        if ($secondVal == 0) {
            if ($val !=0) {
                return false ;
            }
        } elseif (!(abs(($val-$secondVal)/$secondVal) < 0.00001)) {
            return false ;
        }
    }
    return true ;
}

function array_sum_at_date($items, $date)
{
    $total = 0 ;
    foreach ($items as $keys=> $vals) {
        foreach ($vals as $key => $val) {

            if ($key == $date) {
                $total += $val ;
            }
        }
    }
    return $total ;
}
function get_total_with_preserve_key($items)
{

    $result = [];
    if (!count($items)) {
        return [];
    }
    foreach (array_keys(Arr::first($items)) as $date) {
        foreach ($items as $key => $values) {
            $currentValue = $values[$date] ?? 0 ;
            $result[$key] = isset($result[$key]) ? $result[$key] + $currentValue : $currentValue ;
        }
    }
    return $result;

}

function getRevenueStreamTypes(): array
{
    return [
        [
            'value' => 'service',
            'title' => __('Service')
        ],
        [
            'value' => 'trading',
            'title' => __('Trading')
        ],
        [
            'value' => 'manufacturing',
            'title' => __('Manufacturing')
        ]
    ];
}

function getPaymentIntervals(): array
{
    $elements = [];
    for ($i = 2  ; $i<=12 ; $i++) {
        $elements[]=[
            'value' => $i,
            'title' => __('Every').' ' . $i  . ' ' . __('Months')
        ];
    }
    return $elements ;
}
function replaceSingleQuote($string)
{
    return str_replace("'", "\'", $string) ;
}
function getNextDate(?array $array, ?string $date, $datesExistsAsKeys = true)
{

    $searched = array_search($date, $datesExistsAsKeys ? array_keys($array) : $array);
    $arrayPlusOne = $datesExistsAsKeys ? @array_keys($array)[$searched +1] : @($array)[$searched +1];
    if ($searched !== false &&  isset($arrayPlusOne)) {
        return $datesExistsAsKeys ? array_keys($array)[$searched +1] : ($array)[$searched +1];
    }
    return null;
}
// function replaceDateWithLastDateInMonth(string $date)
// {
//     $yearOfEndDate = explode('-', $date)[0];
//     $monthOfEndDate = explode('-', $date)[1];
//     return  Carbon::create($yearOfEndDate, $monthOfEndDate)->lastOfMonth()->format('Y-m-d');
// }


// function sortArr(&$arr)
// {
//     usort($arr, function ($a, $b) {
//         return strtotime($a) - strtotime($b);
//     });
// }
function sumIntervals(array $dateValues, string $intervalName)
{
    return (new IntervalSummationOperations())->sumForInterval($dateValues, $intervalName);
}
function getAllocationsBases()
{
    return [];
}

function getConditionalToSelect()
{
    return
        [
            [
                'title' => __('Greater Than'),
                'value' => 'greater-than'
            ],
            [
                'title' => __('Greater Than Or Equal'),
                'value' => 'greater-than-or-equal'
            ],
            [
                'title' => __('Less Than'),
                'value' => 'less-than'
            ],
            [
                'title' => __('Less Than Or Equal'),
                'value' => 'less-than-or-equal'
            ],
            [
                'title'=>__('Between & Equal'),
                'value'=>'between-and-equal'
            ],
            [
                'title'=>__('Between'),
                'value'=>'between'
            ],
            [
                'title'=>__('Equal'),
                'value'=>'equal'
            ]

        ];
}

function dueInDays()
{
    return [
        [
            'value'=>0 ,
            'title'=>0
        ],
        [
            'value'=>15 ,
            'title'=>15 . ' ' . __('Days')
        ],
        [
            'value'=>30,
            'title'=>30 . ' ' . __('Days')
        ],
        [
            'value'=>60,
            'title'=>60 . ' ' . __('Days')
        ],
        [
            'value'=>90 ,
            'title'=>90 .  ' ' . __('Days')
        ],
        [
            'value'=> 120 ,
            'title'=>120 . ' ' . __('Days')
        ],
        [
            'value'=>150,
            'title'=>150 . ' ' . __('Days')
        ],
        [
            'value'=> 180 ,
            'title'=>180 . ' ' . __('Days')
        ],
        [
            'value'=> 210 ,
            'title'=>210 . ' ' . __('Days')
        ],
        [
            'value'=>240 ,
            'title'=>240 . ' ' . __('Days')
        ],
        [
            'value'=>270 ,
            'title'=>270 . ' ' . __('Days')
        ],

        [
            'value'=>300 ,
            'title'=>300 . ' ' . __('Days')
        ],
        [
            'value'=>330 ,
            'title'=>330 . ' ' . __('Days')
        ],
        [
            'value'=> 360 ,
            'title'=>360 . ' ' . __('Days')
        ],
    ];
}

function factoringDueInDays():array
{
    return [
        [
            'id'=>30,
            'title'=>30 . ' ' . __('Days Factoring')
        ],
        [
            'id'=>45,
            'title'=>45 . ' ' . __('Days Factoring')
        ],
        [
            'id'=>60,
            'title'=>60 . ' ' . __('Days Factoring')
        ],
        [
            'id'=>75,
            'title'=>75 . ' ' . __('Days Factoring')
        ],
        [
            'id'=>90 ,
            'title'=>90 .  ' ' . __('Days Factoring')
        ],
        [
            'id'=> 120 ,
            'title'=>120 . ' ' . __('Days Factoring')
        ],
        [
            'id'=>150,
            'title'=>150 . ' ' . __('Days Factoring')
        ],
        [
            'id'=> 180 ,
            'title'=>180 . ' ' . __('Days Factoring')
        ],
        [
            'id'=> 210 ,
            'title'=>210 . ' ' . __('Days Factoring')
        ],
        [
            'id'=>240 ,
            'title'=>240 . ' ' . __('Days Factoring')
        ],
        [
            'id'=>270 ,
            'title'=>270 . ' ' . __('Days Factoring')
        ],

        [
            'id'=>300 ,
            'title'=>300 . ' ' . __('Days Factoring')
        ],
        [
            'id'=>330 ,
            'title'=>330 . ' ' . __('Days Factoring')
        ],
        [
            'id'=> 360 ,
            'title'=>360 . ' ' . __('Days Factoring')
        ],
    ];
}


function reverseFactoringSelector():array
{
    return [
       
        [
            'id'=>'monthly-interest-and-principle',
            'title'=>__('Monthly Interest & Principle')
        ],
        [
            'id'=>'monthly-interest-and-quarterly-principle',
            'title'=>__('Monthly Interest & Quarterly Principle')
        ],
        [
            'id'=>'quarterly-interest-and-principle',
            'title'=>__('Quarterly Interest & Principle')
        ],
    ];
}


function formatRatesWithDueDays(array $ratesAndDueDays): array
{
    $result = [];
    foreach ($ratesAndDueDays['due_in_days'] ?? [] as $index => $dueDay) {
        $rate = $ratesAndDueDays['rate'][$index] ?? 0;
        if ($rate) {
            if (isset($result[$dueDay])) {
                $result[$dueDay] += $rate;
            } else {
                $result[$dueDay] = $rate;
            }
        }
    }

    return $result;
}
const PERCENTAGE_DECIMALS = 2 ;
function cacheHas($key)
{
    return Cache::has($key);
}
function generateCacheFailedName($companyId, $userId, $modelName)
{
    return 'failed_company_'.$companyId.'user_id'.$userId . 'failed_job' . $modelName;
}
function CacheGetAndRemove($key)
{
    $message = Cache::get($key) ;
    Cache::forget($key);
    return $message;
}
function hasCachingCompany($companyId, $modelName)
{
    return CachingCompany::where('company_id', $companyId)->where('model', $modelName)->count();
}
function generateCacheKeyForValidationRow($company_id, $modelName)
{
    return 'validation_rows'.$modelName . $company_id;
}
function arrayMergeTwoDimArray(...$args)
{
    $mergedArray = [];
    foreach ($args as $index=>$array) {
        foreach ($array as $key=>$values) {
            $mergedArray[$key] = $values;
        }
    }
    return $mergedArray ;
}
function hasFailedRow($companyId, string $modelName)
{
    $cache=Cache::get(generateCacheKeyForValidationRow($companyId, $modelName));
    return $cache && count($cache);
}
function convertIdsToNames(array $elements)
{
    $newItems = [];
    foreach ($elements as $element) {
        $newItems[] =snakeToCamel($element);
    }
    return $newItems ;
}
function snakeToCamel($input)
{
    return ucfirst(str_replace(' ', ' ', ucwords(str_replace('_', ' ', $input))));
}
function sumDueDayWithPayment($paymentRate, $dueDays)
{
    $items = [];
    foreach ($dueDays as $index=>$dueDay) {
        $currentPaymentRate = $paymentRate[$index]??0 ;
        $items[$dueDay] = isset($items[$dueDay]) ? $items[$dueDay] + $currentPaymentRate : $currentPaymentRate;
    }
    return $items;
}

// 1- create model with  name [xyz] and this name will the type parameter in all sections
// 2- create table with name [xyzs]
// 3- in helpers.php search from getUploadParamsFromType add type params
// 4- in tables_field table in db add type with all columns
// 5- add it in getHeaderMenu

function getUploadParamsFromType(?string $type = null):array
{

    $params  = [
        'SalesGathering'=>[
            'fullModel'=>'\App\Models\SalesGathering',
            'dbName'=>'sales_gathering',
            'orderByDateField'=>'date',
            'typePrefixName'=>__('Sales'),
            'viewPermissionName'=>'view sales gathering data',
            'uploadPermissionName'=>'upload sales gathering data',
            'exportPermissionName'=>'export sales gathering data',
            'deletePermissionName'=>'delete sales gathering data',
            'importHeaderText'=>__('Sales Gathering Import'),
        ],
        'ExportAnalysis'=>[
            'fullModel'=>'\App\Models\ExportAnalysis',
            'dbName'=>'export_analysis',
            'typePrefixName'=>__('Export'),
            'orderByDateField'=>'purchase_order_date',
            'viewPermissionName'=>viewExportAnalysisData,
            'uploadPermissionName'=>uploadExportAnalysisData, // important:add this also into permission function names [getPermissions()]
            'exportPermissionName'=>exportExportAnalysisData,// important:add this also into permission function names[getPermissions()]
            'deletePermissionName'=>deleteExportAnalysisData,// important:add this also into permission function names[getPermissions()]
            'importHeaderText'=>__('Export Analysis Import'),
        ],
        'ExpenseAnalysis'=>[
            'fullModel'=>'\App\Models\ExpenseAnalysis',
            'dbName'=>'expense_analysis',
            'typePrefixName'=>__('Expense'),
            'orderByDateField'=>'date',
            'viewPermissionName'=>viewExpenseAnalysisData,
            'uploadPermissionName'=>uploadExpenseAnalysisData, // important:add this also into permission function names [getPermissions()]
            'exportPermissionName'=>exportExpenseAnalysisData,// important:add this also into permission function names[getPermissions()]
            'deletePermissionName'=>deleteExpenseAnalysisData,// important:add this also into permission function names[getPermissions()]
            'importHeaderText'=>__('Expense Analysis Import'),
        ],
        'LabelingItem'=>[
            'fullModel'=>'\App\Models\LabelingItem',
            'dbName'=>'labeling_items',
            'typePrefixName'=>__('Labeling Item'),
            'orderByDateField'=>'id', // important for this case
            'viewPermissionName'=>viewLabelingItemData,// important:add this also into permission function names [getPermissions()]
            'uploadPermissionName'=>uploadLabelingItemData, // important:add this also into permission function names [getPermissions()]
            'exportPermissionName'=>exportLabelingItemData,// important:add this also into permission function names[getPermissions()]
            'deletePermissionName'=>deleteLabelingItemData,// important:add this also into permission function names[getPermissions()]
            'importHeaderText'=>__('Labeling Item Import'),
        ],
        'CustomerInvoice'=>[
            'fullModel'=>'\App\Models\CustomerInvoice',
            'dbName'=>'customer_invoices',
            'typePrefixName'=>__('Customer Invoice'),
            'orderByDateField'=>'invoice_date',
            'viewPermissionName'=>viewCustomerInvoiceData,
            'uploadPermissionName'=>uploadCustomerInvoiceData, // important:add this also into permission function names [getPermissions()]
            'exportPermissionName'=>exportCustomerInvoiceData,// important:add this also into permission function names[getPermissions()]
            'deletePermissionName'=>deleteCustomerInvoiceData,// important:add this also into permission function names[getPermissions()]
            'importHeaderText'=>__('Customer Invoice Import'),
        ],

        'SupplierInvoice'=>[
            'fullModel'=>'\App\Models\SupplierInvoice',
            'dbName'=>'supplier_invoices',
            'typePrefixName'=>__('Supplier Invoice'),
            'orderByDateField'=>'invoice_date',
            'viewPermissionName'=>viewSupplierInvoiceData,
            'uploadPermissionName'=>uploadSupplierInvoiceData, // important:add this also into permission function names [getPermissions()]
            'exportPermissionName'=>exportSupplierInvoiceData,// important:add this also into permission function names[getPermissions()]
            'deletePermissionName'=>deleteSupplierInvoiceData,// important:add this also into permission function names[getPermissions()]
            'importHeaderText'=>__('Supplier Invoice Import'),
        ],
        'LoanSchedule'=>[
            'fullModel'=>'\App\Models\LoanSchedule',
            'dbName'=>'loan_schedules',
            'typePrefixName'=>__('Loan Schedule'),
            'orderByDateField'=>'date',
            'viewPermissionName'=>viewLoanScheduleData,
            'uploadPermissionName'=>uploadLoanScheduleData, // important:add this also into permission function names [getPermissions()]
            'exportPermissionName'=>exportLoanScheduleData,// important:add this also into permission function names[getPermissions()]
            'deletePermissionName'=>deleteLoanScheduleData,// important:add this also into permission function names[getPermissions()]
            'importHeaderText'=>__('Loan Schedule Import'),
        ]

    ] ;
    if ($type) {
        return $params[$type];
    }
    return $params ;

}


function camelToTitle(string $str)
{
    return  ucwords(implode(' ', preg_split('/(?=[A-Z])/', $str)));
    ;
}
function getUploadDataText($typePrefixName)
{
    return __("Upload New ". $typePrefixName  ." " . __('Data'));
}
function convertArrayToSqlString($items)
{
    if (!is_array($items)) {

        return "'".$items."'";
        ;

    }
    $sqlString = "";

    foreach ($items as $item) {
        $sqlString .= "'".$item."',";
    }
    return trim($sqlString, ',');
}
function convertDateToFormatIfDate($strOrDate)
{
    $view = '';

    try {
        if (!Carbon::make($strOrDate)) {
            return $strOrDate;
        }
        $view = Carbon::make($strOrDate)->format('d-m-Y');
    } catch (\Exception $e) {
        $view = $strOrDate ;
    }
    return $view;
}
function changeDateFormatOfArrTo(array $dateValue, string $format)
{
    $newItems = [];
    foreach ($dateValue as $date=>$value) {
        $newItems[Carbon::make($date)->format($format)] = $value ;
    }
    return $newItems ;
}
function removeMinusFromArr(array $items)
{
    $result = [];
    foreach ($items as $date=>$value) {
        if ($value <0) {
            $value = $value  * -1 ;
        }
        $result[$date] = $value ;
    }
    return $result;
}
function getTotalOf(array $items):array
{
    $total = [];
    foreach ($items as $name=>$dateAndValues) {
        foreach ($dateAndValues as $date=>$value) {
            $total[$date] = isset($total[$date]) ? $total[$date]  + $value  : $value ;
        }
    }
    return $total ;
}
function dateIsBetween(string $date, string $dateFrom, string $dateTo)
{
    return Carbon::make($date)->isBetween($dateFrom, $dateTo);
}
function getSegmentBeforeLast()
{
    return Request()->segments()[count(Request()->segments()) - 2 ];
}
function isValidDateFormat(string $date, string $format)
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
function getInvoiceDayIntervals()
{
    return [
        '1-7',
        '8-15',
        '16-30',
        '31-45',
        '46-60',
        '61-90',
        '91-120',
        '121-150'
    ];
}
// function getDatesFromTwoIndexes(string $dayInterval,string $date , string $direction = 'coming' ):array{


// 	$date= Carbon::make($date);
// 	$functionName = $direction == 'past'  ?  'subDays' : 'addDays';
// 	$firstDay = explode('-',$dayInterval)[0];
// 	$secondDay = explode('-',$dayInterval)[1];
// 	$additionalDay = getAdditionalDates()[$dayInterval]?? 0;
// 	return [
// 		$date->$functionName($firstDay+$additionalDay)->format('d-m-Y'),
// 		$date->$functionName($secondDay+$additionalDay)->format('d-m-Y'),
// 	];
// }

// function getWeeksForCurrentDate()
// {
//     $year = date_create('today')->format('Y');
//     //remove comment next line for test's
//     //$year = 2001;

//     $dtStart = date_create('2 jan '.$year)->modify('last Monday');
//     $dtEnd = date_create('last monday of Dec '.$year);

//     for ($weeks = [];$dtStart <= $dtEnd;$dtStart->modify('+1 week')) {
//         $key = $dtStart->format('W-Y');
//         $from = $dtStart->format('d/m/Y');
//         $to = (clone $dtStart)->modify('+6 Days')->format('d/m/Y');
//         $weeks[$key] = $from.' - '.$to;
//     }
//     return $weeks ;
// }




function getNumericExportFields():array
{
    return ['Quantity' , __('Quantity') , 'Quantity Discount' , __('Quantity Discount') , 'Cash Discount' , __('Cash Discount') , 'Special Discount' , __('Special Discount') , __('Other Discounts') , 'Net Sales Value' , __('Net Sales Value'),'Price Per Unit' , __('Price Per Unit') , __('Sales Value') , __('Sales Value'),'Collected Amount',__('Collection Amount'),'Collected Amount',__('Collected Amount'),'Expected Collection Days',__('Expected Collection Days'),'Contracted Collection Days',__('Contracted Collection Days'),'Net Invoice Amount',__('Net Invoice Amount'),'Withhold Amount',__('Withhold Amount'),'Net Balance',__('Net Balance') , 'Vat Amount',__('Vat Amount'),'Withhold Amount',__('Withhold Amount'),'VAT Amount'];
}
function getNumericWithNegativeAllowedExportFields():array
{
    return [
        'Invoice Amount',__('Invoice Amount'),
        'Invoice Amount'=>__('Invoice Amount')
];
}


function getBanksCurrencies():array
{
    return getCurrencies();

}
function getDiffBetweenTwoDatesInDays(?Carbon $firstDate, ?Carbon $secondDate)
{
    if ($firstDate && $secondDate) {
        return $firstDate->diffInDays($secondDate);
    }
    return 0 ;
}
function getEndYearMonthFrom(int $month, int $year)
{
    $monthAndYear = [];
    foreach (range($month, 12) as $month => $year) {
        $monthAndYear[sprintf("%02d", $month)] = $year ;
    }
    return $monthAndYear ;


}
function getCurrenciesForSuppliersAndCustomers(int $companyId):array
{
    $currencyFromBranch = Branch::where('company_id', $companyId)->pluck('currency', 'currency')->toArray();
    $currencyFromAccounts = FinancialInstitutionAccount::where('company_id', $companyId)->pluck('currency', 'currency')->toArray() ;
    return array_merge($currencyFromBranch, $currencyFromAccounts);
   
}
function getCurrencies()
{
    return [
        'EGP' => __('EGP'),
        'USD' => __('USD'),
        'EURO' => __('EURO'),
        'SAR' => __('SAR'),
        'AED' => __('AED'),
        'GBP' => __('GBP'),
        'OMR'=> __('OMR')
    ];
}
function formatDateForDatePicker(?string $date)
{
    if (!$date) {
        return null ;
    }
    return Carbon::make($date)->format('m/d/Y');
}
function stdToArray($items)
{
    return json_decode(json_encode($items)) ;

}
function getColorFromIndex(int $index)
{
    if ($index % 2 == 0) {
        return 'brand';
    }
    return 'warning';
}
// success
// danger
// warning
// brand
function generateMenuItem(string $title, bool $show, string $link, array $submenu = [])
{
    return [
        'title'=>$title,
        'show'=>$show ,
        'link'=>$link,
        'submenu'=>$submenu
    ];
}
function getIncomeStatementSubmenu($user, $company)
{
    $companyId = $company->id ;
    return [
        'forecast-dashboard'=>generateMenuItem(__('Forecast Dashboard'), $user->can('view forecast income statement dashboard'), route('dashboard.breakdown.incomeStatement', ['company'=>$companyId,'reportType'=>'forecast',]), []),
        'actual-dashboard'=>generateMenuItem('view Actual dashboard', $user->can('view actual income statement dashboard'), route('dashboard.breakdown.incomeStatement', ['company'=>$companyId,'reportType'=>'actual']), []),
        'adjusted-dashboard'=>generateMenuItem('view Adjusted dashboard', $user->can('view adjusted income statement dashboard'), route('dashboard.breakdown.incomeStatement', ['company'=>$companyId,'reportType'=>'adjusted']), []),
        'modified-dashboard'=>generateMenuItem('view Modified dashboard', $user->can('view modified income statement dashboard'), route('dashboard.breakdown.incomeStatement', ['company'=>$companyId,'reportType'=>'modified']), []),
        'comparing-dashboard'=>generateMenuItem('Comparing Dashboard', $user->can('view income statement comparing dashboard'), route('dashboard.intervalComparing.incomeStatement', ['company'=>$companyId,'subItemType'=>'comparing']), []),
    ];
}
function getSalesAnalysisReportSubmenu($user, int $companyId):array
{
    $canViewSalesBreakdownAnalysis  = $user->can('view sales breakdown analysis report') ;
    $canViewSalesTrendAnalysis = $user->can('view sales trend analysis') ;
    $canViewSalesReport = $user->can('view sales report') ;
    $canViewAny =  $canViewSalesBreakdownAnalysis|| $canViewSalesTrendAnalysis || $canViewSalesReport;
    if (!$canViewAny) {
        return [];
    }
    return [
        'sales-breakdown-analysis-report'=>[
        'title'=>__('Sales Breakdown Analysis Report'),
        'link'=>route('sales.breakdown.analysis', ['company'=>$companyId]),
        'show'=>$canViewSalesBreakdownAnalysis
    ],
    'sales-trend-analysis'=>[
        'title'=>__('Sales Trend Analysis'),
        'link'=>route('sales.trend.analysis', ['company'=>$companyId]),
        'show'=>$canViewSalesTrendAnalysis
    ],
    'sales-report'=>[
        'title'=>__('Sales Report'),
        'link'=>route('salesReport.view', ['company'=>$companyId]),
        'show'=>$canViewSalesReport
    ]
];

}

function getSalesForecastValueBaseSubmenu(User $user, int $companyId)
{
    $canViewSalesForecastFactSheet = $user->can('view sales forecast value') ;
    $salesForecast = SalesForecast::where('company_id', $companyId)->first() ;
    $modified_seasonality = ModifiedSeasonality::where('company_id', $companyId)->first() && $salesForecast;
    $canViewProductSalesTargetReport = $modified_seasonality ;
    $canViewFirstAllocation = $modified_seasonality && ExistingProductAllocationBase::where('company_id', $companyId)->first() !== null  ;
    $canViewSecondAllocation = $modified_seasonality && SecondExistingProductAllocationBase::where('company_id', $companyId)->first() !== null ;
    $canViewCollectionReport = $modified_seasonality && CollectionSetting::where('company_id', $companyId)->first() !== null ;
    $viewSummaryReport = $modified_seasonality ;
    $canViewSalesForecastValueBase = $canViewSalesForecastFactSheet || $canViewProductSalesTargetReport || $canViewFirstAllocation || $canViewSecondAllocation || $canViewCollectionReport || $viewSummaryReport;
    if (!$canViewSalesForecastValueBase) {
        return [];
    }
    return [
        'sales-forecast-fact-sheet'=>[
            'title'=>__('Sales forecast Fact Sheet'),
            'show'=>$canViewSalesForecastFactSheet,
            'link'=>route('sales.forecast', ['company'=>$companyId]),
        ],
        'product-sales-target-report'=>[
            'title'=>__('Product Sales Target Report'),
            'show'=>$canViewProductSalesTargetReport,
            'link'=>route('products.allocations', ['company'=>$companyId]),
        ],
        'first-allocation'=>[
            'title'=>__('First Allocation'),
            'show'=> $canViewFirstAllocation ,
            'link'=>route('new.product.seasonality', ['company'=>$companyId]),
        ],
        'second-allocation'=>[
            'title'=>__('Second Allocation'),
            'show'=> $canViewSecondAllocation,
            'link'=>route('second.new.product.seasonality', ['company'=>$companyId]),
        ],
        'collection-report'=>[
            'title'=>__('Collection Report'),
            'show'=> $canViewCollectionReport,
            'link'=>route('collection.report', ['company'=>$companyId]),
        ],
        'summary-report'=>[
            'title'=>__('Summary Report'),
            'show'=> $viewSummaryReport  ,
            'link'=>route('forecast.report', ['company'=>$companyId]),
        ],

    ];
}
function getSalesForecastQuantityBaseSubmenu(User $user, int $companyId):array
{
    $canViewFactSheet = $user->can('view sales forecast quantity') ;
    $sales_forecast = QuantitySalesForecast::where('company_id', $companyId)->first();
    $canViewProductSalesTargetReport = $modified_seasonality = QuantityModifiedSeasonality::where('company_id', $companyId)->first() && $sales_forecast ;
    $canViewFirstAllocation = $modified_seasonality && QuantityExistingProductAllocationBase::where('company_id', $companyId)->first() !== null && $sales_forecast;
    $canViewSecondAllocation = $modified_seasonality && QuantitySecondExistingProductAllocationBase::where('company_id', $companyId)->first() !== null && $sales_forecast;
    $canViewCollectionReport = $modified_seasonality && CollectionSetting::where('company_id', $companyId)->first() !== null && $sales_forecast;
    $canViewSummaryReport   = $modified_seasonality && $sales_forecast;
    $canViewSalesForecastQuantityBase =  $canViewFactSheet || $canViewProductSalesTargetReport || $canViewFirstAllocation || $canViewSecondAllocation || $canViewCollectionReport || $canViewSummaryReport;
    if (!$canViewSalesForecastQuantityBase) {
        return [];
    }
    return [
        'sales-forecast-fact-sheet'=>[
            'title'=>__('Sales Forecast Fact Sheet'),
            'link'=>route('sales.forecast.quantity', ['company'=>$companyId]),
            'show'=> $canViewFactSheet,
        ],
        'product-sales-target-report'=>[
            'title'=>__('Product Sales Target Report'),
            'show'=>$canViewProductSalesTargetReport,
            'link'=>route('products.allocations.quantity', ['company'=>$companyId]),
        ],
        'first-allocation'=>[
            'title'=>__('First Allocation'),
            'show'=> $canViewFirstAllocation,
            'link'=>route('new.product.seasonality.quantity', ['company'=>$companyId]),
        ],
        'second-allocation'=>[
            'title'=>__('Second Allocation'),
            'show'=> $canViewSecondAllocation,
            'link'=>route('second.new.product.seasonality.quantity', ['company'=>$companyId]),
        ],
        'collection-report'=>[
            'title'=>__('Collection Report'),
            'show'=> $canViewCollectionReport,
            'link'=>route('collection.quantity.report', ['company'=>$companyId]),
        ],
        'summary-report'=>[
            'title'=>__('Summary Report'),
            'show'=> $canViewSummaryReport ,
            'link'=>route('forecast.quantity.report', ['company'=>$companyId]),
        ]
    ];
}
function getStudyIdFromUrl()
{
    return Request()->segment(5);
}
function getNonBankingNavigation(Company $company, User $user):array
{
    $studyId = getStudyIdFromUrl();
    $study = Study::find($studyId);
    
    $urls = [
        'home'=>generateMenuItem(__('Home'), $user->can('view home'), route('home'), []),
    
        'studies'=>[
            'title'=>__('Studies <br> Table'),
            'show'=>true ,
            'link'=>route('non.banking.view.study', ['company'=>$company->id])
        ]
            
    ];
    if ($study) {
        $isExistingCompany =$study->isExistingCompany();
        $microfinanceFirstPageRoute = $study->getMicrofinanceFirstPage();
        $urls['study-info']= [
            'title'=>__('Study <br> Information'),
            'show'=>true ,
            'link'=>route('non.banking.edit.study', ['company'=>$company->id , 'study'=>$studyId])
        ];
        $urls['opening-balances']= [
            'title'=>__('Opening <br> Balances'),
            'show'=>$isExistingCompany ,
            'link'=>'#'
        ];
        $urls['general-assumption']= [
            'title'=>__('General <br> Assumptions'),
            'show'=>true ,
            'link'=>route('non.banking.create.general.assumption', ['company'=>$company->id , 'study'=>$studyId])
        ];
     
        $urls['sales-projects'] = [
            'title'=>__('Sales <br> Projections'),
            'show'=>true ,
            'link'=>'#',
            'submenu'=>[
                [
                    'title'=>__('Leasing Projection'),
                    'show'=>$study->hasLeasing(),
                    'link'=>route('create.leasing.revenue.stream.breakdown', ['company'=>$company->id,'study'=>$studyId]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],
                [
                    'title'=>__('Direct Factoring Projection'),
                    'show'=>$study->hasDirectFactoring(),
                    'link'=>route('create.direct.factoring.revenue.stream.breakdown', ['company'=>$company->id,'study'=>$studyId]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],
                [
                    'title'=>__('Reverse Factoring Projection'),
                    'show'=>$study->hasReverseFactoring(),
                    'link'=>route('create.reverse.factoring.revenue.stream.breakdown', ['company'=>$company->id,'study'=>$studyId]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],
                [
                    'title'=>__('Ijara Mortgage Projection'),
                    'show'=>$study->hasIjaraMortgage(),
                    'link'=>route('create.ijara.mortgage.revenue.stream.breakdown', ['company'=>$company->id,'study'=>$studyId]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],
                [
                    'title'=>__('Portfolio Mortgage Projection'),
                    'show'=>$study->hasPortfolioMortgage(),
                    'link'=>route('create.portfolio.mortgage.revenue.stream.breakdown', ['company'=>$company->id,'study'=>$studyId]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],
                [
                    'title'=>__('Microfinance Projection'),
                    'show'=>$study->hasMicroFinance(),
                    'link'=>$microfinanceFirstPageRoute['route'],
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],
                [
                    'title'=>__('Securitization Projection'),
                    'show'=>$study->hasSecuritization(),
                    'link'=>route('create.securitization', ['company'=>$company->id,'study'=>$studyId]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],
                [
                    'title'=>__('Consumer Finance Projection'),
                    'show'=>$study->hasConsumerFinance(),
                    'link'=>route('create.consumer.finance', ['company'=>$company->id,'study'=>$studyId]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],
            ]
        ];

        $urls['manpower-projection'] = [
            'title'=>__('Manpower <br> Projection'),
            'show'=>true ,
            'link'=>route('view.manpower.for.non.banking', ['company'=>$company->id , 'study'=>$studyId]),
        ];
        
        $urls['expense-projection'] = [
            'title'=>__('Expenses <br> Projection'),
            'show'=>true ,
            'link'=>route('non.banking.create.expenses', ['company'=>$company->id , 'study'=>$studyId]),
        ];

        $urls['fixed-assets'] = [
            'title'=>__('Fixed <br> Assets'),
            'show'=>true ,
            'link'=>route('non.banking.create.ffe.fixed.assets', ['company'=>$company->id , 'study'=>$studyId]),
        ];
        $urls['financial-results'] = [
            'title'=>__('Financial <br> Results'),
            'show'=>true ,
            'link'=>route('view.non.banking.forecast.income.statement', ['company'=>$company->id , 'study'=>$study->id]),
        ];
        $urls['analytical-reports'] = [
            'title'=>__('Reports'),
            'show'=>true ,
            'link'=>'#',
            'submenu'=>[
            
        [
                    'title'=>__('Expense Report'),
                    'show'=>true ,
                    'link'=>route('non.banking.view.expense.statement.reports', ['company'=>$company->id,'study'=>$study->id]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ]
            ]
        ];
        
        $urls['calculators'] = [
            'title'=>__('Calculators'),
            'show'=>true ,
            'link'=>'#',
            'submenu'=>[
            
        [
                    'title'=>__('Fixed Loan Payments At End'),
                    'show'=>true ,
                    'link'=>route('non.banking.fixed.loan.fixed.at.end', ['company'=>$company->id,'study'=>$study->id]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ]    , [
                    'title'=>__('Fixed Loan Payments At Beginning'),
                    'show'=>true ,
                    'link'=>route('non.banking.fixed.loan.fixed.at.beginning', ['company'=>$company->id,'study'=>$study->id]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
				],[
					// Route::get('variable-payments', 'Loans2Controller@create')->name('variable.payments');
                    'title'=>__('Variable Loans'),
                    'show'=>true ,
                    'link'=>route('non.banking.variable.payments', ['company'=>$company->id,'study'=>$study->id]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
				],
				[
                    'title'=>__('Calculate Loan Amount'),
                    'show'=>true ,
                    'link'=>route('non.banking.calc.loan.amount', ['company'=>$company->id,'study'=>$study->id]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],
				[
                    'title'=>__('Calculate Interest Rate'),
                    'show'=>true ,
                    'link'=>route('non.banking.calc.interest.percentage', ['company'=>$company->id,'study'=>$study->id]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ]
            ]
        ];
        

       
        $urls['opening-balances'] = [
            'title'=>__('Opening <br> Balances'),
            'show'=>$isExistingCompany ,
            'link'=>route('view.opening.balances.for.non.banking', ['company'=>$company->id , 'study'=>$studyId]),
            
        ];
       
    }
    return $urls;
    

}
function getPropertyManagementNavigation(Company $company, User $user):array
{
    $studyId = getStudyIdFromUrl();
    $study = \App\Models\PropertyManagement\Study::find($studyId);
    
    $urls = [
        'home'=>generateMenuItem(__('Home'), $user->can('view home'), route('home'), []),
    
        'studies'=>[
            'title'=>__('Settings <br> & Studies'),
            'show'=>true ,
            'link'=>route('property.management.view.study', ['company'=>$company->id])
		],
		'dashboard'=>[
            'title'=>__('Dashboard'),
            'show'=>true ,
			'link'=>route('property.management.view.property.dashboard', ['company'=>$company->id]),
            'submenu'=>[
				// [
				// 	'title'=>__('Property Dashboard'),
				// 	'show'=>true,
				// 	'link'=>route('property.management.view.property.dashboard', ['company'=>$company->id]),
				// 	'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
				// ],
				// [
				// 	'title'=>__('Cash Flow Forecast'),
				// 	'show'=>true,
				// 	'link'=>route('property.management.view.property.cashflow.forecast.dashboard', ['company'=>$company->id]),
				// 	'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
				// ]	
			]
        ]
            
    ];
    if ($study) {
        $isExistingCompany =$study->isExistingCompany();
        // $microfinanceFirstPageRoute = $study->getMicrofinanceFirstPage();
        $urls['study-info']= [
            'title'=>__('Study <br> Information'),
            'show'=>true ,
            'link'=>route('property.management.edit.study', ['company'=>$company->id , 'study'=>$studyId])
        ];
        $urls['opening-balances']= [
            'title'=>__('Opening <br> Balances'),
            'show'=>$isExistingCompany ,
            'link'=>'#'
        ];
        $urls['general-assumption']= [
            'title'=>__('General <br> Assumptions'),
            'show'=>true ,
            'link'=>route('property.management.create.general.assumption', ['company'=>$company->id , 'study'=>$studyId])
        ];
     
        $urls['sales-projects'] = [
            'title'=>__('Sales <br> Projections'),
            'show'=>true ,
            'link'=>'#',
            'submenu'=>[
                [
                    'title'=>__('Full Rent Coverage'),
                    'show'=>true,
                    'link'=>route('property.management.create.occupied.properties.with.full.rent.coverage.duration', ['company'=>$company->id,'study'=>$studyId]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],[
                    'title'=>__('Partial Rent Coverage & Vacant Properties'),
                    'show'=>true,
                    'link'=>route('property.management.create.occupied.properties.with.partial.rent.coverage.duration', ['company'=>$company->id,'study'=>$studyId]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],[
                    'title'=>__('Properties To Be Delivered'),
                    'show'=>true,
                    'link'=>route('property.management.create.properties.to.be.delivered', ['company'=>$company->id,'study'=>$studyId]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],[
                    'title'=>__('Forecasted Properties Investments'),
                    'show'=>true,
                    'link'=>route('property.management.create.forecasted.properties', ['company'=>$company->id,'study'=>$studyId]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],
          
            ]
        ];

        $urls['manpower-projection'] = [
            'title'=>__('Manpower <br> Projection'),
            'show'=>true ,
            'link'=>route('view.manpower.for.property.management', ['company'=>$company->id , 'study'=>$studyId]),
        ];
        
        $urls['expense-projection'] = [
            'title'=>__('Expenses <br> Projection'),
            'show'=>true ,
            'link'=>route('property.management.create.expenses', ['company'=>$company->id , 'study'=>$studyId]),
        ];

        $urls['fixed-assets'] = [
            'title'=>__('Fixed <br> Assets'),
            'show'=>true ,
            'link'=>route('property.management.create.ffe.fixed.assets', ['company'=>$company->id , 'study'=>$studyId]),
        ];
        $urls['financial-results'] = [
            'title'=>__('Financial <br> Results'),
            'show'=>true ,
            'link'=>route('view.property.management.forecast.income.statement', ['company'=>$company->id , 'study'=>$study->id]),
        ];
        $urls['analytical-reports'] = [
            'title'=>__('Reports'),
            'show'=>true ,
            'link'=>'#',
            'submenu'=>[
            
        [
                    'title'=>__('Expense Report'),
                    'show'=>true ,
                    'link'=>route('property.management.view.expense.statement.reports', ['company'=>$company->id,'study'=>$study->id]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ]
            ]
        ];
        
        $urls['calculators'] = [
            'title'=>__('Calculators'),
            'show'=>true ,
            'link'=>'#',
            'submenu'=>[
            
        [
                    'title'=>__('Fixed Loan Payments At End'),
                    'show'=>true ,
                    'link'=>route('property.management.fixed.loan.fixed.at.end', ['company'=>$company->id,'study'=>$study->id]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ]    , [
                    'title'=>__('Fixed Loan Payments At Beginning'),
                    'show'=>true ,
                    'link'=>route('property.management.fixed.loan.fixed.at.beginning', ['company'=>$company->id,'study'=>$study->id]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
				],[
					// Route::get('variable-payments', 'Loans2Controller@create')->name('variable.payments');
                    'title'=>__('Variable Loans'),
                    'show'=>true ,
                    'link'=>route('property.management.variable.payments', ['company'=>$company->id,'study'=>$study->id]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
				],
				[
                    'title'=>__('Calculate Loan Amount'),
                    'show'=>true ,
                    'link'=>route('property.management.calc.loan.amount', ['company'=>$company->id,'study'=>$study->id]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ],
				[
                    'title'=>__('Calculate Interest Rate'),
                    'show'=>true ,
                    'link'=>route('property.management.calc.interest.percentage', ['company'=>$company->id,'study'=>$study->id]),
                    'icon'=>'kt-menu__link-icon fa fa-crosshairs font-size-15px'
                ]
            ]
        ];
        

       
        $urls['opening-balances'] = [
            'title'=>__('Opening <br> Balances'),
            'show'=>true ,
            // 'show'=>$isExistingCompany ,
            'link'=>route('view.opening.balances.for.property.management', ['company'=>$company->id , 'study'=>$studyId]),
            
        ];
       
    }
    return $urls;
    

}


function getHeaderMenu($currentCompany = null)
{
	

	
	/**
	 * @var Company|null $company
	 */
	$company = app(Company::class);

    $company = $company ?: $currentCompany;
    $user = auth()->user();
    if (!$company) {
        return [
            'home'=>generateMenuItem(__('Home'), $user->can('view home'), route('home'), [])
        ];
    }

    $companyId = $company->id ;
	
    $isNonBankingService = hasMiddleware('isNonBankingService') ;
    $isPropertyManagement = hasMiddleware('isPropertyManagement') ;
    if ($isNonBankingService) {
        return getNonBankingNavigation($company, $user);
    }
	if ($isPropertyManagement) {
		return getPropertyManagementNavigation($company, $user);
	}
	
    $exportablesForSalesGathering = (new ExportTable)->customizedTableField($company, 'SalesGathering', 'selected_fields');
    $hasSelectSalesPersonInTemplate = isset($exportablesForSalesGathering['sales_person']);
    $hasSelectCustomerNameInTemplate = isset($exportablesForSalesGathering['customer_name']);
    $hasSalesGatheringDataUploadData = hasUploadData($company->id) ;
    $canViewSafeStatement = $user->can('view safe statement report');
    $canViewCashExpenseStatement = $user->can('view cash expense report');
    $canViewPartnersStatement = $user->can('view partners statement report');
    $canViewBankStatement = $user->can('view bank statement report') ;
    $canViewLgByBeneficiaryNameReport = $user->can('view lg by beneficiary name report') ;
    $canViewLgByBankNameReport = $user->can('view lg by bank name report') ;
    $canViewLgLcStatement = $user->can('view lc & lg statement report') ;
    $canViewCashFlow = $user->can('view cash flow report');
    $canViewContractCashFlow = $user->can('view contract cash flow report');
    $canViewWithdrawalsSettlementReport = $user->can('view withdrawals settlement report');
    $canViewNotificationSetting = $user->can('view notification settings');
    $canViewCashExpenseCategories = $user->can('view cash expense categories');
    $canViewCustomersSettings = $user->can('view customers');
    $canViewSubsidiaryCompaniesSettings = $user->can('view subsidiary companies');
    $canViewOtherPartnersSettings = $user->can('view other partners');
    $canViewShareholdersSettings = $user->can('view shareholders');
    $canViewDeductionsSettings = $user->can('view deductions');
    $canViewEmployeesSettings = $user->can('view employees');
    $canViewSuppliersSettings = $user->can('view suppliers');
    $canViewBusinessSectorSettings = $user->can('view business sectors');
    $canViewBusinessUnitSettings = $user->can('view business units');
    $canViewSalesChannelsSettings = $user->can('view sales channels');
    $canViewSalesPersonsSettings = $user->can('view sales persons');
    $canViewBranchesSettings = $user->can('view branches');
    $canViewGeneralSetting = $canViewCustomersSettings || $canViewSubsidiaryCompaniesSettings || $canViewOtherPartnersSettings || $canViewShareholdersSettings || $canViewDeductionsSettings || $canViewEmployeesSettings || $canViewSuppliersSettings || $canViewBusinessSectorSettings || $canViewBusinessUnitSettings || $canViewSalesChannelsSettings || $canViewSalesPersonsSettings ||$canViewBranchesSettings || $canViewCashExpenseCategories;
    
    $notificationsSubItems[] = [
        'title'=>__('General Settings'),
        'link'=>'#',
        'show'=>$canViewGeneralSetting ,
        'submenu'=> [
            [
                'title'=>__('Cash Expense'),
            'link'=>route('cash.expense.category.index', ['company'=>$companyId]),
            'show'=>$canViewCashExpenseCategories,
            ],
            [
                'title'=>__('Partners'),
                'link'=>route('partners.index', ['company'=>$companyId]),
                'show'=>true ,
                'submenu'=>[
                    [
                        'title'=>__('All Partners'),
                        'link'=>route('partners.index', ['company'=>$companyId]),
                        'show'=>true ,
                    ],
                    [
                'title'=>__('Customers'),
                'link'=>route('customers.index', ['company'=>$companyId]),
                'show'=>$canViewCustomersSettings
            ],
                    [
                'title'=>__('Suppliers'),
                'link'=>route('suppliers.index', ['company'=>$companyId]),
                'show'=>$canViewSuppliersSettings
            ],
            [
                'title'=>__('Employees'),
                'link'=>route('employees.index', ['company'=>$companyId]),
                'show'=>$canViewEmployeesSettings
            ],
            [
                'title'=>__('Shareholders'),
                'link'=>route('shareholders.index', ['company'=>$companyId]),
                'show'=>$canViewShareholdersSettings
            ],
            [
                'title'=>__('Other Partners'),
                'link'=>route('other.partners.index', ['company'=>$companyId]),
                'show'=>$canViewOtherPartnersSettings
            ],
            
            
                ]
                // 'show'=>$canViewCustomersSettings
            ],
            // [
            // 	'title'=>__('Suppliers'),
            // 	'link'=>route('suppliers.index',['company'=>$companyId]),
            // 	'show'=>$canViewSuppliersSettings
            // ],
            
            [
                'title'=>__('Subsidiary Companies'),
                'link'=>route('subsidiary.companies.index', ['company'=>$companyId]),
                'show'=>$canViewSubsidiaryCompaniesSettings
            ],
            
            
            [
                'title'=>__('Deductions'),
                'link'=>route('deductions.index', ['company'=>$companyId]),
                'show'=>$canViewDeductionsSettings
            ],
            
            [
                'title'=>__('Other Settings'),
                'link'=>'#',
                'show'=>true ,
                'submenu'=>[
                    [
                'title'=>__('Business Sectors'),
                'link'=>route('business.sectors.index', ['company'=>$companyId]),
                'show'=>$canViewBusinessSectorSettings
            ],
            [
                'title'=>__('Business Units'),
                'link'=>route('business.units.index', ['company'=>$companyId]),
                'show'=>$canViewBusinessUnitSettings
            ]
            ,[
                'title'=>__('Sales Channels'),
                'link'=>route('sales.channels.index', ['company'=>$companyId]),
                'show'=>$canViewSalesChannelsSettings
            ],
            [
                'title'=>__('Sales Persons'),
                'link'=>route('sales.persons.index', ['company'=>$companyId]),
                'show'=>$canViewSalesPersonsSettings
            ],
                ]
            ],
            
            
            
        ]
    ];
    $notificationsSubItems2 = \App\Notification::formatForMenuItem($company);
    $notificationsSubItems = array_merge($notificationsSubItems, $notificationsSubItems2);
    
    $notificationsSubItems[]	= [
        'title'=>__('Notification Settings'),
    'link'=>route('notifications-settings.index', ['company'=>$companyId]),
    'show'=>$canViewNotificationSetting,
    ];

    $canViewNotificationsSettingAndGeneralSetting = $canViewNotificationSetting || $canViewGeneralSetting;
    
    
    
    
    $notificationsSubItems[]	= [
        'title'=>__('Permissions'),
        'link'=>route('roles.permissions.edit', ['company'=>$companyId]),
        'show'=>$user->can('update permissions') && ! $user->isSuperAdmin(),
    ];
    
    $notificationsSubItems[]	= [
        'title'=>__('Users'),
        'link'=>route('user.index', ['company'=>$companyId]),
        'show'=>$user->can('view users') && ! $user->isSuperAdmin(),
    ];
    
    
    
    $canViewCashStatusDashboard = $user->can('view cash status dashboard');
    $canViewCashForecastDashboard = $user->can('view cash Forecast dashboard');
    $canViewLgAndLcDashboard = $user->can('view lg & lc dashboard');
    $canViewCashDashboard = $canViewCashStatusDashboard || $canViewCashForecastDashboard ||$canViewLgAndLcDashboard;
    
    
    $canUpdateCashAndChequesOpeningBalances  =$user->can('update cash & cheques opening balances');
    // $canUpdateLgOpeningBalances  =$user->can('update lg opening balances');
    // $canUpdateLcOpeningBalances  =$user->can('update lc opening balances');
    $canViewOpeningBalances =$canUpdateCashAndChequesOpeningBalances
    // || $canUpdateLgOpeningBalances || $canUpdateLcOpeningBalances
    ;
    $resortedNotificationsSubItems = [];
    
    $cashManagementSubItems = [

        'home'=>generateMenuItem(__('Home'), $user->can('view home') && hasMiddleware('isCashManagement'), route('home'), []),
        'notifications'=>[
            'title'=>__('Notifications & Settings'),
            'link'=>'#',
            'show'=>$canViewNotificationsSettingAndGeneralSetting,
            'submenu'=>$notificationsSubItems,
            'is-notification'=>true
        ],
        'cash-dashboard'=>[
            'title'=>__('Dashboard'),
            'show'=>$canViewCashDashboard ,
            'link'=>'#',
            'submenu'=>[
                [
                    'title'=>__('Cash Status'),
                    'link'=>route('view.customer.invoice.dashboard.cash', ['company'=>$companyId]),
                    'show'=>$canViewCashStatusDashboard,
                    'submenu'=>[]
                ],
                [
                    'title'=>__('Cash Forecast'),
                    'link'=>route('view.customer.invoice.dashboard.forecast', ['company'=>$companyId]),
                    'show'=>$canViewCashForecastDashboard,
                    'submenu'=>[]
                ],
                [
                    'title'=>__('LG & LC Dashboard'),
                    'link'=>route('view.lglc.dashboard', ['company'=>$companyId]),
                    'show'=>$canViewLgAndLcDashboard,
                    'submenu'=>[]
                ],
            ]

        ]
        ,
        
        'reports'=>[
            'title'=>__('Reports'),
            'show'=>$canViewCashFlow || $canViewContractCashFlow ||  $canViewSafeStatement || $canViewCashExpenseStatement || $canViewPartnersStatement || $canViewBankStatement|| $canViewLgByBeneficiaryNameReport || $canViewLgByBankNameReport || $canViewLgLcStatement || $canViewWithdrawalsSettlementReport ,
            'link'=>'#',
            'submenu'=>
            [
        
                [
                    'title'=>__('Safe Statement'),
                    'link'=>route('view.safe.statement', ['company'=>$company->id]) ,
                    'show'=>$canViewSafeStatement,
                    'submenu'=>[]
                ],
                
                [
                    'title'=>__('Bank Statement'),
                    'link'=>route('view.bank.statement', ['company'=>$company->id]),
                    'show'=>$canViewBankStatement,
                    'submenu'=>[]
                ],
                [
                    'title'=>__('LG By Beneficiary Name Report'),
                    'link'=>route('view.lg.by.beneficiary.name.report', ['company'=>$company->id]),
                    'show'=>$canViewLgByBeneficiaryNameReport,
                    'submenu'=>[]
                ],[
                    'title'=>__('LG By Bank Name Report'),
                    'link'=>route('view.lg.by.bank.name.report', ['company'=>$company->id]),
                    'show'=>$canViewLgByBankNameReport,
                    'submenu'=>[]
                ]
                ,[
                    'title'=>__('LG & LC Statement'),
                    'link'=>route('view.lg.lc.bank.statement', ['company'=>$company->id]),
                    'show'=>$canViewBankStatement,
                    'submenu'=>[]
                ],
                [
                    'title'=>__('Cash Expense Statement'),
                    'link'=>route('view.cash.expense.statement', ['company'=>$company->id]) ,
                    'show'=>$canViewCashExpenseStatement,
                    'submenu'=>[]
                ],
                [
                    'title'=>__('Partners Statement'),
                    'link'=>route('view.partners.statement', ['company'=>$company->id]) ,
                    'show'=>$canViewPartnersStatement,
                    'submenu'=>[]
                ],
                [
                    'title'=>__('Company Cash Flow Report'),
                    'link'=>route('view.cashflow.report', ['company'=>$companyId]),
                    'show'=>$canViewCashFlow ,
                    'submenu'=>[]
                ],
                [
                    'title'=>__('Contract Cash Flow Report'),
                    'link'=>route('view.contract.cashflow.report', ['company'=>$companyId]),
                    'show'=>$canViewContractCashFlow ,
                    'submenu'=>[]
                ],
                [
                    'title'=>__('Withdrawals Settlement Report'),
                    'link'=>route('view.withdrawals.settlement.report', ['company'=>$companyId]),
                    'show'=>$canViewWithdrawalsSettlementReport ,
                    'submenu'=>[]
                ]
                
                    ],
        ],
        'bank-and-cash-account'=>[
            'title'=>__('Cash & Bank Accounts'),
            'show'=>true ,
            'submenu'=>[
                [
            'title'=>__('Financial Institutions'),
            'link'=>route('view.financial.institutions', ['company'=>$companyId]),
            'show'=>$user->can('view financial institutions')
                ],
                [
                'title'=>__('Safe'),
                'link'=>route('branches.index', ['company'=>$companyId]),
                'show'=>$canViewBranchesSettings
                ],
                [
                    'title'=>__('Opening Balances'),
                    'link'=>'#',
                    'show'=>$canViewOpeningBalances ,
                    'submenu'=>[
                        [
                            'title'=>__('Cash & Cheques Opening Balance'),
                            'link'=>route('opening-balance.index', ['company'=>$companyId]),
                            'show'=>$canUpdateCashAndChequesOpeningBalances,
                        ],
                        [
                            'title'=>__('Customers Opening Balance'),
                            'link'=>route('customers-opening-balance.index', ['company'=>$companyId]),
                            'show'=>$canUpdateCashAndChequesOpeningBalances,
                        ],
                        [
                            'title'=>__('Suppliers Opening Balance'),
                            'link'=>route('suppliers-opening-balance.index', ['company'=>$companyId]),
                            'show'=>$canUpdateCashAndChequesOpeningBalances,
                        ],
        
                    ],
                    
                    
                        ],
                        [
                'title'=>__('Other Odoo Integration Settings'),
                'link'=>route('odoo-settings.index', ['company'=>$companyId]),
                'show'=>$company->hasOdooIntegrationCredentials(),
            ],
                ],
                
        ],
        // 'financial-institution'=>[
        // 	'title'=>__('Financial Institutions'),
        // 	'link'=>route('view.financial.institutions',['company'=>$companyId]),
        // 	'show'=>$user->can('view financial institutions')
        // ],
        'customer-sections'=>[
            'title'=>__('Customer Sections'),
            'link'=>'#',
            'show'=>true,
            'submenu'=>[

                [
                    'title'=>__('Customer Balances'),
                    'link'=>route('view.balances', ['company'=>$companyId,'modelType'=>'CustomerInvoice']),
                    'show'=>$user->can('view customer balances'),
                    'submenu'=>[]
                ],
                [
            'title'=>__('Customer Aging'),
            'link'=>route('view.aging.analysis', ['company'=>$companyId,'modelType'=>'CustomerInvoice']),
            'show'=>$user->can('view customer aging'),
            'submenu'=>[]
            ],
            
            [
                'title'=>__('Collections Effectiveness Index'),
                'link'=>route('view.collections.effectiveness.index', ['company'=>$company->id]) ,
                'show'=>$user->can('view collections effectiveness index'),
                'submenu'=>[]
            ],
            
            
            
            [
                'title'=>__('Customer Contracts'),
            'link'=>route('contracts.index', ['company'=>$companyId,'type'=>'Customer']),
            'show'=>$user->can('view customers contracts'),

            ],
            [
                'title'=>__('Upload New Customer Invoice Data'),
                'link'=>route('view.uploading', ['company'=>$company->id , 'model'=>'CustomerInvoice']),
                'show'=>$user->can(uploadCustomerInvoiceData),
                'submenu'=>[]
            ]







            ]
        ],
        'supplier-sections'=>[
            'title'=>__('Supplier Sections'),
            'link'=>'#',
            'show'=>true,
            'submenu'=>[

                [
                    'title'=>__('Supplier Balances'),
                    'link'=>route('view.balances', ['company'=>$companyId,'modelType'=>'SupplierInvoice']),
                    'show'=>$user->can('view supplier balances'),
                    'submenu'=>[]
                ],
                [
            'title'=>__('Supplier Aging'),
            'link'=>route('view.aging.analysis', ['company'=>$companyId,'modelType'=>'SupplierInvoice']),
            'show'=>$user->can('view supplier aging'),
            'submenu'=>[]
            ],
            [
                'title'=>__('Supplier Contracts'),
            'link'=>route('contracts.index', ['company'=>$companyId,'type'=>'Supplier']),
            'show'=>$user->can('view suppliers contracts'),

            ],
            [
                'title'=>__('Upload New Supplier Invoice Data'),
                'link'=>route('view.uploading', ['company'=>$company->id , 'model'=>'SupplierInvoice']),
                'show'=>$user->can(uploadSupplierInvoiceData),
                'submenu'=>[]
            ]







            ]
        ],
        
        'money-transactions'=>[
            'title'=>__('Money Transactions'),
            'link'=>'#',
            'show'=>true ,
            'submenu'=>[
                [
                    'title'=>__('Money Received'),
                    'link'=>route('view.money.receive', ['company'=>$companyId]),
                    'show'=>$user->can('view money received'),
                    'submenu'=>[]
                ],
                [
                    'title'=>__('Money Payment'),
                    'link'=>route('view.money.payment', ['company'=>$companyId]),
                    'show'=>$user->can('view supplier payment'),
                    'submenu'=>[]
                ],
                [
                    'title'=>__('Cash Expenses'),
                    'link'=>route('view.cash.expense', ['company'=>$companyId]),
                    'show'=>$user->can('view cash expenses'),
                    'submenu'=>[]
                ],
                // [
                // 	'title'=>__('Approved Expenses'),
                // 	'link'=>route('odoo-expenses.index', ['company'=>$companyId]),
                // 	'show'=>$company->hasOdooIntegrationCredentials(),
                // 	'submenu'=>[]
                // ],
                
                [
                    'title'=>__('LC Settlement Internal Transfer'),
                    'link'=>route('lc-settlement-internal-money-transfers.index', ['company'=>$companyId]),
                    'show'=>$user->can('view lc settlement internal transfer'),
                    'submenu'=>[]
                        ],
                [
            'title'=>__('Internal Money Transfer'),
            'link'=>route('internal-money-transfers.index', ['company'=>$companyId]),
            'show'=>$user->can('view internal money transfer'),
            'submenu'=>[]
                ],
                [
                    'title'=>__('Sell Or Buy Currency'),
                    'link'=>route('buy-or-sell-currencies.index', ['company'=>$company->id ]),
                    'show'=>$user->can('view buy or sell currency'),
                    'submenu'=>[]
                ],
                [
                    'title'=>__('Foreign Exchange Rate'),
                    'link'=>route('view.foreign.exchange.rate', ['company'=>$company->id]),
                    'show'=>$user->can('view foreign exchange rate'),
                    'submenu'=>[]
                ],
                
                [
                    'title'=>__('Odoo Integration'),
                    'link'=>'#',
                    'show'=>$company->hasOdooIntegrationCredentials(),
                    'submenu'=>[
                        [
                            'title'=>__('Read Partners'),
                        'link'=>'#',
                        'show'=>true,
                        'data-show-notification-modal'=>'read-partners-modal'
                    ],
                        [
                            'title'=>__('Read Invoices'),
                        'link'=>'#',
                        'show'=>true,
                        'data-show-notification-modal'=>'read-invoices-modal'
                    ],
                            [
                            'title'=>__('Read Contracts'),
                        'link'=>'#',
                        'show'=>true,
                        'data-show-notification-modal'=>'read-contracts-modal'
                    ],
                        
                    // [
                    // 	'title'=>__('Send Collections Or Payments'),
                    // 	'link'=>'#',
                    // 	'show'=>true,
                    // 	'data-show-notification-modal'=>'send-invoices-modal',
                    // ],
                    // [
                    // 	'title'=>__('Read Approved Expenses'),
                    // 	'link'=>'#',
                    // 	'show'=>true,
                    // 	'data-show-notification-modal'=>'read-expenses-modal',
                    // ],
                    ],
                    
                ],
                
                        
                        
                        
                        
                    
                        
                        
                        

            ]
        ]
        ,
        'view letter of guarantee issuance'=>[
            'title'=>__('LG & LC Issuance'),
            'show'=>true ,
            'submenu'=>[
                [
            'title'=>__('Letter Of Guarantee (LG) Issuance'),
            'link'=>route('view.letter.of.guarantee.issuance', ['company'=>$companyId]),
            'show'=>$user->can('view letter of guarantee issuance'),
            'submenu'=>[]
            ],
            [
            'title'=>__('Letter Of Credit (LC) Issuance'),
            'link'=>route('view.letter.of.credit.issuance', ['company'=>$companyId]),
            'show'=>$user->can('view letter of credit issuance'),
            'submenu'=>[]
            ]
            ]
            
            
        ],
        
        ];
    $isCustomerOrSupplierUploading = in_array('CustomerInvoice', Request()->segments()) || in_array('SupplierInvoice', Request()->segments());
    if ($company->hasCashVero() && (hasMiddleware('isCashManagement') || $isCustomerOrSupplierUploading || in_array('LoanSchedule', Request()->segments()))) {
        return $cashManagementSubItems ;
    }
        
    $canViewVeroAnalysisDashboard = $user->can('view sales dashboard') || $user->can('view breakdown dashboard') || ($user->can('view customer dashboard')&& $hasSelectCustomerNameInTemplate)
    || ($user->can('view sales person dashboard')&&$hasSelectSalesPersonInTemplate) || $user->can('view interval comparing dashboard') || $user->can('view expense analysis dashboard')
    || $user->can('view income statement dashboard');
        
        
    $canViewUploadSalesData = $user->can('upload sales gathering data') ;
    $canViewUploadExportData = $user->can(uploadExportAnalysisData) ;
    $canViewUploadCustomerInvoiceData = $user->can(uploadCustomerInvoiceData) ;
    $canViewUploadSupplierInvoiceData = $user->can(uploadSupplierInvoiceData) ;
    $canViewUploadLabelingData = $user->can(uploadLabelingItemData);
    $canViewDataGathering = $canViewUploadSalesData || $canViewUploadExportData || $canViewUploadCustomerInvoiceData || $canViewUploadSupplierInvoiceData || $canViewUploadLabelingData;
        
    $salesAnalysisSubItems = getSalesAnalysisReportSubmenu($user, $companyId) ;
        
    $canViewSalesAnalysisReport = count($salesAnalysisSubItems) ;
    $canExportAnalysisReport = $user->can(viewExportAnalysisData) ;
    $canExpenseAnalysisReport = $user->can(viewExpenseAnalysisData) ;
    $canViewAnalysisReport = $canViewSalesAnalysisReport || $canExportAnalysisReport|| $canExpenseAnalysisReport ;
        
        
    $salesForecastValueBaseSubItems=getSalesForecastValueBaseSubmenu($user, $companyId);
    $canViewSalesForecastValueBase=count($salesForecastValueBaseSubItems);
    // $user->can('view sales forecast value base');
    $salesForecastQuantityBaseSubItems= getSalesForecastQuantityBaseSubmenu($user, $companyId);
    $canViewSalesForecastQuantityBase=count($salesForecastQuantityBaseSubItems);
    $canViewSalesForecast = ($hasSalesGatheringDataUploadData)  && ($canViewSalesForecastValueBase||$canViewSalesForecastQuantityBase);
        
        
        
    return [
        'home'=>generateMenuItem(__('Home'), $user->can('view home'), route('home'), []),
        'dashboard'=>[
            'title'=>__('Dashboard'),
            'show'=>$canViewVeroAnalysisDashboard ,
            'submenu'=>[
                'sales-dashboard'=>generateMenuItem(__('Sales Dashboard'), $user->can('view sales dashboard'), route('dashboard', ['company'=>$companyId]), []),
                'breakdown-dashboard'=>generateMenuItem(__('Breakdown Dashboard'), $user->can('view breakdown dashboard'), route('dashboard.breakdown', ['company'=>$companyId])),
                'customers-dashboard'=>generateMenuItem(__('Customers Dashboard'), $user->can('view customer dashboard') && $hasSelectCustomerNameInTemplate, route('dashboard.customers', ['company'=>$companyId]), []),
                'sales-person-dashboard'=>generateMenuItem(__('Sales Person Dashboard'), $user->can('view sales person dashboard')&&$hasSelectSalesPersonInTemplate, route('dashboard.salesPerson', ['company'=>$companyId])),
                'interval-comparing-dashboard'=>generateMenuItem(__('Interval Comparing Dashboard'), $user->can('view interval comparing dashboard'), route('dashboard.intervalComparing', ['company'=>$companyId]), []),
                'expense-analysis-dashboard'=>generateMenuItem(__('Expense Analysis Dashboard'), $user->can('view expense analysis dashboard'), route('view.expense.analysis.dashboard', ['company'=>$companyId]), []),
                'income-statement'=>generateMenuItem(__('Income Statement'), $user->can('view income statement dashboard'), '#', getIncomeStatementSubmenu($user, $company)),
            ]
                ],
                'data-gathering'=>[
                    'title'=>__('Data Gathering'),
                    'show'=>$canViewDataGathering,
                    'link'=>'#',
                    'submenu'=>[
                        'upload new sales data'=>[
                            'title'=>__('Upload New Sales Data'),
                            'link'=>route('view.uploading', ['company'=>$company->id , 'model'=>'SalesGathering']),
                            'show'=>$canViewUploadSalesData,
                            'submenu'=>[]
                        ],
                        'upload new export data'=>[
                            'title'=>__('Upload New Export Data'),
                            'link'=>route('view.uploading', ['company'=>$company->id , 'model'=>'ExportAnalysis']),
                            'show'=>$canViewUploadExportData,
                            'submenu'=>[]
                        ],
                        'upload new expense data'=>[
                            'title'=>__('Upload New Expense Data'),
                            'link'=>route('view.uploading', ['company'=>$company->id , 'model'=>'ExpenseAnalysis']),
                            'show'=>$canViewUploadExportData,
                            'submenu'=>[]
                        ],
                        'upload new customer invoice data'=>[
                            'title'=>__('Upload New Customer Invoice Data'),
                            'link'=>route('view.uploading', ['company'=>$company->id , 'model'=>'CustomerInvoice']),
                            'show'=>$canViewUploadCustomerInvoiceData,
                            'submenu'=>[]
                        ],
                        'upload new supplier invoice data'=>[
                            'title'=>__('Upload New Supplier Invoice Data'),
                            'link'=>route('view.uploading', ['company'=>$company->id , 'model'=>'SupplierInvoice']),
                            'show'=>$canViewUploadSupplierInvoiceData,
                            'submenu'=>[]
                        ],
                        'upload-new-labeling-data'=>[
                            'title'=>__('Upload New Labeling Data'),
                            'link'=>route('view.uploading', ['company'=>$company->id , 'model'=>'LabelingItem']),
                            'show'=>$canViewUploadLabelingData,
                            'submenu'=>[]
                        ]
                    ]
                        ],
                        'analysis-report'=>[
                            'title'=>__('Analysis Report'),
                            'show'=>$canViewAnalysisReport,
                            'link'=>'#',
                            'submenu'=>[
                                'sales-analysis-report'=>[
                                    'title'=>__('Sales Analysis Report'),
                                    'show'=>$canViewSalesAnalysisReport,
                                    'link'=>'#',
                                    'submenu'=>$salesAnalysisSubItems
                                ] ,
                                'export-analysis-report'=>[
                                    'title'=>__('Export Analysis Report'),
                                    'link'=>route('sales.export.analysis', ['company'=>$companyId]),
                                    'show'=>$canExportAnalysisReport
                                ],	'expense-analysis-report'=>[
                                    'title'=>__('Expense Analysis Report'),
                                    'link'=>route('sales.expense.analysis', ['company'=>$companyId]),
                                    'show'=>$canExpenseAnalysisReport
                                ],



                            ]

                                ],
                                'sales-forecast'=>[
                                    'title'=>__('Sales Forecast'),
                                    'link'=>'#',
                                    'show'=>$canViewSalesForecast  ,
                                    'submenu'=>[
                                        'sales-forecast-value-base'=>[
                                        'title'=>__('Sales Forecast Value Base'),
                                        'link'=>'#',
                                        'show'=>$canViewSalesForecastValueBase,
                                        'submenu'=>$salesForecastValueBaseSubItems
                                        ],
                                        'sales-forecast-quantity-base'=>[
                                            'title'=>__('Sales Forecast Quantity Base'),
                                            'link'=>'#',
                                            'show'=>$canViewSalesForecastQuantityBase,
                                            'submenu'=>getSalesForecastQuantityBaseSubmenu($user, $companyId)
                                        ]
                                    ]
                                        ],
                                        'income-statement-planning'=>[
                                            'title'=>__('Income Statement Planning'),
                                            'link'=>route('admin.view.financial.statement', ['company'=>$companyId]),
                                            'show'=>$user->can('view income statement planning')
                                        ],
                                        'cash-management'=>[
                                            'title'=>__('Cash Management'),
                                            'link'=>'#',
                                            'show'=>$company->hasCashVero()   ,
                                            'submenu'=>$cashManagementSubItems
                                                ],




                                                'quick-price'=>[
                                                    'title'=>__('Quick Price'),
                                                    'link'=>'#',
                                                    'show'=>$user->can('view quick price'),
                                                    'submenu'=>[
                                                        'pricing-plans'=>[
                                                            'title'=>__('Pricing Plan'),
                                                            'link'=>route('admin.view.quick.pricing.calculator', ['company'=>$companyId]),
                                                            'show'=>$user->can('view pricing plans')
                                                        ],

                                                        'quick-price-calculator'=>[
                                                            'title'=>__('Quick Price Calculator'),
                                                            'link'=>route('admin.view.quick.pricing.calculator', ['company'=>$companyId]),
                                                            'show'=>$user->can('view quick price calculator'),
                                                            'submenu'=>[]
                                                        ],

                                                        'setting'=>[
                                                            'title'=>__('Setting'),
                                                            'link'=>'#',
                                                            'show'=>$user->can('view quick price setting'),
                                                            'submenu'=>[
                                                                'revenue-business-line'=>generateMenuItem(__('Revenue Business Line'), $user->can('view revenue business line'), route('admin.view.revenue.business.line', ['company'=>$companyId]), []),
                                                                'positions'=>generateMenuItem(__('Positions'), $user->can('view positions'), route('positions.index', ['company'=>$companyId]), []),
                                                                'expenses'=>generateMenuItem(__('Expenses'), $user->can('view expenses'), route('pricing-expenses.index', ['company'=>$companyId]), []),
                                                            ]
                                                        ],


                                                    ]
                                                    ],
                                                    'labeling-items'=>[
                                                        'title'=>__('Labeling Items'),
                                                        'link'=>'#',
                                                        'show'=>$user->can('view labeling items'),
                                                        'submenu'=>[
                                                            'create-labeling-items'=>generateMenuItem(__('Create Labeling Items'), $user->can('view create labeling items'), route('create.labeling.items', ['company'=>$companyId])),
                                                            'building lable'=>generateMenuItem(__('Building Label'), $user->can('view create labeling items'), route('show.building.label', ['company'=>$companyId])),
                                                            'FF&E lable'=>generateMenuItem(__('FF&E Label'), $user->can('view create labeling items'), route('show.ffe.label', ['company'=>$companyId])),
                                                            'create-labeling-form'=>generateMenuItem(__('Create Labeling Form'), $user->can('view create labeling items'), route('create.labeling.form', ['company'=>$companyId])),

                                                        ]
                                                    ],



    ];
}
function getLgTypes():array
{
    return LgTypes::getAll();
}

function getLcTypes():array
{
    return LcTypes::getAll();
}
function getCommissionInterval():array
{
    return [
        'quarterly'=>__('Quarterly'),
        'annually'=>__('Annually')
    ];
}

function camelizeWithSpace($input, $separator = '-')
{
    return HStr::camelizeWithSpace($input, $separator);
}
function unformat_number($money)
{
    $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
    $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);

    $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

    $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
    $removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '', $stringWithCommaOrDot);

    return (float) str_replace(',', '.', $removedThousandSeparator);
}


// function getRevenueBusinessLineOptions(): array
// {

//     // used by seeder

//     return [
//         'training_service' => __('Training Service'),
//         'consulting_service' => __('Consulting Service'),
//         'internship_service' => __('Internship Service'),
//         // 'internship_service' => __('Internship Service'),
//         'externship_service' => __('Externship Service'),
//         'observership_service' => __('Observership Service'),
//         'observership_service' => __('Observership Service'),
//         'scholarship_service' => __('Scholarship Service'),
//         'fellowship_service' => __('Fellowship Service'),

//     ];
// }
// function getServiceCategories(): array
// {

//     return [
//         'financial_courses' => __('Financial Courses'),
//         'marketing_courses' => __('Marketing Courses'),
//         'hr_courses' => __('Hr Courses'),
//         'financial_consulting' => __('Financial Consulting'),
//         'marketing_consulting' => __('Marketing Consulting'),
//         'hr_consulting' => __('Hr Consulting'),
//     ];
// }
// function getServiceName(): array
// {

//     return [
//         'accounting' => __('Accounting'),
//         'costing' => __('Costing'),
//         'budget' => __('Budget'),
//         'feasibility_study' => __('Feasibility Study'),
//         'valuation' => __('Valuation'),
//         'performance_analysis' => __('Performance Analysis'),
//     ];
// }
// function getServicesNature(): array
// {
//     return [
//         'online' => __('Online'),
//         'physical' => __('Physical')
//     ];
// }
function getCountries(): array
{
    $countries = Country::whereNotIn('name_en', ['United States', 'Kenya'])
        ->get()->pluck('name_' . App()->getLocale(), 'id')->toArray();
    return $countries;
}
function getPositions(): array
{
    return [
        'executive' => __('Executive'),
        'senior' => __('Senior'),
        'officer' => __('Officer')
    ];
}
function getCurrency()
{
    return getCurrencies();

}

function getAddNewFieldRule($fieldName)
{
    return Rule::requiredIf(Request()->get($fieldName) == 'Add New');
}

// route('view.uploading',['company'=>$company->id , 'model'=>$elementModelName])
function getTestBuildingArray()
{
    return [
        [
            'title'=>__('New Cataract'),
            'value'=>__('New Cataract'),
            'data-abb'=>'NECAT',
            'data-code'=>'01'
        ],
        [
            'title'=>__('Old Cataract'),
            'value'=>__('Old Cataract'),
            'data-abb'=>'ODCAT',
            'data-code'=>'02'
        ]
    ];
}
function getTestFfeArray()
{
    return [
        [
            'title'=>__('Furniture'),
            'value'=>'furniture',
            'data-abb'=>'FURN',
            'data-code'=>'01'
        ],
        [
            'title'=>__('Equipment'),
            'value'=>__('Equipment'),
            'data-abb'=>'EQUIP',
            'data-code'=>'02'
        ]
    ];
}

function getTestFloors()
{
    return [
        [
            'title'=>'Floor1',
            'value'=>'floor1',
            'data-abb'=>'FO1',
            'data-code'=>'01'
        ],
        [
            'title'=>'Floor2',
            'value'=>'floor2',
            'data-abb'=>'FO2',
            'data-code'=>'02'
        ],

    ];
}
function getTestCategory()
{
    return [
        [
            'title'=>'Beds',
            'value'=>'beds',
            'data-abb'=>'BDs',
            'data-code'=>'01'
        ],
        [
            'title'=>'Chairs',
            'value'=>'chairs',
            'data-abb'=>'CHs',
            'data-code'=>'02'
        ],

    ];
}
function getTestLabelForm()
{
    return [
        [
            'value'=>'Building',
        'title'=>'Building'
        ],
        [
            'value'=>'FF&E',
        'title'=>'FF&E'
        ]
    ];
}
function getTestBuildNames()
{
    return [
        [
            'value'=>'New Cataract',
        'title'=>'New Cataract'
        ],
        [
            'value'=>'Old Cataract',
        'title'=>'Old Cataract'
        ]
    ];
}
function filterByColumnName($filterByColumnName)
{
    $items = [];
    foreach ($filterByColumnName as $columnValue) {
        $attributes = $columnValue->getAttributes();

        foreach ($attributes as $colName => $colVal) {
            $items[$colName][$colVal] = $colVal ;
        }

    }
    $formatted=[];
    foreach ($items as $colName => $arr) {
        foreach ($arr as $col => $val) {
            $formatted[$colName][] =[
                'title'=>$col,
                'value'=>$val
            ];
        }
    }
    return $formatted ;
}
function formatColumnName($name)
{
    return trim(strtolower(str_replace(' ', '_', lcfirst($name))));
}
function FormatKeyAsColumnName($items)
{
    $result = [];
    foreach ($items as $key => $val) {
        $result[formatColumnName($key)] =$val;
    }
    return $result ;
}
function getValuesStartedAfterIndex(array $items, int $index)
{
    $result = ['QR Code'];
    foreach ($items as $i => $val) {
        if ($i > $index) {
            $result[]=$val ;
        }
    }
    return $result;
}
function qrcodeSpacing($code)
{
    return str_replace(['//','/'], ['// ','/ '], $code);
}
function getDefaultImage()
{
    return asset('custom/images/default-img.png');
}
function array_to_upper(array $items)
{
    $result = [];
    foreach ($items as $item) {
        $result[] = snakeToCamel($item);
    }
    return $result ;
}
function findByKey(array $items, $key, $searchId)
{
    foreach ($items as $item) {
        if (isset($item[$key]) && $item[$key] == $searchId) {
            return $item;
        }
    }
    return [];
}
function touppercase($currentName)
{
    return Str::upper($currentName);
}
function toupperfirst($currentName)
{
    return ucfirst($currentName);
}
function capitalize($currentName)
{
    return toupperfirst($currentName);
}

function dashesToCamelCase($string)
{
    $string = str_replace(['-', '_'], ' ', $string);
    return lcfirst(str_replace(' ', '', ucwords($string)));

}
function isAll($percentageOf)
{
    if (is_null($percentageOf)) {
        return false ;
    }
    $allItems  = is_array($percentageOf) ? $percentageOf : json_decode($percentageOf) ;
    return in_array('all', $allItems);

}
function getAllPercentageOfRevenuesIds(int $incomeStatementId, string $subItemType, int $isQuantity)
{

    return IncomeStatementSubItem::where('is_quantity', $isQuantity)
    ->where('financial_statement_able_item_id', IncomeStatementItem::SALES_REVENUE_ID)
    ->where('sub_item_type', $subItemType)
    ->where('financial_statement_able_id', $incomeStatementId)
    ->pluck('id')->toArray();

}
function getMappingFromForecastToAdjustedOrModified($isPercentageOfs, $currentSubItemType)
{

  
    $newPercentageOf = [];

    // $isPercentageOfs = $incomeStatement->pivot->{$propertyName} ;

    foreach ((array)convertStringArrayToArr($isPercentageOfs) as $percentageOfId) {
        $subItem = IncomeStatementSubItem::find($percentageOfId);
        if ($subItem) {
            $item  = IncomeStatementSubItem::where('financial_statement_able_id', $subItem->financial_statement_able_id)
            // $item  = IncomeStatementSubItem::where('financial_statement_able_id',$incomeStatement->id)
            ->where('financial_statement_able_item_id', $subItem->financial_statement_able_item_id)
            ->where('sub_item_name', $subItem->sub_item_name)
            ->where('sub_item_type', $currentSubItemType)
            ->first();
            ;
            if ($item) {
                $newPercentageOf[] = $item->id;
            }
        }

    }
    return $newPercentageOf;
}
function convertStringArrayToArr($arrayAsString):?array
{
    if (is_string($arrayAsString)) {
        return (array)(json_decode($arrayAsString)) ;
    }
    return $arrayAsString;
}
function hasMiddleware(string $middlewareName)
{
	if(is_null(Route::current())){
		return false;
	}
    return in_array($middlewareName, array_values(Route::current()->gatherMiddleware()));
}
function getModelNameWithoutNamespace($object)
{
    return HHelpers::getClassNameWithoutNameSpace($object);
}
function formatWeeksDatesFromStartDate(string $agingDate, string $format = 'd-m-Y')
{
    return [
        'past_due' => [
            '1-7' => [
                'start_date' => $startDate = Carbon::make($agingDate)->subDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->subDays(6)->format($format)
            ],
            '8-15' => [
                'start_date' => $startDate = Carbon::make($endDate)->subDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->subDays(7)->format($format)
            ],
            '16-30' => [
                'start_date' => $startDate = Carbon::make($endDate)->subDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->subDays(14)->format($format)
            ],
            '31-45' => [
                'start_date' => $startDate = Carbon::make($endDate)->subDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->subDays(14)->format($format)
            ],
            '46-60' => [
                'start_date' => $startDate = Carbon::make($endDate)->subDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->subDays(14)->format($format)
            ],
            '61-90' => [
                'start_date' => $startDate = Carbon::make($endDate)->subDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->subDays(29)->format($format)
            ],
            '91-120' => [
                'start_date' => $startDate = Carbon::make($endDate)->subDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->subDays(29)->format($format)
            ],
            '121-150' => [
                'start_date' => $startDate = Carbon::make($endDate)->subDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->subDays(29)->format($format)
            ],
        ],

        'coming_due' => [
            '1-7' => [
                'start_date' => $startDate = Carbon::make($agingDate)->addDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->addDays(6)->format($format)
            ],
            '8-15' => [
                'start_date' => $startDate = Carbon::make($endDate)->addDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->addDays(7)->format($format)
            ],
            '16-30' => [
                'start_date' => $startDate = Carbon::make($endDate)->addDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->addDays(14)->format($format)
            ],
            '31-45' => [
                'start_date' => $startDate = Carbon::make($endDate)->addDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->addDays(14)->format($format)
            ],
            '46-60' => [
                'start_date' => $startDate = Carbon::make($endDate)->addDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->addDays(14)->format($format)
            ],
            '61-90' => [
                'start_date' => $startDate = Carbon::make($endDate)->addDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->addDays(29)->format($format)
            ],
            '91-120' => [
                'start_date' => $startDate = Carbon::make($endDate)->addDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->addDays(29)->format($format)
            ],
            '121-150' => [
                'start_date' => $startDate = Carbon::make($endDate)->addDay()->format($format),
                'end_date' => $endDate = Carbon::make($startDate)->addDays(29)->format($format)
            ],
        ]

    ];
}



if (!function_exists('str_to_upper')) {
    function str_to_upper($str)
    {
        return ucwords(str_replace(['_', '-'], ' ', $str));
    }
}
if (!function_exists('getFixedLoanTypes')) {
    function getFixedLoanTypes()
    {
        return [
            'normal', 'step-up', 'step-down', 'grace_period_with_capitalization', 'grace_period_without_capitalization', 'grace_step-up_with_capitalization', 'grace_step-up_without_capitalization',
            'grace_step-down_with_capitalization', 'grace_step-down_without_capitalization',
        ];
    }
}
function getDifferenceBetweenTwoDatesInDays(Carbon $firstDate, Carbon $secondDate)
{
    return $secondDate->diffInDays($firstDate);
}
function getBankStatementReviewed($stdClass)
{
    $tableName = null ;
        
    if ($id = $stdClass->money_received_id) {
        $tableName = 'money_received';
    } elseif ($id = $stdClass->money_payment_id) {
        $tableName = 'money_payments';
    } elseif ($id = $stdClass->cash_expense_id) {
        $tableName = 'cash_expenses';
    } elseif ($id = $stdClass->buy_or_sell_currency_id) {
        $tableName = 'buy_or_sell_currencies';
    } elseif ($id = $stdClass->internal_money_transfer_id) {
        $tableName = 'internal_money_transfers';
    }
    if (is_null($tableName)) {
        return [
            'can_not_be_reviewed'=>1,
        ];
    }
    $raw = DB::table($tableName)->find($id);
    if ($raw && !isset($raw->reviewed_by)) {
        return [
            'can_not_be_reviewed'=>1,
        ];
    }
    return $raw && isset($raw->reviewed_by)  ? ['is_reviewed'=>$raw->is_reviewed,'reviewed_by'=>$raw->reviewed_by] : [];
}
function getBankStatementComment($stdClass)
{
    $lang = app()->getLocale() ;
    $columnNameWithoutLang = 'comment_';
    $tableName = null ;
    if ($id = $stdClass->money_received_id) {
        $tableName = 'money_received';
    } elseif ($id = $stdClass->money_payment_id) {
        $tableName = 'money_payments';
    } elseif ($id = $stdClass->cash_expense_id) {
        $tableName = 'cash_expenses';
    } elseif ($id = $stdClass->buy_or_sell_currency_id) {
        $tableName = 'buy_or_sell_currencies';
        if ($stdClass->is_debit) {
            $columnNameWithoutLang = 'buy_comment_';
        } else {
            $columnNameWithoutLang = 'sell_comment_';
        }
    } elseif ($id = $stdClass->internal_money_transfer_id) {
        $tableName = 'internal_money_transfers';
        if ($stdClass->is_debit) {
            $columnNameWithoutLang = 'from_comment_';
        } else {
            $columnNameWithoutLang = 'to_comment_';
        }
    }
    
    if (is_null($tableName)) {
        return __('N/A', [], $lang);
    }
    $raw = DB::table($tableName)->find($id);
    return $raw ? $raw->{$columnNameWithoutLang.$lang} : __('N/A', [], $lang);
}
function getKeysWithSettlementAmount(array $items, string $keyName):string
{
    $result = [];

    foreach ($items as $key => $arr) {
        if (isset($arr[$keyName]) && $arr[$keyName] > 0) {
            $result[] =  $arr['invoice_number'] ;
            // $result[] =  $key ;
        }
    }
    return implode(',', $result) ;
}
function getAllDataKey(array $items):array
{
    $result = [];
    foreach ($items as $key => $value) {
        if (Str::startsWith($key, 'data-')) {
            $result[$key] = $value ;
        }
    }
    return $result ;
}

function formatAccumulatedNetCash(array $netCashItems, array $dates)
{
    $formattedResult = [];
    $netCashItems = HArr::removeKeysFromArray($netCashItems, ['total_of_total']);
    $accumulatedNetCash = 0 ;
    foreach ($dates as $weekAndYear => $startAndEndDateArray) {
        $endDate = $startAndEndDateArray['end_date'];
        $currentNetCash = $netCashItems[$weekAndYear] ?? 0 ;
        $accumulatedNetCash += $currentNetCash ;
        $formattedResult[] = ['date'=>$endDate,'value'=>$accumulatedNetCash ];
    }
    return $formattedResult ;
}
function hasAuthFor($permissionName)
{
    return auth()->user()->can($permissionName);
}
function formatArrayAsGroup(array $permissions):array
{
    $result = [];
    foreach ($permissions as $permissionArr) {
        $result[$permissionArr['group']][] =$permissionArr;
    }
    return $result;
}
function generateModelData($fieldName, $model, $functionName = null, $defaultValue = null)
{
    $oldFromModel = isset($model) ? $model->{$fieldName} : $defaultValue ;
    if ($functionName) {
        $oldFromModel = isset($model) ? $model->$functionName() : $defaultValue ;
    }
    return old($fieldName, $oldFromModel);
}
function fillObjectFromArray(array $items, $object)
{
    $result = [];
    $isString  = $object;
    
    foreach ($items as $arrWithItsKeys) {
        if ($isString) {
            $object = new $object;
        }
        foreach ((array)$arrWithItsKeys as $key => $val) {
            $object->{$key}  = $val;
        }
        $result[] = $object ;
    }

    return $result ;
}
function getCashVeroTableNames()
{
    return [
        'cash_expenses',
        'overdraft_against_commercial_papers',
        'clean_overdrafts',
        'overdraft_against_assignment_of_contracts',
        'fully_secured_overdrafts',
        'settlement_allocations',
        'buy_or_sell_currencies',
        'cash_in_banks','cash_in_safes','cash_in_safe_statements',
        'cash_payments','certificates_of_deposits','cheques'
        ,'supplier_invoices' ,'clean_overdrafts','customer_invoices','financial_institutions','financial_institution_accounts','fully_secured_overdrafts'
        ,'clean_overdraft_bank_statements','clean_overdraft_withdrawals',
        'notifications',
        'current_account_bank_statements','debugging','down_payment_money_payment_settlements','down_payment_settlements','due_date_histories','fully_secured_overdraft_bank_statements','fully_secured_overdraft_withdrawals','incoming_transfers','internal_money_transfers','lc_hundred_percentage_cash_cover_opening_balances'
, "lc_hundred_percentage_cash_cover_opening_balances"
, "lending_information"
, "lending_information_against_assignment_of_contracts"
, "letter_of_credit_cash_cover_statements"
, "letter_of_credit_facilities"
, "letter_of_credit_facility_term_and_conditions"
, "letter_of_credit_opening_balances"
, "letter_of_credit_statements"
, "letter_of_guarantee_cash_cover_statements"
, "letter_of_guarantee_facilities"
, "letter_of_guarantee_facility_term_and_conditions"
, "letter_of_guarantee_issuances"
, "letter_of_guarantee_opening_balances"
, "letter_of_guarantee_statements"
, "lg_against_td_or_cd_opening_balances"
, "lg_hundred_percentage_cash_cover_opening_balances"
, "lg_issuance_advanced_payment_histories"
, "lg_opening_balances"
, "loans",'opening_balances','outgoing_transfers',
'outstanding_breakdowns','overdraft_against_assignment_of_contract_bank_statements',
'overdraft_against_assignment_of_contract_limits','overdraft_against_assignment_of_contract_withdrawals',
'overdraft_against_commercial_paper_bank_statements','overdraft_against_commercial_paper_limits',
'overdraft_against_commercial_paper_withdrawals','payable_cheques',
'payment_settlements','settlements','money_received','money_payments','contracts'

    ];
}
function getReviewedText(array $reviewedArr)
{
    $reviewedText = '-';
    if (isset($reviewedArr['can_not_be_reviewed'])) {
        $reviewedText = '-';
    } elseif (isset($reviewedArr['is_reviewed']) && $reviewedArr['is_reviewed'] == 1) {
        $reviewedText = __('Yes');
    } elseif (isset($reviewedArr['is_reviewed']) && $reviewedArr['is_reviewed'] == 0) {
        $reviewedText = __('No');
    }
    return $reviewedText ;
}
function getReviewPermissionName($modelName):string
{
    if ($modelName == 'CashExpense') {
        return 'review cash expenses';
    }
    if ($modelName =='MoneyReceived') {
        return 'review money received';
    }
    if ($modelName=='MoneyPayment') {
        return 'review supplier payments';
    }
    throw new \Exception('custom exception .. please add permission name here');
}
function AtLeastOnKeyIsTrue(array $items, string $key)
{
    $show = false ;
    foreach (array_column($items, $key) as $boolean) {
        if ($boolean) {
            $show= true ;
        }
    }
    return $show ;
}
function getAllPartnerTypesForSuppliers():array
{
    return ['is_supplier'=>__('Supplier'),'is_subsidiary_company'=>__('Subsidiary Company') , 'is_shareholder'=>__('Shareholder') , 'is_employee'=>__('Employee'),
    'is_other_partner'=>__('Other Partner'),
    'is_tax'=>__('Taxes & Social Insurance')
];

}
function getAllPartnerTypesForCustomers():array
{
    return ['is_customer'=>__('Customer'),'is_subsidiary_company'=>__('Subsidiary Company') , 'is_shareholder'=>__('Shareholder') , 'is_employee'=>__('Employee'),
'is_other_partner'=>__('Other Partner')
];

}
function hasExport(array $fields, int $companyId, $modelName='SalesGathering')
{
    $fieldRow = CustomizedFieldsExportation::where('company_id', $companyId)->where('model_name', $modelName)->first();
    $exportableFields = $fieldRow ? $fieldRow->fields : [];
    foreach ($fields as $field) {
        if (!in_array($field, $exportableFields)) {
            return false ;
        }
    }
    return true ;
}



function sort_by_key_date_string($element1, $element2)
{
    $datetime1 = strtotime($element1);
    $datetime2 = strtotime($element2);
    return $datetime1 - $datetime2;
}
function getDayFromDate(string $date)
{
    return explode('-', $date)[2];
}
function getMonthFromDate(string $date)
{
    return explode('-', $date)[1];
}

function repeatJson($jsonItems)
{
    $itemsArray = is_array($jsonItems) ? $jsonItems : convertJsonToArray($jsonItems);
    if (!count($itemsArray)) {
        return null ;
    }
    $lastKey = array_key_last($itemsArray);
    $loopingKey = $lastKey+1;
    for ($loopingKey ; $loopingKey < MAX_YEARS_COUNT ; $loopingKey++) {
        $itemsArray[$loopingKey] =$itemsArray[$lastKey];
    }
    return json_encode($itemsArray);
}




function sumNumberOfOnes(array $items, int $year, array $datesIndexWithYearIndex)
{
    $counter = [];
    foreach ($items as $loopYear => $dateAndValues) {
        foreach ($dateAndValues as $dateIndex => $value) {
            $loopYear = $datesIndexWithYearIndex[$dateIndex];
            if ($value == 1) {
                $counter[$loopYear] = isset($counter[$loopYear]) ? $counter[$loopYear] + 1 : $value;
            }
        }
    }
    return $counter[$year] ?? 0;
}
// function getPreviousValue(array $array, $specificValue)
// {
//     $keys = array_keys($array); // Get all keys from the array
//     $values = array_values($array); // Get all values from the array
//     $index = array_search($specificValue, $values); // Find the index of the specific value

//     if ($index === false || $index === 0) {
//         // Return null if the value doesn't exist or it's the first value
//         return null;
//     }

//     return $values[$index - 1]; // Return the previous value
// }
function removeSquareBrackets($input)
{
    // Use preg_replace to remove [ ] and text between them
    $result = preg_replace('/\[[^\]]*\]/', '', $input);
    return $result;
}


function getExpenseTypes():array
{
    $isNonBanking = hasMiddleware('isNonBankingService') ;
    $isPropertyManagement = hasMiddleware('isPropertyManagement') ;
    $costOfGoodsText = $isNonBanking||$isPropertyManagement ? __('Cost Of Service') : __('Cost Of Goods Sold');
    return [
        'cost-of-service'=>$costOfGoodsText,
        'marketing-expense'=>__('Marketing Expense'),
        'other-operation-expense'=>__('Other Operations Expenses'),
        'sales-expense'=>__('Sales Expense'),
        'general-expense'=>__('General Expense')
    ];
}

const SHAREABLE_LINKS = 'sharable-links';





// function getDepreciationDurations():array
// {
//     $result = [];
//     for ($i = 2 ; $i <= 25 ; $i++) {
//         $result[] = [
//             'title'=> $i . ' ' . __('Years'),
//             'value'=>$i
//         ];
//     }
//     return $result;
// }
// function getReplacementInterval():array
// {
//     $result = [];
//     for ($i = 1 ; $i <= 5 ; $i++) {
//         $result[] = [
//             'title'=> $i . ' ' . __('Years'),
//             'value'=>$i
//         ];
//     }
//     return $result;
// }
// function sumKeyAcrossArrays($data, $key)
// {
//     $sum = 0;
//     foreach ($data as $subArray) {
//         if (isset($subArray[$key])) {
//             $sum += $subArray[$key];
//         }
//     }
//     return $sum;
// }

function convertIndexKeysToString(array $items, array $datesAsIndexAndString)
{
    $result = [];
    foreach ($items as $dateAsIndex => $value) {
        $dateAsString = $datesAsIndexAndString[$dateAsIndex];
        $result[$dateAsString] = $value;
    }
    return $result ;
}
function sumIntervalsIndexes(array $dateValues, string $intervalName, string $financialYearStartMonth, array $dateIndexWithDate)
{
    return (new IntervalSummationOperations())->sumForInterval($dateValues, $intervalName, $financialYearStartMonth, $dateIndexWithDate, true);
}
function getIntervalFormatted():array
{
    return ['monthly'=>__('Monthly')
    ,'quarterly'=>__('Quarterly'),'semi-annually'=>__('Semi-annually'),'annually'=>__('Annually')
];
}
function removeDateFrom(array $dateIndexWithDate)
{
    $result = [];
    foreach ($dateIndexWithDate as $dateAsIndex => $dateAsString) {
        $dateExploded = explode('-', $dateAsString);
        $month = $dateExploded[1];
        $year = $dateExploded[0];
        $dateMonthAndYear =$month.'-'.$year;
        $result[$dateMonthAndYear] = $dateAsIndex;
    }
    return $result;
}
function getValueFromArrayStringAndIndex(array $items, $dateAsString, $dateAsIndex, $defaultValue = 0)
{
    if (isset($items[$dateAsString])) {
        return $items[$dateAsString];
    }
    if (isset($items[$dateAsIndex])) {
        return $items[$dateAsIndex];
    }
    return $defaultValue ;
}
function convertStringWithNumberToNumber(string $value):float
{
    $numericString = preg_replace('/[^0-9.,]/', '', $value);

    // Remove commas
    $numericString = str_replace(',', '', $numericString);

    // Convert to float
    $number = floatval($numericString);

    return  $number; // 2496335

}

function getExpensesTypes():array
{
    return [
        // 'varying_amount',
        // 'fixed_percentage_of_sales',
        // 'varying_percentage_of_sales',
        // 'fixed_cost_per_unit',
        // 'varying_cost_per_unit',
        // 'expense_per_employee',
        // 'intervally_repeating_amount',
        // 'one_time_expense',
        'fixed_monthly_repeating_amount',
        'expense_as_percentage',
            'cost_per_unit',
            'one_time_expense'
    ];
}
function getTableNames(?string $connectionName = null):array
{
    $connectionName = $connectionName ?? config('database.default');
    $database = DB::connection($connectionName)->getDatabaseName();
    // $tableName = config('app.env') == 'local' ? 'TABLE_NAME': 'table_name';
    return DB::connection($connectionName)->table('information_schema.tables')
	->selectRaw('TABLE_NAME as table_name')
        ->where('table_schema', $database)
        ->where('table_type', 'BASE TABLE')
        ->pluck('table_name')->toArray();
}
function getTableNamesThatHasColumn(string $columnName, ?string $connectionName = null)
{
    $database = DB::connection($connectionName)->getDatabaseName();
    // $tableName = config('app.env') == 'local' ? 'TABLE_NAME': 'table_name';
  
    return DB::connection($connectionName)->table('information_schema.columns')
	    ->selectRaw('TABLE_NAME as table_name')
        ->where('column_name', $columnName)
        ->where('table_schema', $database)
        ->distinct()->pluck('table_name')->toArray();

}




function getMonthsList(): array
{
    $months = [];
    for ($i = 1; $i <= 12; $i++) {
        $months[$i-1] = date('F', mktime(0, 0, 0, $i, 1));
    }
    return $months;
}
// function generateOldNameFromFieldName(string $str):string
// {
//     $field = preg_replace('/\[([^\]]+)\]/', '.$1', $str);

//     return $field;
// }


function isSecuritized($securitizationDateIndex , $currentMonthIndex):bool
{
    return is_numeric($securitizationDateIndex) && $currentMonthIndex>= $securitizationDateIndex;
}
function getDivisionNumber()
{
    return 1000;
}
function routeWithQueryParam(string $route):string
{
    foreach (Request()->query() as $queryParam => $value) {
        return $route.'?'.$queryParam.'='.$value;
    }
    return $route;
}

// function showCertificateOfDeposits():bool
// {
// 	return true ;
// }

 
	
function formatDateForVueDatePicker(?string $dateString):array
{
	if(is_null($dateString)){
		return [];
	}
	$carbonDate = Carbon::parse($dateString);
	$year  = $carbonDate->year;
	$month = $carbonDate->month - 1; // لو عايزين 0-based
	return [
		'month'=>$month,
		'year'=>$year
	];
}

function convertJsDateToDB($year , $month)
{
	if(is_null($year)  || is_null($month)){
		return null;
	}
	return Carbon::create($year, $month + 1, 1)->format('Y-m-d');
	// return Carbon::create($year, $month, 1)->format('Y-m-d');
}
	
function getOnlyFilterOptions(array $options ,array $selected):array 
{
	$result = [];
	foreach($selected as $selectedKey){
		$hisOptions = $options[$selectedKey]??[];
		foreach($hisOptions as $option){
			$result[] = [
				'id'=>(string)$option['id'], // (string) important for vueSelect comparison 
				'title'=>$option['title']
			];
		}
	}
	return $result;
}
function convertArrayNumericValuesToStringValues(array $items){
	$result = [];
	foreach($items as $id){
		$result[] = (string)$id;
	}
	return $result;
}
function getLastMonthIndexInEachYear(array $items):array{
	$result = [];
	foreach($items as $index => $arr){
		$result[] = array_key_last($arr);
	}
	return $result;
}
function formatDateFromMonthPicker($date): ?string
    {
        if (is_null($date)) {
            return null;
        }
        
        // If it's already a string, return it
        if (is_string($date)) {
            return $date;
        }
        
        // If it's an array from month picker: {month: 0, year: 2024}
        if (is_array($date)) {
            $year = $date['year'] ?? date('Y');
            $month = isset($date['month']) ? ($date['month'] + 1) : 1; // VueDatePicker months are 0-indexed
            return sprintf('%04d-%02d-01', $year, $month);
        }
        
        return null;
    }
	function replaceIndexKeysWithDate(array $items, array $dates):array
	{
		$result = [];
		foreach($dates as $dateAsIndex=>$dateAsString ) {
			$result[$dateAsString] = $items[$dateAsIndex] ?? 0;
		}
		return $result;
	}
	function getValueAtMonthAndYear(array $items, string $currentDate):float
	{
		$currentDate = explode('-', $currentDate);
		$month = $currentDate[1];
		$year = $currentDate[0];
		
		foreach($items as $date=>$value){
			if (Carbon::make($date)->format('m')== $month && $year == Carbon::make($date)->format('Y')) {
				return $value ;
			}
		}
		return 0;
		
	}
	
	
function getBusinessSectors():array
{
	return [
		'Agro-Processing',
		'Automotive',
		'Cement',
		'Ceramics',
		'Chemicals',
		'Consultation',
		'Electronics',
		'Entertainment',
		'Fertilizers',
		'Food & Beverage',
		'Glass',
		'Healthcare',
		'Heavy Machinery',
		'Home Appliance',
		'Leather Products',
		'Logistics',
		'Maintenance Service',
		'Marketing Agencies',
		'Medical Disposables & Devices',
		'Metal Industries & Fabrication',
		'Other Construction Materials',
		'Others',
		'Pharmaceuticals',
		'Plastics',
		'Printing & Packaging',
		'Professional Services',
		'Ready Made Concrete',
		'Sanitary Products',
		'Software',
		'Steel',
		'System Integrator',
		'Textiles & Garments',
		'Wood & Furniture',
	];
	}
	
	
