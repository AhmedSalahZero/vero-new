<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Schema;

class EmptyCashveroCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'empty:cashvero';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Empty CashVero';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
		$cashVeroTables = getCashVeroTableNames();
		
		foreach($cashVeroTables as $tableName){
			if(Schema::hasColumn($tableName,'company_id')){
				DB::table($tableName)->where('company_id','!=',41)->delete();
			}
		}
		
       
    }	
}
