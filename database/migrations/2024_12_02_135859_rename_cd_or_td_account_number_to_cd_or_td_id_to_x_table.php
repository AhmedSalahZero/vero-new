<?php

use App\Models\AccountType;
use App\Models\CertificatesOfDeposit;
use App\Models\TimeOfDeposit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class RenameCdOrTdAccountNumberToCdOrTdIdToXTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['letter_of_credit_issuances','letter_of_guarantee_issuances'] as $tableName){
			Schema::table($tableName, function (Blueprint $table) use($tableName){
				if(Schema::hasColumn($tableName,'cd_or_td_account_number')){
					$table->renameColumn('cd_or_td_account_number','cd_or_td_id');
				}
			});	
			DB::table($tableName)->get()->each(function($letterOfIssuance) use($tableName){
				$accountType = AccountType::find($letterOfIssuance->cd_or_td_account_type_id);
				if(is_null($accountType)){
					return ;
				}
				if($accountType->isCertificateOfDeposit()){
					$cdOrTd = CertificatesOfDeposit::findByAccountNumber($letterOfIssuance->cd_or_td_id,$letterOfIssuance->company_id);
					$cdOrTdId = $cdOrTd ? $cdOrTd->id : null ;
					DB::table($tableName)->where('id',$letterOfIssuance->id)->update([
						'cd_or_td_id'=>$cdOrTdId  
					]);
				}else{
					$cdOrTd = TimeOfDeposit::findByAccountNumber($letterOfIssuance->cd_or_td_id,$letterOfIssuance->company_id);
					$cdOrTdId = $cdOrTd ? $cdOrTd->id : null ;
					DB::table($tableName)->where('id',$letterOfIssuance->id)->update([
						'cd_or_td_id'=>$cdOrTdId  
					]);
				}
			});
		}
		
		Artisan::call('refresh:permissions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cd_or_td_id_to_x', function (Blueprint $table) {
            //
        });
    }
}
