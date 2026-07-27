<?php

namespace Database\Factories\PropertyManagement;

use App\Models\Company;
use App\Models\PropertyManagement\ExpenseName;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseNameFactory extends Factory
{
    protected $model = ExpenseName::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement([
                'Office Rent',
                'Utilities',
                'Insurance',
                'Marketing',
                'Training',
                'Travel',
                'Supplies',
                'Equipment',
                'Maintenance',
                'Consulting',
                'Legal Fees',
                'Subscriptions',
                'Communication',
            ]),
            'expense_type' => $this->faker->randomElement([
                'operational_expenses',
                'sales_expenses',
                'administrative_expenses',
                'employee_expenses',
            ]),
            'company_id' => Company::factory(),
            'is_employee_expense' => $this->faker->boolean(30),
            'is_branch_expense' => $this->faker->boolean(30),
        ];
    }

    /**
     * Indicate that the expense name is for employees.
     */
    public function forEmployees()
    {
        return $this->state(function (array $attributes) {
            return [
                'expense_type' => 'employee_expenses',
                'is_employee_expense' => 1,
                'is_branch_expense' => 0,
            ];
        });
    }

    /**
     * Indicate that the expense name is for branches.
     */
    public function forBranches()
    {
        return $this->state(function (array $attributes) {
            return [
                'expense_type' => 'operational_expenses',
                'is_employee_expense' => 0,
                'is_branch_expense' => 1,
            ];
        });
    }
}
