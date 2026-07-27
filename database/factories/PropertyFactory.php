<?php

namespace Database\Factories;

use App\Models\PropertyManagement\Category;
use App\Models\PropertyManagement\City;
use App\Models\PropertyManagement\Ownership;
use App\Models\PropertyManagement\Property;
use App\Models\PropertyManagement\PropertyType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PropertyFactory extends Factory
{
 
    public function definition()
    {
		$natureId = $this->faker->randomElement([Property::UNIT]);
		$categoryId = $this->faker->randomElement(Category::where('company_id',31)->get()->pluck('id')->toArray());

		$ownershipId = $this->faker->randomElement(Ownership::where('company_id',31)->get()->pluck('id')->toArray());
		$types = PropertyType::where('category_id',$categoryId)->get();
		$typeId = $this->faker->randomElement($types->pluck('id')->toArray());
		$cityId = $this->faker->randomElement(City::get()->pluck('id')->toArray());
		$marketValues = [
			[
				'date'=>$date = $this->faker->date(),
				'value'=>$this->faker->numberBetween(100000,1000000)
			],
			[
				'date'=>$date = Carbon::make($date)->addMonths(1)->format('Y-m-d'),
				'value'=>$this->faker->numberBetween(100000,1000000)
			],
			[
				'date'=>$date = Carbon::make($date)->addMonths(1)->format('Y-m-d'),
				'value'=>$this->faker->numberBetween(100000,1000000)
			],
			[
				'date'=>$date = Carbon::make($date)->addMonths(1)->format('Y-m-d'),
				'value'=>$this->faker->numberBetween(100000,1000000)
			]
			];
			// $contracts = [
			// 	[
			// 		'property_id'=>$this->faker->name(),
			// 		'tenant_type'=>$this->faker->randomElement(['individual','corporate']),
			// 		'monthly_rent'=>$this->faker->numberBetween(100000,1000000),
			// 		'contract_start_date'=>$this->faker->date(),
			// 		'contract_end_date'=>$this->faker->date(),
			// 	]
			// ];
        return [
            'name' => 'unit '.$this->faker->buildingNumber(),
			'location'=>$this->faker->address(),
			'code'=>$this->faker->unique()->word(5),
            'nature_id' => $natureId,
            'ownership_id' =>$ownershipId,
			'category_id'=>$categoryId,
			'type_id'=>$typeId,
            'area' => $this->faker->numberBetween(50,200),
			'unit_of_measurement'=>'sqm',
			'acquisition_cost'=>$this->faker->numberBetween(100000,1000000),
			'acquisition_date'=>$this->faker->date(),
			'current_book_value'=>$this->faker->numberBetween(100000,1000000),
			'month_depreciation'=>$this->faker->numberBetween(1000,10000),
			'duration_in_months'=>$this->faker->numberBetween(12,120),
			'parent_property_id'=>null,
			'country_id'=>1,
			'governorate_id'=>1,
			'city_id'=>$cityId,
			'company_id'=>31,
			'market_values'=>$marketValues,
        ];
    }
}
