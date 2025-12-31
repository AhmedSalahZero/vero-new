@foreach($salesProjectsPerTypes as $type => $salesProjectsPerProducts)
@php
$rowsTotals =[];
$columnsTotals =[];
$titleFormatted = [
'all-branches'=>__('Existing Branches'),
'new-branches'=>__('New Branches'),
'by-branch'=>$branchName ? $branchName : __('Existing Branches')
][$type];

@endphp
@if(isset($salesProjectsPerProducts['total']) && array_sum($salesProjectsPerProducts['total']))
<div class="kt-portlet">
    <div class="kt-portlet__body">
        <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
            {{ __('Monthly Loan Amounts ') . $titleFormatted }}
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
							
                            @endphp

                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                            <td>

                                @php
                                $currentVal = $monthlyLoanAmounts[$yearOrMonthAsIndex]??0;
                                $columnsTotals[$yearOrMonthAsIndex] = isset($columnsTotals[$yearOrMonthAsIndex] ) ? $columnsTotals[$yearOrMonthAsIndex] +$currentVal : $currentVal;
                              //  $rowsTotals[$product->id] = isset($rowsTotals[$product->id] ) ? $rowsTotals[$product->id] +$currentVal : $currentVal;
                                $currentYearTotal+=$currentVal;
                                @endphp
                                <x-repeat-right-dot-inputs data-group-index="{{ $currentYearRepeaterIndex }}" :disabled="true" :numberFormatDecimals="0" :formattedInputClasses="' '" :mark="''" :removeThreeDots="true" :removeCurrency="true" :currentVal="$currentVal" :classes="'repeater-with-collapse-input'" :is-percentage="false" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                            </td>





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



                            @endforeach

                            <td>
                                <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="0" :formattedInputClasses="' '" :mark="''" :removeThreeDots="true" :removeCurrency="true" :currentVal="$columnsTotals[$yearOrMonthAsIndex]" :classes="''" :is-percentage="false" :name="''" :columnIndex="-1"></x-repeat-right-dot-inputs>
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
							$totalRow = 0;
                            @endphp
                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                            <td>

                                @php
                                $currentVal = $columnsTotals[$yearOrMonthAsIndex]??0;
                                $columnsTotals[$yearOrMonthAsIndex] = isset($columnsTotals[$yearOrMonthAsIndex] ) ? $columnsTotals[$yearOrMonthAsIndex] +$currentVal : $currentVal;
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
							$totalRow +=$currentYearTotal;
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
@endif

@endforeach


@foreach($salesProjectsPerFundedBy as $fundedBy => $salesProjectsPerProducts)

@php
$rowsTotals =[];
$columnsTotals =[];
$fundedByFormatted = [
'by-odas'=>__('By ODAs'),
'by-mtls'=>__('By MTLs')
][$fundedBy];
@endphp
@if(isset($salesProjectsPerProducts['total']) && array_sum($salesProjectsPerProducts['total']))
<div class="kt-portlet">
    <div class="kt-portlet__body">
        <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
            {{ __('Monthly Loan Amounts ') . $fundedByFormatted }}
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
							$rowsTotals = [];
                            @endphp

                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                            <td>

                                @php
                                $currentVal = $monthlyLoanAmounts[$yearOrMonthAsIndex]??0;
                                $columnsTotals[$yearOrMonthAsIndex] = isset($columnsTotals[$yearOrMonthAsIndex] ) ? $columnsTotals[$yearOrMonthAsIndex] +$currentVal : $currentVal;
                                $rowsTotals[$product->id] = isset($rowsTotals[$product->id] ) ? $rowsTotals[$product->id] +$currentVal : $currentVal;
                                $currentYearTotal+=$currentVal;
					
                                @endphp
                                <x-repeat-right-dot-inputs data-group-index="{{ $currentYearRepeaterIndex }}" :disabled="true" :numberFormatDecimals="0" :formattedInputClasses="' '" :mark="''" :removeThreeDots="true" :removeCurrency="true" :currentVal="$currentVal" :classes="'repeater-with-collapse-input'" :is-percentage="false" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                            </td>


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
                            $currentYearTotal = 0 ;
                            @endphp
                            @endif


                            @endforeach

                            <td>
                                <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="0" :formattedInputClasses="' '" :mark="''" :removeThreeDots="true" :removeCurrency="true" :currentVal="array_sum($rowsTotals)" :classes="''" :is-percentage="false" :name="''" :columnIndex="-1"></x-repeat-right-dot-inputs>
                            </td>


                        </tr>
                        @endforeach

                        <tr data-repeat-formatting-decimals="0" data-repeater-style>
                            <td class="td-classes">
                                <div>

                                    <input value="{{ __('Totals2222') }}" disabled="" class="form-control text-left min-w-300" type="text">
                                </div>

                            </td>


                            @php
                            $currentYearRepeaterIndex = 0;
                            $currentYearTotal = 0 ;
							$rowTotal = 0 ;
                            @endphp

                            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                            @php
                            $currentVal = $columnsTotals[$yearOrMonthAsIndex]??0;
							$rowTotal+=$currentVal;
                            $columnsTotals[$yearOrMonthAsIndex] = isset($columnsTotals[$yearOrMonthAsIndex] ) ? $columnsTotals[$yearOrMonthAsIndex] +$currentVal : $currentVal;
                    //        $rowsTotals[$product->id] = isset($rowsTotals[$product->id] ) ? $rowsTotals[$product->id] +$currentVal : $currentVal;
                            $currentYearTotal+=$currentVal;
                            @endphp
                            <td>

                                <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="0" :formattedInputClasses="' '" :mark="''" :removeThreeDots="true" :removeCurrency="true" :currentVal="$currentVal" :classes="''" :is-percentage="false" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                            </td>

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


                            @endforeach

                            <td>

                                <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="0" :formattedInputClasses="' '" :mark="''" :removeThreeDots="true" :removeCurrency="true" :currentVal="$rowTotal" :classes="''" :is-percentage="false" :name="''" :columnIndex="-1"></x-repeat-right-dot-inputs>
                            </td>


                        </tr>





                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>
