<?php

namespace App\Models;

use App\Helpers\HArr;
use App\Traits\StaticBoot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
/**
 * @property int $id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property string|null $update_at
 * @property string|null $building_name
 * @property string|null $c1
 * @property string|null $sub_2
 * @property string|null $c2
 * @property string|null $location
 * @property string|null $c3
 * @property string|null $sub_3
 * @property string|null $c4
 * @property string|null $classification
 * @property string|null $c5
 * @property string|null $sub_22
 * @property string|null $c6
 * @property string|null $sub_32
 * @property string|null $c7
 * @property string|null $qty
 * @property string|null $code
 * @property string|null $Code
 * @property string|null $item
 * @property string|null $17
 * @property string|null $ahmed_salah
 * @property string|null $serial_number
 * @property string|null $floor_number
 * @property string|null $furniture
 * @property string|null $muneera_experience_center
 * @property string|null $14
 * @property string|null $21
 * @property string|null $28
 * @property string|null $name_code&part_number
 * @property string|null $qr_code
 * @property string|null $depratment
 * @property string|null $abc
 * @property string|null $27
 * @property string|null $sn
 * @property string|null $cod_location
 * @property string|null $sub-location
 * @property string|null $codsub-location
 * @property string|null $items
 * @property string|null $items_summary
 * @property string|null $items_code
 * @property string|null $qty_cod
 * @property string|null $supplier
 * @property string|null $supplier_cod
 * @property string|null $dimension
 * @property string|null $condition-stored
 * @property string|null $stored
 * @property string|null $condition_stored_cod
 * @property string|null $name_code
 * @property string|null $part_number
 * @property string|null $location_cod_
 * @property string|null $sub-location_cod
 * @property string|null $item_summary
 * @property string|null $condition-furnished
 * @property string|null $furnished
 * @property string|null $code_name_&_nuber
 * @property string|null $name
 * @property string|null $dimension_
 * @property string|null $details
 * @property string|null $code_and_part_number
 * @property string|null $name_and_part_number
 * @property string|null $name&part_number
 * @property string|null $word_
 * @property string|null $word
 * @property string|null $number
 * @property string|null $print
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem where14($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem where17($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem where21($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem where27($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem where28($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereAbc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereAhmedSalah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereBuildingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereC1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereC2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereC3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereC4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereC5($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereC6($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereC7($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereClassification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCodLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCodeAndPartNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCodeName&Nuber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCodsubLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereConditionFurnished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereConditionStored($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereConditionStoredCod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereDepratment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereDimension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereFloorNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereFurnished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereFurniture($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereItemSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereItemsCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereItemsSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereLocationCod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereMuneeraExperienceCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereName&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereNameAndPartNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereNameCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereNameCode&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem wherePartNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem wherePrint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereQrCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereQtyCod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereSn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereStored($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereSub2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereSub22($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereSub3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereSub32($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereSubLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereSubLocationCod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereSupplierCod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereUpdateAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereWord($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCodeName&Nuber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereName&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereNameCode&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCodeName&Nuber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereName&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereNameCode&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCodeName&Nuber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereName&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereNameCode&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCodeName&Nuber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereName&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereNameCode&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCodeName&Nuber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereName&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereNameCode&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCodeName&Nuber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereName&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereNameCode&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCodeName&Nuber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereName&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereNameCode&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCodeName&Nuber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereName&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereNameCode&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereCodeName&Nuber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereName&partNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LabelingItem whereNameCode&partNumber($value)
 * @mixin \Eloquent
 */
class LabelingItem extends Model
{
    use StaticBoot;
    

    protected $guarded = [];


    protected $table = 'labeling_items';
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id?? Request('company_id') );
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
		
		$company= app(Company::class);
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
			return  $this->generateCodeForRow();
		}
		if($this->code){
			return $this->removeUnwantedChars($this->code) ;
		}
		if($this->Code){
			return $this->removeUnwantedChars($this->Code); 
		}
		return $this->removeUnwantedChars($this->generateCodeForRow());
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
