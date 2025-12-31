 {{-- start of fixed monthly repeating amount --}}
 
                    @php
                    $repeaterId = $tableId.'_repeater';
					use App\Models\NonBankingService\MicrofinanceProduct;
					
                    @endphp
                    <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
                    <x-tables.repeater-table :removeRepeater="false" :repeater-with-select2="true" :canAddNewItem="$canAddNewItem" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                        <x-slot name="ths">
                           <x-tables.repeater-table-th class="header-border-down" :title="__('Name')"></x-tables.repeater-table-th>
							<x-tables.repeater-table-th class="header-border-down " :title="__('Is Active')"></x-tables.repeater-table-th>
                         </x-slot>
                        <x-slot name="trs">
                            @php
                            $rows = isset($model) ? $model->microfinanceProducts : [-1] ;
                            @endphp
                            @foreach( count($rows) ? $rows : [-1] as $subModel)
                            @php
                            if( !($subModel instanceof MicrofinanceProduct) ){
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
								 <input value="{{ (isset($subModel) ?$subModel->getName() : '') }}" @if($isRepeater) name="title" @else name="{{ $tableId }}[0][title]" @endif class="form-control text-left " type="text">
							    </td>
								
								<td class="text-center">
								
								       <div class="form-group d-inline-block">
                                            <div class="kt-radio-inline">
                                                <label class="mr-3">

                                                </label>
                                                <label class="kt-radio kt-radio--success text-black font-size-16px font-weight-bold">

                                                    <input  type="radio" value="1" name="is_active" 
													@if(isset($subModel) && $subModel->isActive()) checked @endisset
													> {{ __('Active') }}
                                                    <span></span>
                                                </label>
										
                                                <label class="kt-radio kt-radio--danger text-black font-size-16px font-weight-bold">
                                                    <input type="radio" value="0" name="is_active" 
													@if(isset($subModel) && !$subModel->isActive()) checked @endisset
													> {{ __('Inactive') }}
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
										
								</td>

                            </tr>
                            @endforeach

                        </x-slot>




                    </x-tables.repeater-table>
                    {{-- end of fixed monthly repeating amount --}}
