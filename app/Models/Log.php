<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Log extends Model
{
	protected $guarded = ['id'];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public static function storeNewLogRecord(string $type , ?User $user = null , ?string $sectionName = null ):?Log
	{
		$user = $user ?: Auth()->user();
		$message = Log::generateLogMessage($type,$sectionName);
		if(is_null(getCurrentCompanyId()) || !is_numeric(getCurrentCompanyId())){
			return null;
		}
		
		$lastRecordFromTheSameActivity = Log::where('company_id',getCurrentCompanyId())->where('activity',$message)->where('user_id',$user->id)->latest()->first();
		$lastRecordForAllActivities = Log::where('company_id',getCurrentCompanyId())->where('user_id',$user->id)->latest()->first();
		if(!$lastRecordForAllActivities || !$lastRecordFromTheSameActivity || ($lastRecordFromTheSameActivity->id != $lastRecordForAllActivities->id )){
			return Log::create([
				'activity'=>$message,
				'created_at'=>now(),
				'user_id'=>$user->id,
				'company_id' => getCurrentCompanyId() 
			]);
		}
		return null ;
		
	}
		
	public function company()
	{
		return $this->belongsTo(Company::class , 'company_id','id');
	}

	public static function generateLogMessage(string $type , ?string $sectionName=null):string
	{
		return [
			'successLogin'=> __('Logged In !'),
			'successLogout'=> __('Logged Out'),
			'enterSection'=>__('Enter Section') . ' [ ' . $sectionName . ' ]'
		][$type];
	}
}

// money_received
