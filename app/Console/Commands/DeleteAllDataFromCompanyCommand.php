<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Schema;

class DeleteAllDataFromCompanyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:all {company_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete All Data For Company';

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
	protected function tryToDeleteThisTables(int $companyId, array $tablesNamesToBeDeleted , int $attemptNumber = 1 ):array
	{
		$tablesCanNotBeDeletedInFirstAttempt = [];
		foreach($tablesNamesToBeDeleted as $tableNameToBeDeleted){
			try{
				if(Schema::hasColumn($tableNameToBeDeleted,'company_id')){
					DB::table($tableNameToBeDeleted)->where('company_id',$companyId)->delete();
				}
			}catch(\Exception $e){
				$tablesCanNotBeDeletedInFirstAttempt[]=$tableNameToBeDeleted;
			}
		}
		return $tablesCanNotBeDeletedInFirstAttempt;
		
	}
    public function handle()
    {
		$companyId = $this->argument('company_id') ;
		$tablesNamesToBeDeleted = DB::connection()->getDoctrineSchemaManager()->listTableNames();
		$attemptNumber = 1 ;
		while($attemptNumber <= 10 && count($tablesNamesToBeDeleted)){
			$tablesNamesToBeDeleted = $this->tryToDeleteThisTables($companyId,$tablesNamesToBeDeleted,$attemptNumber);
			$attemptNumber++ ;
		}
    }
}
