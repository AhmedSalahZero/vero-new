<?php

namespace App\Jobs;

use App\Models\CachingCompany;
use App\Models\CustomerInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CalculateNetBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $companyId ; 
    private $modelName ; 
    
    public function __construct(int $companyId,string $modelName)
    {
        $this->companyId = $companyId ;
        $this->modelName = $modelName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //  $customerInvoices = CustomerInvoice::where('company_id',$this->companyId)->where('net_balance',null)->get();
		//  foreach($customerInvoices as $customerInvoice){
		// 	$customerInvoice->syncNetBalance();
		//  }
        
    }
}
