 {{-- start of fixed monthly repeating amount --}}

 @php
 $repeaterId = $tableId.'_repeater';
 use App\Models\NonBankingService\LeasingRevenueStreamBreakdown;
 use App\Formatter\Select2Formatter;

 @endphp
 <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
 <x-tables.repeater-table :removeDisabledWhenAddNew="true" :removeRepeater="false" :repeater-with-select2="true" :canAddNewItem="$canAddNewItem" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
     <x-slot name="ths">
         <x-tables.repeater-table-th class=" category-selector-class header-border-down first-column-th-class-16 " :title="__('Leasing <br> Category')"></x-tables.repeater-table-th>
         <x-tables.repeater-table-th class="category-selector-class header-border-down first-column-th-class-13" :title="__('Loan <br> Nature')" :helperTitle="__('If you have different expense items under the same category, please insert Category Name')"></x-tables.repeater-table-th>
         <x-tables.repeater-table-th class="loan-type-class header-border-down  " :title="__('Loan <br> Type')" :helperTitle="__('Please insert amount excluding VAT')"></x-tables.repeater-table-th>
         <x-tables.repeater-table-th class=" rate-class header-border-down " :title="__('Tenor <br> Months')"></x-tables.repeater-table-th>
         <x-tables.repeater-table-th class=" rate-class header-border-down " :title="__('Grace <br> Period')"></x-tables.repeater-table-th>
         <x-tables.repeater-table-th class=" rate-class header-border-down " :title="__('Spread <br> Rate')" :helperTitle="__('You can either choose one of the system default terms (cash, quarterly, semi-annually, or annually), if else please choose Customize to insert your payment terms')"></x-tables.repeater-table-th>
         <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Installment <br> Interval')"></x-tables.repeater-table-th>
         <x-tables.repeater-table-th class=" rate-class header-border-down " :title="__('Step <br> Rate (+/-)')" :helperTitle="__('Withhold Tax rate will be calculated based on Monthly Amount excluding VAT')"></x-tables.repeater-table-th>
         <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Step <br> Interval')"></x-tables.repeater-table-th>
     </x-slot>
     <x-slot name="trs">
         @php
         $rows = isset($model) ? $model->leasingRevenueStreamBreakdown : [-1] ;
         @endphp
         @foreach( count($rows) ? $rows : [-1] as $subModel)
         @php
         if( !($subModel instanceof LeasingRevenueStreamBreakdown) ){
         unset($subModel);
         }
         @endphp
         <tr data-repeater-style="{{ $isRepeater ? 1 : -1 }}" @if($isRepeater) data-repeater-item @endif>
             <td class="text-center">
                 <div class="">
                     <i data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle">
                     </i>
                 </div>
             </td>


             <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">
             <td>
                 <x-form.select :selectedValue="isset($subModel) ? $subModel->getCategoryId() : 'annually' " :options="$company->getLeasingCategoriesFormattedForSelect()" :add-new="false" class="select2-select   repeater-select" :all="false" name="{{ $isRepeater ? 'category_id':$tableId.'[0][category_id]' }}"></x-form.select>
             </td>
             <td>
                 <x-form.select :selectedValue="isset($subModel) ? $subModel->getLoanNature() : 'annually' " :options="[['title'=>'Fixed At End','value'=>'fixed-at-end'],['title'=>'Fixed At Beginning','value'=>'fixed-at-beginning']]" :add-new="false" class="select2-select   repeater-select" :all="false" name="{{ $isRepeater ? 'loan_nature':$tableId.'[0][loan_nature]' }}"></x-form.select>
             </td>
             <td>

                 <div class="d-flex align-items-center js-common-parent">
                     <x-form.select :selectedValue="isset($subModel) ? $subModel->getLoanType() : 'normal' " :options="Select2Formatter::formatForIndexedArr(getFixedLoanTypes())" :add-new="false" class="select2-select   repeater-select" :all="false" name="{{ $isRepeater ? 'loan_type':$tableId.'[0][loan_type]' }}"></x-form.select>

                 </div>
             </td>

             <td>
                 <input value="{{ (isset($subModel) ? number_format($subModel->getTenor(),0) : 12) }}" @if($isRepeater) name="tenor" @else name="{{ $tableId }}[0][tenor]" @endif class="form-control text-center only-greater-than-zero-allowed" type="text">

             </td>
             <td>
                 <input value="{{ (isset($subModel) ? number_format($subModel->getGracePeriod(),0) : 0) }}" @if($isRepeater) name="grace_period" @else name="{{ $tableId }}[0][grace_period]" @endif class="form-control text-center only-greater-than-or-equal-zero-allowed" type="text">
             </td>
             <td>
                 <div class="d-flex align-items-center">
                     <input @if($isRepeater) name="margin_rate" @else name="{{ $tableId }}[0][margin_rate]" @endif class="form-control only-percentage-allowed text-center" value="{{ isset($subModel) ? number_format($subModel->getMarginRate(),PERCENTAGE_DECIMALS):0  }}" type="text">
                     <span style="margin-left:3px	">%</span>

                 </div>
             </td>



             <td>
                 <x-form.select :selectedValue="isset($subModel) ? $subModel->getInstallmentInterval() : 'monthly' " :options="[['title'=>__('Monthly'),'value'=>'monthly'],['title'=>__('Quarterly'),'value'=>'quarterly'],['value'=>'semi annually','title'=>__('Semi-annually')]]" :add-new="false" class="select2-select   repeater-select" :all="false" name="{{ $isRepeater ? 'installment_interval':$tableId.'[0][installment_interval]' }}"></x-form.select>
             </td>


             <td>
                 <div class="d-flex align-items-center">
                     <input @if($isRepeater) name="step_rate" @else name="{{ $tableId }}[0][step_rate]" @endif class="form-control only-percentage-allowed-between-minus-plus-hundred text-center" value="{{ isset($subModel) ? $subModel->getStepRate() : 0 }}" type="text">
                     <span style="margin-left:3px	">%</span>
                 </div>
             </td>
             <td>
                 <x-form.select :selectedValue="isset($subModel) ? $subModel->getStepInterval() : 'annually' " :options="[['title'=>__('Quarterly'),'value'=>'quarterly'],['value'=>'semi annually','title'=>__('Semi-annually')],['title'=>__('Annually'),'value'=>'annually']]" :add-new="false" class="select2-select   repeater-select" :all="false" name="{{ $isRepeater ? 'step_interval':$tableId.'[0][step_interval]' }}"></x-form.select>
             </td>


         </tr>
         @endforeach

     </x-slot>




 </x-tables.repeater-table>
 {{-- end of fixed monthly repeating amount --}}