@endif

@if(!isset($hideFundingStructure))

<div class="kt-portlet  " id="loan-portfolio">
    <div class="kt-portlet__body">
        <div class="row">

            <div class="col-md-10">
                <div class="d-flex align-items-center ">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                        {{ __('Microfinance New Portfolio Funding Structure') . ' [ ' . $fundedByFormatted . ' ]' }}
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
                            <input value="{{ __('Microfinance New Portfolio Amounts') }}" disabled class="form-control min-w-300 text-left mt-2" type="text">
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
                                <x-repeat-right-dot-inputs data-group-index="{{ $currentYearRepeaterIndex }}" :numberFormatDecimals="0" :readonly="true" :removeThreeDots="true" :inputHiddenAttributes="''" :currentVal="$currentVal=$columnsTotals[$yearOrMonthAsIndex]??0" :classes="'js-recalculate-equity-funding-value repeater-with-collapse-input total-loans-hidden'" :is-percentage="false" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                            </div>
                        </td>


                        @php
                        $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                        $currentMonthNumber = explode('-',$dateAsString)[1];
                        $currentYear= explode('-',$dateAsString)[0];
                        $currentTotalPortfolioAmount[$yearOrMonthAsIndex] = $currentVal ;
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
                            <input value="{{ __('Equity Funding Rate (%)') }}" disabled class="form-control  text-left mt-2" type="text">

                        </td>
                        @php
                        $columnIndex = 0 ;
                        $currentYearRepeaterIndex = 0 ;
                        @endphp
                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                        <td>
                            <div class="d-flex align-items-center justify-content-center">
                                <x-repeat-right-dot-inputs data-group-index="{{ $currentYearRepeaterIndex }}" data-column-index="{{ $yearOrMonthAsIndex }}" :inputHiddenAttributes="'js-recalculate-equity-funding-value'" :currentVal="$currentFundingRate = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEquityFundingRatesAtYearOrMonthIndex($yearOrMonthAsIndex,$fundedBy):0" :classes="'repeater-with-collapse-input only-greater-than-or-equal-zero-allowed equity-funding-rates equity-funding-rate-input-hidden-class'" :is-percentage="true" :name="'equity_funding_rates['.$fundedBy.']['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                            </div>
                        </td>





                        @php
                        $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                        $currentMonthNumber = explode('-',$dateAsString)[1];
                        $currentYear= explode('-',$dateAsString)[0];
                        $currentFundingRates[$yearOrMonthAsIndex] = $currentFundingRate;
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
                            <input value="{{ __('Equity Funding Value') }}" disabled class="form-control  text-left mt-2" type="text">

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

                                <x-repeat-right-dot-inputs data-group-index="{{ $currentYearRepeaterIndex }}" :readonly="true" :numberFormatDecimals="0" :currentVal="$currentVal=$currentTotalPortfolioAmount[$yearOrMonthAsIndex] * $currentFundingRates[$yearOrMonthAsIndex]/100 " :classes="'repeater-with-collapse-input only-greater-than-or-equal-zero-allowed '" :formatted-input-classes="'equity-funding-formatted-value-class'" :is-percentage="false" :name="'equity_funding_values['.$fundedBy.']['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

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
                                <input type="text" data-column-index="{{ $columnIndex }}" readonly class="form-control expandable-percentage-input new-loan-function-rates-js" name="new_loans_funding_rates[{{ $fundedBy }}][{{ $yearOrMonthAsIndex }}]" value="{{ $currentBorrowingRate = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingRatesAtYearOrMonthIndex($yearOrMonthAsIndex,$fundedBy):100 }}"> <span class="ml-2">%</span>
                            </div>
                        </td>



                        @php
                        $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
                        $currentMonthNumber = explode('-',$dateAsString)[1];
                        $currentYear= explode('-',$dateAsString)[0];
                        $currentBorrowingRates[$yearOrMonthAsIndex] = $currentBorrowingRate;
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
                                <x-repeat-right-dot-inputs data-group-index="{{ $currentYearRepeaterIndex }}" :readonly="true" :numberFormatDecimals="0" :formatted-input-classes="'new-loans-funding-formatted-value-class'" :currentVal="$currentVal=$currentTotalPortfolioAmount[$yearOrMonthAsIndex] * $currentBorrowingRates[$yearOrMonthAsIndex]/100" :classes="'repeater-with-collapse-input only-greater-than-or-equal-zero-allowed'" :is-percentage="false" :name="'new_loans_funding_values['.$fundedBy.']['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

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
@endif

@endforeach
