<div class="modal fade " id="{{ 'forecast-'.convertStringToClass($type) }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <form action="#" class="modal-content" method="post">


            @csrf
            <div class="modal-header">
                <h5 class="modal-title" style="color:#0741A5 !important" id="exampleModalLongTitle"> {{  __(ucwords(str_replace('_',' ',$type)))  . ' ' . __('- Next Three Months Forecast') }} </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="customize-elements">
                    <table class="table">
                        <thead>
						
                            <tr>


                                <th class="text-center w-40-percentage text-capitalize th-main-color">{{ __('Item Name') }}</th>
                             
								@php
									$index = 0 ;
								@endphp
								@foreach($simpleLinearRegressionDatesForAllTypes[$type]??[] as $simpleLinearRegressionDate )
								@php
								$totalAtDate =[];
							@endphp
							
                                <th class="text-center w-15-percentage text-capitalize th-main-color"> {{ \Carbon\Carbon::make($simpleLinearRegressionDate)->format('d-m-Y') }} 
								<br>
								@if($index==0)
								{{ __('Actual') }}
								@else
								{{ __('Forecast') }}
								
								@endif 
								</th>
						
						@php
							$index++ ;
						@endphp
								@endforeach
								
								


                            </tr>
                        </thead>
                        <tbody>

                            @php
                            $total = 0 ;
							$totalInMainFunctionalCurrency = 0 ;

                            @endphp
							{{-- {{ dd($simpleLinearRegressionForAllTypes,$type) }} --}}
                            @foreach($simpleLinearRegressionForAllTypes[$type]??[] as $name => $nameAndValues )
							@if($name != 'total')
                            <tr>
                                <td class="w-40-percentage">
                                    <div class="kt-input-icon ">
                                        <div class="input-group">
                                            <input disabled type="text" step="0.1" class="form-control ignore-global-style" value="{{ $name }}">
                                        </div>
                                    </div>
                                </td>
							
						
							@foreach($simpleLinearRegressionDatesForAllTypes[$type]??[]  as $date )
							@php
								$value = $simpleLinearRegressionForAllTypes[$type][$name][$date] ?? 0 ;
								
							@endphp
                                <td class="w-10-percentage">
                                    <div class="kt-input-icon ">
                                        <div class="input-group">
                                            <input disabled type="text" class="form-control text-center ignore-global-style" value="{{ is_numeric($value) ?  number_format($value) : $value }}">
                                        </div>
                                    </div>
                                </td>
							@endforeach 


                              

                            </tr>
							@endif
                            @endforeach 
							
							
							<tr>
                                 <td class="w-40-percentage">
                                    <div class="kt-input-icon ">
                                        <div class="input-group">
                                            <input disabled type="text" step="0.1" class="form-control ignore-global-style" value="{{ __('Others') }}">
                                        </div>
                                    </div>
                                </td>
								@php
									$index=0;
								@endphp
							@foreach($simpleLinearRegressionDatesForAllTypes[$type]??[] as $date)
							@php
							$value = $simpleLinearRegressionForAllTypes[$type][$name][$date] ?? 0 ;
							$forecastForCompany =$simpleLinearRegressionForCompany['next'.$index.'ForecastForCompany']; 
							$index++;
							$currentVal = $forecastForCompany-$value;
							$simpleLinearRegressionForAllTypes[$type]['total'][$date] = isset($simpleLinearRegressionForAllTypes[$type]['total'][$date]) ? $simpleLinearRegressionForAllTypes[$type]['total'][$date] + $currentVal :$currentVal;
							 
							@endphp 
                                <td class="w-10-percentage">
                                    <div class="kt-input-icon ">
                                        <div class="input-group">
                                            <input disabled type="text" class="form-control text-center ignore-global-style" value="{{ number_format($currentVal)  }}">
                                        </div>
                                    </div>
                                </td>
								@endforeach
                            </tr>
							
							
					
                        <tr>
                                <td>
								{{ __('Total') }}
                                </td>
							@foreach($simpleLinearRegressionDatesForAllTypes[$type]??[] as $date)
                                <td>
								{{ number_format($simpleLinearRegressionForAllTypes[$type]['total'][$date]??0) }}
                                </td>
								@endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary 
				{{-- submit-form-btn --}}
				" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </form>
    </div>
</div>
