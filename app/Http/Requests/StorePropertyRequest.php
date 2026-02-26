<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
	protected function prepareForValidation()
	{
		if($this->nature_id == 'unit' || $this->nature_id == 'land'){
			$this->merge([
				'units' =>[],
			]);
		}
		return [];

	}
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $propertyId = $this->route('property') ? $this->route('property')->id : null;
        $natureId = $this->input('nature_id', 'unit');
       
        $rules = [
            // Common fields for all property types
           'nature_id' => 'required|in:unit,land,complex,building',
            'name' => 'required|string|max:255',
            'ownership_id' => 'nullable|integer',
            'country_id' => 'required|integer|exists:property_management.countries,id',
            'governorate_id' => 'required|integer|exists:property_management.governorates,id',
            'city_id' => 'nullable|integer|exists:property_management.cities,id',
        ];
        // Unit and Land specific fields
        if (in_array($natureId, ['unit', 'land'])) {
            $rules = array_merge($rules, [
                'area' => 'required|numeric|min:0',
                'unit_of_measurement' => 'required|string',
                'acquisition_cost' => 'required|numeric|min:0',
                'acquisition_date' => 'required', // Can be array (month picker) or string
                'current_book_value' => 'required|numeric|min:0',
                'month_depreciation' => 'required|numeric|min:0',
                'duration_in_months' => 'required|integer|min:0',
     
            
            ]);
        }
        
        // Complex and Building specific fields (units repeater)
        // if (in_array($natureId, ['complex', 'building'])) {
        //     $rules = array_merge($rules, [
        //         'units' => 'required|array|min:1',
        //         'units.*.id' => 'nullable|integer',
        //         'units.*.name' => 'nullable|string|max:255',
        //         'units.*.code' => 'nullable|string|max:255',
        //         'units.*.area' => 'nullable|numeric|min:0',
        //         'units.*.unit_of_measurement' => 'nullable|string',
        //         'units.*.acquisition_cost' => 'nullable|numeric|min:0',
        //         'units.*.acquisition_date' => 'nullable',
        //         'units.*.current_book_value' => 'nullable|numeric|min:0',
        //         'units.*.month_depreciation' => 'nullable|numeric|min:0',
        //         'units.*.duration_in_months' => 'nullable|integer|min:0',
        //         'units.*.usage_status_id' => 'nullable|integer',
        //         'units.*.taxes' => 'nullable|array',
        //         'units.*.taxes.*.id' => 'nullable|integer',
        //         'units.*.taxes.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        //         'units.*.taxes.*.date' => 'nullable',
        //         'units.*.market_values' => 'nullable|array',
        //         'units.*.market_values.*.id' => 'nullable|integer',
        //         'units.*.market_values.*.value' => 'nullable|numeric|min:0',
        //         'units.*.market_values.*.date' => 'nullable',
        //     ]);
        // }
        return $rules;
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'nature_id' => __('Property Type'),
            'name' => __('Name'),
            'code' => __('Code'),
            'ownership_id' => __('Ownership'),
            'area' => __('Area'),
            'unit_of_measurement' => __('Unit Of Measurement'),
            'acquisition_cost' => __('Acquisition Cost'),
            'acquisition_date' => __('Acquisition Date'),
            'current_book_value' => __('Book Value'),
            'month_depreciation' => __('Monthly Depreciation'),
            'duration_in_months' => __('Depreciation (MTH)'),

            'country_id' => __('Country'),
            'governorate_id' => __('Governorate'),
            'city_id' => __('City'),
            'taxes.*.tax_rate' => __('Tax Rate'),
            'taxes.*.date' => __('Date'),
            'market_values.*.value' => __('Market Value'),
            'market_values.*.date' => __('Date'),
            'units' => __('Units'),
            'units.*.name' => __('Unit Name'),
            'units.*.code' => __('Unit Code'),
            'units.*.area' => __('Unit Area'),
            'units.*.unit_of_measurement' => __('Unit Of Measurement'),
            'units.*.acquisition_cost' => __('Acquisition Cost'),
            'units.*.acquisition_date' => __('Acquisition Date'),
            'units.*.current_book_value' => __('Book Value'),
            'units.*.month_depreciation' => __('Monthly Depreciation'),
            'units.*.duration_in_months' => __('Depreciation (MTH)'),
            'units.*.taxes.*.tax_rate' => __('Tax Rate'),
            'units.*.taxes.*.date' => __('Date'),
            'units.*.market_values.*.value' => __('Market Value'),
            'units.*.market_values.*.date' => __('Date'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // 'nature_id.required' => __('The property type is required.'),
            // 'nature_id.in' => __('The property type must be unit, land, complex, or building.'),
            'code.unique' => __('The code has already been taken for this company.'),
            // 'nature_id.exists' => __('The selected nature is invalid.'),
            'ownership_id.exists' => __('The selected ownership is invalid.'),

            'units.required' => __('At least one unit is required for complex/building.'),
            'units.min' => __('At least one unit is required for complex/building.'),
        ];
    }
    
    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
      
    }
}
