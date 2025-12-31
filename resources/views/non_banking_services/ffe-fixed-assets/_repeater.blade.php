@php
use App\Models\NonBankingService\FixedAssetName;
@endphp
<div data-card-id="{{ $cardId }}" class="kt-portlet parent-card ">
    <div class="kt-portlet__body">
        <h3 class="font-weight-bold text-black form-label kt-subheader__title small-caps mr-5 text-nowrap" style=""> {{ __('Furniture, Fixtures, and Equipment (FF&E) Cost') }}</h3>
        <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
        <x-tables.repeater-table :hideByDefault="false" :initEmpty="false" :removeActionBtn="false" :first-element-deletable="false" :font-size-class="'font-14px'" :append-save-or-back-btn="false" :repeater-with-select2="true" :parentClass="'js-toggle-visibility-----'" :tableName="$tableId " :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
            <x-slot name="ths">
                {{-- <x-tables.repeater-table-th :font-size-class="'font-14px'" class="  header-border-down first-column-th-class" :title="__('Actions')"></x-tables.repeater-table-th> --}}
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="  header-border-down first-column-th-class" :title="__('Item <br> Name')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" tenor-selector-class header-border-down " :title="__('Item <br> Cost')"></x-tables.repeater-table-th>
                {{-- <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" header-border-down rate-class" :title="__('VAT <br> Rate')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" header-border-down rate-class" :title="__('Withhold <br> Tax %')"></x-tables.repeater-table-th> --}}
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" header-border-down rate-class" :title="__('Contingency <br> Rate %')"></x-tables.repeater-table-th>

                <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" tenor-selector-class header-border-down " :title="__('Cost Annual <br> Increase %')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="header-border-down" :title="__('Payment <br> Terms')" :helperTitle="__('You can either choose one of the system default terms (cash, quarterly, semi-annually, or annually), if else please choose Customize to insert your payment terms')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="header-border-down" :title="__('Depreciation <br> Duration')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="header-border-down" :title="__('Replacement <br> Cost %')"></x-tables.repeater-table-th>
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="header-border-down" :title="__('Replacement <br> Interval')"></x-tables.repeater-table-th>

                @foreach($studyMonthsForViews as $dateAsIndex=>$dateAsString)
                @php
                $currentMonthNumber = explode('-',$dateAsString)[1];
                $currentYear= explode('-',$dateAsString)[0];
                $currentYearRepeaterIndex = 0 ;
                @endphp
                <x-tables.repeater-table-th data-column-index="{{ $dateAsIndex }}" :font-size-class="'font-14px'" class=" interval-class header-border-down " :title="dateFormatting($dateAsString, 'M\' Y') . ' <br> ' .__('Count #')"></x-tables.repeater-table-th>
                @if($financialYearEndMonthNumber == $currentMonthNumber || $loop->last)
                <x-tables.repeater-table-th :icon="true" data-column-index="{{ $dateAsIndex }}" :font-size-class="'font-14px'" class=" tenor-selector-class header-border-down {{ 'year-repeater-index-'.$currentYearRepeaterIndex }} collapse-before-me exclude-from-collapse" :title="__('Total Yr.').' <br> '. $currentYear"></x-tables.repeater-table-th>
                @php
                $currentYearRepeaterIndex ++;
                @endphp
                @endif

                @endforeach
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
                    <input type="hidden" name="type" value="{{ $fixedAssetType }}">
                    <td>
                        <div class="max-w-200">
                            <x-form.select :selectedValue="isset($subModel) ? $subModel->getNameId() : 0" :options="FixedAssetName::getGeneralAllForSelect2($company)" :add-new="false" class="select2-select repeater-select expense_category " :all="false" name="@if($isRepeater) name_id @else {{ $tableId }}[0][name_id] @endif"></x-form.select>
                        </div>
                    </td>
                    <td>
                        <div class="">
                            <input value="{{ isset($subModel) ? $subModel->getItemCost() : 0 }}" @if($isRepeater) name="ffe_item_cost" @else name="{{ $tableId }}[0][ffe_item_cost]" @endif class="form-control expandable-amount-input text-left ffe-item-cost trigger-change-repeater recalculate-monthly-increase-amounts" type="text">
                        </div>
                    </td>


                    <td>
                        <div class="d-flex align-items-center">
                            <input value="{{ isset($subModel) ? $subModel->getContingencyRate():0 }}" @if($isRepeater) name="contingency_rate" @else name="{{ $tableId }}[0][contingency_rate]" @endif class="form-control contingency-rate recalculate-monthly-increase-amounts exclude-from-trigger-change-when-repeat expandable-percentage-input text-left exclude-from-trigger-change-when-repeat" type="text">
                            <span style="margin-left:3px	">%</span>
                        </div>
                    </td>

                    <td>


                        <div class="d-flex align-items-center">
                            <input value="{{ isset($subModel) ? $subModel->getCostAnnualIncreaseRate():0 }}" @if($isRepeater) name="cost_annual_increase_rate" @else name="{{ $tableId }}[0][cost_annual_increase_rate]" @endif :formattedInputClasses="'exclude-from-trigger-change-when-repeat'" class="form-control expandable-percentage-input text-left cost-annually-increase-rate recalculate-monthly-increase-amounts" type="text">
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
                        <x-form.select :selectedValue="isset($subModel) ? $subModel->getDepreciationDuration() : 0" :options="getDepreciationDurations()" :add-new="false" class="select2-select repeater-select depreciation_duration " :all="false" name="@if($isRepeater) depreciation_duration @else {{ $tableId }}[0][depreciation_duration] @endif"></x-form.select>
                    </td>
                    <td>

						<div class="d-flex align-items-center justify-content-center">
                            <input value="{{ isset($subModel) ? $subModel->getReplacementCostRate():0 }}" @if($isRepeater) name="replacement_cost_rate" @else name="{{ $tableId }}[0][replacement_cost_rate]" @endif  class="form-control expandable-percentage-input exclude-from-trigger-change-when-repeat text-left" type="text">
                            <span style="margin-left:3px	">%</span>
                        </div>
						
                    </td>
                    <td>
                        <x-form.select :selectedValue="isset($subModel) ? $subModel->getReplacementInterval() : 'cash'" :options="getReplacementInterval()" :add-new="false" class="select2-select repeater-select  " :all="false" name="@if($isRepeater) replacement_interval @else {{ $tableId }}[0][replacement_interval] @endif"></x-form.select>
                    </td>

                    @php
                    $columnIndex = 0 ;
                    $currentYearRepeaterIndex = 0 ;
                    @endphp

                    @foreach($studyMonthsForViews as $dateAsIndex=>$dateAsString)
                    <td data-column-index="{{ $dateAsIndex }}">
                        <div class="d-flex align-items-center justify-content-center">
                            @php
                            $name = "ffe_counts" ;
                            @endphp
                            <x-repeat-right-dot-inputs :isMultiple="true" :dataCurrentYear="$monthsWithItsYear[$dateAsIndex]" :removeCurrency="true" :removeThreeDots="true" :removeThreeDotsClass="true" :number-format-decimals="0" :mark="' '" :currentVal="isset($subModel) ? $subModel->getFfeCountsAtDateIndex($dateAsIndex) : 0 " data-group-index="{{ $currentYearRepeaterIndex }}" :formattedInputClasses="'exclude-from-trigger-change-when-repeat'" :classes="'repeater-with-collapse-input only-greater-than-or-equal-zero-allowed  ffe_counts recalculate-monthly-increase-amounts'" :is-percentage="true" :name="$name" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                        </div>
                        <input type="hidden" value="{{ isset($subModel) ? $subModel->getMonthlyAmountAtMonthIndex($dateAsIndex) : 0 }}" name="monthly_amounts" multiple class="current-month-amounts" data-column-index="{{ $dateAsIndex }}">
                    </td>
                    @php
                    $currentMonthNumber = explode('-',$dateAsString)[1];
                    $currentYear= explode('-',$dateAsString)[0];
                    @endphp


                    @if($financialYearEndMonthNumber == $currentMonthNumber || $loop->last)
                    <td data-column-index="{{ $dateAsIndex }}" class="exclude-from-collapse">
                        <div class="d-flex align-items-center justify-content-center">
                            <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="' '" :currentVal="0 " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat'" :classes="'year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$dateAsIndex"></x-repeat-right-dot-inputs>
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



                </tr>
                @endforeach
            </x-slot>





        </x-tables.repeater-table>




        {{-- end of fixed monthly repeating amount --}}
        {{-- </form> --}}
        @php
        $isFullyFundingTroughEquity = $model->getFixedAssetStructureForFixAssetType($fixedAssetType) ? $model->getFixedAssetStructureForFixAssetType($fixedAssetType)->is_fully_funded_though_equity : 1;

        @endphp

        <div class="form-group d-inline-block">
            <div class="kt-radio-inline">
                <label class="mr-3">

                </label>
                <label class="kt-radio kt-radio--success text-black font-size-18px font-weight-bold">

                    <input class="is-fully-funded-checkbox exclude-from-trigger-change-when-repeat" type="radio" value="1" name="generalFixedAssetsFundingStructure[is_fully_funded_though_equity]" @if(!isset($subModel) || ($isFullyFundingTroughEquity)) checked @endisset> {{ __('Fully Funded Through Equity') }}
                    <span></span>
                </label>

                <label class="kt-radio kt-radio--danger text-black font-size-18px font-weight-bold">
                    <input class="is-fully-funded-checkbox exclude-from-trigger-change-when-repeat" type="radio" value="0" name="generalFixedAssetsFundingStructure[is_fully_funded_though_equity]" @if(isset($subModel) && !$isFullyFundingTroughEquity) checked @endisset> {{ __('Funded Through Equity & Debt') }}
                    <span></span>
                </label>
            </div>
        </div>

    </div>


</div>
