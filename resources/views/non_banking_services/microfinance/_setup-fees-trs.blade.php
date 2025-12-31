<tr data-repeat-formatting-decimals="0" data-repeater-style>
                                        <td class="td-classes">
                                            <div>

                                                <input style="background-color:#DDEBF7 !important" value="{{ __('Setup Fees Rates [From Flat Rate]') }}" disabled="" class="form-control text-left min-w-300" type="text">
                                            </div>

                                        </td>


                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td >
                                            @php
                                       	     $currentVal = $subModel ? $subModel->getSetupFeesRateAtYearOrMonthIndex($yearOrMonthAsIndex) : 1;
                                            @endphp
                                           
                                            <x-repeat-with-calc :bgColor="'#DDEBF7'" :justifyLeft="true" :showIcon="false" :numberFormatDecimals="2" :formattedInputClasses="'calcField '" :mark="'%'" :removeCurrency="true" :currentVal="number_format($currentVal,1)" :classes="''" :is-percentage="true" :name="'microfinanceProductSalesProjects['.$product->id.'][fees_rates]['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-with-calc>

                                       

                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach


                                    </tr>
									
									
									
									
									
									 <tr data-repeat-formatting-decimals="0" data-repeater-style>
                                        <td class="td-classes">
                                            <div>

                                                <input style="background-color:#DDEBF7 !important" value="{{ __('Setup Fees Duration') }}" disabled="" class="form-control text-left min-w-300" type="text">
                                            </div>

                                        </td>


                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td >
                                            @php
                                       	     $currentVal = $subModel ? $subModel->getSetupFeesDurationAtYearOrMonthIndex($yearOrMonthAsIndex) : 12;
                                            @endphp
                                           
                                            <x-repeat-with-calc  :bgColor="'#DDEBF7'" :showIcon="false" :numberFormatDecimals="0" :formattedInputClasses="' '" :mark="'Mth'" :removeCurrency="true" :currentVal="number_format($currentVal,1)" :classes="''" :is-percentage="true" :name="'microfinanceProductSalesProjects['.$product->id.'][setup_fees_durations]['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-with-calc>

                                       

                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach


                                    </tr>
