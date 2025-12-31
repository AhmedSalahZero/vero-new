<?php

namespace App\Console\Commands;

use App\Models\TablesField;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CreateUploadExcelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:upload {model_name} {fields}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Upload Excel Table Fields And DB';

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
		$modelName = $this->argument('model_name');
		$dbName = convertModelToTableName($modelName);
		$fields = $this->argument('fields');
		if (!Schema::hasTable($dbName)) {
			Schema::create($dbName, function($table) use($fields,$modelName){
				   $table->increments('id');
				   $table->integer('company_id');
				   $table->integer('created_by');
				   foreach($fields as $fieldName => $fieldTitle){
					   $table->string($fieldName);
					   TablesField::create([
						'model_name'=>$modelName , 
						'field_name'=>$fieldName ,
						'view_name'=>$fieldTitle
					   ]);
				   }
				   
				   $table->timestamps();
		   });
	   }

		// if(count($company_id)){
		// 	$companies = Company::whereIn('id',$company_id)->get();
		// }else{
		// 	$companies = Company::all();
		// }
        
       
    }
}
