<?php

namespace Database\Factories\PropertyManagement;

use App\Models\Company;
use App\Models\PropertyManagement\Expense;
use App\Models\PropertyManagement\Study;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition()
    {
        return [
            'study_id' => Study::factory(),
            'company_id' => Company::factory(),
            'model_id' => function (array $attributes) {
                return $attributes['study_id'];
            },
            'model_name' => 'Study',
            'expense_name_id' => $this->faker->numberBetween(1, 100),
            'expense_category' => $this->faker->randomElement([
         //       'operational_expenses',
                'sales_expenses',
           //     'administrative_expenses',
        //        'employee_expenses',
            ]),
            'amount' => $this->faker->numberBetween(1000, 100000),
            'relation_name' => $this->faker->randomElement([
                'fixed_monthly_repeating_amount',
                'percentage_of_sales',
                'cost_per_unit',
                'one_time_expense',
                'expense_per_employee',
            ]),
            'expense_type' => 'Expense',
            'start_date' => 0,
            'end_date' => 12,
            'vat_rate' => $this->faker->randomElement([0, 5, 10, 14]),
            'withhold_tax_rate' => $this->faker->randomElement([0, 2, 5]),
            'payment_terms' => $this->faker->randomElement(['cash', 'net_30', 'net_60', 'customize']),
            'is_deductible' => $this->faker->boolean(),
            'monthly_percentage' => $this->faker->randomFloat(2, 0, 20),
            'monthly_cost_of_unit' => $this->faker->numberBetween(100, 5000),
            'percentage_of' => $this->faker->randomElement(['net_sales', 'gross_sales']),
            'revenue_stream_type' => $this->faker->randomElements([
                'has_leasing',
                'has_ijara_mortgage',
                'has_reverse_factoring',
                'has_portfolio_mortgage',
                'has_direct_factoring',
                'has_micro_finance',
                'has_consumer_finance',
            ], $this->faker->numberBetween(1, 3)),
            'stream_category_ids' => $this->faker->randomElements([1, 2, 3, 4, 5], $this->faker->numberBetween(0, 3)),
            'position_ids' => $this->faker->randomElements([1, 2, 3, 4], $this->faker->numberBetween(0, 2)),
            'amortization_months' => $this->faker->randomElement([6, 12, 24, 36]),
            'custom_collection_policy' => null,
            'increase_rates' => [],
        ];
    }

    /**
     * Indicate that the expense is a fixed monthly repeating amount.
     */
    public function fixedMonthlyRepeating()
    {
        return $this->state(function (array $attributes) {
            return [
                'relation_name' => 'fixed_monthly_repeating_amount',
                'amount' => $this->faker->numberBetween(5000, 50000),
            ];
        });
    }

    /**
     * Indicate that the expense is a percentage of sales.
     */
    public function percentageOfSales()
    {
        return $this->state(function (array $attributes) {
            return [
                'relation_name' => 'percentage_of_sales',
                'monthly_percentage' => $this->faker->randomFloat(2, 1, 15),
                'percentage_of' => 'net_sales',
            ];
        });
    }

    /**
     * Indicate that the expense is cost per unit.
     */
    public function costPerUnit()
    {
        return $this->state(function (array $attributes) {
            return [
                'relation_name' => 'cost_per_unit',
                'monthly_cost_of_unit' => $this->faker->numberBetween(100, 2000),
            ];
        });
    }

    /**
     * Indicate that the expense is a one-time expense.
     */
    public function oneTimeExpense()
    {
        return $this->state(function (array $attributes) {
            return [
                'relation_name' => 'one_time_expense',
                'amount' => $this->faker->numberBetween(10000, 200000),
                'amortization_months' => $this->faker->randomElement([12, 24, 36]),
                'end_date' => null,
            ];
        });
    }

    /**
     * Indicate that the expense is per employee.
     */
    public function perEmployee()
    {
        return $this->state(function (array $attributes) {
            return [
                'relation_name' => 'expense_per_employee',
                'monthly_cost_of_unit' => $this->faker->numberBetween(500, 5000),
                'position_ids' => [1, 2],
            ];
        });
    }

    /**
     * Indicate that the expense has a custom collection policy.
     */
    public function withCustomCollectionPolicy()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_terms' => 'customize',
                'custom_collection_policy' => [
                    '0' => 50,
                    '30' => 30,
                    '60' => 20,
                ],
            ];
        });
    }

    /**
     * Indicate that the expense has increase rates.
     */
    public function withIncreaseRates()
    {
        return $this->state(function (array $attributes) {
            return [
                'increase_rates' => [0, 5, 10],
            ];
        });
    }
}
