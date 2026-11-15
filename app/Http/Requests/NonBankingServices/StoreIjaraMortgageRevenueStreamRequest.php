<?php

namespace App\Http\Requests\NonBankingServices;

use App\Rules\TotalBreakdownMustBeHundredRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreIjaraMortgageRevenueStreamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


	/**
	 * An ijara is a normal loan, and a normal loan has no grace period (phase 4): the page no longer has the field and
	 * a grace sent anyway is stored as 0.
	 */
	public function prepareForValidation(): void
	{
		$hasSubItems = $this->has('ijaraMortgageBreakdowns.sub_items');
		$rows = $hasSubItems ? $this->input('ijaraMortgageBreakdowns.sub_items', []) : $this->input('ijaraMortgageBreakdowns', []);
		if (!is_array($rows)) {
			return;
		}
		foreach ($rows as $index => $row) {
			if (is_array($row)) {
				$rows[$index]['grace_period'] = 0;
			}
		}
		$this->merge(['ijaraMortgageBreakdowns' => $hasSubItems ? array_merge((array) $this->input('ijaraMortgageBreakdowns'), ['sub_items' => $rows]) : $rows]);
	}

    public function rules()
    {
          return [
            'total_must_be_hundred'=>[new TotalBreakdownMustBeHundredRule('ijaraMortgageBreakdowns')]
        ];
    }
}
