@extends('layouts.dashboard')
@section('dash_nav')
@include('client_view.home_dashboard.main_navs-income-statement',['active'=>'interval_dashboard'])

@endsection
@section('css')
<link href="{{ url('assets/vendors/general/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/vendors/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css')}}" rel="stylesheet" type="text/css" />
<link href="{{url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css')}}" rel="stylesheet" type="text/css" />


<style>
    .modal-backdrop {
        display: none !important;
    }

    .main-with-no-child {
        background-color: rgb(238, 238, 238) !important;
        font-weight: bold;
    }

    .is-sub-row td.sub-text-bg {
        background-color: #aedbed !important;
        color: black !important;

    }

    .sub-numeric-bg {
        text-align: center;

    }


 .is-sub-row td.sub-numeric-bg,
    .is-sub-row td.sub-text-bg {
        background-color: #E2EFFE !important;
        color: black !important;
		
    }
	
	.is-sub-row td.sub-numeric-bg,
    .is-sub-row td.sub-text-bg {
        background-color: #0e96cd !important;
        color: white !important;
		
		
		background-color:#E2EFFE !important;
		color:black !important

    }
		.table-bordered th, .table-bordered td {
		border:1px solid white !important;
	}
    .header-tr {
        background-color: #046187 !important;
    }

    .dt-buttons.btn-group {
        display: flex;
        align-items: flex-start;
        justify-content: flex-end;
        margin-bottom: 1rem;
    }

    .is-sales-rate,
    .is-sales-rate td,
    .is-sales-growth-rate,
    .is-sales-growth-rate td {
        background-color: #046187 !important;
        color: white !important;
    }

    .dataTables_wrapper .dataTable th,
    .dataTables_wrapper .dataTable td {
        font-weight: bold;
        color: black;
    }

    a[data-toggle="modal"] {
        color: #046187 !important;
    }

    a[data-toggle="modal"].text-white {
        color: white !important;
    }

    .btn-border-radius {
        border-radius: 10px !important;
    }

</style>


<style>
    table {
        white-space: nowrap;
    }

