@extends('layouts.dashboard')
@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/af-2.4.0/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/cr-1.5.6/date-1.1.2/fc-4.1.0/fh-3.2.3/r-2.3.0/rg-1.2.0/sl-1.4.0/sr-1.1.1/datatables.min.css" />

<style>
.failed-td{
}
.success-td{
}
    .table-bordered.table-hover.table-checkable.dataTable.no-footer.fixedHeader-floating {
        display: none
    }

    table.dataTable thead tr>.dtfc-fixed-left,
    table.dataTable thead tr>.dtfc-fixed-right {
        background-color: #086691;
    }

    thead * {
        text-align: center !important;
    }

</style>
<style>
    table {
        white-space: nowrap;
    }

    .bg-table-head {
        background-color: #075d96;
        color: white !important;
    }

</style>
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        <!--begin::Portlet-->
        <!--begin::Form-->
        <form class="kt-form kt-form--label-right" action="#" enctype="multipart/form-data">
            <div class="kt-portlet">
                <div class="kt-portlet__head">
                    <div class="kt-portlet__head-label w-full "> 
                        <h3 class="kt-portlet__head-title head-title text-primary w-full">
                            {{ __('Failed Rows') }} <br>
							<span style="display: block;

    text-align: center;
    color: red;">
							 {{ 					 __('Review and Correct mentioned errors in your excel and please upload your excel again') }}
							 
							</span>
                        </h3>
						
				{{-- <h3 class="kt-portlet__head-title head-title text-primary">
                            {{ 					 __('Review and Correct mentioned errors and please reupload your excel') }}<br>

                        </h3> --}}
						
						<br>
						<h3 class="d-block"></h3>
						
                    </div>
					
                </div>
                  <div class="kt-portlet__body">

                                         
                                            <x-table  :tableClass="'kt_table_with_no_pagination_no_fixed_right'">
                                                @slot('table_header')
                                                    <tr class="table-active text-center">
                                                        <th>{{__('Row Number')}}</th>
														@foreach($headers as $header)
														<th>{{ $header }}</th>
														@endforeach 
                                                      
                                                    </tr>
                                                @endslot
                                                @slot('table_body')


                                                    @foreach ($rows as $rowNumber => $items)
													@php
													@endphp
                                                        <tr>
															{{-- @foreach($headers as $header) --}}
															<th style="font-weight:bold;text-align:center;"> {{ $rowNumber }} </th>
															@foreach($headers as $header)
															@php
														$failed = isset($items[$header]['value']) ;
																
															@endphp
															<td class="{{ $failed ? 'failed-td':'success-td'    }}">
															@if($failed)
															{{ $items[$header]['message'] ??'-' }} [ {{ $items[$header]['value'] ??'-' }} ]
															@else
															
															@endif 
															
															</td>
															{{-- <td>{{ $items[$header]['message'] ??'-' }}</td> --}}
															@endforeach 
															{{-- @endforeach  --}}
	
                                                        </tr>
                                                    @endforeach

                                                
                                                @endslot
                                            </x-table>

                                            <!--end: Datatable -->
                                        </div>
            </div>


        </form>
        <!--end::Form-->
        
        <!--end::Portlet-->
    </div>
    {{-- <div class="kt-portlet text-center">
        <div class="kt-portlet__head kt-portlet__head--lg">
            <div class="kt-portlet__head-label d-flex justify-content-start">
                {{ $salesGatherings->appends(Request::except('page'))->links() }}
            </div>
        </div>
    </div> --}}
</div>
@endsection

@section('js')
@include('js_datatable')
{{-- <script src="{{ url('assets/vendors/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script> --}}
<script src="{{ url('assets/js/demo1/pages/crud/datatables/basic/paginations.js') }}" type="text/javascript">
</script>


@endsection
