@extends('layouts.dashboard')
@section('css')
<x-styles.commons></x-styles.commons>
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<style>
.js-parent-to-table{
	overflow:scroll
}

</style>
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">
@endsection
@section('sub-header')
<x-main-form-title :id="'main-form-title'" :class="''">{{ $title }}</x-main-form-title>

{{-- <x-navigators-dropdown :navigators="$navigators"></x-navigators-dropdown> --}}

@endsection
@section('content')
@php
	$months = $study->getMicrofinanceMonths() ;
@endphp
<div class="row">
    <div class="col-md-12">

        <form id="form-id" class="kt-form kt-form--label-right" method="POST" enctype="multipart/form-data" action="{{  isset($disabled) && $disabled ? '#' :  $storeRoute  }}">

            @csrf
            <input type="hidden" name="company_id" value="{{ getCurrentCompanyId()  }}">
            <input type="hidden" name="creator_id" value="{{ \Auth::id()  }}">
            <input type="hidden" name="study_id" id="study-id-js" value="{{ $study->id }}">

            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                        {{ __('Existing Branches Product Mix') }}
                    </h3>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    <div class="row reserve-and-profit-distribution-assumption">


                        <div class="table-responsive">
                            <table class="table table-white repeater-class repeater ">
                                <thead>
                                    <tr>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Product <br> Name') !!}</th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Tenor <br> (Months)') !!} </th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Early Payment <br> Installments Count') !!}</th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Avg <br> Amount') !!}</th>
                                        @if(!$model->isMonthlyStudy())
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Annual <br> Increase %') !!}</th>
                                        @endif
                                        {{-- <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Product <br> Mix %') !!}</th> --}}
                                        <th class="min-w-90 form-label font-weight-bold text-center align-middle   header-border-down">{!! __('Funded <br> By') !!}</th>
                                        {{-- <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{{ __('Allocations') }}</th> --}}
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! $yearOrMonthFormatted .' <br> ' . __('Flat Rates %') !!}</th>
                                        @endforeach

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                    @php
                                   
                                    $subModel = $model->microfinanceByBranchProductMixes->where('microfinance_product_id',$product->id)->first();
                                    @endphp
                                    <input type="hidden" name="microfinanceByBranchProductMixes[{{ $product->id }}][id]" value="{{ $subModel  ? $subModel->id : 0 }}">
                                    {{-- <input type="hidden" name="microfinanceByBranchProductMixes[{{ $product->id }}][type]" value="{{ $branchPlanningBaseType }}"> --}}
                                    <input type="hidden" name="microfinanceByBranchProductMixes[{{ $product->id }}][microfinance_product_id]" value="{{ $product->id }}">
                                    <input type="hidden" name="microfinanceByBranchProductMixes[{{ $product->id }}][company_id]" value="{{ $company->id }}">

                                    <tr data-repeat-formatting-decimals="0" data-repeater-style>
                                        <td class="td-classes">
                                            <div>

                                                <input value="{{ $product->getName() }}" disabled="" class="form-control text-left min-w-300" type="text">
                                            </div>

                                        </td>

                                        <td>
                                            @php
                                            $currentVal = $subModel ? $subModel->getTenor() : 12;
                                            $tenorClass = 'tenor-class'.$product->id
                                            // $product->name
                                            @endphp

                                            <x-repeat-right-dot-inputs :formattedInputClasses="'min-w-90'" :numberFormatDecimals="0" :removeThreeDots="true" :removeCurrency="true" :currentVal="number_format($currentVal,0)" :classes="'only-greater-than-zero-allowed '.$tenorClass" :is-percentage="false" :name="'microfinanceByBranchProductMixes['.$product->id.'][tenor]'" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                        </td>


<td>
                                            @php
                                            $currentVal = $subModel ? $subModel->getEarlyPaymentInstallmentCounts() : 0 ;
                                            @endphp
                                            <x-repeat-right-dot-inputs :numberFormatDecimals="0" :removeThreeDots="true" :removeCurrency="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="'microfinanceByBranchProductMixes['.$product->id.'][early_payment_installment_counts]'" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                        </td>
										
                                        <td>
                                            @php
                                            $currentVal = $subModel ? $subModel->getAvgAmount() : 0 ;
                                            @endphp
                                            <x-repeat-right-dot-inputs :removeThreeDots="true" :removeCurrency="true" :currentVal="$currentVal" :classes="'only-greater-than-zero-allowed'" :is-percentage="false" :name="'microfinanceByBranchProductMixes['.$product->id.'][avg_amount]'" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                        </td>

                                        @if(!$model->isMonthlyStudy())
									
                                        <td>
                                            <div class="d-flex align-items-center increase-rate-parent">
                                                <button class="btn btn-primary btn-md text-nowrap increase-rate-trigger-btn" type="button" data-toggle="modal">{{ __('Increase Rates') }}</button>
                                                <x-modal.increase-rates  :name="'microfinanceByBranchProductMixes['.$product->id.'][increase_rates]'" :study="$study" :subModel="isset($subModel) ? $subModel : null "></x-modal.increase-rates>
                                            </div>
                                        </td>
                                        @endif

                                        <td>
                                            <x-form.select :required="true" :label="''" :pleaseSelect="false" :selectedValue="isset($subModel) ? $subModel->getFundedBy():0" :options="getMicrofinanceFundingBySelector()" :add-new="false" class="select2-select min-w-120 repeater-select  " :all="false" name="microfinanceByBranchProductMixes[{{ $product->id }}][funded_by]"></x-form.select>
                                        </td>



                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td data-product-id="{{ $product->id }}">


                                            @php
                                            $currentVal = $subModel ? $subModel->getFlatRateAtYearOrMonthIndex($yearOrMonthAsIndex) : 0;
                                            @endphp



                                            @php
                                            $currentModalId = 'current-modal-id'.($columnIndex+1) . $product->id
                                            @endphp
                                            <x-repeat-with-calc :currentModalId="$currentModalId" :showIcon="true" :numberFormatDecimals="2" :formattedInputClasses="'calcField flat-rate-input'" :mark="'%'" :removeThreeDots="false" :removeCurrency="true" :currentVal="number_format($currentVal,1)" :classes="''" :is-percentage="true" :name="'microfinanceByBranchProductMixes['.$product->id.'][flat_rates]['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-with-calc>
                                            {{-- <x-repeat-with-calc :currentModalId="$currentModalId" :showIcon="true" :numberFormatDecimals="2" :formattedInputClasses="'calcField flat-rate-input'" :mark="'%'" :removeThreeDots="false" :removeCurrency="true" :currentVal="number_format($currentVal,1)" :classes="''" :is-percentage="true" :name="'flat_rates['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-with-calc> --}}




                                            <div class="modal fade " id="{{ $currentModalId }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" style="color:#0741A5 !important" id="exampleModalLongTitle"> {{ __('Decreasing Rate') }} % </h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="customize-elements">
                                                                <table class="table">
                                                                    <thead>

                                                                        <tr>


                                                                            <th class="text-center  text-capitalize th-main-color">{{ __('Flat Rate') }}</th>
                                                                            <th class="text-center  text-capitalize th-main-color">{{ __('Decreasing Rate') }}</th>





                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>


                                                                        <tr>
                                                                            <td class="">
                                                                                <div class="kt-input-icon ">
                                                                                    <div class="input-group">
                                                                                        <input disabled type="text" step="0.1" class="form-control ignore-global-style flat-rate-id" value="{{ 0 }}">
                                                                                    </div>
                                                                                </div>
                                                                            </td>

                                                                            <td class="">
                                                                                <div class="kt-input-icon ">
                                                                                    <div class="input-group">
                                                                                        <input disabled type="text" step="0.1" class="form-control ignore-global-style decreasing-rate-id" value="{{ 0 }}">
                                                                                    </div>
                                                                                </div>
                                                                            </td>

                                                                        </tr>




                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-primary " data-dismiss="modal">{{ __('Close') }} </button>
                                                        </div>

                                                    </div>

                                                </div>
                                            </div>

                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach



                                    </tr>
                                    @endforeach




                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>
            </div>
			
			  <div class="kt-portlet " style="margin-bottom:5px;">
                <div class="kt-portlet__body">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ __('New Loan Officers Cases Projection') }} </h3>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>

                    @php
                    $tableId = 'microfinanceByBranchProductMixes';
                    $repeaterId = $tableId.'_repeater';
                    @endphp
                    <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
                    <x-tables.repeater-table :hideByDefault="false" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden overflow-scroll'" :hide-add-btn="true" :repeater-with-select2="true" :parentClass="''" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=false">
                        <x-slot name="ths">
                            {{-- <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" header-border-down" :title="__('Loan Officer <br> Count')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th> --}}
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="max-w-200 header-border-down" :title="__('Position')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th>
                            @for($i = 0 ; $i<= $months ; $i++) <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" interval-class header-border-down " :title="__('Mth-').$i . ' <br> ' .__('Cases #')">
                                </x-tables.repeater-table-th>
                                @endfor
                        </x-slot>
                        <x-slot name="trs">
                            @php
                     
                     	  $rows =  [null,null] ;
                            @endphp
                            @foreach($rows as $currentIndex=>$subModel)
                            @php
                            $isSeniors = [
                            0 => [
                            'is_senior'=>1 ,
                            'title'=> __('Senior Loan Officer'),
							'name'=>'product_mix_senior_loan_officers'
                            ],
                            1=> [
                            'is_senior'=>0 ,
                            'title'=>__('Loan Officer'),
							'name'=>'product_mix_loan_officers'
                            ]
                            ][$currentIndex];
                            $isSenior = $isSeniors['is_senior'];
                            $title = $isSeniors['title'];
							$name = $isSeniors['name'];
                            @endphp

                            <tr data-repeater-style>
                        
                                <td>
                                    <input readonly value="{{ $title }}" class="form-control" type="text">

                                </td>
                                @php
                                $columnIndex = 0 ;
								
                                @endphp
                                @for($i = 0 ; $i<= $months ; $i++) @php $currentVal= $study->{$name}[$i]??0 ;

                                    @endphp
                                    <td>
                                        <x-repeat-right-dot-inputs :numberFormatDecimals="0" :multiple="true" :removeCurrency="true" :name="$name.'['.$i.']'" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                    </td>
                                    @php
                                    $columnIndex++;
                                    @endphp
                                    @endfor



                            </tr>
                            @endforeach


                            {{-- <tr data-repeater-style>
                                <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">

                            <td>
                                <input readonly value="{{ __('Loan Officer') }}" class="form-control" type="text">

                            </td>
                            @php
                            $columnIndex = 0 ;
                            @endphp
                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                            @php
                            $currentVal=isset($subModel) ? $subModel->getCountsAtMonthIndex($yearOrMonthAsIndex) : 0 ;

                            @endphp
                            <td>
                                <x-repeat-right-dot-inputs :numberFormatDecimals="0" :multiple="true" :removeCurrency="true" :name="'counts'" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                            </td>
                            @php
                            $columnIndex++;
                            @endphp
                            @endforeach



                            </tr> --}}






                        </x-slot>




                    </x-tables.repeater-table>

                    {{-- </form> --}}
                    {{-- end of one time expense --}}




                </div>
            </div>
			

            {{-- start of reserve assumption  --}}
            {{-- <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                        {{ __('Existing Branches Product Mix') }}
            </h3>
            <div class="row">
                <hr style="flex:1;background-color:lightgray">
            </div>
            <div class="row reserve-and-profit-distribution-assumption">


                <div class="table-responsive">
                    <table class="table table-white repeater-class repeater ">
                        <thead>
                            <tr>
                                <th class="first-column-th-class-medium form-label font-weight-bold text-center align-middle interval-class header-border-down">{{ __('Product Name') }}</th>
                                <th class="first-column-th-class-medium form-label font-weight-bold text-center align-middle interval-class header-border-down">{{ __('Tenor (Months)') }}</th>
                                <th class="first-column-th-class-medium form-label font-weight-bold text-center align-middle interval-class header-border-down">{{ __('Avg Amount') }}</th>
                                @for($i = 0 ; $i< $microfinanceProductMixCount ; $i++ ) <th class="form-label font-weight-bold  text-center align-middle interval-class header-border-down">
                                    @php
                                    $currentVal = 'd' ;
                                    @endphp
                                    <input name="{{ 'existing_names[' . $i .']' }}" value="{{ $currentVal }}" class="form-control text-left " type="text">
                                    </th>
                                    @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr data-repeat-formatting-decimals="0" data-repeater-style>
                                <td class="td-classes">
                                    <div>

                                        <input value="{{ $product->getName() }}" disabled="" class="form-control text-left mt-2" type="text">
                                    </div>

                                </td>

                                <td>
                                    @php
                                    $currentVal = 0;
                                    @endphp
                                    <x-repeat-right-dot-inputs :numberFormatDecimals="0" :removeThreeDots="true" :removeCurrency="true" :currentVal="number_format($currentVal,0)" :classes="'only-greater-than-zero-allowed'" :is-percentage="false" :name="'existing_branch_tenor'" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                </td>

                                <td>
                                    @php
                                    $currentVal = 0;
                                    @endphp
                                    <x-repeat-right-dot-inputs :removeThreeDots="true" :removeCurrency="true" :currentVal="number_format($currentVal,1)" :classes="'only-greater-than-zero-allowed'" :is-percentage="false" :name="'existing_avg_amount'" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                </td>


                                @php
                                $columnIndex = 0 ;
                                @endphp
                                @for($i = 0 ; $i< $microfinanceProductMixCount ; $i++ ) <td>

                                    @php
                                    $currentVal = 0;
                                    @endphp
                                    <x-repeat-right-dot-inputs :removeThreeDots="false" :removeCurrency="true" :currentVal="number_format($currentVal,1)" :classes="'only-greater-than-zero-allowed'" :is-percentage="true" :name="'existing_allocations['.$i.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endfor


                            </tr>
                            @endforeach




                        </tbody>
                    </table>
                </div>

            </div>

    </div>
</div> --}}
{{-- end of reserve assumption  --}}




{{-- end of general assumption  --}}














































<x-save-or-back :btn-text="__('Create')" />
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


</script>

<script>
    $(document).on('click', '.save-form', function(e) {
        e.preventDefault(); {

            const hasSalesChannel = $('#add-sales-channels-share-discount-id:checked').length

            let canSubmitForm = true;
            let errorMessage = '';
            let messageTitle = 'Oops...';



            if (!canSubmitForm) {
                Swal.fire({
                    icon: "warning"
                    , title: messageTitle
                    , text: errorMessage
                , })

                return;
            }


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
					let errorMessage = res.responseJSON.message;
					if (res.responseJSON && res.responseJSON.errors) {
                            errorMessage = res.responseJSON.errors[Object.keys(res.responseJSON.errors)[0]][0]
                        }
                    Swal.fire({
                        icon: 'error'
                        , title: errorMessage
                    , });
                }
            });
        }
    })

