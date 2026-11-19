<?php

namespace App\Http\Requests;

use App\Models\NonBankingService\Study;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSecuritizationRequest extends FormRequest
{
	/**
	 * * الوصول للدراسة بيتأكد منه الـ middleware بتاع الـ route (الشركة والدراسة تبعها).
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * * الشهرين لازم يكونوا من شهور الدراسة (study_dates) ، لأن الموديل بيحولهم لـ index منها.
	 * * الفاضي مسموح زي ما كان (الموديل بيتجاهل الصف ده).
	 */
	public function rules(): array
	{
		$studyMonths = $this->studyMonths();

		return [
			'securitizations' => ['nullable', 'array'],
			'securitizations.*.disbursement_date' => ['nullable', Rule::in($studyMonths)],
			'securitizations.*.securitization_date' => ['nullable', Rule::in($studyMonths)],
		];
	}

	public function messages(): array
	{
		return [
			'securitizations.*.disbursement_date.in' => __('Disbursement Date Must Be Within The Study Dates'),
			'securitizations.*.securitization_date.in' => __('Securitization Date Must Be Within The Study Dates'),
		];
	}

	/**
	 * * الفورم بيبعت الشهر Y-m ، و study_dates متخزنة Y-m-d (أول الشهر).
	 *
	 * @return string[]
	 */
	private function studyMonths(): array
	{
		/** @var Study $study */
		$study = $this->route('study');

		return array_map(fn (string $date) => substr($date, 0, 7), array_values($study->getStudyDates()));
	}
}
