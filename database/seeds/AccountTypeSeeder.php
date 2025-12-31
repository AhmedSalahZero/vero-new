<?php

use App\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		foreach([
			[
				'name_en'=>$name_en ='Current Account',
				'name_ar'=>'الحساب الجاري',
				'type'=>'debit',
				'slug'=>Str::slug($name_en),
				'model_name'=>'FinancialInstitutionAccount'
				] ,
				[
					'name_en'=> $name_en='Time Deposit (T/D)',
					'name_ar'=>'حساب الودائع لأجل',
					'type'=>'debit',
					'slug'=>Str::slug($name_en),
					'model_name'=>'TimeDeposit'
				],[
					'name_en'=> $name_en = 'Certificate Of Deposit (C/D)',
					'name_ar'=>'شهادات الاستثمار',
					'type'=>'debit',
					'slug'=>Str::slug($name_en),
					'model_name'=>'CertificatesOfDeposit'
				],
				[
					'name_en'=>$name_en='Clean Overdraft',
					'name_ar'=>'حد جاري مدين بدون ضمان',
					'type'=>'credit',
					'slug'=>Str::slug($name_en),
					'model_name'=>'CleanOverdraft'
				],
				[
					'name_en'=>$name_en = 'Overdraft Against Commercial Paper',
					'name_ar'=>'حد جاري مدين بضمان شيكات',
					'type'=>'credit',
					'slug'=>Str::slug($name_en),
					'model_name'=>'OverdraftAgainstCommercialPaper'
				],
				[
					'name_en'=>$name_en='Overdraft Against Assignment Of Contracts',
					'name_ar'=>'حد جاري مدين مقابل تنازل عن العقود',
					'type'=>'credit',
					'slug'=>Str::slug($name_en),
					'model_name'=>'OverdraftAgainstASsignmentOfContract'
				],
				[
					'name_en'=>$name_en='Discounting Cheques',
					'name_ar'=>'حد خصم شيكات',
					'type'=>'credit',
					'slug'=>Str::slug($name_en),
					'model_name'=>'DiscountCheque'
				],
				[
					'name_en'=>$name_en='Letter Of Guarantee (LGs)',
					'name_ar'=>'حد خطابات ضمان',
					'type'=>'credit',
					'slug'=>Str::slug($name_en),
					'model_name'=>'LetterOfGuarantee'
					
				],
				[
					'name_en'=>$name_en='Letter Of Credit (LCs)',
					'name_ar'=>'حد إعتمادات مستندية',
					'type'=>'credit',
					'slug'=>Str::slug($name_en),
					'model_name'=>'LetterOfCredit'
					
			],
			
			] as $item){
				factory(AccountType::class)->create($item);
			}
		
    }
}
