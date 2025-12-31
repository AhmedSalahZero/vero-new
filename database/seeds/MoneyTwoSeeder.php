<?php

use Illuminate\Database\Seeder;

class MoneyTwoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		for($i = 0 ; $i<=159591;$i++){
			DB::table('money2')->create([
				'cash'=>250000,
				'cheque'=>250000,
				'transfer'=>250000,
				'deposit'=>250000,
			]);
		}
    }
}
