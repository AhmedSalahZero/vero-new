<?php

namespace App\Models;

use App\Helpers\HArr;
use App\Traits\StaticBoot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LabelingItem extends Model
{
    use StaticBoot;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */

    protected $guarded = [];


    protected $table = 'labeling_items';
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id?? Request('company_id') );
    }
	private static function generateSubTabArr()
	{
		return [];
	}
	public function getPreviousRowsQuantities()
	{
		return self::where('id', '<',$this['id'])->where('company_id',$this['company_id'])->sum('qty');
	}
	
	
	public function quantityStartFrom()
	{
		
		$defaultStartNumber = 100000 ;
		return $defaultStartNumber;
		// $str = null ;
		// $start = '';
		// $canWeStart = false ;
		// $canWeEnd = false;
		// $firstItem = self::where('company_id',$this['company_id'])->orderBy('id','asc')->first();
		// if($firstItem && $firstItem->code){
		// 	$str = $firstItem->code;
		// }
		// if($firstItem && $firstItem->Code){
		// 	$str = $firstItem->Code;
		// }
		// if($str === null){
		// 	return $defaultStartNumber;
		// }		
		// $zeroAfterTFound = false ;
		// $tIsFound = false ;
		// foreach(array_reverse(str_split($str)) as $s){
		// 	if(strtolower($s) == 't'){
		// 		$tIsFound = true ;
		// 	}
		// 	if($s == '0' && $tIsFound){
		// 		$zeroAfterTFound = true ;
		// 	}
		// 	if($tIsFound && $zeroAfterTFound){
		// 		$canWeStart = true ;
		// 	} 
		// 	if($canWeStart){
		// 		$start.= $s ;
		// 		if($s == '1'){
		// 			$canWeEnd = true ;
		// 		}
		// 		if($canWeEnd){
		// 			$start = strrev($start);
		// 			break;
		// 		}
		// 	}
		// }
		// return $start?: $defaultStartNumber ; 
}
	
	public  function generateCodeForRow(
		// $serial,$returnQuantityString = false
		 )
	{
		
		$company= getCurrentCompany();
		$row = $this->getAttributes() ;
		$previousRowLastQuantity = $this->getPreviousRowsQuantities();
		$textPart = '';
		$numericParent = '';
		// $numericParent = '//'.$serial;
		$quantityStartFrom = $this->quantityStartFrom() ;
		foreach($row as $key=>$val){
			if(!in_array($key , (array)$company->generate_labeling_code_fields ))
			{
				continue;	
			}
			if(is_numeric($val)){
				$numericParent.= $val;
			}else{
				$textPart.= '/'.$val;
			}
		}
		$text = trim($textPart . $numericParent,'/') ;
		
		if($text != ''){
			return trim($textPart . $numericParent,'/');
		}
		return '-';
	}
	public static function getHeaderFromElement(? LabelingItem $item){
		if(! $item){
			return [];
		}
		return HArr::removeKeyFromArrayByValue(array_keys($item->getAttributes()),['id','company_id','update_at','created_at']);
	}
	
	public function getCode(int $index,$returnQuantityString=false)
	{
		if($returnQuantityString){
			return  $this->generateCodeForRow(1,$returnQuantityString);
		}
		if($this->code){
			return $this->removeUnwantedChars($this->code) ;
		}
		if($this->Code){
			return $this->removeUnwantedChars($this->Code); 
		}
		return $this->removeUnwantedChars($this->generateCodeForRow($index));
	}
	protected function removeUnwantedChars($code)
	{
		return str_replace([' To ','//'],['To','/'],$code);
	}

	
	public static  function hasCodeField():bool
	{
		$hasCodeField = false ; 
		$labelingItems = LabelingItem::where('company_id',getCurrentCompanyId())->get();
		foreach($labelingItems as $labeItem){
			if($labeItem->code || $labeItem->Code){
				$hasCodeField = true ; 
				break;
			}
		}
		return $hasCodeField ; 
	}
	public static function generateSerial( $paginationItems , $index)
	{
		if($paginationItems instanceof LengthAwarePaginator){
			$pageFactor = $paginationItems->perPage() * ($paginationItems->currentPage() - 1 );
			$serial = $pageFactor + $index +1 ;
			return $serial ;
		}
		return $index + 1;
	}

}
