 {{-- start of fixed monthly repeating amount --}}

 @php
 $repeaterId = $tableId.'_repeater';
 use App\Formatter\Select2Formatter;
 use App\Models\Trading\Tenant;
 @endphp
 <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
 <x-tables.repeater-table :removeActionBtn="false" :hideDeleteBtn="$inEditMode" :hideAddBtn="$inEditMode" :hideAddBtnAndPlus="$inEditMode" :hideActionBtn="$inEditMode" :removeRepeater="$inEditMode" :repeater-with-select2="false" :canAddNewItem="!$inEditMode" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
     <x-slot name="ths">
         <x-tables.repeater-table-th class=" category-selector-class header-border-down  " :title="__('Tenant Name')"></x-tables.repeater-table-th>
         <x-tables.repeater-table-th class=" category-selector-class header-border-down  " :title="__('Nature')"></x-tables.repeater-table-th>
         <x-tables.repeater-table-th class=" category-selector-class header-border-down  " :title="__('Business Sector')"></x-tables.repeater-table-th>
         <x-tables.repeater-table-th class=" category-selector-class  header-border-down  " :title="__('Related Party')"></x-tables.repeater-table-th>
     </x-slot>
     <x-slot name="trs">
         @php
         $rows = isset($inEditMode) ? $tenants : [-1] ;
         @endphp
         @foreach( count($rows) ? $rows : [-1] as $subModel)
         @php
         if( !($subModel instanceof Tenant) ){
         unset($subModel);
         }
         @endphp
         <tr data-repeater-style="{{ $isRepeater ? 1 : -1 }}" @if($isRepeater) data-repeater-item @endif>
             <td class="text-center">
                 @if(!$inEditMode)
                 <div class="">
                     <i data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle">
                     </i>
                 </div>
                 @endif
             </td>


             <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">

             <td>
                 <input value="{{ (isset($subModel) ?$subModel->getName() : '') }}" @if($isRepeater) name="name" @else name="{{ $tableId }}[0][name]" @endif class="form-control text-left " type="text">
             </td>
             <td>
                 <div class="row">

                     <div class="col-md-12 mb-0 mt-4 text-center">
                         <div class="form-group d-inline-block">
                             <div class="kt-radio-inline">
                                 <label class="mr-3">
                                     {{-- {{ __('Nature') }} --}}
                                 </label>
                                 <label class="kt-radio kt-radio--success text-black font-size-16px font-weight-bold">

                                     <input type="radio" value="individual" name="nature" @if(isset($subModel) && $subModel->getNature() == 'individual') checked @elseif(!isset($subModel)) checked @endif
                                     > {{ __('Individual') }}
                                     <span></span>
                                 </label>
                                 <label class="kt-radio kt-radio--success text-black font-size-16px font-weight-bold">

                                     <input type="radio" value="corporate" name="nature" @if(isset($subModel) && $subModel->getNature() == 'corporate') checked @endif
                                     > {{ __('Corporate') }}
                                     <span></span>
                                 </label>



                             </div>
                         </div>
                     </div>

                 </div>

             </td>
             <td>
                 <select name="business_sector" class="form-control">
                     @foreach(getBusinessSectors() as $id => $businessSector)
                     <option value="{{ $id }}" @if(isset($subModel) && $subModel->getBusinessSector() == $id) selected @endif>{{ $businessSector }}</option>
                     @endforeach
                 </select>
             </td>
             <td>
                 <div class="row">

                     <div class="col-md-12 mb-0 mt-4 min-w-140 text-center">
                         <div class="form-group d-inline-block">
                             <div class="kt-radio-inline">
                                 <label class="mr-3">

                                 </label>
                                 <label class="kt-radio kt-radio--success text-black font-size-16px font-weight-bold">

                                     <input type="radio" value="yes" name="related_party" @if(isset($subModel) && $subModel->getRelatedParty() == 'yes') checked @elseif(!isset($subModel)) checked @endif
                                     > {{ __('Yes') }}
                                     <span></span>
                                 </label>
                                 <label class="kt-radio kt-radio--success text-black font-size-16px font-weight-bold">

                                     <input type="radio" value="no" name="related_party" @if(isset($subModel) && $subModel->getRelatedParty() == 'no') checked @endif
                                     > {{ __('No') }}
                                     <span></span>
                                 </label>



                             </div>

             </td>

             </div>

         </tr>
         @endforeach

     </x-slot>




 </x-tables.repeater-table>

 {{-- end of fixed monthly repeating amount --}}
