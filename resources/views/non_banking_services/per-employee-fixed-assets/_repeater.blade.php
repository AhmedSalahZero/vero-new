@php
use App\Models\NonBankingService\FixedAssetName;
@endphp

<div class="kt-portlet">
    <div class="kt-portlet__body">
        {{-- <h3 class="font-weight-bold text-black form-label kt-subheader__title small-caps mr-5 text-nowrap" style=""> {{ __('Items Cost') }}</h3> --}}
        <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
        <input id="net-branch-opening-projections" class="net-branch-opening-projections" type="hidden" value="{{ json_encode($newBranchCountPerDateIndex) }}">
        @foreach($newBranchCountPerDateIndex as $dateAsIndex=>$newBranchCountPerDateIndexRow)
        <input data-month-index="{{ $dateAsIndex }}" data-year-index="{{ $datesIndexWithYearIndex[$dateAsIndex] }}" class="year-index-month-index" type="hidden">
        @endforeach

        <x-tables.repeater-table :hideByDefault="false" :initEmpty="false" :removeActionBtn="false" :first-element-deletable="false" :font-size-class="'font-14px'" :append-save-or-back-btn="false" :repeater-with-select2="true" :parentClass="'js-toggle-visibility-----'" :tableName="$tableId " :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
            <x-slot name="ths">
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="  header-border-down " :title="__('Item <br> Name')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" header-border-down " :title="__('Department <br> Name')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" header-border-down " :title="__('Position <br> Name')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" header-border-down " :title="__('Item <br> Cost')"></x-tables.repeater-table-th>
                {{-- <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" header-border-down rate-class" :title="__('VAT <br> Rate')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" header-border-down rate-class" :title="__('Withhold <br> Tax %')"></x-tables.repeater-table-th> --}}
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" header-border-down rate-class" :title="__('Contingency <br> Rate %')"></x-tables.repeater-table-th>

                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="  header-border-down " :title="__('Cost Annual <br> Increase %')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="header-border-down" :title="__('Payment <br> Terms')" :helperTitle="__('You can either choose one of the system default terms (cash, quarterly, semi-annually, or annually), if else please choose Customize to insert your payment terms')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="header-border-down" :title="__('Depreciation <br> Duration')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="header-border-down" :title="__('Replacement <br> Cost %')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="header-border-down" :title="__('Replacement <br> Interval')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="header-border-down" :title="__('Count')"></x-tables.repeater-table-th>

            </x-slot>
            <x-slot name="trs">
                @php
                $rows = isset($model) ? $model->fixedAssets->where('type',$fixedAssetType) : [-1] ;
                @endphp
                @foreach( count($rows) ? $rows : [-1] as $subModel)
                @php
                if( !($subModel instanceof \App\Models\NonBankingService\FixedAsset) ){
                unset($subModel);
                }
                @endphp
                <tr data-repeater-item data-repeat-formatting-decimals="2" data-repeater-style>

                    <td class="text-center">
                        <div class="">
                            <i data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle">
                            </i>
                        </div>
                    </td>
                    <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">

                    <td>
                        <div class="min-w-200">
                            <x-form.select :selectedValue="isset($subModel) ? $subModel->getNameId() : 0" :options="FixedAssetName::getPerEmployeeAllForSelect2($company)" :add-new="false" class="select2-select repeater-select expense_category " :all="false" name="@if($isRepeater) name_id @else {{ $tableId }}[0][name_id] @endif"></x-form.select>
                        </div>
                    </td>

                    <td>
                        <div class="min-w-200">
                            <x-form.select :multiple="true" :selectedValue="isset($subModel) ? $subModel->getDepartmentIds() : []" :options="$departmentFormattedForSelect2" :add-new="false" class="select2-select repeater-select department-class " :all="false" name="@if($isRepeater) department_ids @else {{ $tableId }}[0][department_ids] @endif"></x-form.select>
                        </div>

                    </td>

                    <td>
                        <div class="min-w-200">
                            <x-form.select :selectedValue="isset($subModel) ? $subModel->getPositionIds() : []" :multiple="true" :options="[]" :add-new="false" class="select2-select repeater-select position-class " :all="false" name="@if($isRepeater) position_ids @else {{ $tableId }}[0][position_ids] @endif"></x-form.select>
                        </div>
                    </td>

                    <td>
                        <div class="">
                            <input value="{{ isset($subModel) ? $subModel->getItemCost() : 0 }}" @if($isRepeater) name="ffe_item_cost" @else name="{{ $tableId }}[0][ffe_item_cost]" @endif class="form-control expandable-amount-input text-left ffe-item-cost trigger-change-repeater recalculate-monthly-increase-amounts-branches" type="text">
                        </div>
                    </td>


{{-- 
                    <td>


                        <div class="d-flex align-items-center">
                            <input value="{{ isset($subModel) ? $subModel->getVatRate():0 }}" @if($isRepeater) name="vat_rate" @else name="{{ $tableId }}[0][vat_rate]" @endif class="form-control  exclude-from-trigger-change-when-repeat expandable-percentage-input text-left " type="text">
                            <span style="margin-left:3px	">%</span>
                        </div>
                    </td>
                    <td>


                        <div class="d-flex align-items-center">
                            <input value="{{ isset($subModel) ? $subModel->getWithholdTaxRate():0 }}" @if($isRepeater) name="withhold_tax_rate" @else name="{{ $tableId }}[0][withhold_tax_rate]" @endif class="form-control exclude-from-trigger-change-when-repeat expandable-percentage-input text-left exclude-from-trigger-change-when-repeat" type="text">
                            <span style="margin-left:3px	">%</span>
                        </div>
                    </td> --}}

                    <td>
                        <div class="d-flex align-items-center">
                            <input value="{{ isset($subModel) ? $subModel->getContingencyRate():0 }}" @if($isRepeater) name="contingency_rate" @else name="{{ $tableId }}[0][contingency_rate]" @endif class="form-control contingency-rate recalculate-monthly-increase-amounts-branches exclude-from-trigger-change-when-repeat expandable-percentage-input text-left exclude-from-trigger-change-when-repeat" type="text">
                            <span style="margin-left:3px	">%</span>
                        </div>
                    </td>

                    <td>


                        <div class="d-flex align-items-center">
                            <input value="{{ isset($subModel) ? $subModel->getCostAnnualIncreaseRate():0 }}" @if($isRepeater) name="cost_annual_increase_rate" @else name="{{ $tableId }}[0][cost_annual_increase_rate]" @endif :formattedInputClasses="'exclude-from-trigger-change-when-repeat'" class="form-control expandable-percentage-input text-left cost-annually-increase-rate recalculate-monthly-increase-amounts-branches" type="text">
                            <span style="margin-left:3px	">%</span>
                        </div>
                    </td>
                    <td>
                        <div class="min-w-200">
                            <x-form.select :selectedValue="isset($subModel) ? $subModel->getPaymentTerm() : 'cash'" :options="getFfePaymentTerms()" :add-new="false" class="select2-select repeater-select payment_terms " :all="false" name="@if($isRepeater) payment_terms @else {{ $tableId }}[0][payment_terms] @endif"></x-form.select>
                        </div>
                        <x-modal.custom-collection :subModel="isset($subModel) ? $subModel : null " :tableId="$tableId" :isRepeater="$isRepeater" :id="$repeaterId.'test-modal-id'"></x-modal.custom-collection>
                    </td>
                    <td>
                        <x-form.select :selectedValue="isset($subModel) ? $subModel->getDepreciationDuration() : 5" :options="getDepreciationDurations()" :add-new="false" class="select2-select repeater-select depreciation_duration " :all="false" name="@if($isRepeater) depreciation_duration @else {{ $tableId }}[0][depreciation_duration] @endif"></x-form.select>
                    </td>
                    <td>


                        <div class="d-flex align-items-center justify-content-center">
                            <input value="{{ isset($subModel) ? $subModel->getReplacementCostRate():0 }}" @if($isRepeater) name="replacement_cost_rate" @else name="{{ $tableId }}[0][replacement_cost_rate]" @endif class="form-control expandable-percentage-input exclude-from-trigger-change-when-repeat text-left" type="text">
                            <span style="margin-left:3px	">%</span>
                        </div>


                    </td>
                    <td>
                        <x-form.select :selectedValue="isset($subModel) ? $subModel->getReplacementInterval() : 1" :options="getReplacementInterval()" :add-new="false" class="select2-select repeater-select  " :all="false" name="@if($isRepeater) replacement_interval @else {{ $tableId }}[0][replacement_interval] @endif"></x-form.select>
                    </td>
                    <td>


                        <div class="">
                            <input value="{{ isset($subModel) ? $subModel->getCount():1 }}" @if($isRepeater) name="counts" @else name="{{ $tableId }}[0][counts]" @endif class="form-control expandable-percentage-input current-count recalculate-monthly-increase-amounts-branches exclude-from-trigger-change-when-repeat text-left " type="text">
                        </div>
                        <div>
                            <input class="current-row-counts" type="hidden" name="ffe_counts" value="">

                        </div>


                        @foreach($newBranchCountPerDateIndex as $dateAsIndex=>$newBranchCountPerDateIndexRow)
                        <input type="hidden" value="{{ isset($subModel) ? $subModel->getMonthlyAmountAtMonthIndex($dateAsIndex) : 0 }}" name="monthly_amounts" multiple class="current-month-amounts" data-column-index="{{ $dateAsIndex }}">

                        @endforeach

                    </td>





                </tr>
                @endforeach
            </x-slot>





        </x-tables.repeater-table>





        @php
        $isFullyFundingTroughEquity = $model->getFixedAssetStructureForFixAssetType($fixedAssetType) ? $model->getFixedAssetStructureForFixAssetType($fixedAssetType)->is_fully_funded_though_equity : 1;
        @endphp

        <div class="form-group " style="visibility:hidden !important;">
            <div class="kt-radio-inline">
                <label class="mr-3">

                </label>
                <label class="kt-radio kt-radio--success text-black font-size-18px font-weight-bold">
                    <input class="is-fully-funded-checkbox exclude-from-trigger-change-when-repeat" type="radio" value="1" name="perEmployeeFixedAssetsFundingStructure[is_fully_funded_though_equity]" @if(!isset($subModel) || ($isFullyFundingTroughEquity)) dd checked @endisset> {{ __('Fully Funded Through Equity') }}
                    <span></span>
                </label>


          
            </div>
        </div>
		
		


    </div>


</div>
