<?php
namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait IsBankStatement
{
	public function handleFullDateAfterDateEdit(string $date,$debit,$credit , $additionUpdateData = [])
	{ 
			$date = Carbon::make($date)->format('Y-m-d');
			$modelName = get_class($this);
			$orderBy = Schema::hasColumn($this->getTable(),'priority') ? 'date asc , priority asc, id asc' : 'date asc, id asc';
			$currentFullDate =$this->full_date ;
			$currentDate =$this->date ;
			
			$time  = Carbon::make($currentFullDate)->format('H:i:s');
			$newFullDateTime = date('Y-m-d H:i:s', strtotime("$date $time")) ;
			$minDate = min($currentDate , $date);
			$updatedData = [
				'date'=>$date,
				'full_date'=>$newFullDateTime ,
				'credit'=>$credit , 
				'debit'=>$debit 
			] ;
			$updatedData = array_merge($updatedData , $additionUpdateData);
			$isEndOfMonthRow = false ;
			if(isset($this->interest_type)){
				$isEndOfMonthRow =  $this->interest_type=='end_of_month' || $this->interest_type =='end_of_month_final';
			}
			if($isEndOfMonthRow){
				if(Request()->has('is_end_of_month_final')){
					$updatedData['interest_type']='end_of_month_final';
				}else{
					$updatedData['interest_type']='end_of_month';
				}
			}
			
			// Concurrent editors must not interleave balance cascades.
			DB::transaction(function () use ($updatedData, $modelName, $minDate, $orderBy) {
				$this->update($updatedData);

				$query = $modelName::where('date', '>=', $minDate);
				foreach ($this->getForeignKeyNamesThatUsedInFilter() as $columnName) {
					$query->where($columnName, $this->{$columnName});
				}

				$row = $query->orderByRaw($orderBy)->lockForUpdate()->first();
				if ($row) {
					$row->update([
						'updated_at' => now(),
					]);
				}
			});
			
	}
}
