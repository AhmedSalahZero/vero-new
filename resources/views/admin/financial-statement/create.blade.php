@extends('layouts.dashboard')
@section('css')
<x-styles.commons></x-styles.commons>
@endsection
@section('sub-header')
<x-main-form-title :id="'main-form-title'" :class="''">{{ __('Income Statement Planning') }}
</x-main-form-title>
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">

        <form id="form-id" class="kt-form kt-form--label-right" method="POST" enctype="multipart/form-data" action="{{  isset($disabled) && $disabled ? '#' : (isset($model) ? route('admin.update.financial.statement',[$company->id , $model->id]) : $storeRoute)  }}">

            @csrf
            <input type="hidden" name="company_id" value="{{ getCurrentCompanyId()  }}">
            <input type="hidden" name="creator_id" value="{{ \Auth::id()  }}">
            <div class="kt-portlet">


                <div class="kt-portlet__body">

                    {{-- <h2 for="" class="d-bloxk mb-4">{{ __('Information:') }}</h2> --}}



                    <div class="form-group row">
<input type="hidden" name="type" value="forecast">
                        {{-- <div class="col-md-3 mb-4">
                            <label class="form-label font-weight-bold">{{ __('Type') }} </label>
                            <div class="kt-input-icon">
                                <div class="input-group">
                                    <label style="font-size:18px;margin-right:25px;" for="forecast">{{ __('Forecast & Actual') }}</label>
                                    <input style="width:20px;height:16px;margin-right:20px;position:initial !important" id="forecast" value="forecast" class="form-check-input financial-statement-type" type="radio" name="type" checked>

                                    <label style="font-size:18px;margin-right:25px;" for="actual">{{ __('Actual') }}</label>
                                    <input style="width:20px;height:16px;position:initial !important" id="actual" value="actual" class="form-check-input financial-statement-type" type="radio" name="type">

                                </div>

                            </div>

                        </div> --}}

                        <div class="col-md-4 mb-4">
                            <label class="form-label font-weight-bold">{{ __('Name') }} </label>
                            <div class="kt-input-icon">
                                <div class="input-group">
                                    <input id="name" type="text" required class="form-control" name="name" value="{{ isset($model) ? $model->getName() : old('name') }}">
                                </div>
                            </div>
                        </div>


                        <div class="col-md-2 mb-4">
                            <x-form.select :options="$durationTypes" :add-new="false" :label="__('Duration Type')" class="select2-select   " data-filter-type="{{ $type }}" :all="false" name="duration_type" id="{{$type.'_'.'duration_type' }}" :selected-value="isset($model) ? $model->getDurationType() : 'monthly'"></x-form.select>
                        </div>


                        <div class="col-md-1 mb-4">
                            <label class="form-label font-weight-bold">{{ __('Duration') }} </label>
                            <div class="kt-input-icon">
                                <div class="input-group">
                                    <input title="{{ __('Max 24') }}" id="duration" type="number" class="form-control only-greater-than-zero-allowed" name="duration" value="{{ isset($model) ? $model->getDuration() : old('duration',12) }}" step="1">
								</div>
                                  <label id="allowed-duration" class="form-label"> Max 24 </label>
                            </div>
                        </div>
						
						        <div class="col-md-2 mb-2">
                             
								  <x-form.label :class="'label form-label font-weight-bold'" :id="'test-id'">{{ __('Start From') }}</x-form.label>
                                <div class="kt-input-icon">
                                    <div class="input-group date">
                                        <input id="study-start-date" type="text" name="start_from" class="only-month-year-picker date-input form-control " readonly value="{{ isset($model) ? $model->start_from : getCurrentDateForFormDate('date') }}" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="la la-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
							

                        {{-- <div class="col-md-2 mb-4">

                            <x-form.label :class="'label form-label font-weight-bold'" :id="'test-id'">{{ __('Start From') }}</x-form.label>
                            <div class="kt-input-icon">
                                <div class="input-group date">
                                    <input type="text" name="start_from" class="form-control" value="{{ isset($model) ? $model->start_from : getCurrentDateForFormDate('date') }}" id="kt_datepicker_3" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="la la-calendar"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
						
						
						 <div class="col-md-2 mb-4">
                                <label class="form-label font-weight-bold text-nowrap">{{ __('Corporate Taxes %') }} @include('star') </label>
                                <div class="kt-input-icon">
                                    <div class="input-group">
                                        <input type="number" class="form-control only-greater-than-or-equal-zero-allowed" name="corporate_taxes_rate" value="{{ isset($model) ? $model->corporate_taxes_rate : 22.5 }}" step="0.1">
                                    </div>
                                </div>
                            </div>
							
							
							 {{-- <div class="col-md-1 mb-4">
                                <label class="form-label font-weight-bold text-nowrap">{{ __('Salary Taxes %') }} @include('star') </label>
                                <div class="kt-input-icon">
                                    <div class="input-group">
                                        <input type="number" class="form-control only-greater-than-or-equal-zero-allowed" name="salary_taxes_rate" value="{{ isset($model) ? $model->getSalaryTaxesRate() : 0 }}" step="0.1">
                                    </div>
                                </div>
                            </div> --}}
							
							 {{-- <div class="col-md-1 mb-4">
                                <label class="form-label font-weight-bold text-nowrap">{{ __('Social Insurance %') }} @include('star') </label>
                                <div class="kt-input-icon">
                                    <div class="input-group">
                                        <input type="number" class="form-control only-greater-than-or-equal-zero-allowed" name="social_insurance_rate" value="{{ isset($model) ? $model->getSocialInsuranceRate() : 0 }}" step="0.1">
                                    </div>
                                </div>
                            </div> --}}
							


                        <div class="col-lg-12 kt-align-right">
                            <button type="submit" class="btn active-style save-form">{{ __('Save') }}</button>
                            {{-- <button type="reset" class="btn btn-secondary">{{__('Cancel')}}</button> --}}
                        </div>








                        <br>
                        <hr>

                    </div>
                </div>
            </div>

            {{-- <x-create :btn-text="__('Create')" /> --}}



            <!--end::Form-->

            <!--end::Portlet-->
    </div>


</div>

</div>




</div>









</div>
</div>
</form>

</div>
@endsection
@section('js')
<x-js.commons></x-js.commons>

<script>
$(document).on('change','.financial-statement-type',function(){
	validateDuration();
})
$(document).on('change','select[name="duration_type"]',function(){
	validateDuration();
})
$(document).on('change','#duration',function(){
	validateDuration();
})
function validateDuration()
{
	let type = $('input[name="type"]:checked').val();
	type = type ? type : 'forecast';
	let durationType = $('select[name="duration_type"]').val();
	let duration = $('#duration').val();
	let isValid = true ; 
	let allowedDuration = 24 ;
	if(type == 'forecast' && durationType == 'monthly'){
		allowedDuration = 24 ;  
		isValid = duration <= allowedDuration;
	}
	if(type == 'forecast' && durationType == 'quarterly'){
		allowedDuration = 8;
		isValid = duration <= allowedDuration  
	}
	if(type == 'forecast' && durationType == 'semi-annually'){
		allowedDuration = 4 
		isValid = duration <= allowedDuration  
	}
	if(type == 'forecast' && durationType == 'annually'){
		allowedDuration = 2 ;
		isValid = duration <= allowedDuration  
	}
	if(type == 'actual' && durationType == 'monthly'){
		allowedDuration = 36 ;  
		isValid = duration <= allowedDuration;
	}
	if(type == 'actual' && durationType == 'quarterly'){
		allowedDuration = 12 
		isValid = duration <= allowedDuration;  
	}
	if(type == 'actual' && durationType == 'semi-annually'){
		allowedDuration = 6 ;
		isValid = duration <= allowedDuration  
	}
	if(type == 'actual' && durationType == 'annually'){
		allowedDuration =3 
		isValid = duration <= allowedDuration 
	}
	let allowedDurationText = "{{ __('Max') }}";
	
	$('#allowed-duration').html(allowedDurationText + '  '+ allowedDuration)
	
	if(!isValid){
		Swal.fire({
                        icon: 'error'
                        , title: 'Invalid Duration. Allowed [ ' +allowedDuration + ' ]'
                    , })
					
		$('#duration').val(allowedDuration).trigger('change');
		
	}
	
	
}

$(function(){
	$('.financial-statement-type').trigger('change')
	
})
</script>

<script>
    $(document).on('click', '.save-form', function(e) {
        e.preventDefault(); {

            let form = document.getElementById('form-id');
            var formData = new FormData(form);
            $('.save-form').prop('disabled', true);

            $.ajax({
                cache: false
                , contentType: false
                , processData: false
                , url: form.getAttribute('action')
                , data: formData
                , type: form.getAttribute('method')
                , success: function(res) {
                    $('.save-form').prop('disabled', false)

                    Swal.fire({
                        icon: 'success'
                        , title: res.message,

                    });

                    window.location.href = res.redirectTo;




                }
                , complete: function() {
                    $('#enter-name').modal('hide');
                    $('#name-for-calculator').val('');

                }
                , error: function(res) {
                    $('.save-form').prop('disabled', false);
                    $('.submit-form-btn-new').prop('disabled', false)
                    Swal.fire({
                        icon: 'error'
                        , title: res.responseJSON.message
                    , });
                }
            });
        }
    })

</script>
<script>
    $(document).find('.datepicker-input').datepicker({
        dateFormat: 'mm-dd-yy'
        , autoclose: true
    })

</script>
@endsection
