<?php

namespace Database\Seeders;

use App\Models\PropertyManagement\Contract;
use App\Models\PropertyManagement\Property;
use Illuminate\Database\Seeder;


class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Property::factory()->count(50)->create()->each(function(Property $property){
			$contract = $property->contracts()->create(Contract::factory()->make()->toArray());
			/**
			 * @var Contract $contract
			 */
			$insuranceAmount = $contract->insurance_months_count * $contract->monthly_rent;
			$dates  =generateDatesBetweenTwoDatesWithoutOverflow($contract->contract_start_date, $contract->contract_end_date);
			$contract->rent_revenues = replaceIndexKeysWithDate($contract->calculateRentRevenues(), $dates);
			$contract->rent_collections = replaceIndexKeysWithDate($contract->calculateRentCollections($contract->calculateRentRevenues()['after_vat'],$insuranceAmount), $dates);
			$contract->save();
		});
    }
}
