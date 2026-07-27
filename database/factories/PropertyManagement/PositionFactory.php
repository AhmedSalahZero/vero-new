<?php

namespace Database\Factories\PropertyManagement;

use App\Models\Company;
use App\Models\PropertyManagement\Department;
use App\Models\PropertyManagement\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement([
                'Developer',
                'Senior Developer',
                'Manager',
                'Assistant Manager',
                'Analyst',
                'Coordinator',
                'Specialist',
                'Director',
                'Team Lead',
                'Executive',
            ]),
            'department_id' => Department::factory(),
            'company_id' => Company::factory(),
        ];
    }
}
