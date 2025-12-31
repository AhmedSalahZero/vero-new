<?php

namespace App\Console\Commands;

use App\Providers\NonBankingServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Schema;

class DeleteAllDataFromNonBankingStudyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:study {study_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete All Data For Non Banking Study';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
	protected function tryToDeleteThisTables(int $studyId, array $tablesNamesToBeDeleted , int $attemptNumber = 1 ):array
	{
		$tablesCanNotBeDeletedInFirstAttempt = [];
		foreach($tablesNamesToBeDeleted as $tableNameToBeDeleted){
			try{
				if(Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->hasColumn($tableNameToBeDeleted,'study_id')){
					DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table($tableNameToBeDeleted)->where('study_id',$studyId)->delete();
				}
			}catch(\Exception $e){
				$tablesCanNotBeDeletedInFirstAttempt[]=$tableNameToBeDeleted;
			}
		}
		return $tablesCanNotBeDeletedInFirstAttempt;
		
	}
    public function handle()
    {
		$studyId = $this->argument('study_id') ;
		$tablesNamesToBeDeleted = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->getDoctrineSchemaManager()->listTableNames();
		$attemptNumber = 1 ;
		while($attemptNumber <= 10 && count($tablesNamesToBeDeleted)){
			$tablesNamesToBeDeleted = $this->tryToDeleteThisTables($studyId,$tablesNamesToBeDeleted,$attemptNumber);
			$attemptNumber++ ;
		}
	
    }
}
