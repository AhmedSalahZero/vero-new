@extends('layouts.dashboard')
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title head-title text-primary">
                        {{ camelToTitle($modelName) }} — {{ __('Edit Row') }}
                    </h3>
                </div>
            </div>
        </div>

        <form class="kt-form kt-form--label-right" method="POST"
            action="{{ route('salesGatheringTest.updateCachedRow', array_merge(['company' => $company->id, 'model' => $modelName, 'rowId' => $rowId], $loanId ? ['medium_term_loan_id' => $loanId] : [])) }}">
            @csrf
            @method('PUT')
            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <div class="row">
                        @foreach ($exportableFields as $fieldName => $label)
                            @php
                                $fieldMeta = \App\Helpers\HGlobal::getFieldTypeAndClassFromTitle($modelName, $label);
                                $inputType = $fieldMeta['type'] ?? 'text';
                                $inputClass = trim(($fieldMeta['class'] ?? '') . ' form-control');
                                $value = $row[$fieldName] ?? '';
                                if ($inputType === 'date' && $value) {
                                    try {
                                        $value = \Carbon\Carbon::parse($value)->format('Y-m-d');
                                    } catch (\Exception $e) {
                                        $value = $row[$fieldName] ?? '';
                                    }
                                }
                            @endphp
                            <div class="form-group col-md-6">
                                <label>{{ __($label) }}</label>
                                <input type="{{ $inputType }}"
                                    name="{{ $fieldName }}"
                                    value="{{ $value }}"
                                    class="{{ $inputClass }}"
                                    placeholder="{{ __($label) }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <x-submitting />
        </form>
    </div>
</div>
@endsection