</script>



<script>







</script>
<script>
    $(document).on('change', '[data-calc-adr-operating-date]', function() {
        const power = parseFloat($('#daysDifference').val());
        const roomTypeId = $(this).attr('data-room-type-id');
        let avgDailyRate = $('.avg-daily-rate[data-room-type-id="' + roomTypeId + '"]').val();
        avgDailyRate = number_unformat(avgDailyRate)
        let ascalationRate = $('.adr-escalation-rate[data-room-type-id="' + roomTypeId + '"]').val() / 100;

        const result = avgDailyRate * Math.pow(((1 + ascalationRate)), power)
        $('.value-for-adr_at_operation_date[data-room-type-id="' + roomTypeId + '"]').val(result)
        $('.html-for-adr_at_operation_date[data-room-type-id="' + roomTypeId + '"]').val(number_format(result))
    })
    $(document).on('change', '.add-sales-channels-share-discount', function() {
        let val = +$(this).attr('value');
        if (val) {
            $('[data-is-sales-channel-revenue-discount-section]').show();
        } else {
            $('[data-is-sales-channel-revenue-discount-section]').hide();

        }
    })
    $(document).on('change', '.occupancy-rate', function() {
        let val = $(this).attr('value');

        if (val == 'general_occupancy_rate') {
            $('[data-name="general_occupancy_rate"]').fadeIn(300)
            $('[data-name="occupancy_rate_per_room"]').fadeOut(300)
        } else {
            $('[data-name="general_occupancy_rate"]').fadeOut(300)
            $('[data-name="occupancy_rate_per_room"]').fadeIn(300)

        }
    })
    $(document).on('change', '.collection_rate_class', function() {
        let val = $(this).val();
        if (val == 'terms_per_sales_channel') {
            $('[data-name="per-sales-channel-collection"]').fadeIn(300)
            $('[data-name="general-collection-policy"]').fadeOut(300)
        } else {
            $('[data-name="per-sales-channel-collection"]').fadeOut(300)
            $('[data-name="general-collection-policy"]').fadeIn(300)

        }
    })

    $(document).on('change', '.seasonlity-select', function() {
        const mainSelect = $('.main-seasonality-select').val()
        const secondarySelect = $('.secondary-seasonality-select').val();
        $('.one-of-seasonality-tables-parent').addClass('d-none');
        $('[data-select-1*="' + mainSelect + '"][data-select-2*="' + secondarySelect + '"]').removeClass('d-none')

    })

    $(document).on('change', '.collection_rate_input', function() {
        let salesChannelName = $(this).attr('data-sales-channel-name')
        let total = 0;
        $('.collection_rate_input[data-sales-channel-name="' + salesChannelName + '"]').each(function(index, input) {
            total += parseFloat(input.value)
        })
        $('.collection_rate_total_class[data-sales-channel-name="' + salesChannelName + '"]').val(total)
    })


    $(function() {
        $('[data-calc-adr-operating-date]').trigger('change')
        $('.occupancy-rate:checked').trigger('change')
        $('.collection_rate_class:checked').trigger('change')
        $('.add-sales-channels-share-discount:checked').trigger('change')
        $('.main-seasonality-select').trigger('change')
        $('[data-repeater-create]').trigger('')
    })

    $(document).on('change keyup', '.recalc-avg-weight-total', function() {
        const order = this.getAttribute('data-order')
        let currentTotal = 0;
        $('.revenue-share-percentage[data-order="' + order + '"]').each(function(i, revenueSharePercentageInput) {
            var currentIndex = revenueSharePercentageInput.getAttribute('data-index');
            var revenueSharePercentageAtIndex = $(revenueSharePercentageInput).parent().find('input[type="hidden"]').val();
            revenueSharePercentageAtIndex = revenueSharePercentageAtIndex ? revenueSharePercentageAtIndex / 100 : 0;
            var discountSharePercentageAtIndex = $('.discount-commission-percentage[data-order="' + order + '"][data-index="' + currentIndex + '"]').parent().find('input[type="hidden"]').val();
            discountSharePercentageAtIndex = discountSharePercentageAtIndex ? discountSharePercentageAtIndex / 100 : 0;
            currentTotal += discountSharePercentageAtIndex * revenueSharePercentageAtIndex;
        })
        currentTotal = currentTotal * 100;
        $('.weight-avg-total-hidden[data-order="' + order + '"]').val(currentTotal);
        $('.weight-avg-total[data-order="' + order + '"]').val(number_format(currentTotal, 1)).trigger('keyup');
    })


    $(function() {



        $('.recalc-avg-weight-total').trigger('change')
    })
    $(function() {
        $('.choosen-currency-class').on('change', function() {
            $('.choosen-currency-class').val($(this).val())
        })
        $('.choosen-currency-class').trigger('change');
    })

</script>
<script src="/custom/js/non-banking-services/common.js"></script>
<script src="/custom/js/non-banking-services/select2.js"></script>

@endsection
