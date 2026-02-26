 {{-- start of fixed monthly repeating amount --}}
 
                    @php
                    $repeaterId = $tableId.'_repeater';
					use App\Formatter\Select2Formatter; 
					
                    @endphp
                    <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
                    <x-tables.repeater-table :action-btn-title="__('+/-')" :removeRepeater="false" :repeater-with-select2="true" :canAddNewItem="$canAddNewItem" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                        <x-slot name="ths">
                            <x-tables.repeater-table-th  class=" rate-class {{ $class }} header-border-down " :title="$tableHeaderTitle"></x-tables.repeater-table-th>
                        </x-slot>
                        <x-slot name="trs">
                            @php
                            $rows = isset($model) ? $newItems : [-1] ;
                            @endphp
                            @foreach( count($rows) ? $rows : [-1] as $subModel)
                            @php
                            if( !(is_object($subModel)) ){
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
                                    <input value="{{ isset($subModel) ? $subModel->getName() : '' }}" @if($isRepeater) name="name" @else name="{{ $tableId }}[0][name]" @endif class="form-control text-center" type="text">

                                </td>
                                


                            </tr>
                            @endforeach

                        </x-slot>




                    </x-tables.repeater-table>
                    {{-- end of fixed monthly repeating amount --}}
