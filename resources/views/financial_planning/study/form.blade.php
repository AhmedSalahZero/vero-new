@extends('layouts.dashboard')
@section('css')
<x-styles.commons></x-styles.commons>
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">
<style>
    .ui-datepicker-calendar {
        display: none;
    }

</style>
@endsection
@section('sub-header')
<x-main-form-title :id="'main-form-title'" :class="''">{{ $title }}</x-main-form-title>
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">

        <form id="form-id" class="kt-form kt-form--label-right" method="POST" enctype="multipart/form-data" action="{{  $actionRoute  }}">
            @csrf
            @if(isset($model))
            @method('put')
            @endif
            <input type="hidden" name="company_id" value="{{ getCurrentCompanyId()  }}">
            <input type="hidden" name="creator_id" value="{{ \Auth::id()  }}">
            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ __('Study Main Information') }} </h3>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>

                    <div class="form-group  mt-3">
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label class="form-label font-weight-bold">{{ __('Study Name') }} @include('star') </label>
                                <div class="kt-input-icon">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="{{ __('Please Enter Study Name') }}" name="name" value="{{ isset($model) ? $model->getName() : null }}" required>
                                    </div>
                                </div>
                            </div>

                            @php
                            $mainCurrencies[] = $currencies[0]??[];
                            @endphp
                            <div class="col-md-2 mb-4">
                                <x-form.select :is-select2="false" :is-required="true" :options="[['title'=>__($company->getMainFunctionalCurrency()) , 'value'=>$company->getMainFunctionalCurrency()]]" :add-new="false" :label="__('Main Functional Currency')" class=" main_functional_currency" :all="false" name="main_functional_currency" :selected-value="isset($model) ? $model->getMainFunctionalCurrency() : 0"></x-form.select>
                            </div>
                            <div class="col-md-2 mb-4">
                                <x-form.select :is-select2="false" :is-required="true" :options="[['title'=>__('Existing Company' ) , 'value'=>'existing'] , ['title'=>__('New Company') ,'value'=>'new']]" :add-new="false" :label="__('Company Nature')" class=" " :all="false" name="company_nature" :selected-value="isset($model) ? $model->getCompanyNature() : 0"></x-form.select>
                            </div>

                            <div class="col-md-4 mb-4">
                                <x-form.select :options="[
																	
																	  ]" :add-new="false" :is-required="false" :label="__('To Be Consolidated To Financial Plan: (Optional)')" class="select2-select   " :all="false" name="to_be_consolidated_from_study_id" :selected-value="isset($model) ? $model->getToBeConsolidatedFromStudyId() : 0"></x-form.select>
                            </div>




                            <div class="col-md-4 mb-4">
                                <x-form.label :class="'label'" :id="'test-id'">{{ __('Study Start Date') }} @include('star') </x-form.label>
                                <div class="kt-input-icon">
                                    <div class="input-group date">
                                        <input id="study-start-date" type="text" name="study_start_date" class="only-month-year-picker date-input form-control recalc-study-end-date study-start-date  recalate-operation-start-date" readonly value="{{ isset($model) ? $model->getStudyStartDate() : getCurrentDateForFormDate('date') }}" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="la la-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>




                            <div class="col-md-4 mb-4">
                                <x-form.select :options="[
																		1=>['title'=>1 ,'value'=>'1'],
																		66=>['title'=>1.5 ,'value'=>'1.5'],
																		2=>['title'=>2 ,'value'=>'2'],
																		3=>['title'=>3 ,'value'=>'3'],
																		4=>['title'=>4 ,'value'=>'4'],
																		5=>['title'=>5 ,'value'=>'5'],
																		6=>['title'=>6 ,'value'=>'6'],
																		7=>['title'=>7 ,'value'=>'7'],
																	  ]" :add-new="false" :is-required="true" :label="__('Study Duration In Years')" class="select2-select recalc-study-end-date study-duration" :all="false" name="duration_in_years" :selected-value="isset($model) ? $model->getDurationInYears() : 0"></x-form.select>
                            </div>





                            <div class="col-md-4 ">

                                <x-form.label :class="'label'" :id="'test-id'">{{ __('Study End Date') }} </x-form.label>
                                <div class="kt-input-icon">
                                    <div class="input-group date">
                                        <input id="study-end-date" type="hidden" name="study_end_date" class=" form-control" readonly value="{{ isset($model) ? $model->getStudyEndDate() : getCurrentDateForFormDate('date') }}" />
                                        <input id="study-end-date-text" type="text" class=" form-control" readonly value="{{ isset($model) ? $model->getStudyEndDate() : getCurrentDateForFormDate('date') }}" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="la la-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>



                       



                            <div class="col-md-4 mb-4">

                                <x-form.label :class="'label'" :id="'test-id'">{{ __('Operation Start Date') }} </x-form.label>
                                <div class="kt-input-icon">
                                    <div class="input-group date">
                                        <input id="operation-start-date" readonly type="text" name="operation_start_date" class="form-control" readonly value="{{ isset($model) ? $model->getOperationStartDate() : getCurrentDateForFormDate('date') }}" max="{{ date('m-d-Y') }}" id="kt_datepicker_3" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="la la-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>





                            <div class="col-md-4 mb-4">
                                <x-form.select :is-select2="false" :is-required="true" :options="getFinancialMonthsForSelect()" :add-new="false" :label="__('Financial Year Start Month')" class="" :all="false" name="financial_year_start_month" :selected-value="isset($model) ? $model->financialYearStartMonth() : 'january'"></x-form.select>
                            </div>


                            <div class="col-md-4 mb-4">
                                <label class="form-label font-weight-bold">{{ __('Corporate Taxes Rate %') }} @include('star') </label>
                                <div class="kt-input-icon">
                                    <div class="input-group">
                                        <input type="number" class="form-control only-greater-than-or-equal-zero-allowed" name="corporate_taxes_rate" value="{{ isset($model) ? $model->getCorporateTaxesRate() : 0 }}" step="0.1">
                                    </div>
                                </div>
                            </div>
							
							
							<div class="col-md-4 mb-4">
                                <label class="form-label font-weight-bold">{{ __('Annual Salary Increase %') }} @include('star') </label>
                                <div class="kt-input-icon">
                                    <div class="input-group">
                                        <input type="number" class="form-control only-greater-than-or-equal-zero-allowed" name="annual_salary_increase_rate" value="{{ isset($model) ? $model->getAnnualSalaryIncreaseRate() : 0 }}" step="0.1">
                                    </div>
                                </div>
                            </div>
							


                            <div class="col-md-4 mb-4">
                                <label class="form-label font-weight-bold">{{ __('Salary Taxes Rate %') }} @include('star') </label>
                                <div class="kt-input-icon">
                                    <div class="input-group">
                                        <input type="number" class="form-control only-greater-than-or-equal-zero-allowed" name="salary_taxes_rate" value="{{ isset($model) ? $model->getSalaryTaxesRate() : 0 }}" step="0.1">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label font-weight-bold">{{ __('Social Insurance Rate %') }} @include('star') </label>
                                <div class="kt-input-icon">
                                    <div class="input-group">
                                        <input type="number" class="form-control only-greater-than-or-equal-zero-allowed" name="social_insurance_rate" value="{{ isset($model) ? $model->getSocialInsuranceRate() : 0 }}" step="0.1">
                                    </div>
                                </div>
                            </div>



                            <div class="col-md-4 mb-4">
                                <label class="form-label font-weight-bold">{{ __('Revenues Multiplier') }} @include('star') </label>
                                <div class="kt-input-icon">
                                    <div class="input-group">
                                        <input type="number" class="form-control only-greater-than-or-equal-zero-allowed" name="revenue_multiplier" value="{{ isset($model) ? $model->getRevenueMultiplier() : 1 }}" step="0.1">
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4 mb-4">
                                <label class="form-label font-weight-bold">{{ __('EBITDA Multiplier') }} @include('star') </label>
                                <div class="kt-input-icon">
                                    <div class="input-group">
                                        <input type="number" class="form-control only-greater-than-or-equal-zero-allowed" name="ebitda_multiplier" value="{{ isset($model) ? $model->getEbitdaMultiplier() : 0 }}" step="0.1">
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4 mb-4">
                                <label class="form-label font-weight-bold">{{ __('Shareholder Equity Multiplier') }} @include('star') </label>
                                <div class="kt-input-icon">
                                    <div class="input-group">
                                        <input type="number" class="form-control only-greater-than-or-equal-zero-allowed" name="shareholder_equity_multiplier" value="{{ isset($model) ? $model->getShareholderEquityMultiplier() : 0 }}" step="0.1">
                                    </div>
                                </div>
                            </div>


                        </div>
                        <br>
                        <hr>

                    </div>
                </div>
            </div>












            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <div class="row">
                        <div class="col-md-10">
                            <div class="d-flex align-items-center ">
                                <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ __('Revenue Stream Types') }} </h3>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    <div class="row">

                        <div class="form-group row" style="flex:1;">
                            <div class="col-md-12 mt-3">
                                <div class="row">




                                    <div class="col-md-12 mb-0 mt-4 text-left">
                                        <div class="form-group d-inline-block">
                                            <div class="kt-radio-inline">
                                                <label class="mr-3">

                                                </label>
                                                <label class="kt-radio kt-radio--success text-black font-size-18px font-weight-bold">

                                                    <input type="checkbox" value="1" name="has_trading" @if(isset($model) && $model->hasTrading()) checked @endisset
                                                    > {{ __('Trading') }}
                                                    <span></span>
                                                </label>

                                                <label class="kt-radio kt-radio--danger text-black font-size-18px font-weight-bold">
                                                    <input type="checkbox" value="1" name="has_manufacturing" @if(isset($model) && $model->hasManufacturing()) checked @endisset
                                                    > {{ __('Manufacturing') }}
                                                    <span></span>
                                                </label>

                                                <label class="kt-radio kt-radio--primary text-black font-size-18px font-weight-bold">
                                                    <input type="checkbox" value="1" name="has_service" @if(isset($model) && $model->hasService()) checked @endisset
                                                    > {{ __('Service') }}
                                                    <span></span>
                                                </label>






                                                <label class="kt-radio kt-radio--success text-black font-size-18px font-weight-bold">

                                                    <input type="checkbox" value="1" name="has_service_with_inventory" @if(isset($model) && $model->hasServiceWithInventory()) checked @endisset
                                                    > {{ __('Service With Inventory') }}
                                                    <span></span>
                                                </label>






                                            </div>
                                        </div>
                                    </div>
                                </div>



                            </div>

                        </div>




                    </div>

                </div>
            </div>




            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <div class="row">
                        <div class="col-md-10">
                            <div class="d-flex align-items-center ">
                                <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ __('Planning Base') }} </h3>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>



                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <x-form.select :id="'main-planning-base-id'" :options="$mainPlanningBasesForSelector" :add-new="false" :is-required="true" :label="__('Main Planning Base')" class="select2-select" :all="false" name="main_planning_base" :selected-value="isset($model) ? $model->getMainPlanningBase() : 'product_or_service' "></x-form.select>
                        </div>
                        <div class="col-md-4 mb-4">
                            <x-form.select :id="'sub-planning-base-id'" :please-select="true" :options="$mainPlanningBasesForSelector" :add-new="false" :is-required="false" :label="__('Sub Planning Base (Optional)')" class="select2-select" :all="false" name="sub_planning_base" :selected-value="isset($model) ? $model->getSubPlanningBase() : '' "></x-form.select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-weight-bold  ">
                                {{ __('Do You Want To Add') }}
                            </label>

                            <div>
                                <div class="kt-radio-inline">
                                    <label class="mr-3">

                                    </label>
                                    <label class="kt-radio  kt-radio--success text-black font-size-18px font-weight-bold">

                                        <input id="add-new-from-main-planning" type="checkbox" value="1" name="add_new_from_main_planning" @if(isset($model) && $model->addNewFromMainPlanning()) checked @endisset
                                        >

                                        <p id="add-new-from-main-planning-text">
                                            {{ __('Product / Service') }}
                                        </p>
                                        <span></span>
                                    </label>

                                    <label id="add-new-from-sub-planning-parent" class="kt-radio  kt-radio--danger text-black font-size-18px font-weight-bold">
                                        <input id="add-new-from-sub-planning" type="checkbox" value="1" name="add_new_from_sub_planning" @if(isset($model) && $model->addNewFromSubPlanning()) checked @endisset
                                        > 
										<p id="add-new-from-sub-planning-text">
										
										{{ __('Manufacturing') }}
										</p>
                                        <span></span>
                                    </label>







                                </div>
                            </div>

                        </div>



                    </div>
                </div>
















            </div>
			
			
			 <div class="kt-portlet" id="please-add-card-id">
            <div class="kt-portlet__body">
                <div class="row">
                    <div class="col-md-10">
                        <div class="d-flex align-items-center ">

                            <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ __('Please Add ') }}
							<span id="please-add-1">{{ __('Product / Service') }}</span> 
							<span id="please-add-and">&</span>
							<span id="please-add-2">{{ __('Sales Channel') }}</span>
							 </h3>
                        </div>
                    </div>
                    <div class="col-md-2 text-right">
                        <x-show-hide-btn :query="'.add-new-card'"></x-show-hide-btn>
                    </div>
                </div>
                <div class="row">
                    <hr style="flex:1;background-color:lightgray">
                </div>
                <div class="row add-new-card">

                    <div class="form-group row" style="flex:1;">
						@foreach(['first_new_items'=>[
							'name'=>__('Product / Services'),
							'class'=>'th-name-class-1',
							'card-class'=>'card-1-class',
							'newItems'=>$firstNewItems
						],'second_new_items'=>[
							'name'=>__('Sales Channels'),
							'class'=>'th-name-class-2',
							'card-class'=>'card-2-class',
							'newItems'=>$secondNewItems
						]] as $tableId=>$tableOptions)
                        <div class="col-md-4 mt-3" data-repeater-row=".{{ $tableOptions['card-class'] }}">

                                <div id="{{ $tableId }}" class="leasing-repeater-parent">
                                    <div class="form-group2  m-form__group2 row">
                                        <div data-repeater-list="leasingRevenueStreamBreakdown" class="col-lg-12">

                                            @include('financial_planning.study.add_new_product_repeater' , [
												'tableId'=>$tableId,
												'isRepeater'=>true ,
												'canAddNewItem'=>true ,
												'model'=>$model,
												'newItems'=>$tableOptions['newItems'],
												'class'=>$tableOptions['class'],
												'tableHeaderTitle'=>$tableOptions['name']
                                            ])



                                        </div>
                                    </div>

                                </div>
                                
                        </div>
						@endforeach


                    </div>

                </div>
            </div>

        </div>
		
            <x-save-or-back :btn-text="__('Create')" />

    </div>
    </form>

