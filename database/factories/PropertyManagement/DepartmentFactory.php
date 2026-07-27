<?php

namespace Database\Factories\PropertyManagement;

use App\Models\Company;
use App\Models\PropertyManagement\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement([
                'IT Department',
                'Sales Department',
                'Marketing Department',
                'HR Department',
                'Finance Department',
                'Operations Department',
                'Customer Service Department',
            ]),
            'company_id' => Company::factory(),
        ];
    }
}
