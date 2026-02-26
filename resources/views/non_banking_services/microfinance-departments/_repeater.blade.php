 {{-- start of fixed monthly repeating amount --}}
 
                    @php
                    $repeaterId = $tableId.'_repeater';
					use App\Formatter\Select2Formatter; 
					 use App\Models\NonBankingService\Position;
                    @endphp
                    <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
                    <x-tables.repeater-table :removeRepeater="false" :repeater-with-select2="true" :canAddNewItem="$canAddNewItem" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                        <x-slot name="ths">
                            <x-tables.repeater-table-th class=" category-selector-class header-border-down  " :title="__('Position Name')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th class=" category-selector-class header-border-down  " :title="__('Expense Type')"></x-tables.repeater-table-th>
                        </x-slot>
                        <x-slot name="trs">
                            @php
                            $rows = isset($model) ? $model->positions : [-1] ;
                            @endphp
                            @foreach( count($rows) ? $rows : [-1] as $subModel)
                            @php
                            if( !($subModel instanceof Position) ){
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
 									<input value="{{ (isset($subModel) ?$subModel->getName() : '') }}" @if($isRepeater) name="name" @else name="{{ $tableId }}[0][name]" @endif class="form-control text-center " type="text">
                                </td>
								<td>


                                            <div class="kt-input-icon">
                                                <div class="kt-input-icon">
                                                    <div class="input-group date">
                                                        <select data-live-search="true" data-actions-box="true" name="expense_type" class="form-control ">
                                                            @foreach(getExpenseTypes() as $id => $title )
                                                            <option @if( isset($subModel) && $subModel->getExpenseTypeId() == $id ) selected @endif value="{{ $id }}">{{$title}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

								</td>
                             


                            </tr>
                            @endforeach

                        </x-slot>




                    </x-tables.repeater-table>
                    {{-- end of fixed monthly repeating amount --}}