</div>
@endsection
@section('js')
<x-js.commons></x-js.commons>
<script src="/custom/js/non-banking-services/common.js"></script>
<script src="/custom/js/non-banking-services/select2.js"></script>
<script>
    $(document).on('change', '.recalc-study-end-date', function(e) {
        e.preventDefault()
        const studyStartDate = new Date($('.study-start-date').val());
        const studyDuration = parseFloat($('.study-duration option:selected').attr('value'));
        if (studyDuration || studyDuration == '0') {
            const numberOfMonths = (studyDuration * 12) - 1
            let studyEndDate = studyStartDate.addMonths(numberOfMonths)
            let dateFormattedForView = new Date(studyEndDate.getFullYear(), studyEndDate.getMonth() + 1, 0)
            $('#study-end-date-text').val(convertDateToDefaultDateFormat(formatDate(dateFormattedForView)))
            studyEndDate = convertDateToDefaultDateFormat(formatDate(studyEndDate))
            $('#study-end-date').val(studyEndDate).trigger('change')

        }

    })
  
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
	$(document).on('change','#main-planning-base-id',function(){
		const mainPlanningBaseId = $(this).val();
		const mainPlanningBaseName = $(this).find('option:selected').html();
		$('#please-add-1').html(mainPlanningBaseName);
		$('#add-new-from-main-planning-text').html(mainPlanningBaseName);
		$('.th-name-class-1').html(mainPlanningBaseName);
		$('#add-new-from-main-planning').trigger('change')
	})
	$(document).on('change','#sub-planning-base-id',function(){
		const subPlanningBaseId = $(this).val();
		const subPlanningBaseName = $(this).find('option:selected').html();
		if(subPlanningBaseId){
		$('#please-add-and').html('&');
		$('#please-add-2').html(subPlanningBaseName);
		$('.th-name-class-2').html(subPlanningBaseName);
		$('#add-new-from-sub-planning-parent').show();
		$('#add-new-from-sub-planning-text').html(subPlanningBaseName);
			
		}else{
		$('#please-add-and').html('');
		$('#please-add-2').html('');
		$('#add-new-from-sub-planning-text').html(subPlanningBaseName);
		$('#add-new-from-sub-planning-parent').hide();
		
			
		}
		$('#add-new-from-sub-planning').trigger('change')

	})
	$('#add-new-from-main-planning').on('change',function(){
		let checked = $(this).is(":checked");
	
		if(checked){
		$('[data-repeater-row=".card-1-class"]').show();
		$('#please-add-1').show();
		$('#please-add-and').show();
		}else{
		$('[data-repeater-row=".card-1-class"]').hide();
				$('#please-add-1').hide();
				$('#please-add-and').hide();
		}
		let firstIsChecked = $('#add-new-from-main-planning').is(":checked");
		let secondIsChecked = $('#add-new-from-sub-planning').is(":checked");
		if(!firstIsChecked && !secondIsChecked){
			$('#please-add-card-id').hide();
		}else{
			$('#please-add-card-id').show();
			
		}
	})
	$('#add-new-from-sub-planning').on('change',function(){
		let checked = $(this).is(":checked");
	
		if(checked){
		$('[data-repeater-row=".card-2-class"]').show();
			$('#please-add-2').show();
		$('#please-add-and').show();
		}else{
		$('[data-repeater-row=".card-2-class"]').hide();
		$('#please-add-2').hide();
		$('#please-add-and').hide();
		}
		let firstIsChecked = $('#add-new-from-main-planning').is(":checked");
		let secondIsChecked = $('#add-new-from-sub-planning').is(":checked");
		if(!firstIsChecked && !secondIsChecked){
			$('#please-add-card-id').hide();
		}else{
			$('#please-add-card-id').show();
			
		}
		
	})
</script>
<script>
$(function(){
	$('#main-planning-base-id').trigger('change');
	$('#sub-planning-base-id').trigger('change');
})
</script>
@endsection
