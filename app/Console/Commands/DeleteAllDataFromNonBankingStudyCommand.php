<?php

namespace App\Console\Commands;

use App\Providers\NonBankingServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Schema;

class DeleteAllDataFromNonBankingStudyCommand extends Command
{

    protected $signature = 'delete:study {study_id} {--connection=non_banking_service : the module database the study lives in (non_banking_service or property_management)}';

  
    protected $description = 'Delete all the data of one study from its own module database';

	/**
	 * * كل module (non-banking ، property management) له قاعدة لوحده ، والدراسات في القاعدتين أرقامها بتتكرر.
	 * * الأمر لازم يمسح من قاعدة الدراسة بس: مسح دراسة عقارات رقم 50 كان بيمسح داتا دراسة non-banking رقم 50.
	 */
	private const MODULE_CONNECTIONS = [NON_BANKING_SERVICE_CONNECTION_NAME, PROPERTY_MANAGEMENT_CONNECTION_NAME];

   
    public function __construct()
    {
        parent::__construct();
    }

  
	protected function tryToDeleteThisTables(string $connection, int $studyId, array $tablesNamesToBeDeleted , int $attemptNumber = 1 ):array
	{
		$tablesCanNotBeDeletedInFirstAttempt = [];
		foreach($tablesNamesToBeDeleted as $tableNameToBeDeleted){
			try{
				if(Schema::connection($connection)->hasColumn($tableNameToBeDeleted,'study_id')){
					DB::connection($connection)->table($tableNameToBeDeleted)->where('study_id',$studyId)->delete();
				}
			}catch(\Exception $e){
				$tablesCanNotBeDeletedInFirstAttempt[]=$tableNameToBeDeleted;
			}
		}
		return $tablesCanNotBeDeletedInFirstAttempt;
		
	}
    public function handle()
    {
		$studyId = (int) $this->argument('study_id') ;
		$connection = (string) $this->option('connection');
		if (! in_array($connection, self::MODULE_CONNECTIONS, true)) {
			$this->error("Unknown module database [{$connection}]: use ".implode(' or ', self::MODULE_CONNECTIONS).'.');
			return self::FAILURE;
		}
		$tablesNamesToBeDeleted = getTableNames($connection);
		$attemptNumber = 1 ;
		while($attemptNumber <= 10 && count($tablesNamesToBeDeleted)){
			$tablesNamesToBeDeleted = $this->tryToDeleteThisTables($connection,$studyId,$tablesNamesToBeDeleted,$attemptNumber);
			$attemptNumber++ ;
		}
		return self::SUCCESS;
	
    }
}
