@php
use App\Models\NonBankingService\FixedAssetName;
@endphp
@foreach(count($rows) ? $rows : [null] as $model)
<div data-repeater-item class="form-group  row align-items-center 
 m-form__group
closest-parent
 repeater_item
common-parent
 ">

    <input type="hidden" name="id" value="{{ isset($model) ? $model->id:0 }}">
    <div class="col-md-2 pr-2 pl-4">
        <label class="form-label">{{ __('Name') }} <br> <span class="visible-hidden">Name</span> </label>
        <x-form.select :selectedValue="isset($model) ? $model->getNameId() :''" :options="FixedAssetName::getGeneralAllForSelect2($company)" :add-new="false" class="select2-select repeater-select expense_category " :all="false" name="name_id"></x-form.select>

        {{-- <div class="kt-input-icon">
            <div class="input-group">
                <input type="text" class="form-control " name="name" value="{{ isset($model) ? $model->getName() : old('name') }}">
    </div>
</div> --}}
</div>

<div class="col-md-1 pr-2 pl-2">
    <label class="form-label text-left">{!! __('Gross <br> Amount') !!} </label>
    <div class="kt-input-icon">
        <div class="input-group">
            <input type="text" class="form-control number_minus_field_1 only-greater-than-or-equal-zero-allowed " name="gross_amount" value="{{ isset($model) ? $model->getGrossAmount() : old('gross_amount',0) }}">
        </div>
    </div>
</div>
<div class="col-md-1 pr-2 pl-2">
    <label class="form-label text-left"> {!! __('Accumulated <br> Depreciation') !!} </label>
    <div class="kt-input-icon">
        <div class="input-group">
            <input type="text" class="form-control  number_minus_field_2 only-greater-than-or-equal-zero-allowed " name="accumulated_depreciation" value="{{ isset($model) ? $model->getAccumulatedDepreciation() : old('accumulated_depreciation',0) }}" step="0.5">
        </div>
    </div>
</div>

<div class="col-md-1 pr-2 pl-2">
    <label class="form-label text-left"> {!! __('Net <br> Amount') !!} </label>
    <div class="kt-input-icon">
        <div class="input-group">
            <input readonly type="text" class="form-control number_minus_number_result only-greater-than-or-equal-zero-allowed " value="{{ isset($model) ? $model->getNetAmount() : old('net_amount',0) }}" step="0.5">
        </div>
    </div>
</div>

<div class="col-md-1 pr-2 pl-2">
    <label class="form-label text-left">{!! __('Monthly <br> Depreciation') !!} </label>
    <div class="kt-input-icon">
        <div class="input-group">
            <input type="text" class="form-control  only-greater-than-or-equal-zero-allowed " name="monthly_depreciation" value="{{ isset($model) ? $model->getMonthlyDepreciation() : old('monthly_depreciation',0) }}" step="0.5">
        </div>
    </div>
</div>


<div class="col-md-1 pr-2 pl-2">
    <label class="form-label text-left">{!! __('Monthly <br> Count') !!}</label>
    <div class="kt-input-icon">
        <div class="input-group">
            <input type="text" class="form-control   only-greater-than-or-equal-zero-allowed " name="monthly_counts" value="{{ isset($model) ? $model->getMonthlyCounts() : old('monthly_counts',60) }}" step="0.5">
        </div>
    </div>
</div>



{{-- <div class="col-md-1 pr-2 pl-2 ">
        <label class="form-label"> {!! __('Administration <br> Depreciation %') !!} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="text" class="form-control only-greater-than-or-equal-zero-allowed hundred-minus-number" name="admin_depreciation_percentage" value="{{ isset($model) ? $model->getAdminDepreciationPercentage() : old('admin_depreciation_percentage',0) }}" step="0.5">
</div>
</div>
</div>

<div class="max-w-10 pr-2 pl-2 ">
    <label class="form-label">{!! __('Manufacturing <br> Depreciation %') !!} </label>
    <div class="kt-input-icon">
        <div class="input-group">
            <input type="text" class="form-control  only-greater-than-or-equal-zero-allowed hundred-minus-number-result" readonly name="manufacturing_depreciation_percentage" value="{{ isset($model) ? $model->getManufacturingDepreciationPercentage() : old('manufacturing_depreciation_percentage',0) }}" step="0.5">
        </div>
    </div>
</div> --}}


{{-- <div class="col-md-1 pr-2 pl-2 allocate-parent">
        <label class="form-label ">{{ __('Allocate') }} <br> <span class="visible-hidden">Allocate</span> </label>
<div class="kt-input-icon ">
    <div class="input-group ">
        <button class="btn btn-primary btn-md allocate-parent-trigger text-nowrap w-full" type="button" data-toggle="modal" data-target="#modal-allocate-{{ $repeaterId }}">{{ __('Allocate') }}</button>
    </div>
</div>

@include('expenses._allocate_modal',['subModel'=>$model])



</div> --}}





<div style="max-width:40px;margin-bottom:-20px" class=" ">
    <div class="d-flex flex-column">
        <label for="" class="visibility-hidden">delete</label>
        <input data-repeater-delete type="button" class="btn btn-danger btn-md ml-2" value="{{ __('X') }}">
    </div>
</div>

</div>
@endforeach
