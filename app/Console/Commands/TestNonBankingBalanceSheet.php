<?php

namespace App\Console\Commands;


use App\Http\Controllers\CashFlowStatementController;


use App\Http\Controllers\NonBankingServices\BalanceSheetController;

use App\Http\Controllers\NonBankingServices\CashInOutFlowController;

use App\Http\Controllers\NonBankingServices\IncomeStatementController;
use App\Models\Company;
use App\Models\NonBankingService\FixedAsset;
use App\Models\NonBankingService\Study;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestNonBankingBalanceSheet extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'run:test-non-banking-balance-sheet';
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Test Balance Sheet Is Zero Or Not In Non Banking';

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
		    $this->info('Check Balance Sheet');
			
			$study = Study::find(109);
			
				/**
	 * @var Study $study
	 */
			$study->recalculateAllRevenuesLoans(new Request);
			$fixedAssetTypes = [
				'ffe'=>FixedAsset::FFE,
				'per-employee'=>FixedAsset::PER_EMPLOYEE,
				'new-branch'=>FixedAsset::NEW_BRANCH,
			];

			foreach($fixedAssetTypes as $fixedAssetType){
				$study->recalculateFixedAssets($fixedAssetType);
			}
		
			
			$study->recalculateManpower();
			(new IncomeStatementController)->index($study->company, $study);
			(new CashInOutFlowController)->view(new Request, $study->company, $study);
			$balanceSheetViewVars = $study->getBalanceSheetViewVars();
			
        $this->error('❌ Authentication failed: Invalid credentials or Odoo not reachable.');
        return Command::FAILURE;
	
	$this->info('✅ Connected successfully!');
  

    return Command::SUCCESS;
		
	}

	
}
