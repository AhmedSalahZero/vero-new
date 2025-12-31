@extends('layouts.dashboard')
@php
use App\Models\NonBankingService\Study;
use App\Models\NonBankingService\PortfolioMortgageBreakdown;
@endphp
@section('css')
<x-styles.commons></x-styles.commons>
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">

@endsection
@section('sub-header')
<x-main-form-title :id="'main-form-title'" :class="''">{{ $title }}</x-main-form-title>

{{-- <x-navigators-dropdown :navigators="$navigators"></x-navigators-dropdown> --}}

@endsection
@section('content')

<div class="row">
    <div class="col-md-12">


        <form id="factoring-loans" class="kt-form kt-form--label-right" method="POST" enctype="multipart/form-data" action="{{  isset($disabled) && $disabled ? '#' :  $storeRoute  }}">

            @csrf
            <input type="hidden" name="company_id" value="{{ getCurrentCompanyId()  }}">
            <input type="hidden" name="creator_id" value="{{ \Auth::id()  }}">
            <input type="hidden" name="study_id" value="{{ $study->id }}">


            {{-- start of Factoring Revenue Projection By Category   --}}

            {{-- start of Factoring New Portfolio Funding Structure   --}}
            @php
            $countCategories = $model->portfolioMortgageRevenueProjectionByCategories->count() ;
            @endphp
            @foreach(count( $model->portfolioMortgageRevenueProjectionByCategories) ? $model->portfolioMortgageRevenueProjectionByCategories : [null] as $currentIndex => $portfolioMortgageRevenueProjectionByCategory )
            <div class="kt-portlet ">
                <div class="kt-portlet__body">

                    <div class="row">

                        <div class="col-md-11">
                            <div class="d-flex align-items-center ">
                                <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                                    {{ __('Portfolio Mortgage Revenue Projection - Please Choose Duration ') }}
									
									 {{-- {{ getThreeDotsHint() }} --}}
                                </h3>
                                <div class="form-group mb-0 d-flex w-10 mr-2" style="gap:20px;">
                                    <input type="hidden" name="portfolioMortgageRevenueProjectionByCategories[{{ $currentIndex }}][id]" value="{{ $portfolioMortgageRevenueProjectionByCategory ? $portfolioMortgageRevenueProjectionByCategory->id :0 }}">
                                    <select name="portfolioMortgageRevenueProjectionByCategories[{{ $currentIndex }}][portfolio_mortgage_duration]" class="form-control  border-red seasonlity-select main-seasonality-select">
                                        @for($i = 5 ; $i <= 10 ; $i++) <option value="{{ $i }}" @if($portfolioMortgageRevenueProjectionByCategory && $portfolioMortgageRevenueProjectionByCategory->portfolio_mortgage_duration == $i )
                                            selected
                                            @endif

                                            > {{ $i }} {{ __('Years') }} </option>
                                            @endfor
                                    </select>



                                </div>
 <h3 class="font-weight-bold form-label kt-subheader__title small-caps " style="">
                                   {{ getThreeDotsHint() }}
                                </h3>
                            </div>

     

                        </div>
                        <div class="col-md-1 text-right">
                            <x-show-hide-btn :query="'.revenue-projection-by-category'"></x-show-hide-btn>
                        </div>
                    </div>



                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    <div class="row revenue-projection-by-category">
                        @php
                        $rowIndex = 0;
						$currentYearRepeaterIndex = 0 ;
					
                        @endphp


                        <x-tables.repeater-table :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden overflow-scroll'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                            <x-slot name="ths">
                                <x-tables.repeater-table-th class="  header-border-down first-column-th-class" :title="__('Item')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class=" tenor-selector-class header-border-down " :title="__('Spread <br> Rate')"></x-tables.repeater-table-th>
                                @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                <x-tables.repeater-table-th data-column-index="{{ $yearOrMonthAsIndex }}" class=" interval-class header-border-down " :title="$yearOrMonthFormatted"></x-tables.repeater-table-th>
								
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
                                <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Total')"></x-tables.repeater-table-th>
                            </x-slot>
                            <x-slot name="trs">
                                @if($isYearsStudy)
                                <tr data-repeat-formatting-decimals="0" data-repeater-style>

                                    <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">


                                    <td>
                                        <div class="">
                                            <input value="{{ __('Operating Months Per Year') }}" disabled class="form-control min-width-300 text-left mt-2" type="text">
                                        </div>
                                    <td></td>

                                    </td>
                                    @php
                                    $columnIndex = 0 ;
									 $currentYearRepeaterIndex = 0;
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
                                    $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                                    $currentMonthNumber = explode('-',$dateAsString)[1];
                                    $currentYear= explode('-',$dateAsString)[0];
                                    @endphp
                                    @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
                                    <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :isNumber="false" :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="' '" :currentVal="'-' " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
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
                                @endif










                                <tr data-repeat-formatting-decimals="0" data-repeater-style total-row-tr data-row-total>

                                    <td>
                                        <input value="{{ __('Portfolio Mortgage Avg Transactions Amount') }}" disabled class="form-control min-width-300 text-left mt-2" type="text">
                                    </td>
                                    <!-- margin rate -->
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :removeThreeDots="true" :removeThreeDotsClass="true" :currentVal="$portfolioMortgageRevenueProjectionByCategory ? $portfolioMortgageRevenueProjectionByCategory->getMarginRate() : 0" :classes="'only-greater-than-or-equal-zero-allowed exclude-from-total'" :is-percentage="true" :name="'portfolioMortgageRevenueProjectionByCategories['.$currentIndex.']['.'margin_rate'.']'" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>

                                    @php
                                    $columnIndex = 0 ;
									$currentYearRepeaterIndex = 0 ;
										$currentYearTotal = 0;
						$currentRowTotal = 0;
                                    @endphp
                                    @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                    @php
                                    $currentVal = $portfolioMortgageRevenueProjectionByCategory ? $portfolioMortgageRevenueProjectionByCategory->getPortfolioMortgageTransactionProjectionAtYearOrMonthIndexIndex($yearOrMonthAsIndex) : 0;
									if(!$isYearsStudy){
										$totalPerYears[$yearOrMonthAsIndex] = isset($totalPerYears[$yearOrMonthAsIndex]) ? $totalPerYears[$yearOrMonthAsIndex] + $currentVal : $currentVal;
									}
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :number-format-decimals="0" :currentVal="$currentVal" data-group-index="{{ $currentYearRepeaterIndex }}" :classes="'only-greater-than-or-equal-zero-allowed repeater-with-collapse-input  js-recalculate-equity-funding-value'" :is-percentage="false" :name="'portfolioMortgageRevenueProjectionByCategories['.$currentIndex.']['.'portfolio_mortgage_transactions_projections'.']['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
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
									$currentYearTotal = 0;
                                    @endphp
                                    @endif
									
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach

                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <input type="text" class="form-control expandable-amount-input sum-total-row sum-percentage-css" disabled value="{{ number_format($currentRowTotal) }}">
                                        </div>
                                    </td>




                                </tr>
                                @if($isYearsStudy)

                                <tr data-repeat-formatting-decimals="0" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Frequency Per Year') }}" disabled class="form-control min-width-300 text-left mt-2" type="text">
                                    </td>
                                    <td></td>

                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                    @php
                                    $currentVal = $portfolioMortgageRevenueProjectionByCategory ? $portfolioMortgageRevenueProjectionByCategory->getFrequencyPerYearAtYearOrMonthIndex($yearOrMonthAsIndex) : 1;
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-form.select :required="true" :label="''" :pleaseSelect="false" :selectedValue="$currentVal" :options="[['title'=>__('Once Per Year'),'value'=>0],['title'=>__('Monthly'),'value'=>1],['title'=>__('Every 2 Months'),'value'=>'2'],['title'=>__('Every 3 Months'),'value'=>'3'],['title'=>__('Every 4 Months'),'value'=>'4'],['title'=>__('Every 6 Months'),'value'=>'6']]" :add-new="false" class="select2-select  repeater-select  " :all="false" :name="'portfolioMortgageRevenueProjectionByCategories['.$currentIndex.']['.'frequency_per_year'.']['.$yearOrMonthAsIndex.']'"></x-form.select>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <input type="text" class="form-control expandable-amount-input  sum-percentage-css" disabled value="-">
                                        </div>
                                    </td>

                                </tr>

                                <tr data-repeat-formatting-decimals="0" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Start From') }}" disabled class="form-control min-width-300 text-left mt-2" type="text">
                                    </td>
                                    <td></td>

                                    @php
                                    $columnIndex = 0 ;
                                    $startFromIndex = 0 ;
                                    @endphp
                                    @foreach($yearsWithItsMonths as $year=>$monthsForThisYearArray)
                                    @php
                                    $startFromIndex = array_key_first($monthsForThisYearArray);

                                    $months = getMonthNames($startFromIndex);
                                    $currentVal = $portfolioMortgageRevenueProjectionByCategory ? $portfolioMortgageRevenueProjectionByCategory->getStartFromAtYearIndex($year) : 1;
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-form.select :required="true" :label="''" :pleaseSelect="false" :selectedValue="$currentVal" :options="$months" :add-new="false" class="select2-select  repeater-select  " :all="false" :name="'portfolioMortgageRevenueProjectionByCategories['.$currentIndex.']['.'start_from'.']['.$year.']'"></x-form.select>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach

                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <input type="text" class="form-control expandable-amount-input  sum-percentage-css" disabled value="-">
                                        </div>
                                    </td>
                                </tr>




                                <tr data-repeat-formatting-decimals="0" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Total Per Year') }}" disabled class="form-control min-width-300 text-left mt-2" type="text">
                                    </td>
                                    <td>

                                    </td>

                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                    @php
									$currentVal = 0 ;
									if($portfolioMortgageRevenueProjectionByCategory){
                                    	$currentVal = array_values($portfolioMortgageRevenueProjectionByCategory->total_monthly_amounts_per_years?:[])[$columnIndex]??0;
									}
									$totalPerYears[$columnIndex] = isset($totalPerYears[$columnIndex]) ? $totalPerYears[$columnIndex]+$currentVal:$currentVal  ;
									
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :removeThreeDots="true" :removeThreeDotsClass="true" :readonly="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="''" :is-percentage="false" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <input type="text" class="form-control expandable-amount-input  sum-percentage-css" disabled value="-">
                                        </div>
                                    </td>

                                </tr>
                                @endif



                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                      
                                    
									
									              <td></td>
												  
												  <td>

                                        @if($countCategories > 1)
                                        <div class="row">
                                            <div class="col-md-12">

                                                <div class="text-center">
                                                    <a href="{{ route('delete.portfolio.mortgage.category',['company'=>$company->id,'study'=>$study->id,'portfolioMortgageCategory'=>$portfolioMortgageRevenueProjectionByCategory->id]) }}" class="btn btn-danger  w-full text-white " value="">{{ __('Delete') }}</a>
                                                </div>

                                            </div>

                                        </div>
                                        @endif
                                    </td>
									
												  <td>
									<div class="row">
										<div class="col-md-12">
										 <input type="submit" name="calculate-portfolio" class="btn bg-green active-style save-form" value="{{  __('Calculate Net Disbursement') }}">
										</div>
									</div>
									</td>
									
									
                                    <td>
                                        @if($loop->last)
                                        <div class="row">
                                            <div class="col-md-12">
                                                {{-- <div class="text-right"> --}}
                                                    <a href="{{ route('add.new.portfolio.mortgage.category',['company'=>$company->id,'study'=>$study->id]) }}" type="submit" name="save-and-continue" class="btn active-style">
                                                        {{ __('Add New Portfolio') }}
                                                    </a>
                                                {{-- </div> --}}


                                            </div>

                                        </div>
                                        @endif
										
										

                                    </td>
									
                                </tr>



















                            </x-slot>





                        </x-tables.repeater-table>





                        {{-- end of fixed monthly repeating amount --}}


                    </div>

                </div>
            </div>
            @endforeach

            {{-- end of Factoring New Portfolio Funding Structure   --}}


            {{-- end of Factoring Revenue Projection By Category   --}}



			@foreach($totalPerYears??[] as $columnIndex => $totalForLoan)
					<input type="hidden" class="total-loans-hidden" data-column-index="{{ $columnIndex }}" value="{{ $totalForLoan }}">
			@endforeach




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
                            <x-show-hide-btn :query="'.admin-fees'"></x-show-hide-btn>

                        </div>
                    </div>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    <div class="row admin-fees">
                        @php
                        $rowIndex = 0;
                        @endphp


                        <x-tables.repeater-table :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden overflow-scroll'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                            <x-slot name="ths">
                                <x-tables.repeater-table-th class=" header-border-down " :title="__('Item')"></x-tables.repeater-table-th>
                                @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                <x-tables.repeater-table-th class="header-border-down " :title="$yearOrMonthFormatted"></x-tables.repeater-table-th>
                                @endforeach
                            </x-slot>
                            <x-slot name="trs">

                                <tr data-repeat-formatting-decimals="2" data-repeater-style>

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


                                            <x-repeat-right-dot-inputs :currentVal="$eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getAdminFeesRatesAtYearOrMonthIndex($yearOrMonthAsIndex):0" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="'admin_fees_rates['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++;
                                    @endphp
                                    @endforeach



                                </tr>


                                <tr data-repeat-formatting-decimals="2" data-repeater-style>


                                    <td>
                                        <input disabled value="{{ __('Expected Credit Loss Rate (ECL %)') }}" class="form-control max-w-300 text-left" type="text">

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
            {{-- end of Administration Fees Rate & ECL Rate   --}}




            {{-- start of Factoring New Portfolio Funding Structure   --}}
            <div class="kt-portlet" id="loan-portfolio">
                <div class="kt-portlet__body">
                    <div class="row">

                        <div class="col-md-10">
                            <div class="d-flex align-items-center ">
                                <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                                    {{ __('Portfolio Mortgage New Portfolio Funding Structure') }}
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
						 $currentYearRepeaterIndex = 0;
                        @endphp


                        <x-tables.repeater-table :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden overflow-scroll'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                            <x-slot name="ths">
                                <x-tables.repeater-table-th class=" category-selector-class header-border-down " :title="__('Item')"></x-tables.repeater-table-th>
                                @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                <x-tables.repeater-table-th data-column-index="{{ $yearOrMonthAsIndex }}" class=" interval-class header-border-down " :title="$yearOrMonthFormatted"></x-tables.repeater-table-th>
								
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
                                <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Total')"></x-tables.repeater-table-th>
                            </x-slot>
                            <x-slot name="trs">
							
								@include('loan-structure-trs')
								

                                {{-- <tr data-repeat-formatting-decimals="2" data-repeater-style >



                                    <td>
                                        <input value="{{ __('Equity Funding Rate (%)') }}" disabled class="form-control min-width-300 text-left mt-2" type="text">

                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">

                                            <x-repeat-right-dot-inputs :inputHiddenAttributes="'js-recalculate-equity-funding-value'" :currentVal="$eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEquityFundingRatesAtYearOrMonthIndex($yearOrMonthAsIndex):0" :classes="'only-greater-than-or-equal-zero-allowed equity-funding-rates equity-funding-rate-input-hidden-class'" :is-percentage="true" :name="'equity_funding_rates['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                                        </div>
                                    </td>
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
                                    @endphp
                                    @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :readonly="true" :numberFormatDecimals="0" :currentVal="$eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEquityFundingValuesAtYearOrMonthIndex($yearOrMonthAsIndex):0" :classes="'only-greater-than-or-equal-zero-allowed '" :formatted-input-classes="'equity-funding-formatted-value-class'" :is-percentage="false" :name="'equity_funding_values['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++;
                                    @endphp
                                    @endforeach

                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <input type="text" class="form-control expandable-amount-input sum-total-row sum-percentage-css" disabled value="0">
                                        </div>
                                    </td>

                                </tr>



                                <tr data-repeat-formatting-decimals="2" data-repeater-style>
                                    <td>
                                        <input disabled value="{{ __('New Loans Funding Rate (%)') }}" class="form-control max-w-300 text-left" type="text">
                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp

                                    @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)


                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <input type="text" data-column-index="{{ $columnIndex }}" readonly class="form-control  expandable-percentage-input new-loan-function-rates-js" name="new_loans_funding_rates[{{ $yearOrMonthAsIndex }}]" value="{{ $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingRatesAtYearOrMonthIndex($yearOrMonthAsIndex):100 }}"> <span class="ml-2">%</span>
                                        </div>
                                    </td>
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
                                        <input disabled value="{{ __('New Loans Funding Value') }}" class="form-control max-w-300 text-left" type="text">

                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp

                                    @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)


                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :readonly="true" :numberFormatDecimals="0" :formatted-input-classes="'new-loans-funding-formatted-value-class'" :currentVal="$eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingValuesAtYearOrMonthIndex($yearOrMonthAsIndex):0 " :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="false" :name="'new_loans_funding_values['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++;
                                    @endphp

                                    @endforeach

                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <input type="text" class="form-control expandable-amount-input sum-total-row sum-percentage-css" disabled value="0">
                                        </div>
                                    </td>

                                </tr> --}}

                            </x-slot>




                        </x-tables.repeater-table>
                        {{-- end of fixed monthly repeating amount --}}


                    </div>

                </div>
            </div>
            {{-- end of Factoring New Portfolio Funding Structure   --}}

            <x-save-or-back />





























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

            let formId = $(this).closest('form').attr('id')

            let form = document.getElementById(formId);
            var formData = new FormData(form);
            formData.append('save', $(this).attr('name'))
            formData.append('submitBtnType', formId)

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

<script src="/custom/js/non-banking-services/common.js"></script>
<script src="/custom/js/non-banking-services/select2.js"></script>
<script src="/custom/js/non-banking-services/revenue-stream-breakdown.js"></script>
<script>
    // const clone = $('#salah').clone();
    //  $('#factoring-loans').append(clone)
    $(document).on('click', '.delete-class', function() {
        // $(this).closest('.kt-portlet').remove();
    })

</script>
{{-- <script></script> --}}
@endsection
