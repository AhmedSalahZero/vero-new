<?php
namespace App\Traits;

use App\Models\NonBankingService\Seasonality;
use App\Models\NonBankingService\Study;


trait HasSeasonality
{
	
	public function seasonality()
	{
		$revenueType = Request()->segment(7);
		if(in_array($revenueType,[Study::LEASING , Study::IJARA , Study::DIRECT_FACTORING,Study::REVERSE_FACTORING])){
			return $this->hasOne(Seasonality::class,'study_id','id')->where('model_name',$revenueType);
		}
		dd('invalid revenue type' . $revenueType);
	}
	public function isFlatSeasonality():bool
	{
		return $this->seasonality && $this->seasonality->type  == 'flat';
	}
	public function isQuarterlySeasonality():bool
	{
		return $this->seasonality && $this->seasonality->type  == 'quarterly';
	}
	public function isMonthlySeasonality():bool
	{
		return $this->seasonality && $this->seasonality->type  == 'monthly';
	}
	public function getSeasonalityType():?string 
	{
		return $this->seasonality ? $this->seasonality->getType() : 'flat' ;
	}
	/**
	 * * دي زي ما هي متسجله في الفورمة وليكن مثلا في حاله ال
	 * * quarterly هتكون 
	 * * [0 => 25 , 1 => 20 , 2 => 30 , 3 => 25 ]
	 * * علشان نعرضها زي ما هي في ال
	 * * old data in the related form
	 */
	public function getSeasonalityPercentagesAtIndex(int $index):float
	{
		return $this->seasonality && count($this->seasonality->percentages) ? $this->seasonality->percentages[$index] : 0;
	}
	/**
	 * * دي اللي بنمررها للفانكشن بتاعت ال 
	 * * seasonality per month 
	 * * ودايما بتكون مفرودة وليكن مثلا في حاله ال flat 
	 * * [01 => 10 , 02 => 10 , 03 => 15 , .... 12 => 5 ] and so on
	 * * بحيث تكون جاهزة للحسابات علطول ودي عباره عن 
	 * * auto calculate column
	 */
	public function getDistributedPercentages():array 
	{
		return $this->seasonality ? $this->seasonality->distributed_percentages : [];
	}
	
	/**
	 * $seasonalityArray [
	 * 		'type' => 'quarterly' , 
	 * 		'quarterly'=>[],
	 * 		'monthly'=>[]
	 * ]
	 */
	public function syncSeasonality(array $seasonalityArray,string $revenueType,int $companyId ):void
	{
		$type = $seasonalityArray['type']??'flat' ;
		$percentages = $seasonalityArray[$type]??[] ;
		$data  = [
			'type'=>$type,
			'percentages'=>$percentages = array_map(function($percentage) {
					return $percentage === null ? 0 : $percentage;
				}, $percentages) 
		];
		$data = array_merge($data , [
			'company_id'=>$companyId , 
			'model_id'=>$this->id ,  // study id
			'model_name'=>$revenueType
		]);
		if($this->seasonality){
			$this->seasonality->update($data);
			return ;
		}
		$this->seasonality()->create($data);
		
	}
}
