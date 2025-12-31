@extends('layouts.dashboard')
@php
use App\Models\NonBankingService\Study;
@endphp
@section('css')
<link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">
<style>
    input.form-control[type="text"][readonly] {
        background-color: white !important;
        color: black !important;
        font-weight: 400 !important;
    }

    .fixed-column-table {
        width: 100%;
        overflow-x: auto;
        /* Enable horizontal scrolling */
        border-collapse: collapse;
    }

    .fixed-column {
        position: sticky;
        left: -15px;
        background: #f8f8f8;
        /* Optional: distinguish the fixed column */
        z-index: 1;
        /* Ensure it stays above other cells */
    }

    html body input.custom-input-string-width,
    .name-max-width-class {
        width: 400px !important;
        min-width: 400px !important;
        max-width: 400px !important;
    }

</style>
@endsection
@section('sub-header')
{{ $title }}
@endsection
@section('content')
<div id="study-duration" data-duration="{{ $study->duration_in_years }}"></div>
<div class="kt-portlet kt-portlet--tabs">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-toolbar justify-content-between flex-grow-1">
            <ul class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ !Request('active') || Request('active') == Study::STUDY ?'active':'' }}" data-toggle="tab" href="#{{Study::STUDY  }}" role="tab">
                        <i class="fa fa-money-check-alt"></i> {{ $tableTitle }}
                    </a>
                </li>
            </ul>



        </div>
    </div>
    <div class="kt-portlet__body">
        <div class="tab-content  kt-margin-t-20">

            @php
            $currentType = 'study' ;
            @endphp
            <!--Begin:: Tab Content-->
            <div class="tab-pane {{  !Request('active') || Request('active') == $currentType ?'active':'' }}" id="{{ $currentType }}" role="tabpanel">
                <div class="kt-portlet kt-portlet--mobile">
                    <form method="post" action="{{ route('store.previous.non.banking.forecast.income.statement',['company'=>$company->id,'study'=>$study->id]) }}">
                        @csrf

                        <x-tables.repeater-table :tableClasses="'table-condensed fixed-column-table table-row-spacing income-class-table'" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden scrollable-table'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                            <x-slot name="ths">
                                <x-tables.repeater-table-th :subParentClass="'plus-max-width-class fixed-column'" class="  header-border-down plus-max-width-class" :title="__('+/-')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th :subParentClass="'name-max-width-class fixed-column'" class="  header-border-down name-max-width-class exclude-from-collapse" :title="__('Name')"></x-tables.repeater-table-th>
                                {{-- <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('')"></x-tables.repeater-table-th> --}}
                                @foreach($studyMonthsForViews as $dateAsIndex=>$dateAsString)
                                <x-tables.repeater-table-th data-column-index="{{ $dateAsIndex }}" class=" header-border-down " :title="$dateAsString"></x-tables.repeater-table-th>
                                @endforeach
                            </x-slot>
                            <x-slot name="trs">

                                @php
                                @endphp
                                @foreach($tableDataFormatted as $tableIndex => $currentTableData)
                                @php
                                $subItems = $currentTableData['sub_items']??[] ;
                                $hasSubItems = count($subItems);
                                if(!isset($currentTableData['main_items'])){
                                continue;
                                }
                                @endphp
                                <tr data-is-main-row data-repeat-formatting-decimals="0" data-repeater-style>
                                    <td class="fixed-column">
                                        @if($hasSubItems)
                                        <a href="#" class="btn btn-1-bg btn-sm btn-brand add-btn-class  text-center add-btn-js">
                                            <i class="fas fa-angle-double-down expand-icon   exclude-icon"></i>
                                        </a>
                                        @endif
                                    </td>
                                    <td class="fixed-column">
                                        <div class="d-flex align-items-center justify-content-center flex-column name-max-width-class" style="gap:10px">
                                            @php
                                            $currentIndex = 0 ;

                                            @endphp
                                            @foreach($currentTableData['main_items'] as $mainItemId => $mainItemArr)
                                            <div class="input-hidden-parent">
                                                <input readonly data-number-of-decimals="0" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control exclude-from-collapse text-left copy-value-to-his-input-hidden 

						 						  			  repeat-to-right-input-formatted  exclude-from-collapse custom-input-string-width input-text-left  " type="text" value="{{ $mainItemArr['options']['title']??$mainItemId }}" data-column-index="-1">
                                            </div>
                                            @php
                                            @endphp
                                            @endforeach

                                        </div>
                                    </td>

                                    @php
                                    $currentYearRepeaterIndex = 0 ;
                                    @endphp

                                    @foreach($studyMonthsForViews as $dateAsIndex=>$dateAsString)

                                    <td data-column-index="{{ $dateAsIndex }}">

                                        <div data-column-index="{{ $dateAsIndex }}" class="d-flex align-items-center justify-content-center flex-column" style="gap:10px">
                                            @php
                                            $currentIndex = 0 ;
                                            $parentIndex = 0 ;
                                            $firstMainItemId = null;
                                            @endphp

                                            @foreach($currentTableData['main_items'] as $mainItemId => $mainItemArr)
                                            @php
                                            if($loop->first){
                                            $firstMainItemId =$mainItemId;
                                            }
                                            $isPercentage = $mainItemArr['options']['is-percentage']??$defaultClasses[$currentIndex]['is-percentage'] ;
                                            $name=$firstMainItemId.'[main_items]['.$mainItemId.']['. $dateAsIndex .']';
                                            @endphp
                                            @if($isPercentage)
                                            <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                                <div class="input-hidden-parent">
                                                    <input disabled data-number-of-decimals="2" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

							  expandable-percentage-input  			  repeat-to-right-input-formatted   " type="text" value="{{ number_format($previous_years_income_statement[$firstMainItemId]['main_items'][$mainItemId][$dateAsIndex]??0,2) }}" data-column-index="{{ $dateAsIndex }}">
                                                    <input data-number-of-decimals="2" data-group-index="{{ $currentIndex ==0   ? $currentYearRepeaterIndex : -1 }}" type="hidden" data-name="{{ $name }}" name="{{ $name }}" data-is-main data-is-percentage data-first-parent-id="{{ $firstMainItemId }}" data-id="{{ $mainItemId }}" class="repeat-to-right-input-hidden input-hidden-with-name  " value="{{ $mainItemArr['data'][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                                                </div>
                                                <span class="ml-2 currency-class">%</span>
                                            </div>
                                            @else
										
                                            <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                                <div class="input-hidden-parent">
                                                    <input @if($mainItemId!='corporate-taxes' ) disabled @endif data-number-of-decimals="0" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

							  expandable-amount-input 			  repeat-to-right-input-formatted  custom-input-numeric-width  " type="text" value="{{ number_format($previous_years_income_statement[$firstMainItemId]['main_items'][$mainItemId][$dateAsIndex]??0) }}" data-column-index="{{ $dateAsIndex }}">
                                                    <input data-number-of-decimals="0" data-group-index="{{ $currentIndex ==0   ? $currentYearRepeaterIndex : -1 }}" type="hidden" data-name="{{ $name }}" name="{{ $name }}" data-id="{{ $mainItemId }}" data-is-main @if($mainItemId=='sales-revenue' ) data-is-sales-revenue @endif class="repeat-to-right-input-hidden input-hidden-with-name  repeater-with-collapse-input" value="{{ $previous_years_income_statement[$firstMainItemId]['main_items'][$mainItemId][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                                                </div>
                                            </div>
                                            @endif


                                            {{-- <x-repeat-right-dot-inputs :readonly="false" :classes="$mainItemArr['options']['classes']??$defaultClasses[$currentIndex]['classes']" data-group-index="{{ $currentIndex ==0   ? $currentYearRepeaterIndex : -1 }}" :formattedInputClasses="$mainItemArr['options']['formatted-input-classes']??$defaultClasses[$currentIndex]['formatted-input-classes']" :removeThreeDots="true" :removeCurrency="true" :mark="$isPercentage ? '%' : ''" :is-number="true" :removeThreeDotsClass="true" :numberFormatDecimals="$mainItemArr['options']['number-format-decimals']??$defaultClasses[$currentIndex]['number-format-decimals']" :currentVal="$mainItemArr['data'][$dateAsIndex]??0" :is-percentage="$isPercentage" :name="''" :columnIndex="$dateAsIndex"></x-repeat-right-dot-inputs> --}}
                                            @php
                                            $currentIndex++;
                                            $parentIndex++;
                                            @endphp
                                            @endforeach

                                        </div>




                                    </td>




                                    @endforeach

                                </tr>
                                @foreach($subItems as $subItemId => $subItemArr)
                                <tr class="hidden" data-is-sub-row data-repeat-formatting-decimals="0">
                                    <td class="fixed-column">
                                    </td>
                                    <td class="fixed-column">
                                        <div class="d-flex align-items-center justify-content-center flex-column ml-5" style="gap:10px">

                                            <div class="">
                                                <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                                    <div class="input-hidden-parent">
                                                        <input style="text-align:left !important;" readonly data-number-of-decimals="0" readonly="" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

										  expandable-amount-input 			  repeat-to-right-input-formatted  custom-input-string-width  input-text-left  " type="text" value="{{ $subItemArr['options']['title']??$subItemId }}" data-column-index="-1">
                                                        <input data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  " value="{{ $subItemArr['options']['title']??$subItemId }}" data-column-index="-1">
                                                    </div>
                                                    <span class="ml-2 currency-class"> </span>
                                                </div>

                                            </div>

                                        </div>
                                    </td>


                                    @foreach($studyMonthsForViews as $dateAsIndex=>$dateAsString)
                                    @php

                                    $name=$firstMainItemId.'[sub_items]['.$subItemId.']['. $dateAsIndex .']';
                                    @endphp
                                    <td data-column-index="{{ $dateAsIndex }}">

                                        <div data-column-index="{{ $dateAsIndex }}" class="d-flex align-items-center justify-content-center flex-column" style="gap:10px">

                                            <div class="">
                                                <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                                    <div class="input-hidden-parent">
                                                        <input data-number-of-decimals="0" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

			 							 expandable-amount-input 			  repeat-to-right-input-formatted  custom-input-numeric-width  " type="text" value="{{ number_format($previous_years_income_statement[$firstMainItemId]['sub_items'][$subItemId][$dateAsIndex]??0) }}" data-column-index="{{ $dateAsIndex }}">
                                                        <input data-name="{{ $name }}" name="{{ $name }}" data-id="{{ $firstMainItemId }}" data-is-sub data-sub-id="{{ $subItemId }}" data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  repeater-with-collapse-input" value="{{ $previous_years_income_statement[$firstMainItemId]['sub_items'][$subItemId][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                                                    </div>
                                                    <span class="ml-2 currency-class"> </span>
                                                </div>

                                            </div>
                                        </div>
                                    </td>




                                    @endforeach


                                </tr>


                                @endforeach
                                @endforeach



                            </x-slot>




                        </x-tables.repeater-table>
                        <button class="btn btn-primary">{{ __('Save') }}</button>
                    </form>
                </div>


            </div>


            @if(isset($nextButton))
            <div class="text-right mt-4 cash-flow-btn">
                <a href="{{ $nextButton['link'] }}" class="btn btn-primary ">{{ $nextButton['title'] }}</a>
            </div>
            @endif



            <!--End:: Tab Content-->



            <!--End:: Tab Content-->
        </div>
    </div>

</div>

@endsection
@section('js')

{{-- <script src="{{ url('assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script> --}}

<script src="{{ url('assets/vendors/general/jquery.repeater/src/jquery.input.js') }}" type="text/javascript">
</script>


<script>
    $(document).on('click', '.js-close-modal', function() {
        $(this).closest('.modal').modal('hide');
    })

</script>

@endsection
@push('js')
<script src="/custom/js/non-banking-services/common.js"></script>
<script src="/custom/js/non-banking-services/select2.js"></script>
<script>
    $(function() {
        const studyDuration = $('#study-duration').attr('data-duration');
        if (studyDuration > 1) {
            $('.collapse-before-me').trigger('click')
        }
        // $('.expense-category-class').trigger('change')
    })

    $(function() {
        //	$('[data-group-index]').trigger('change');
    })

</script>

<script>
    let recalculateIncomeStatement = function() {
        var totalSalesRevenues = calculateTotalOfMainRowFromItsSubItems('sales-revenue');
        var totalCostOfService = calculateTotalOfMainRowFromItsSubItems('cost-of-service');
        var totalGrossProfit = calculateGrossProfit(totalSalesRevenues, totalCostOfService);
        var totalOtherOperationExpenses = calculateTotalOfMainRowFromItsSubItems('other-operation-expense');
        var totalSalesExpenses = calculateTotalOfMainRowFromItsSubItems('sales-expense');
        var TotalMarketingExpenses = calculateTotalOfMainRowFromItsSubItems('marketing-expense');
        var totalGeneralExpenses = calculateTotalOfMainRowFromItsSubItems('general-expense');
        var totalEclAndDepreciationExpenses = calculateTotalOfMainRowFromItsSubItems('ecl-and-depreciation-expenses');
        var totalEBITDA = calculateEBITDA(totalSalesRevenues, totalGrossProfit, totalSalesExpenses, totalOtherOperationExpenses, TotalMarketingExpenses, totalGeneralExpenses);
        var totalEBIT = calculateEBIT(totalSalesRevenues, totalEBITDA);
        var totalFinanceExpense = calculateTotalOfMainRowFromItsSubItems('finance_exp');
        var totalEBT = calculateEBT(totalSalesRevenues, totalEBIT, totalFinanceExpense);
        calculateCorporateTaxesPercentageOfRevenue(totalSalesRevenues);
        var totalNetProfit = calculateNetProfit(totalSalesRevenues, totalEBT);


    }
    // recalculateIncomeStatement();

    function calculateTotalOfMainRowFromItsSubItems(firstMainRowId) {
        let total = {};
        $('input[type="hidden"][data-is-sub][data-id="' + firstMainRowId + '"]').each(function(index, inputHidden) {
            var val = parseFloat($(inputHidden).val());
            var columnIndex = $(inputHidden).attr('data-column-index');
            if (total[columnIndex]) {
                total[columnIndex] = total[columnIndex] + val;
            } else {
                total[columnIndex] = val;
            }

        })
        var previousTotal = 0;
        var isSalesRevenue = firstMainRowId == 'sales-revenue';
        for (var columnIndex in total) {
            var currentTotal = total[columnIndex];
            $('input[type="hidden"][data-is-main][data-id="' + firstMainRowId + '"][data-column-index="' + columnIndex + '"]').val(currentTotal).trigger('change');
            if (isSalesRevenue) {
                // growth rate
                var growthRate = previousTotal ? ((currentTotal - previousTotal) / previousTotal) : 0;
                growthRate = growthRate * 100;
                $('input[data-first-parent-id="' + firstMainRowId + '"][data-is-main][data-id="growth-rate"][data-column-index="' + columnIndex + '"]').val(growthRate).trigger('change');
                previousTotal = currentTotal;
            }
            // percentage of revenue
            if (!isSalesRevenue) {
                var currentSalesRevenueTotal = $('[data-is-sales-revenue][data-column-index="' + columnIndex + '"]').val();
                percentageOfRevenue = currentSalesRevenueTotal ? currentTotal / currentSalesRevenueTotal * 100 : 0;
                $('input[data-first-parent-id="' + firstMainRowId + '"][data-is-main][data-id="% Of Revenue"][data-column-index="' + columnIndex + '"]').val(percentageOfRevenue).trigger('change');
            }

        }
        return total;
    }

    function calculateGrossProfit(totalSalesRevenues, totalCostOfService) {
        var totals = {};
        $('input[type="hidden"][data-id="gross-profit"]').each(function(index, inputHidden) {
            var columnIndex = $(inputHidden).attr('data-column-index');
            currentSalesRevenueTotal = totalSalesRevenues[columnIndex];
            currentCostOfServiceTotal = totalCostOfService[columnIndex];
            var currentTotal = currentSalesRevenueTotal - currentCostOfServiceTotal;
            totals[columnIndex] = currentTotal;
            $(inputHidden).val(currentTotal).trigger('change');
        })
        return totals;
    }

    function calculateEBITDA(totalSalesRevenues, totalGrossProfit, totalSalesExpenses, totalOtherOperationExpenses, TotalMarketingExpenses, totalGeneralExpenses) {
        var totals = {};
        $('input[type="hidden"][data-id="ebitda"]').each(function(index, inputHidden) {
            var columnIndex = $(inputHidden).attr('data-column-index');
            var currentGrossProfit = totalGrossProfit[columnIndex] ? totalGrossProfit[columnIndex] : 0;
            var currentSalesExpenses = totalSalesExpenses[columnIndex] ? totalSalesExpenses[columnIndex] : 0;
            var currentOtherOperationExpenses = totalOtherOperationExpenses[columnIndex] ? totalOtherOperationExpenses[columnIndex] : 0;
            var currentMarketingExpenses = TotalMarketingExpenses[columnIndex] ? TotalMarketingExpenses[columnIndex] : 0;
            var currentGeneralExpenses = totalGeneralExpenses[columnIndex] ? totalGeneralExpenses[columnIndex] : 0;
            var currentEclExpense = parseFloat($('input[data-sub-id="ECL Expense"][data-column-index="' + columnIndex + '"]').val());
            var currentTotalDepreciation = parseFloat($('input[data-sub-id="total-depreciation"][data-column-index="' + columnIndex + '"]').val());
            var currentTotal = currentGrossProfit + currentTotalDepreciation - currentSalesExpenses - currentOtherOperationExpenses - currentMarketingExpenses - currentGeneralExpenses - currentEclExpense;
            totals[columnIndex] = currentTotal;
            $(inputHidden).val(currentTotal).trigger('change');
            var currentSalesRevenueTotal = totalSalesRevenues[columnIndex];
            percentageOfRevenue = currentSalesRevenueTotal ? currentTotal / currentSalesRevenueTotal * 100 : 0;
            $('input[data-first-parent-id="ebitda"][data-is-main][data-id="% Of Revenue"][data-column-index="' + columnIndex + '"]').val(percentageOfRevenue).trigger('change');
        })
        return totals;
    }

    function calculateEBIT(totalSalesRevenues, totalEBITDA) {
        var totals = {};
        $('input[type="hidden"][data-id="ebit"]').each(function(index, inputHidden) {
            var columnIndex = $(inputHidden).attr('data-column-index');
            var currentTotalEBITDA = totalEBITDA[columnIndex] ? totalEBITDA[columnIndex] : 0;
            var currentTotalDepreciation = parseFloat($('input[data-sub-id="total-depreciation"][data-column-index="' + columnIndex + '"]').val());
            var currentTotal = currentTotalEBITDA - currentTotalDepreciation;
            totals[columnIndex] = currentTotal;
            $(inputHidden).val(currentTotal).trigger('change');
            var currentSalesRevenueTotal = totalSalesRevenues[columnIndex];
            percentageOfRevenue = currentSalesRevenueTotal ? currentTotal / currentSalesRevenueTotal * 100 : 0;
            $('input[data-first-parent-id="ebit"][data-is-main][data-id="% Of Revenue"][data-column-index="' + columnIndex + '"]').val(percentageOfRevenue).trigger('change');
        })
        return totals;
    }

    function calculateEBT(totalSalesRevenues, totalEBIT, totalFinanceExpense) {
        var totals = {};
        $('input[type="hidden"][data-id="ebt"]').each(function(index, inputHidden) {
            var columnIndex = $(inputHidden).attr('data-column-index');
            var currentTotalEBIT = totalEBIT[columnIndex] ? totalEBIT[columnIndex] : 0;
            var currentTotalFinanceExpense = totalFinanceExpense[columnIndex] ? totalFinanceExpense[columnIndex] : 0;
            var currentTotal = currentTotalEBIT - currentTotalFinanceExpense;
            totals[columnIndex] = currentTotal;
            $(inputHidden).val(currentTotal).trigger('change');
            var currentSalesRevenueTotal = totalSalesRevenues[columnIndex];
            percentageOfRevenue = currentSalesRevenueTotal ? currentTotal / currentSalesRevenueTotal * 100 : 0;
            $('input[data-first-parent-id="ebt"][data-is-main][data-id="% Of Revenue"][data-column-index="' + columnIndex + '"]').val(percentageOfRevenue).trigger('change');
        })
        return totals;
    }

    function calculateCorporateTaxesPercentageOfRevenue(totalSalesRevenues) {

        $('input[type="hidden"][data-id="corporate-taxes"]').each(function(index, inputHidden) {
            var columnIndex = $(inputHidden).attr('data-column-index');
            var corporateTaxesValue = $(inputHidden).val();
            var currentSalesRevenueTotal = totalSalesRevenues[columnIndex];
            percentageOfRevenue = currentSalesRevenueTotal ? corporateTaxesValue / currentSalesRevenueTotal * 100 : 0;
            $('input[data-first-parent-id="corporate-taxes"][data-is-main][data-id="% Of Revenue"][data-column-index="' + columnIndex + '"]').val(percentageOfRevenue).trigger('change');
        })

    }

    function calculateNetProfit(totalSalesRevenues, totalEBT) {
        var totals = {};
        $('input[type="hidden"][data-id="net-profit"]').each(function(index, inputHidden) {
            var columnIndex = $(inputHidden).attr('data-column-index');
            var currentTotalEBT = totalEBT[columnIndex] ? totalEBT[columnIndex] : 0;
            var currentCorporateTaxes = parseFloat($('input[data-id="corporate-taxes"][data-column-index="' + columnIndex + '"]').val());
            var currentTotal = currentTotalEBT - currentCorporateTaxes;
            totals[columnIndex] = currentTotal;
            $(inputHidden).val(currentTotal).trigger('change');
            var currentSalesRevenueTotal = totalSalesRevenues[columnIndex];
            percentageOfRevenue = currentSalesRevenueTotal ? currentTotal / currentSalesRevenueTotal * 100 : 0;
            $('input[data-first-parent-id="net-profit"][data-is-main][data-id="% Of Revenue"][data-column-index="' + columnIndex + '"]').val(percentageOfRevenue).trigger('change');
        })
        return totals;
    }

    function _debounce(func, delay) {
        let timeoutId // لتخزين الـ timeout الحالي
        return function(...args) {
            const context = this
            clearTimeout(timeoutId) // إلغاء أي timeout سابق
            timeoutId = setTimeout(() => {
                func.apply(context, args) // تنفيذ الدالة بعد انتهاء التأخير
            }, delay)
        }
    }
    $(document).on('change', 'input:not([type="hidden"])', _debounce(recalculateIncomeStatement, 500));

</script>
@endpush
