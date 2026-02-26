<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateIncomeStatementCalculationForTypesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $financialStatementAble ; 
    protected $type ; 
    public function __construct($financialStatementAble,$type)
    {
		$this->financialStatementAble = $financialStatementAble;
		$this->type = $type ;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		$this->financialStatementAble->refreshCalculationFor($this->type);
		$this->financialStatementAble->{'is_caching_'.$this->type} = 0 ;
		$this->financialStatementAble->save();
    }
}
