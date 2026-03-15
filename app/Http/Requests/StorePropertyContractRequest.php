<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            // Contract fields
            'property_id' => 'required|integer|exists:property_management.properties,id',
            'tenant_id' => 'required|integer|exists:property_management.tenants,id',
          
            'monthly_rent' => 'required|numeric|gt:0',
            'contract_start_date' => 'required',
            'contract_end_date' => 'required|after:contract_start_date',
            'collection_interval' => 'required|in:monthly,quarterly,semi-annually,annually',
            'insurance_months_count' => 'required|integer|gt:0',
            'annually_increase_rate' => 'nullable|numeric|min:0|max:100',
            'collection_policy' => 'nullable|string',

            // Installment fields (optional)
            // 'installment' => 'nullable|array',
            // 'installment.installment_type' => 'required_with:installment|in:regular,variable',

            // // Regular installment fields
            // 'installment.installment_amount' => 'required_if:installment.installment_type,regular|nullable|numeric|gt:0',
            // 'installment.start_date' => 'required_if:installment.installment_type,regular',
            // 'installment.end_date' => 'required_if:installment.installment_type,regular',
            // 'installment.number_of_months' => 'required_if:installment.installment_type,regular|nullable|integer|gt:0',

            // // Optional annual fields for regular installment
            // 'installment.annual_date' => 'nullable',
            // 'installment.annual_amount' => 'nullable|numeric|gt:0',
            // 'installment.annual_count' => 'nullable|integer|gt:0',

            // // Variable installment fields
            // 'installment.installment_details' => 'required_if:installment.installment_type,variable|nullable|array',
            // 'installment.installment_details.*.date' => 'required',
            // 'installment.installment_details.*.amount' => 'required|numeric|gt:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'property_id.required' => __('Property is required'),
            'property_id.exists' => __('Selected property does not exist'),
            'tenant_id.required' => __('Tenant id is required'),
            'tenant_id.exists' => __('Selected tenant does not exist'),
           
            // 'tenant_name.max' => __('Tenant name must not exceed 255 characters'),
            'tenant_type.required' => __('Tenant type is required'),
            'tenant_type.in' => __('Tenant type must be either individual or corporate'),
            'monthly_rent.required' => __('Monthly rent is required'),
            'monthly_rent.gt' => __('Monthly rent must be greater than 0'),
            'contract_start_date.required' => __('Contract start date is required'),
            'contract_end_date.required' => __('Contract end date is required'),
            'contract_end_date.after' => __('Contract end date must be after start date'),
            'collection_interval.required' => __('Collection interval is required'),
            'insurance_months_count.required' => __('Insurance months count is required'),
            'insurance_months_count.gt' => __('Insurance months count must be greater than 0'),
            'installment.installment_type.required_with' => __('Installment type is required'),
            'installment.installment_amount.required_if' => __('Installment amount is required for regular installments'),
            'installment.start_date.required_if' => __('Start date is required for regular installments'),
            'installment.end_date.required_if' => __('End date is required for regular installments'),
            'installment.number_of_months.required_if' => __('Number of months is required for regular installments'),
            'installment.installment_details.required_if' => __('Installment details are required for variable installments'),
        ];
    }
}
