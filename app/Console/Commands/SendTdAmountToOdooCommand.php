<?php

namespace App\Console\Commands;

use App\Models\TimeOfDeposit;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class SendTdAmountToOdooCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:deposit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
		Auth::onceUsingId(64);
		$timeOfDeposit  = TimeOfDeposit::findOrFail(39);
		$actualDepositDate = $timeOfDeposit->deposit_date;
		if(!$actualDepositDate){
			throw new Exception('Break Data Not Found');
		}
		$timeOfDeposit->handleTdOrCdStoreDepositForOdoo(true,$actualDepositDate);
	         return self::SUCCESS;
    }
}
