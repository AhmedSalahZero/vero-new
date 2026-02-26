<?php

namespace Database\Factories;

use App\Models\PropertyManagement\Property;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
           'property_id'=>$this->faker->randomElement(Property::get()->pluck('id')->toArray()),
		   'tenant_name'=>$this->faker->name(),
		   'tenant_type'=>$this->faker->randomElement(['individual','corporate']),
		   'monthly_rent'=>$this->faker->numberBetween(100000,1000000),
		   'contract_start_date'=>$date = $this->faker->dateTimeBetween('-1 year', '+2 years')->format('Y-m-d'),
		   'contract_end_date'=>Carbon::make($date)->addMonth(12)->format('Y-m-d'),
		   'contract_currency'=>'EGP',
		   'collection_currency'=>'EGP',
		   'collection_interval'=>'monthly',
		   'insurance_months_count'=>2,
		   'insurance_amount'=>$this->faker->numberBetween(100000,1000000),
		   'status'=>'running',
		   'annually_increase_rate'=>$this->faker->numberBetween(5,20),
		   'company_id'=>31
        ];
    }
}
