<?php

namespace Database\Factories\PropertyManagement;

use App\Models\Company;
use App\Models\PropertyManagement\Study;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudyFactory extends Factory
{
    protected $model = Study::class;

    public function definition()
    {
        // Use fixed dates for easier testing
        $startDate = '2024-01-01';
        $endDate = '2026-12-01';
        $durationYears = 3;

        // Generate monthly dates array
        $studyDates = $this->generateMonthlyDates($startDate, $endDate);
        $operationDates = $studyDates; // Same as study dates for simplicity

        return [
            'name' => $this->faker->words(3, true) . ' Study',
            'company_id' => Company::factory(),
            'study_start_date' => $startDate,
            'study_end_date' => $endDate,
            'operation_start_date' => $startDate,
            'operation_start_month' => 0,
            'financial_year_start_month' => 1,
            'duration_in_years' => $durationYears,
            'corporate_taxes_rate' => 22.5,
            'salary_taxes_rate' => 10.0,
            'social_insurance_rate' => 12.0,
        
            'perpetual_growth_rate' => 3.0,
            'shareholder_equity_multiplier' => 1.5,
            'study_dates' => $studyDates,
            'operation_dates' => $operationDates,
        ];
    }

    /**
     * Generate array of monthly dates between start and end date
     */
    protected function generateMonthlyDates(string $startDate, string $endDate): array
    {
        $dates = [];
        $current = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        
        $index = 0;
        while ($current <= $end) {
            $dates[$index] = $current->format('Y-m-d');
            $current->modify('+1 month');
            $index++;
        }
        
        return $dates;
    }
}