</style>
@endsection
@section('content')
<div class="kt-portlet">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title head-title text-primary">
                {{ __('Income Statement Comparing') }}
            </h3>
        </div>
    </div>
    <div class="kt-portlet__body">
        <form action="{{route('dashboard.intervalComparing.incomeStatement',['company'=>$company,'subItemType'=>Request()->segments()[4]])}}" method="POST">
            @csrf
            <div class="form-group row ">
                <div class="col-md-4">
                    <label style="margin-right: 10px;"><b>{{__('Comparing Types')}}</b></label>
                </div>
                <div class="col-md-8">
                    <div class="input-group date">
                        <select data-actions-box="true" data-live-search="true" data-max-options="0" name="types[]" required class="form-control select2-select form-select form-select-2 form-select-solid fw-bolder select-all" multiple>
                            @foreach ($permittedTypes as $id=>$name)
                            <option value="{{ $id }}" @if(in_array($id , $selectedTypes ) || $selectAllOptions) selected @endif> {{ $name }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>


            </div>
            <div class="form-group row ">

                <div class="col-md-4">
                    <label><b>{{__('Income Statements')}}</b></label>
                </div>
                <div class="col-md-4">
                    <label>{{__('First Income Statement')}}</label>

                    <select id="income_statement_first_id" data-live-search="true" data-max-options="2" name="financial_statement_able_first_interval" required class="form-control select2-select form-select form-select-2 form-select-solid fw-bolder" {{-- multiple --}}>
                        @foreach($incomeStatements as $incomeSatatement)
                        <option value="{{ $incomeSatatement->id }}" @if($firstIncomeStatement->id == $incomeSatatement->id ) selected @endif > {{ $incomeSatatement->name  }}</option>
                        @endforeach
                    </select>

                </div>


                <div class="col-md-4">
                    <label>{{__('Second Income Statement')}}</label>
                    <select id="income_statement_second_id" data-live-search="true" data-max-options="2" name="financial_statement_able_second_interval" required class="form-control select2-select form-select form-select-2 form-select-solid fw-bolder" {{-- multiple --}}>
                        @foreach($incomeStatements as $incomeStatement)
                        <option value="{{ $incomeStatement->id }}" @if($secondIncomeStatement->id == $incomeStatement->id ) selected @endif > {{ $incomeStatement->name }}</option>
                        @endforeach

                    </select>

                </div>





            </div>



            <div class="form-group row ">
                <div class="col-md-4">
                    <label><b>{{__('Report Type')}}</b></label>
                </div>


                <div class="col-md-4">
                    <label>{{__('First Report Type')}}</label>
                    <select id="first-report-type" data-actions-box="false" data-live-search="true" data-max-options="1" name="first_report_type" required class="form-control select2-select form-select form-select-2 form-select-solid fw-bolder select-all">
                        @foreach (getAllFinancialAbleTypes() as $firstReportType)
                        <option  value="{{ $firstReportType }}" @if($firstReportType==$selectedItems['first_report_type']) selected @endif> {{ toupperfirst($firstReportType) }} </option>
                        @endforeach

                    </select>
                </div>
				
				
				 <div class="col-md-4">
                    <label>{{__('Second Report Type')}}</label>
                    <select id="second-report-type" data-actions-box="false" data-live-search="true" data-max-options="1" name="second_report_type" required class="form-control select2-select form-select form-select-2 form-select-solid fw-bolder select-all">
                        @foreach (getAllFinancialAbleTypes() as $secondReportType)
                        <option value="{{ $secondReportType }}" @if($secondReportType==$selectedItems['second_report_type']) selected @endif> {{ toupperfirst($secondReportType) }} </option>
                        @endforeach

                    </select>
                </div>
				










            </div>






            <div class="form-group row ">
                <div class="col-md-4">
                    <label><b>{{__('Start Date')}}</b></label>
                </div>


                <div class="col-md-4">
                    <label>{{__('Start Date One')}}</label>
                    <div class="kt-input-icon">
                        <div class="input-group date">
                            <input type="date" id="start_date_one_input_id" name="start_date_one" required value="{{$start_date_0}}" class="form-control" placeholder="Select date" />
                        </div>
                    </div>
                </div>



                <div class="col-md-4">
                    <label>{{__('Start Date Two')}}</label>
                    <div class="kt-input-icon">
                        <div class="input-group date">
                            <input type="date" id="start_date_second_input_id" name="start_date_two" required value="{{$start_date_1}}" class="form-control" placeholder="Select date" />
                        </div>
                    </div>
                </div>






            </div>



            <div class="form-group row ">
                <div class="col-md-4">
                    <label><b>{{__('End Date')}}</b></label>
                </div>



                <div class="col-md-4">
                    <label>{{__('End Date One')}}</label>
                    <div class="kt-input-icon">
                        <div class="input-group date">
                            <input id="end_date_one_input_id" type="date" name="end_date_one" required value="{{$end_date_0}}" class="form-control" placeholder="Select date" />
                        </div>
                    </div>
                </div>



                <div class="col-md-4">
                    <label>{{__('End Date Two')}}</label>
                    <div class="kt-input-icon">
                        <div class="input-group date">
                            <input id="end_date_second_input_id" type="date" name="end_date_two" required value="{{$end_date_1}}" class="form-control" placeholder="Select date" />
                        </div>
                    </div>
                </div>


            </div>

            <x-run />
        </form>
    </div>
</div>

<div class="row">




    <div class="row w-100">

        <div class="col-md-12
            ">
            <div class="kt-portlet kt-portlet--mobile">

                <div class="kt-portlet__body dataTables_wrapper dt-bootstrap4 no-footer">
                      <table class="table table-striped- table-bordered table-hover table-checkable position-relative table-with-two-subrows main-table-class dataTable no-footer">
                        <thead>
                            <tr class="header-tr ">
                                <th class="text-center view-table-th header-th sorting_disabled sub-text-bg text-nowrap editable editable-text is-name-cell trigger-expand is-opened" style="cursor:pointer">{{ __('Expand All') }}</th>
                                <th class="text-center view-table-th header-th sorting_disabled sub-text-bg text-nowrap editable editable-text is-name-cell">Name</th>
                                @foreach ($intervals as $index => $intervalName )
								
                                <th class="text-center view-table-th header-th sorting_disabled sub-text-bg text-nowrap editable editable-text is-name-cell text-capitalize"> 
								@if($index == 0)
								{{ $firstIncomeStatement->getName() }} <br>
								{{$selectedItems['first_report_type'] ?? ''}}
								@else
								{{ $secondIncomeStatement->getName() }} <br>
								{{$selectedItems['second_report_type'] ?? ''}}
								@endif 
								 <br> ({{ getIntervalFromString($intervalName) }})</th>
                                <th class="text-center view-table-th header-th sorting_disabled sub-text-bg text-nowrap editable editable-text is-name-cell text-capitalize"> {{ __('% / Revenues') }} </th>
                                @endforeach
                                <th class="text-center view-table-th header-th sorting_disabled sub-text-bg text-nowrap editable editable-text is-name-cell">{{ __('Variance') }}</th>
                                <th class="text-center view-table-th header-th sorting_disabled sub-text-bg text-nowrap editable editable-text is-name-cell">{{ __('Percentage') }}</th>
                            </tr>
                        </thead>
                        <tbody>

								@php
								$typeIndex = 0 ;
								$currentTotalsOfSalesRevenues = [];
								@endphp 
                            @foreach ($intervalComparing as $theType => $intervals)
                            <tr class="sub-numeric-bg text-nowrap" data-model-id="{{ convertStringToClass($theType) }}">
                                <td class=" reset-table-width trigger-child-row-1 cursor-pointer sub-text-bg sub-closed">+</td>
                                <td class="sub-text-bg text-nowrap is-name-cell text-left" style="text-align: left !important;">{{ $theType }}</td>
                                @php
                                $currentValue =[ ];
                                $subIndex = 0;
								@endphp
								
                                @foreach ($intervals as $intervalName => $data )
                                @php
                                $currentValue[] = sum_all_keys($intervalComparing[$theType][$intervalName]) ;
								if($typeIndex == 0){
                                $currentTotalsOfSalesRevenues[] = sum_all_keys($intervalComparing[$theType][$intervalName]) ;
									
								}
                                $totalOfRevenue = sum_all_keys($intervalComparing[$theType][$intervalName])
								@endphp
								
                                <td class="sub-numeric-bg text-nowrap "> {{  number_format( $totalOfRevenue  ) }} </td>
								<td 
									@if($subIndex == 1)
								style="color:{{ getColorForIndexes($currentTotalsOfSalesRevenues[0],$currentTotalsOfSalesRevenues[1],$typeIndex ) }}"
								@endif 
								>
								@if($typeIndex == 0 )
								-
								@elseif($currentTotalsOfSalesRevenues[$subIndex])
								
								{{number_format($currentValue[$subIndex] /$currentTotalsOfSalesRevenues[$subIndex] * 100,2)}} % 
								@else
								
								0 % 
								
								@endif 
								</td>
								@php
								$subIndex++;
								@endphp
                                @endforeach
                                @php
                                $val = $currentValue[1] - $currentValue[0] ;
                                $percentage = isset($currentValue[0]) && $currentValue[0] ? number_format($val/ $currentValue[0] * 100 , 2) : number_format(0,2) ;
                                if($val > 0 && $currentValue[0] <0) { $percentage=$percentage * -1; } $color=getPercentageColorOfSubTypes($val,$theType) ; @endphp <td class="sub-numeric-bg text-nowrap " style="color:{{  $color }} !important">{{ number_format($val)  }}</td>
                                    <td class="sub-numeric-bg text-nowrap  " style="color:{{ getPercentageColorOfSubTypes($percentage , $theType) }} !important">
                                        {{ $percentage . ' %' }}
                                    </td>

                            </tr>
                            @php
                            $currentValue=[];
					
                            @endphp

                            @foreach(getSubItemsNames($intervalComparing[$theType]) as $subItemName=>$values )

                            <tr class="edit-info-row add-sub maintable-1-row-class{{ convertStringToClass($theType) }} is-sub-row even d-none">
                                <td class="sub-text-bg text-nowrap editable editable-text is-name-cell"> </td>
                                <td class="sub-text-bg text-nowrap editable editable-text is-name-cell">
                                    {{ $subItemName }}
                                </td>
                                @php
                                $currentValues =[];
                                $intervalIndex = 0;
								$currentPercentageValueArr = [];
								@endphp
								
                                @foreach($intervals as $newIntervalName => $intervalValue)
                                @php
                                $salesValue = $values[$newIntervalName] ?? 0;
                                $currentValues[] = $salesValue ;
                                @endphp
								
                                <td class=" sub-numeric-bg sub-text-bg text-nowrap editable editable-text is-name-cell  "> {{ number_format($salesValue) }} </td>
                                @php
								$currentPercentageValue = !isQuantitySubItem($subItemName) ? ($currentTotalsOfSalesRevenues[$intervalIndex] ? $salesValue / $currentTotalsOfSalesRevenues[$intervalIndex] * 100 : 0) : '-';
								$currentPercentageValueArr[] = !isQuantitySubItem($subItemName) ? ($currentTotalsOfSalesRevenues[$intervalIndex] ? $salesValue / $currentTotalsOfSalesRevenues[$intervalIndex] * 100 : 0) : '-';
								@endphp 
								<td class=" sub-numeric-bg sub-text-bg text-nowrap editable editable-text is-name-cell  "
								@if($intervalIndex == 1)
								style="color:{{ getColorForIndexes($currentPercentageValueArr[0],$currentPercentageValueArr[1],$typeIndex ) }}"
								@endif 
								> 
								{{
									
									is_numeric($currentPercentageValue) ? number_format($currentPercentageValue , 2)  . ' %' : $currentPercentageValue
								
								}}  </td>
                                @php
								$intervalIndex ++ ;
								@endphp
								@endforeach

                                @php

                                $val = $currentValues[1] - $currentValues[0] ;
                                $percentage = isset($currentValues[0]) && $currentValues[0] ? number_format($val/ $currentValues[0] * 100 , 2) : number_format(0,2) ;
                                if($val > 0 && $currentValues[0] <0) { $percentage=$percentage * -1; } $color=getPercentageColorOfSubTypes($val,$theType) ; @endphp <td class="sub-numeric-bg   text-nowrap editable editable-text is-name-cell " style="color:{{ getPercentageColorOfSubTypes($val , $theType) }} !important">
                                    {{ number_format($val ) }}
                                    </td>
                                    <td class="sub-numeric-bg   text-nowrap editable editable-text is-name-cell " style="color:{{ getPercentageColorOfSubTypes($percentage , $theType) }} !important">
                                        {{ $percentage .' %' }}
                                    </td>
                                    @endforeach


                            </tr>
							@php
							$typeIndex++ ;
							@endphp 
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- @endforeach --}}





    </div>


    @endsection
    @section('js')

    <script src="{{ url('assets/js/demo1/pages/crud/datatables/basic/paginations.js') }}" type="text/javascript"></script>
    <script src="{{ url('assets/vendors/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
    <script src="{{url('assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}" type="text/javascript"></script>
    <script src="{{url('assets/vendors/custom/js/vendors/bootstrap-datepicker.init.js')}}" type="text/javascript"></script>
    <script src="{{url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-datepicker.js')}}" type="text/javascript"></script>
    <script src="{{url('assets/vendors/general/bootstrap-select/dist/js/bootstrap-select.js')}}" type="text/javascript"></script>
    <script src="{{url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-select.js')}}" type="text/javascript"></script>

    <script src="{{ url('assets/vendors/general/select2/dist/js/select2.full.js') }}" type="text/javascript"></script>
    <script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/select2.js') }}" type="text/javascript"></script>
    <script>
        reinitializeSelect2();

    </script>
    <script>
        $(document).on('click', '.trigger-child-row-1', function(e) {
            const parentId = $(e.target.closest('tr')).data('model-id');
            var parentRow = $(e.target).parent();
            var subRows = parentRow.nextAll('tr.add-sub.maintable-1-row-class' + parentId);

            subRows.toggleClass('d-none');
            if (subRows.hasClass('d-none')) {
                parentRow.find('td.trigger-child-row-1').html('+');
            } else if (!subRows.length) {
                // if parent row has no sub rows then remove + or - 
                parentRow.find('td.trigger-child-row-1').html('×');
            } else {
                parentRow.find('td.trigger-child-row-1').html('-');
            }

        });

    </script>



    <script src="{{ asset('custom/axios.js') }}"></script>
	@if($canRefreshDates)
    <script>
        $(function() {
            const incomeStatementElement = document.querySelector('#income_statement_first_id');
            incomeStatementElement.addEventListener('change', function(e) {
                e.preventDefault();
	
                const income_statement_id = e.target.value
                const startDateInput = document.querySelector('#start_date_one_input_id')
                const endDateInput = document.querySelector('#end_date_one_input_id')
                if (income_statement_id ) {
                    startDateInput.setAttribute('disabled', true)
                    endDateInput.setAttribute('disabled', true)
						$('.save-form').attr('disabled',true)
					
                    axios.get('/getStartDateAndEndDateOfIncomeStatementForCompany', {
                        params: {
                            company_id: '{{ getCurrentCompanyId() }}'
                            , income_statement_id
                        }
                    }).then((res) => {
                        if (res.data && res.data.status) {
                            startDateInput.value = res.data.dates.start_date
                            endDateInput.value = res.data.dates.end_date
						
                        }
                    }).catch(err => {
                    }).finally(ee => {
                        startDateInput.removeAttribute('disabled')
                        endDateInput.removeAttribute('disabled')
							$('.save-form').attr('disabled',false)
                    })
                }
            })
            incomeStatementElement.dispatchEvent(new Event('change'))

            const incomeStatementSecondElement = document.querySelector('#income_statement_second_id');
            incomeStatementSecondElement.addEventListener('change', function(e) {
                e.preventDefault();
                const income_statement_id = e.target.value
                const startDateInput = document.querySelector('#start_date_second_input_id')
                const endDateInput = document.querySelector('#end_date_second_input_id')
                if (income_statement_id) {
                    startDateInput.setAttribute('disabled', true)
                    endDateInput.setAttribute('disabled', true)
						$('.save-form').attr('disabled',true)
                    axios.get('/getStartDateAndEndDateOfIncomeStatementForCompany', {
                        params: {
                            company_id: '{{ getCurrentCompanyId() }}'
                            , income_statement_id
                        }
                    }).then((res) => {
                        if (res.data && res.data.status) {
                            startDateInput.value = res.data.dates.start_date
                            endDateInput.value = res.data.dates.end_date
                        }
                    }).catch(err => {
                    }).finally(ee => {
                        startDateInput.removeAttribute('disabled')
                        endDateInput.removeAttribute('disabled')
							$('.save-form').attr('disabled',false)
                    })
                }
            })
            incomeStatementSecondElement.dispatchEvent(new Event('change'))

        })
    </script>
	@endif 
    @endsection
