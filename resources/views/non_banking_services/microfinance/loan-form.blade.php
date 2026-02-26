@extends('layouts.dashboard')
@php
use App\Models\NonBankingService\Expense;
@endphp
@section('css')
<x-styles.commons></x-styles.commons>
<link rel="stylesheet" href="/custom/css/non-banking-services/expenses.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">
<style>
    .expenses-table {
        min-height: 50vh !important;
    }

</style>
@endsection
@section('sub-header')

<x-main-form-title :id="'main-form-title'" :class="''">{{ $title  }}</x-main-form-title>
@endsection
@section('content')
@php
$months = $study->getMicrofinanceMonths() ;
@endphp
<div class="row">
    <div class="col-md-12">
        <form id="form-id" class="kt-form kt-form--label-right" method="POST" enctype="multipart/form-data" action="{{ $storeRoute }}">
            @csrf
            <input type="hidden" name="model_id" value="{{ $model->id ?? 0  }}">
            <input type="hidden" name="company_id" value="{{ getCurrentCompanyId()  }}">
            <input type="hidden" name="model_name" value="Study">
            {{-- <input type="hidden" name="expense_type" value="{{ $expenseType }}"> --}}
            <input type="hidden" name="study_id" id="study-id-js" value="{{ $study->id }}">
            <input type="hidden" id="study-start-date" value="{{ $study->getStudyStartDate() }}">
            <input type="hidden" id="study-end-date" value="{{ $study->getStudyEndDate() }}">




            {{-- start of New Branches Product Mix  --}}
            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                        {{ __('Monthly Loan Amounts By Product') }}
                    </h3>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    <div class="row reserve-and-profit-distribution-assumption">

                        @php
                        $currentYearRepeaterIndex = 0 ;
                        @endphp
                        <div class="table-responsive">
                            <table class="table table-white repeater-class repeater ">
                                <thead>
                                    <tr>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Product <br> Name') !!}</th>
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                     	   <th data-column-index="{{ $yearOrMonthAsIndex }}" class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! $yearOrMonthFormatted .' <br> ' . __('Loan <br> Amount') !!}</th>
                                        @php
                                        $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                                        $currentMonthNumber = explode('-',$dateAsString)[1];
                                        $currentYear= explode('-',$dateAsString)[0];
                                        @endphp

                                        @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
                                        <x-tables.repeater-table-th :icon="true" data-column-index="{{ $yearOrMonthAsIndex }}" :font-size-class="'font-14px'" class=" tenor-selector-class header-border-down {{ 'year-repeater-index-'.$currentYearRepeaterIndex }} collapse-before-me exclude-from-collapse" :title="__('Total Yr.').' <br> '. $currentYear"></x-tables.repeater-table-th>
                                        @php
                                        $currentYearRepeaterIndex ++;
                                        @endphp
                                        @endif

                                        @endforeach
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{{ __('Total') }}</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                    @php
                                    $monthlyLoanAmounts = $salesProjectsPerProducts[$product->id] ?? [];
                                    if(!count($monthlyLoanAmounts)){
                                    continue;
                                    }
                                    @endphp


                                    <tr data-repeat-formatting-decimals="0" data-repeater-style>
                                        <td class="td-classes">
                                            <div>

                                                <input value="{{ $product->getName() }}" disabled="" class="form-control text-left min-w-300" type="text">
                                            </div>

                                        </td>



                                        @php
                                        $currentYearRepeaterIndex = 0;
                                        $currentYearTotal = 0 ;
										$rowTotal = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            $currentVal = $monthlyLoanAmounts[$yearOrMonthAsIndex]??0;
                                            $columnsTotals[$yearOrMonthAsIndex] = isset($columnsTotals[$yearOrMonthAsIndex] ) ? $columnsTotals[$yearOrMonthAsIndex] +$currentVal : $currentVal;
                                         //   $rowsTotals[$product->id] = isset($rowsTotals[$product->id] ) ? $rowsTotals[$product->id] +$currentVal : $currentVal;
										 $rowTotal+=$currentVal;
                                            $currentYearTotal+=$currentVal;
                                            @endphp
											
											
                                            <x-repeat-right-dot-inputs data-group-index="{{ $currentYearRepeaterIndex }}" :classes="' repeater-with-collapse-input'" :disabled="true" :numberFormatDecimals="0" :formattedInputClasses="' '" :mark="''" :removeThreeDots="true" :removeCurrency="true" :currentVal="$currentVal"  :is-percentage="false" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                            @php
                                            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                                            $currentMonthNumber = explode('-',$dateAsString)[1];
                                            $currentYear= explode('-',$dateAsString)[0];
                                            @endphp
                                            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
                                 	       <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
												<div class="d-flex align-items-center justify-content-center">
													<x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="' '" :currentVal="$currentYearTotal " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
												</div>
                                	        </td>
                                    	    @php
                                        $currentYearRepeaterIndex++;
                                        $currentYearTotal = 0;
                                        @endphp
                                        @endif
                                        </td>


                                        @endforeach

                                        <td>
                                            <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="0" :formattedInputClasses="' '" :mark="''" :removeThreeDots="true" :removeCurrency="true" :currentVal="$rowTotal" :classes="''" :is-percentage="false" :name="''" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                        </td>


                                    </tr>
                                    @endforeach

                                    <tr data-repeat-formatting-decimals="0" data-repeater-style>
                                        <td class="td-classes">
                                            <div>

                                                <input value="{{ __('Totals') }}" disabled="" class="form-control text-left min-w-300" type="text">
                                            </div>

                                        </td>


 @php
                                        $currentYearRepeaterIndex = 0;
                                        $currentYearTotal = 0 ;
										$totalRow = 0 ;
                                        @endphp

                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        <td>
											 @php
                                            $currentVal = $columnsTotals[$yearOrMonthAsIndex]??0;
											$totalRow+=$currentVal;
                                            $currentYearTotal+=$currentVal;
                                            @endphp
											
                                            <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="0" :formattedInputClasses="' '" :mark="''" :removeThreeDots="true" :removeCurrency="true" :currentVal="$currentVal" :classes="''" :is-percentage="false" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
											
											 @php
                                            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                                            $currentMonthNumber = explode('-',$dateAsString)[1];
                                            $currentYear= explode('-',$dateAsString)[0];
                                            @endphp
                                            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
                                 	       <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
												<div class="d-flex align-items-center justify-content-center">
													<x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="' '" :currentVal="$currentYearTotal " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
												</div>
                                	        </td>
                                    	    @php
                                        $currentYearRepeaterIndex++;
                                        $currentYearTotal = 0;
                                        @endphp
                                        @endif
										
                                        </td>
                                        @endforeach

                                        <td>

                                            <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="0" :formattedInputClasses="' '" :mark="''" :removeThreeDots="true" :removeCurrency="true" :currentVal="$totalRow" :classes="''" :is-percentage="false" :name="''" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                        </td>


                                    </tr>





                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>
            </div>





            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <div class="row">

                        <div class="col-md-10">
                            <div class="d-flex align-items-center ">
                                <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                                    {{ __('Administration Fees Rate & ECL Rate') }}
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-2 text-right">
                            <x-show-hide-btn :query="'.direct-factoring-revenue-projection-by-category'"></x-show-hide-btn>

                        </div>
                    </div>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    <div class="row factoring-revenue-projection-by-category">
                        @php
                        $rowIndex = 0;
                        @endphp


                        <x-tables.repeater-table :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden overflow-scroll'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                            <x-slot name="ths">
                                <x-tables.repeater-table-th class="  header-border-down " :title="__('Item')"></x-tables.repeater-table-th>
                                @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                <x-tables.repeater-table-th class="  header-border-down " :title="$yearOrMonthFormatted"></x-tables.repeater-table-th>
                                @endforeach
                            </x-slot>
                            <x-slot name="trs">

                                <tr data-repeat-formatting-decimals="0" data-repeater-style>

                                    {{-- <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}"> --}}


                                    <td>
                                        <input value="{{ __('Administration Fees Rate') }}" disabled class="form-control min-width-300 text-left mt-2" type="text">

                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :currentVal=" $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getAdminFeesRatesAtYearOrMonthIndex($yearOrMonthAsIndex):0" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="'admin_fees_rates['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++;
                                    @endphp
                                    @endforeach



                                </tr>


                                <tr data-repeat-formatting-decimals="0" data-repeater-style>


                                    <td>
                                        <input disabled value="{{ __('Expected Credit Loss Rate (ECL %)') }}" class="form-control min-width-300 text-left" type="text">

                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp

                                    @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)


                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :currentVal="$eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEclRatesAtYearOrMonthIndex($yearOrMonthAsIndex):0" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="'ecl_rates['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++;
                                    @endphp

                                    @endforeach



                                </tr>


                            </x-slot>




                        </x-tables.repeater-table>
                        {{-- end of fixed monthly repeating amount --}}


                    </div>

                </div>
            </div>


            @include('non_banking_services.microfinance._loan_report')



















            <x-save-and-next-btn />
            {{-- <x-save-or-continue-btn /> --}}




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




