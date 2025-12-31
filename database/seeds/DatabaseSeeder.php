<?php


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
	
    public function run()
    {
		$this->call(TestSeeder::class);
		// $this->call(AccountTypeSeeder::class);
		// for($i = 0 ; $i<=159591;$i++){
		// 	DB::table('money2')->insert([
		// 		'cash'=>250000,
		// 		'cheque'=>250000,
		// 		'transfer'=>250000,
		// 		'deposit'=>250000,
		// 	]);
		// }
    }
}
