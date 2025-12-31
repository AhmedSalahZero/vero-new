<?php

use App\Models\Money;
use Illuminate\Database\Seeder;

class MoneySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		for($i = 0 ; $i<=500000;$i++){
			factory(Money::class)->create();
		}
    }
}