</script>

<script>
    $(document).on('click', '.save-form', function(e) {
        e.preventDefault(); {

            let form = document.getElementById('form-id');
            var formData = new FormData(form);
            $('.save-form').prop('disabled', true);
            var saveAndContinue = $(this).attr('data-save-and-continue');
            formData.append('saveAndContinue', saveAndContinue);
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
@endsection



@push('js_end')
<script src="{{ url('custom/math.js') }}" type="text/javascript"></script>

<script>
</script>
<script>
    $(document).on('change', 'input:not([placeholder])[type="number"],input:not([placeholder])[type="password"],input:not([placeholder])[type="text"],input:not([placeholder])[type="email"],input:not(.exclude-text)', function() {
        if (!$(this).hasClass('exclude-text')) {
            let val = $(this).val()
            val = number_unformat(val)
            if (isNumber(val)) {
                $(this).parent().find('input[type="hidden"]:not([name="_token"])').val(val)
            }

        }
    })
    $(document).on('click', '.repeat-to-r', function() {
        const columnIndex = $(this).data('column-index');
        const digitNumber = $(this).data('digit-number');
        const val = $(this).parent().find('input[type="hidden"]').val();
        $(this).closest('tr').find('.can-be-repeated-parent').each(function(index, parent) {
            if (index > columnIndex) {
                $(parent).find('.can-be-repeated-text').val(val);
                $(parent).find('.can-be-repeated-text').val(number_format(val, digitNumber));

            }
        })
    })

</script>
<script>
    $(document).on('change', '.rate-element', function() {
        let total = 0;
        const parent = $(this).closest('tbody');
        parent.find('.rate-element-hidden').each(function(index, element) {
            total += parseFloat($(element).val());
        });
        parent.find('td.td-for-total-payment-rate').html(number_format(total, 2) + ' %');

    })

</script>
<script src="/custom/js/non-banking-services/common.js"></script>
<script src="/custom/js/non-banking-services/select2.js"></script>

<script>
    $(document).on('change', '.equity-funding-rate-input-hidden-class', function() {
        const value = number_unformat($(this).val());
        const columnIndex = parseInt($(this).attr('data-column-index'));
        $('input.new-loan-function-rates-js[data-column-index="' + columnIndex + '"]').val(100 - value).trigger('change');
    })

    $(document).on('change', '.recalculate-total-branches', function() {
        var totalBranchesCount = 0;
        $('.recalculate-total-branches').each(function(index, element) {
            var currentBranchCount = parseInt(number_unformat($(element).val()));
            totalBranchesCount += currentBranchCount;
            $(element).closest('tr').find('.total-branches-text').val(totalBranchesCount).trigger('change');
        })
    })
    $(function() {
        //		$('.recalculate-total-branches:eq(0)').trigger('change')
    })

</script>
@endpush
