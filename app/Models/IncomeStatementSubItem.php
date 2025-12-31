<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class  IncomeStatementSubItem extends Model 
{
	protected $table = 'financial_statement_able_main_item_sub_items';
	protected $guarded = [
		'id'
	];
}
