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
            <input type="hidden" name="study_id" id="study-id-js" value="{{ $study->id }}">
            <input type="hidden" id="study-start-date" value="{{ $study->getStudyStartDate() }}">
            <input type="hidden" id="study-end-date" value="{{ $study->getStudyEndDate() }}">

            {{-- start of Existing Branches Product Mix  --}}
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
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Avg <br> Amount') !!}</th>
                                        @if(!$model->durationIsLessThanOneOrEqualYear())
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Annual <br> Increase %') !!}</th>
                                        @endif
                                        {{-- <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Product <br> Mix %') !!}</th> --}}
                                        <th class="min-w-90 form-label font-weight-bold text-center align-middle   header-border-down">{!! __('Funded <br> By') !!}</th>
                                        {{-- <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{{ __('Allocations') }}</th> --}}
                                        {{-- @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! $yearOrMonthFormatted .' <br> ' . __('Product <br> Mix %') !!}</th>
                                        @endforeach --}}

                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $currentTotals=[];
                                    @endphp
                                    @foreach($products as $product)
                                    @php
                                    $subModel = $model->consumerfinanceProductSalesProjects->where('consumerfinance_product_id',$product->id)->first();
                                    @endphp
                                    <input type="hidden" name="consumerfinanceProductSalesProjects[{{ $product->id }}][id]" value="{{ $subModel  ? $subModel->id : 0 }}">
                                    <input type="hidden" name="consumerfinanceProductSalesProjects[{{ $product->id }}][consumerfinance_product_id]" value="{{ $product->id }}">
                                    <input type="hidden" name="consumerfinanceProductSalesProjects[{{ $product->id }}][company_id]" value="{{ $company->id }}">

                                    <tr data-repeat-formatting-decimals="0" data-repeater-style data-current-product-id="{{ $product->id }}">
                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ $product->getName() }}" disabled="" class="form-control text-left min-w-300" type="text">
                                            </div>

                                        </td>

                                        <td>
                                            @php
                                            $currentVal = $subModel ? $subModel->getTenor() : 12;

                                            $tenorClass = 'tenor-class'.$product->id
                                            @endphp

                                            <x-repeat-right-dot-inputs :readonly="false" :formattedInputClasses="'min-w-90'" :numberFormatDecimals="0" :removeThreeDots="true" :removeCurrency="true" :currentVal="number_format($currentVal,0)" :classes="'only-greater-than-zero-allowed '.$tenorClass" :is-percentage="false" :name="'consumerfinanceProductSalesProjects['.$product->id.'][tenor]'" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                        </td>

                                        <td>
                                            @php
                                            $currentVal = $subModel ? $subModel->getAvgAmount() : 0 ;

                                            @endphp
                                            <x-repeat-right-dot-inputs :readonly="false" :removeThreeDots="true" :removeCurrency="true" :currentVal="$currentVal" :classes="'only-greater-than-zero-allowed'" :is-percentage="false" :name="'consumerfinanceProductSalesProjects['.$product->id.'][avg_amount]'" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                        </td>

                                        @if(!$model->durationIsLessThanOneOrEqualYear())
                                        <td>
                                            <div class="d-flex align-items-center increase-rate-parent">
                                                <button class="btn btn-primary btn-md text-nowrap increase-rate-trigger-btn" type="button" data-toggle="modal">{{ __('Increase Rates') }}</button>
                                                <x-modal.increase-rates :product="$product" :isByBranch="false" :name="'consumerfinanceProductSalesProjects['.$product->id.'][increase_rates]'" :study="$study" :subModel="isset($subModel) ? $subModel : null "></x-modal.increase-rates>
                                            </div>
                                        </td>
                                        @endif
                                        <td>
                                            <x-form.select :readonly="false" :required="true" :label="''" :pleaseSelect="false" :selectedValue="isset($subModel) ? $subModel->getFundedBy():0" :options="getMicrofinanceFundingBySelector()" :add-new="false" class="select2-select min-w-120 repeater-select  recalculate-mtl-and-odas-loans" :all="false" name="consumerfinanceProductSalesProjects[{{ $product->id }}][funded_by]"></x-form.select>

                                        </td>


                                        @php
                                        $columnIndex = 0 ;

                                        @endphp
                                       


                                    </tr>
                                    @endforeach

                                




                        </tbody>
                        </table>
                    </div>

                </div>

            </div>
    </div>
    {{-- end of Existing Branches Product Mix  --}}








    {{-- start of Products Mix Pricing (Flat Rates %)  --}}
    <div class="kt-portlet">
        <div class="kt-portlet__body">
            <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                {{ __('Products Mix Pricing (Flat Rates %)') }}
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
                                @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! $yearOrMonthFormatted .' <br> ' . __('Flat Rates %') !!}</th>
                                @endforeach

                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            @php
                            $subModel = $model->consumerfinanceProductSalesProjects->where('consumerfinance_product_id',$product->id)->first();
                            @endphp
                            <tr data-repeat-formatting-decimals="0" data-repeater-style>
                                <td class="td-classes">
                                    <div>

                                        <input value="{{ $product->getName() }}" disabled="" class="form-control text-left min-w-300" type="text">
                                    </div>

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
                                    <x-repeat-with-calc :removeThreeDots="false" :readonly="false" :currentModalId="$currentModalId" :showIcon="true" :numberFormatDecimals="2" :formattedInputClasses="'calcField flat-rate-input'" :mark="'%'" :removeCurrency="true" :currentVal="number_format($currentVal,1)" :classes="''" :is-percentage="true" :name="'consumerfinanceProductSalesProjects['.$product->id.'][flat_rates]['.$yearOrMonthAsIndex.']'" :columnIndex="$yearOrMonthAsIndex"></x-repeat-with-calc>
                                    {{-- <x-repeat-with-calc :currentModalId="$currentModalId" :showIcon="true" :numberFormatDecimals="2" :formattedInputClasses="'calcField flat-rate-input'" :mark="'%'" :removeThreeDots="false" :removeCurrency="true" :currentVal="number_format($currentVal,1)" :classes="''" :is-percentage="true" :name="'flat_rates['.$yearOrMonthAsIndex.']'" :columnIndex="$yearOrMonthAsIndex"></x-repeat-with-calc> --}}




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
    {{-- end of Products Mix Pricing (Flat Rates %)  --}}




    <div class="kt-portlet">
        <div class="kt-portlet__body">
            <div class="row">

                <div class="col-md-10">
                    <div class="d-flex align-items-center ">
                        <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                            {{ __('Consumer Finance Projection') }} {{ getThreeDotsHint() }}
                        </h3>
                    </div>
                </div>
                <div class="col-md-2 text-right">
                    <x-show-hide-btn :query="'.leasing-revenue-projection-by-category'"></x-show-hide-btn>
                </div>
            </div>
            <div class="row">
                <hr style="flex:1;background-color:lightgray">
            </div>
            <div class="row leasing-revenue-projection-by-category">
                @php
                $rowIndex = 0;
                @endphp

                @php
                $currentYearRepeaterIndex = 0 ;
                @endphp
                <x-tables.repeater-table :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden overflow-scroll'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                    <x-slot name="ths">
                        <x-tables.repeater-table-th class="  header-border-down first-column-th-class" :title="__('Item')"></x-tables.repeater-table-th>
                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                        @php
                        $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                        $currentMonthNumber = explode('-',$dateAsString)[1];
                        $currentYear= explode('-',$dateAsString)[0];
                        @endphp
                        <x-tables.repeater-table-th data-column-index="{{ $yearOrMonthAsIndex }}" class=" interval-class header-border-down " :title="$yearOrMonthFormatted"></x-tables.repeater-table-th>
                        @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
                        <x-tables.repeater-table-th :icon="true" data-column-index="{{ $yearOrMonthAsIndex }}" :font-size-class="'font-14px'" class=" tenor-selector-class header-border-down {{ 'year-repeater-index-'.$currentYearRepeaterIndex }} collapse-before-me exclude-from-collapse" :title="__('Total Yr.').' <br> '. $currentYear"></x-tables.repeater-table-th>
                        @php
                        $currentYearRepeaterIndex ++;
                        @endphp
                        @endif
                        @endforeach
                        <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Total')"></x-tables.repeater-table-th>
                    </x-slot>
                    <x-slot name="trs">
                        @if($isYearsStudy)
                        <tr data-repeat-formatting-decimals="0" data-repeater-style>

                            <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">

                            <td>
                                <div class="">
                                    <input value="{{ __('Operating Months Per Year') }}" disabled class="form-control min-width-hover-300 text-left mt-2" type="text">
                                </div>


                            </td>
                            @php
                            $columnIndex = 0 ;
                            @endphp
                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                            <td>
                                <div class="form-group three-dots-parent">
                                    <div class="input-group input-group-sm align-items-center justify-content-center div-for-percentage">
                                        <input type="text" style="max-width: 60px;min-width: 60px;text-align: center" value="{{ sumNumberOfOnes($yearsWithItsMonths,$yearOrMonthAsIndex,$datesIndexWithYearIndex) }}" readonly onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control target_repeating_amounts only-percentage-allowed size" data-date="#" data-section="target" aria-describedby="basic-addon2">
                                        <span class="ml-2">
                                            <b style="visibility:hidden">%</b>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            @php
                            $columnIndex++;
                            @endphp
                            @endforeach


                        </tr>
                        @endif
                        @if($isYearsStudy)
                        <tr data-repeat-formatting-decimals="2" data-repeater-style>

                            <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">


                            <td>
                                <div class="">
                                    <input value="{{ __('Growth Rate %') }}" disabled class="form-control min-width-hover-300 text-left mt-2" type="text">
                                </div>


                            </td>
                            @php
                            $columnIndex = 0 ;

                            @endphp
                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                            @php


                            $currentVal = $model->getLeasingGrowthRateAtYearOrMonthIndex($yearOrMonthAsIndex) ;
                            @endphp

                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs :currentVal="$currentVal" :classes="'only-number-allowed recalculate-gr gr-field'" :is-percentage="true" :name="'growth_rate['.$yearOrMonthAsIndex.']'" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>

                                </div>
                            </td>
                            @php
                            $columnIndex++;
                            @endphp
                            @endforeach

                            <td>

                                <div class="d-flex align-items-center justify-content-center">
                                    <input type="text" class="form-control expandable-amount-input sum-percentage-css" disabled value="-">
                                </div>
                            </td>




                        </tr>
                        @endif



                        @php
                        $currentLoanTotalPerYear = [];
                        @endphp

                        @foreach ($products as $product)

                        @php
                        $subModel = $model->consumerfinanceProductSalesProjects->where('consumerfinance_product_id',$product->id)->first();

                        $totalOfRow = 0;
                        @endphp

                        <tr data-repeat-formatting-decimals="0" data-repeater-style total-row-tr projection-product-id="{{ $product->id }}">

                            <td>
                                <div class="">
                                    <input value="{{ $product->getName() }}" disabled class="form-control min-width-hover-300 text-left mt-2" type="text">
                                </div>
                            </td>


                            @php
                            $columnIndex = 0 ;
                            $currentYearRepeaterIndex = 0;
                            $currentYearTotal = 0 ;
                            @endphp
                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                            @php
                            $currentVal = $subModel ? $subModel->getSalesRevenuePayloadAtYearOrMonthIndex($yearOrMonthAsIndex) : 0 ;
                            $currentLoanTotalPerYear[$yearOrMonthAsIndex] = isset($currentLoanTotalPerYear[$yearOrMonthAsIndex]) ? $currentLoanTotalPerYear[$yearOrMonthAsIndex]+ $currentVal : $currentVal;
                            @endphp
                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs data-consumer-projection-product-id="{{ $product->id }}" data-group-index="{{ $currentYearRepeaterIndex }}" :number-format-decimals="0" :currentVal="$currentVal" :formattedInputClasses="'current-growth-rate-result-value-formatted'" :classes="'only-greater-than-or-equal-zero-allowed recalculate-mtl-and-odas-loans current-loan-input current-growth-rate-result-value repeater-with-collapse-input'" :is-percentage="false" :name="'consumerfinanceProductSalesProjects['.$product->id.'][loan_amounts]['.$yearOrMonthAsIndex.']'" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                </div>
                            </td>

                            @php
                            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                            $currentMonthNumber = explode('-',$dateAsString)[1];
                            $currentYear= explode('-',$dateAsString)[0];
                            $currentYearTotal+=$currentVal;
                            $totalOfRow+=$currentVal;
                            @endphp


                            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
                            <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="' '" :currentVal="$currentYearTotal" :formattedInputClasses="'exclude-from-collapse exclude-from-total exclude-from-trigger-change-when-repeat expandable-amount-input'" :classes="'year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse exclude-from-total'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                </div>

                            </td>
                            @php
                            $currentYearRepeaterIndex++;

                            $currentYearTotal = 0 ;
                            @endphp
                            @endif



                            @php
                            $columnIndex++ ;
                            @endphp

                            @endforeach

                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <input type="text" class="form-control expandable-amount-input sum-total-row sum-percentage-css" disabled value="{{ number_format($totalOfRow) }}">
                                </div>
                            </td>
                        </tr>




                        @endforeach


                        <tr data-repeat-formatting-decimals="0" data-repeater-style total-row-tr data-row-total>
                            <td>
                                <div class="">
                                    <input value="{{ __('Total') }}" disabled class="form-control text-left mt-2 min-width-hover-300" type="text">
                                </div>
                            </td>
                            @php
                            $columnIndex = 0 ;
                            $totalOfRow = 0 ;
                            $currentYearRepeaterIndex = 0;
                            $currentYearTotal=0;
                            @endphp

                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                            @php
                            $currentLoanTotal = $currentLoanTotalPerYear[$yearOrMonthAsIndex] ;

                            @endphp
                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="form-group three-dots-parent">
                                        <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                            <div class="input-hidden-parent">
                                                <input readonly class="form-control copy-value-to-his-input-hidden  expandable-amount-input  repeat-to-right-input-formatted  " type="text" value="{{ number_format($currentLoanTotal,0)  }}" data-column-index="{{ $yearOrMonthAsIndex }}">
                                                <input type="hidden" class="repeat-to-right-input-hidden input-hidden-with-name  total-loans-hidden repeater-with-collapse-input" value="{{ $currentLoanTotal  }}" data-group-index="{{ $currentYearRepeaterIndex }}" data-column-index="{{ $yearOrMonthAsIndex }}" name="ee">
                                            </div>
                                            <span class="ml-2 currency-class">
                                                {{ $company->getMainFunctionalCurrency() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>





                            @php
                            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                            $currentMonthNumber = explode('-',$dateAsString)[1];
                            $currentYear= explode('-',$dateAsString)[0];
                            $currentYearTotal+=$currentLoanTotal;
                            $totalOfRow+=$currentLoanTotal;
                            @endphp


                            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))

                            <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="' '" :currentVal="$currentYearTotal" :formattedInputClasses="'exclude-from-collapse exclude-from-total exclude-from-trigger-change-when-repeat expandable-amount-input'" :classes="'year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed  exclude-from-collapse '" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                </div>

                            </td>

                            @php
                            $currentYearRepeaterIndex++;

                            $currentYearTotal = 0 ;
                            @endphp
                            @endif

                            @php
                            $columnIndex++ ;
                            @endphp

                            @endforeach

                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <input type="text" class="form-control expandable-amount-input sum-total-row sum-percentage-css" disabled value="{{ number_format($totalOfRow) }}">
                                </div>
                            </td>



                        </tr>




                    </x-slot>




                </x-tables.repeater-table>



            </div>

        </div>
    </div>


    {{-- start of Administration Fees Rate & ECL Rate   --}}
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
                    <x-show-hide-btn :query="'.leasing-admin'"></x-show-hide-btn>

                </div>
            </div>
            <div class="row">
                <hr style="flex:1;background-color:lightgray">
            </div>
            <div class="row leasing-admin">
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

                        <tr data-repeat-formatting-decimals="2" data-repeater-style>

                            <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">


                            <td>
                                <input value="{{ __('Administration Fees Rate') }}" disabled class="form-control min-width-hover-300 text-left mt-2" type="text">
                            </td>
                            @php
                            $columnIndex = 0 ;
                            @endphp
                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                            @php

                            $currentAdminFeesRateAtYearIndex = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getAdminFeesRatesAtYearOrMonthIndex($yearOrMonthAsIndex):0;
                            @endphp
                            <td>
                                <div class="d-flex align-items-center justify-content-center">


                                    <x-repeat-right-dot-inputs :currentVal="$currentAdminFeesRateAtYearIndex" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="'admin_fees_rates['.$yearOrMonthAsIndex.']'" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>

                                </div>
                            </td>
                            @php
                            $columnIndex++;
                            @endphp
                            @endforeach



                        </tr>


                        <tr data-repeat-formatting-decimals="2" data-repeater-style>


                            <td>
                                <input disabled value="{{ __('Expected Credit Loss Rate (ECL %)') }}" class="form-control min-width-hover-300 text-left" type="text">

                            </td>
                            @php
                            $columnIndex = 0 ;
                            @endphp

                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                            @php
                            $currentExpectedCreditLossRateAtYearIndex = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEclRatesAtYearOrMonthIndex($yearOrMonthAsIndex):0;

                            @endphp

                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs :currentVal="$currentExpectedCreditLossRateAtYearIndex" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="'ecl_rates['.$yearOrMonthAsIndex.']'" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>

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
    {{-- end of Administration Fees Rate & ECL Rate   --}}


    @php
    $fundedByFormatted = [
    'by-odas'=>__('By ODAs'),
    'by-mtls'=>__('By MTLs')
    ];

    @endphp

    @foreach($fundedByFormatted as $fundedById => $fundedByTitle)
    <div class="kt-portlet  " id="loan-portfolio">
        <div class="kt-portlet__body">
            <div class="row">

                <div class="col-md-10">
                    <div class="d-flex align-items-center ">
                        <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                            {{ __('Consumer New Portfolio Funding Structure') . ' [ ' . $fundedByTitle . ' ]' }}
                        </h3>
                    </div>
                </div>
                <div class="col-md-2 text-right">
                    <x-show-hide-btn :query="'.new-portfolio-funding'"></x-show-hide-btn>
                </div>
            </div>
            <div class="row">
                <hr style="flex:1;background-color:lightgray">
            </div>
            <div class="row new-portfolio-funding">
                @php
                $rowIndex = 0;
                $currentYearRepeaterIndex = 0 ;
                @endphp

                <x-tables.repeater-table :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden overflow-scroll'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                    <x-slot name="ths">
                        <x-tables.repeater-table-th class="  header-border-down " :title="__('Item')"></x-tables.repeater-table-th>
                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                        <x-tables.repeater-table-th data-column-index="{{ $yearOrMonthAsIndex }}" class="  header-border-down " :title="$yearOrMonthFormatted"></x-tables.repeater-table-th>







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
                        <x-tables.repeater-table-th class="  header-border-down " :title="__('Total')"></x-tables.repeater-table-th>
                    </x-slot>
                    <x-slot name="trs">

                        <tr data-repeat-formatting-decimals="0" data-repeater-style total-row-tr data-row-total>




                            <td>
                                <input value="{{ __('Total Funded') . ' ' . $fundedByTitle  }}" disabled class="form-control min-width-300 text-left mt-2" type="text">

                            </td>
                            @php
                            $columnIndex = 0 ;
                            $currentYearRepeaterIndex = 0 ;
                            $currentYearTotal = 0;
                            $currentRowTotal = 0;
                            @endphp
                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs data-product-id="{{ $product->id }}" data-total-projection="{{ $fundedById }}" data-group-index="{{ $currentYearRepeaterIndex }}" :numberFormatDecimals="0" :readonly="true" :removeThreeDots="true" :inputHiddenAttributes="''" :currentVal="$currentVal=$columnsTotals[$yearOrMonthAsIndex]??0" js-recalculate-equity-funding-value2 :classes="' repeater-with-collapse-input total-loans-hidden'" :is-percentage="false" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                </div>
                            </td>


                            @php
                            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                            $currentMonthNumber = explode('-',$dateAsString)[1];
                            $currentYear= explode('-',$dateAsString)[0];
                            $currentYearTotal+=$currentVal;
                            $currentRowTotal+=$currentVal;
                            @endphp


                            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
                            <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="' '" :currentVal="$currentYearTotal " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                </div>

                            </td>
                            @php
                            $currentYearRepeaterIndex++;
                            $currentYearTotal=0;
                            @endphp
                            @endif




                            @php
                            $columnIndex++;
                            @endphp
                            @endforeach

                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <input type="text" class="form-control expandable-amount-input sum-total-row sum-percentage-css" disabled value="{{ number_format($currentRowTotal) }}">
                                </div>
                            </td>

                        </tr>



                        <tr data-repeat-formatting-decimals="0" data-repeater-style>




                            <td>
                                <input value="{{ __('Equity Funding Rate (%)') }}" disabled class="form-control min-width-300 text-left mt-2" type="text">

                            </td>
                            @php
                            $columnIndex = 0 ;
                            $currentYearRepeaterIndex = 0 ;
                            @endphp
                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs data-group-index="{{ $currentYearRepeaterIndex }}" data-column-index="{{ $yearOrMonthAsIndex }}" :inputHiddenAttributes="'js-recalculate-equity-funding-value2'" :currentVal="$eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEquityFundingRatesAtYearOrMonthIndex($yearOrMonthAsIndex,$fundedById):0" :classes="'repeater-with-collapse-input only-greater-than-or-equal-zero-allowed equity-funding-rates-'.$fundedById.' equity-funding-rate-input-hidden-class'" :is-percentage="true" :name="'equity_funding_rates['.$fundedById.']['.$yearOrMonthAsIndex.']'" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                </div>
                            </td>





                            @php
                            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                            $currentMonthNumber = explode('-',$dateAsString)[1];
                            $currentYear= explode('-',$dateAsString)[0];
                            @endphp



                            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
                            <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs :isNumber="false" :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="' '" :currentVal="'-' " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                </div>

                            </td>
                            @php
                            $currentYearRepeaterIndex++;
                            @endphp
                            @endif




                            @php
                            $columnIndex++;
                            @endphp
                            @endforeach
                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <input type="text" class="form-control expandable-amount-input  sum-percentage-css" disabled value="-">
                                </div>
                            </td>


                        </tr>



                        <tr data-repeat-formatting-decimals="0" data-repeater-style total-row-tr data-row-total>

                            <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">


                            <td>
                                <input value="{{ __('Equity Funding Value') }}" disabled class="form-control min-width-300 text-left mt-2" type="text">

                            </td>
                            @php
                            $columnIndex = 0 ;
                            $currentYearRepeaterIndex = 0;
                            $currentYearTotal = 0;
                            $currentRowTotal=0;
                            @endphp
                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                            <td>
                                <div class="d-flex align-items-center justify-content-center">

                                    <x-repeat-right-dot-inputs data-group-index="{{ $currentYearRepeaterIndex }}" :readonly="true" :numberFormatDecimals="0" :currentVal="$currentVal=$eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEquityFundingValuesAtYearOrMonthIndex($yearOrMonthAsIndex,$fundedById):0" :classes="'repeater-with-collapse-input only-greater-than-or-equal-zero-allowed '" :formatted-input-classes="'equity-funding-formatted-value-class-'.$fundedById" :is-percentage="false" :name="'equity_funding_values['.$fundedById.']['.$yearOrMonthAsIndex.']'" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>

                                </div>
                            </td>

                            @php
                            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                            $currentMonthNumber = explode('-',$dateAsString)[1];
                            $currentYear= explode('-',$dateAsString)[0];
                            $currentYearTotal+=$currentVal;
                            $currentRowTotal+=$currentVal;
                            @endphp


                            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
                            <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="' '" :currentVal="$currentYearTotal " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                </div>

                            </td>
                            @php
                            $currentYearRepeaterIndex++;
                            $currentYearTotal = 0 ;
                            @endphp
                            @endif

                            @php
                            $columnIndex++;
                            @endphp
                            @endforeach

                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <input type="text" class="form-control expandable-amount-input sum-total-row sum-percentage-css" disabled value="{{ number_format($currentRowTotal) }}">
                                </div>
                            </td>

                        </tr>



                        <tr data-repeat-formatting-decimals="0" data-repeater-style>
                            <td>
                                <input disabled value="{{ __('Borrowing Funding Rate (%)') }}" class="form-control text-left" type="text">
                            </td>
                            @php
                            $columnIndex = 0 ;
                            $currentYearRepeaterIndex = 0;
                            @endphp

                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)


                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <input type="text" data-column-index="{{ $yearOrMonthAsIndex }}" readonly class="form-control expandable-percentage-input new-loan-function-rates-js" name="new_loans_funding_rates[{{ $fundedById }}][{{ $yearOrMonthAsIndex }}]" value="{{ $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingRatesAtYearOrMonthIndex($yearOrMonthAsIndex,$fundedById):100 }}"> <span class="ml-2">%</span>
                                </div>
                            </td>



                            @php
                            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                            $currentMonthNumber = explode('-',$dateAsString)[1];
                            $currentYear= explode('-',$dateAsString)[0];
                            @endphp



                            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
                            <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs :isNumber="false" :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="' '" :currentVal="'-' " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                </div>

                            </td>
                            @php
                            $currentYearRepeaterIndex++;
                            @endphp
                            @endif


                            @php
                            $columnIndex++;
                            @endphp

                            @endforeach

                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <input type="text" class="form-control expandable-amount-input  sum-percentage-css" disabled value="-">
                                </div>
                            </td>

                        </tr>






                        <tr data-repeat-formatting-decimals="0" data-repeater-style total-row-tr data-row-total>


                            <td>
                                <input disabled value="{{ __('Borrowing Funding Value') }}" class="form-control text-left" type="text">

                            </td>
                            @php
                            $columnIndex = 0 ;
                            $currentYearRepeaterIndex = 0;
                            $currentYearTotal = 0 ;
                            $currentRowTotal = 0 ;
                            @endphp

                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs data-group-index="{{ $currentYearRepeaterIndex }}" :readonly="true" :numberFormatDecimals="0" :formatted-input-classes="'new-loans-funding-formatted-value-class-'.$fundedById" :currentVal="$currentVal=$eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingValuesAtYearOrMonthIndex($yearOrMonthAsIndex,$fundedById):0 " :classes="'repeater-with-collapse-input only-greater-than-or-equal-zero-allowed'" :is-percentage="false" :name="'new_loans_funding_values['.$fundedById.']['.$yearOrMonthAsIndex.']'" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>

                                </div>
                            </td>

                            @php
                            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                            $currentMonthNumber = explode('-',$dateAsString)[1];
                            $currentYear= explode('-',$dateAsString)[0];
                            $currentYearTotal+=$currentVal;
                            $currentRowTotal+=$currentVal;
                            @endphp


                            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
                            <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="' '" :currentVal="$currentYearTotal " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                </div>

                            </td>
                            @php
                            $currentYearRepeaterIndex++;
                            $currentYearTotal = 0 ;
                            @endphp
                            @endif

                            @php
                            $columnIndex++;
                            @endphp

                            @endforeach

                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <input type="text" class="form-control expandable-amount-input sum-total-row sum-percentage-css" disabled value="{{ number_format($currentRowTotal) }}">
                                </div>
                            </td>

                        </tr>



                    </x-slot>




                </x-tables.repeater-table>
                {{-- end of fixed monthly repeating amount --}}


            </div>

        </div>
    </div>

    @endforeach







    <x-save-and-next-btn />




    <!--end::Form-->

    <!--end::Portlet-->
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
    $(document).on('change', '.financial-statement-type', function() {
        validateDuration();
    })
    $(document).on('change', 'select[name="duration_type"]', function() {
        validateDuration();
    })
    $(document).on('change', '#duration', function() {
        validateDuration();
    })

    function validateDuration() {
        let type = $('input[name="type"]:checked').val();
        let durationType = $('select[name="duration_type"]').val();
        let duration = $('#duration').val();
        let isValid = true;
        let allowedDuration = 24;
        if (type == 'forecast' && durationType == 'monthly') {
            allowedDuration = 24;
            isValid = duration <= allowedDuration;
        }
        if (type == 'forecast' && durationType == 'quarterly') {
            allowedDuration = 8;
            isValid = duration <= allowedDuration
        }
        if (type == 'forecast' && durationType == 'semi-annually') {
            allowedDuration = 4
            isValid = duration <= allowedDuration
        }
        if (type == 'forecast' && durationType == 'annually') {
            allowedDuration = 2;
            isValid = duration <= allowedDuration
        }
        if (type == 'actual' && durationType == 'monthly') {
            allowedDuration = 36;
            isValid = duration <= allowedDuration;
        }
        if (type == 'actual' && durationType == 'quarterly') {
            allowedDuration = 12
            isValid = duration <= allowedDuration;
        }
        if (type == 'actual' && durationType == 'semi-annually') {
            allowedDuration = 6;
            isValid = duration <= allowedDuration
        }
        if (type == 'actual' && durationType == 'annually') {
            allowedDuration = 3
            isValid = duration <= allowedDuration
        }
        let allowedDurationText = "{{ __('Allowed Duration') }}";

        $('#allowed-duration').html(allowedDurationText + '  ' + allowedDuration)

        if (!isValid) {
            Swal.fire({
                icon: 'error'
                , title: 'Invalid Duration. Allowed [ ' + allowedDuration + ' ]'
            , })

            $('#duration').val(allowedDuration).trigger('change');

        }


    }

    $(function() {
        $('.financial-statement-type').trigger('change')

    })

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
    function reinitalizeMonthYearInput(dateInput) {
        var currentDate = $(dateInput).val();
        var startDate = "{{ isset($studyStartDate) && $studyStartDate ? $studyStartDate : -1 }}";
        startDate = startDate == '-1' ? '' : startDate;
        var endDate = "{{ isset($studyEndDate) && $studyEndDate? $studyEndDate : -1 }}";
        endDate = endDate == '-1' ? '' : endDate;
        if (startDate && endDate) {
            $(dateInput).datepicker({
                    viewMode: "year"
                    , minViewMode: "year"
                    , todayHighlight: false
                    , clearBtn: true,


                    autoclose: true
                    , format: "yyyy-mm-01"
                , })
                .datepicker('setDate', new Date(currentDate))
                .datepicker('setStartDate', new Date(startDate))
                .datepicker('setEndDate', new Date(endDate))
        } else {
            $(dateInput).datepicker({
                    viewMode: "year"
                    , minViewMode: "year"
                    , todayHighlight: false
                    , clearBtn: true,


                    autoclose: true
                    , format: "yyyy-mm-01"
                , })
                .datepicker('setDate', new Date(currentDate))
        }



    }


    //  $(document).on('change', '#expense_type', function() {
    //      $('.js-parent-to-table').hide();
    //      let tableId = '.' + $(this).val();
    //      $(tableId).closest('.js-parent-to-table').show();
    //
    //  }) 



    $(function() {
        $('#expense_type').trigger('change')
        $('.js-type-btn.active').trigger('click')
    })

    $(function() {
        $(document).on('click', '.js-show-all-categories-trigger', function() {
            const elementToAppendIn = $(this).parent().find('.js-append-into');
            const texts = [];
            let lis = '';
            text = '<u><a href="#" data-close-new class="text-decoration-none mb-2 d-inline-block text-nowrap ">' + 'Add New' + '</a></u>'
            lis += '<li >' + text + '</li>'
            $(this).closest('table').find('.js-show-all-categories-popup').each(function(index, element) {
                let text = $(element).val().trim();
                if (text && !texts.includes(text)) {
                    texts.push(text)
                    text = '<a href="#" data-add-new class="text-decoration-none mb-2 d-inline-block">' + text + '</a>'
                    lis += '<li >' + text + '</li>'
                }
            })




            elementToAppendIn.removeClass('d-none');
            elementToAppendIn.find('ul').empty().append(lis);
        })


    })
    $(document).on('click', '[data-add-new]', function(e) {
        e.preventDefault();
        let content = $(this).html();
        $(this).closest('.js-common-parent').find('input').val(content);
    })
    $(document).on('click', '[data-close-new]', function(e) {
        e.preventDefault();
        $(this).closest('.js-append-into').addClass('d-none');
        $(this).closest('.js-common-parent').find('input').val('').focus();
    })
    $(document).on('click', function(e) {
        let closestParent = $(e.target).closest('.js-append-into').length;
        if (!closestParent && !$(e.target).hasClass('js-show-all-categories-trigger')) {
            $('.js-append-into').addClass('d-none');
        }
    })
    $(function() {
        $('.repeater-with-select2').closest('.repeater-class').find('[data-repeater-delete]').trigger('click');
        $('.repeater-with-select2').closest('.repeater-class').find('[data-repeater-create]').trigger('click');
    });

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


    $('select.js-condition-to-select').change(function() {
        const value = $(this).val();
        const conditionalValueTwoInput = $(this).closest('tr').find('input.conditional-b-input');
        if (value == 'between-and-equal' || value == 'between') {
            conditionalValueTwoInput.prop('disabled', false).trigger('change');
        } else {
            conditionalValueTwoInput.prop('disabled', true).trigger('change');
        }
    })

    $('select.js-condition-to-select').trigger('change');
    $(document).on('change', '.conditional-input', function() {
        if (!$(this).closest('tr').find('conditional-b-input').prop('disabled')) {
            const conditionalA = $(this).closest('tr').find('.conditional-a-input').val();
            const conditionalB = $(this).closest('tr').find('.conditional-b-input').val();
            if (conditionalA >= conditionalB) {
                if (conditionalA == 0 && conditionalB == 0) {
                    return;
                }
                Swal.fire('conditional a must be less than conditional b value');
                $(this).closest('tr').find('.conditional-a-input').val($(this).closest('tr').find('.conditional-b-input').val() - 1);
            }
        }

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
    $(document).on('change', 'select.expense_category', function() {
        const parent = $(this).closest('tr');
        const expenseCategoryId = $(this).val();
        const currentSelected = $(parent).find('select.expense_name_id').attr('data-current-selected');
        $.ajax({
            url: "{{ route('get.expense.name.for.category.only.in.branch',['company'=>$company->id,'study'=>$study->id]) }}"
            , data: {
                expenseCategoryId
            }
            , success: function(res) {
                let result = res.data;
                let options = '';
                for (index in result) {
                    var row = result[index];
                    options += `<option ${currentSelected==row.id ? 'selected':''} value="${row.id}">${row.name}</option>`;
                }
                $(parent).find('select.expense_name_id').empty().append(options).trigger('change');
            }
        })
    })
    $('select.expense_category').trigger('change')

</script>

<script>
    $(function() {
        //	$('button.js-type-btn[data-value="percentage_of_sales"]').trigger('click')
    })

</script>
<script>


</script>
<script>
    $('.product-input-class').on('change', function() {
        const columnIndex = $(this).attr('data-column-index');
        let total = 0;
        $(this).closest('table').find('input[type="hidden"][data-column-index="' + columnIndex + '"]').each(function(index, currentInput) {
            total += parseFloat($(currentInput).val());
        })
        $(this).closest('table').find('input.sum-total-row[data-column-index="' + columnIndex + '"]').val(total);
    })

    $(document).on('change', 'input.seasonality-class', function() {
        let total = 0;
        $(this).closest('tr').find('input[type="hidden"]').each(function(index, currentInput) {
            total += parseFloat($(currentInput).val());
        })
        $(this).closest('tr').find('input.sum-total-column').val(number_format(total, 1));
    })

</script>
<script>
    $(document).on('change', '.current-loan-input', function() {
        let total = 0
        let currentLoanIndex = parseInt($(this).attr('data-column-index'))
        $('.current-loan-input[data-column-index="' + currentLoanIndex + '"]').each(function(index, element) {
            total += parseFloat($(element).val())
        })
        $(this).closest('table').find('[data-row-total] .repeat-to-right-input-formatted[data-column-index="' + currentLoanIndex + '"]').val(number_format(total)).trigger('change')

    })

</script>

<script>
    

</script>
@endpush
