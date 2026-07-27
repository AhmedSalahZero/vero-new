<?php
namespace App\Helpers;

use App\Models\Company;
use App\Models\NonBankingService\ExpenseName;
use Illuminate\Support\Facades\DB;

class HNonBanking
{
    
    
    public static function getBranchExpenseCategoriesForSelect2():array
    {
        $results = [];
        $expenseCategories = ExpenseName::getCategoriesForBranch(app(Company::class));
        foreach ($expenseCategories as $type => $name) {
            $results[] = [
                'title'=>HStr::camelizeWithSpace($type) ,
                'value'=>$type
            ];
        }
        return $results;

    }

	
public static function getUserCommentFromModel($stdClass)
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
    // elseif($id = $stdClass->letter_of_guarantee_issuance_id){
    // 	$tableName = 'letter_of_guarantee_issuances';
    // }
    // elseif($id = $stdClass->letter_of_credit_issuance_id){
    // 	$tableName = 'letter_of_credit_issuances';
    // }
    if (isset($stdClass->letter_of_guarantee_issuance_id) &&$stdClass->letter_of_guarantee_issuance_id) {
        $id = $stdClass->letter_of_guarantee_issuance_id ;
        $tableName = 'letter_of_guarantee_issuances';
    }
    if (isset($stdClass->letter_of_credit_issuance_id) &&$stdClass->letter_of_credit_issuance_id) {
        $id = $stdClass->letter_of_credit_issuance_id ;
        $tableName = 'letter_of_credit_issuances';
    }
    if (is_null($tableName)) {
        return '' ;
    }
    $row = DB::table($tableName)->where('id', $id)->first();
    if ($row && $row->user_comment) {
        return '[ '.  $row->user_comment . ' ]' ;
    }
    return '';

}


public static function getMicrofinanceFundingBySelector():array
{
    return [
        [
            'title'=>__('By ODAs'),
            'value'=>'by-odas',
        ],
        [
            'title'=>__('By MTLs'),
            'value'=>'by-mtls'
        ]
    ];
}


public static function getMicrofinanceNewBranchesFixedExpenseSelector():array
{
    return [
        [
            'title'=>__('Start Date'),
            'value'=>'start-date',
        ],
        [
            'title'=>__('Operation Date'),
            'value'=>'operation-date'
        ]
    ];
}
public static function getThreeDotsHint():string 
{
	return __('[you can use the three dots to repeat within the same year]');
}
public static function getAnalysisAccountIds(array $analytic_distribution,?int $partnerId = null):array
{
	if (is_null($partnerId)) {
		return [[6, 0, []]];
	}
	$distribution_analytic_account_ids = [];
	foreach (array_keys($analytic_distribution) as $key) {
		if ($key > 0) {
			$distribution_analytic_account_ids[] = [0, (int)$key];
		}
	}
	// Wrap in outer array with 6 and 0
	if (count($distribution_analytic_account_ids)) {
		$distribution_analytic_account_ids = [[6, 0, ...$distribution_analytic_account_ids]];
	} else {
		$distribution_analytic_account_ids = [[6, 0, []]];
	}
	return $distribution_analytic_account_ids;
}

}
