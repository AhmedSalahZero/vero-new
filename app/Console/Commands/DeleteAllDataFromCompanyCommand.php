<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Schema;

class DeleteAllDataFromCompanyCommand extends Command
{

    protected $signature = 'delete:all {company_id}';

    protected $description = 'Delete All Data For Company';

	/**
	 * * الشركة ليها داتا في قاعدة كل module كمان (non-banking ، property management): كانت بتتمسح من القاعدة
	 * * الرئيسية بس ، فدراساتها في القواعد التانية كانت بتفضل من غير شركة.
	 */
	private const MODULE_CONNECTIONS = [NON_BANKING_SERVICE_CONNECTION_NAME, PROPERTY_MANAGEMENT_CONNECTION_NAME];


    public function __construct()
    {
        parent::__construct();
    }

	protected function tryToDeleteThisTables(int $companyId, array $tablesNamesToBeDeleted , int $attemptNumber = 1, ?string $connection = null ):array
	{
		$tablesCanNotBeDeletedInFirstAttempt = [];
		foreach($tablesNamesToBeDeleted as $tableNameToBeDeleted){
			try{
				if(Schema::connection($connection)->hasColumn($tableNameToBeDeleted,'company_id')){
					DB::connection($connection)->table($tableNameToBeDeleted)->where('company_id',$companyId)->delete();
				}
			}catch(\Exception $e){
				$tablesCanNotBeDeletedInFirstAttempt[]=$tableNameToBeDeleted;
			}
		}
		return $tablesCanNotBeDeletedInFirstAttempt;

	}

	/** every study of the company in a module database (delete:study, its own database only), then the company's rows left there */
	protected function deleteFromModuleDatabase(int $companyId, string $connection): void
	{
		if (! Schema::connection($connection)->hasTable('studies') || ! Schema::connection($connection)->hasColumn('studies', 'company_id')) {
			return;
		}
		foreach (DB::connection($connection)->table('studies')->where('company_id', $companyId)->pluck('id') as $studyId) {
			$this->call('delete:study', ['study_id' => $studyId, '--connection' => $connection]);
		}
		$tablesNamesToBeDeleted = getTableNames($connection);
		$attemptNumber = 1 ;
		while($attemptNumber <= 10 && count($tablesNamesToBeDeleted)){
			$tablesNamesToBeDeleted = $this->tryToDeleteThisTables($companyId,$tablesNamesToBeDeleted,$attemptNumber,$connection);
			$attemptNumber++ ;
		}
	}

    public function handle()
    {
		$companyId = (string) $this->argument('company_id') ;
		if (! ctype_digit($companyId) || (int) $companyId <= 0) {
			// a typo such as "12abc" would otherwise delete company 12
			$this->error("company_id must be a positive whole number ({$companyId} given).");
			return self::FAILURE;
		}
		$companyId = (int) $companyId ;
		foreach (self::MODULE_CONNECTIONS as $connection) {
			$this->deleteFromModuleDatabase($companyId, $connection);
		}
		$tablesNamesToBeDeleted = getTableNames();
		$attemptNumber = 1 ;
		while($attemptNumber <= 10 && count($tablesNamesToBeDeleted)){
			$tablesNamesToBeDeleted = $this->tryToDeleteThisTables($companyId,$tablesNamesToBeDeleted,$attemptNumber);
			$attemptNumber++ ;
		}
    }
}
