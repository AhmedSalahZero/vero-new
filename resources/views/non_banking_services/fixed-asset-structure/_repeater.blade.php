 {{-- start of fixed monthly repeating amount --}}
 
                    @php
                    $repeaterId = $tableId.'_repeater';
					use App\Formatter\Select2Formatter; 
					use App\Models\NonBankingService\FixedAssetName;
                    @endphp
                    <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
                    <x-tables.repeater-table :initialJs="$isRepeater" :showAddBtnAndPlus="$isRepeater" :hideAddBtn="!$isRepeater" :removeRepeater="!$isRepeater" :repeater-with-select2="true" :canAddNewItem="$canAddNewItem" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater">
                        <x-slot name="ths">
                            <x-tables.repeater-table-th class=" category-selector-class header-border-down  " :title="__('Fixed Asset Name')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th class=" category-selector-class header-border-down  " :title="__('Optional')"></x-tables.repeater-table-th>
                        </x-slot>
                        <x-slot name="trs">
                            @php
                            $rows = isset($inEditMode) ? $fixedAssetNames : [-1] ;
                            @endphp
                            @foreach( count($rows) ? $rows : [-1] as $subModel)
                            @php
                            if( !($subModel instanceof FixedAssetName) ){
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
 									<input value="{{ (isset($subModel) ?$subModel->getName() : '') }}" @if($isRepeater) name="name" @else name="{{ $tableId }}[0][name]" @endif class="form-control text-left " type="text">
                                </td>
                               <td>
							   <div class="row">
							   
							   <div class="col-md-12 mb-0 mt-4 text-left">
                                        <div class="form-group d-inline-block">
                                            <div class="kt-radio-inline">
                                                <label class="mr-3">

                                                </label>
                                                <label class="kt-radio kt-radio--success text-black font-size-16px font-weight-bold">

                                                    <input type="checkbox" value="1" name="is_employee_asset" @if(isset($subModel) && $subModel->isEmployeeAsset()) checked @endisset
                                                    > {{ __('Is Employee Asset') }}
                                                    <span></span>
                                                </label>

                                                <label class="kt-radio kt-radio--danger text-black font-size-16px  font-weight-bold">
                                                    <input type="checkbox" value="1" name="is_branch_asset" @if(isset($subModel) && $subModel->isBranchAsset()) checked @endisset
                                                    > {{ __('Is Branch Asset') }}
                                                    <span></span>
                                                </label>


                                            </div>
                                        </div>
                                    </div>
									
							   </div>
							   
							   </td>


                            </tr>
                            @endforeach

                        </x-slot>




                    </x-tables.repeater-table>
                    {{-- end of fixed monthly repeating amount --}}
