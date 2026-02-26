<?php

namespace Database\Factories\PropertyManagement;

use App\Models\Company;
use App\Models\PropertyManagement\Property;
use App\Models\PropertyManagement\PropertyTax;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyTaxFactory extends Factory
{
    protected $model = PropertyTax::class;

    public function definition()
    {
        return [
            'property_id' => Property::factory(),
            'tax_rate' => $this->faker->randomFloat(2, 0, 25),
            'date' => $this->faker->date(),
            'company_id' => Company::factory(),
        ];
    }
}
