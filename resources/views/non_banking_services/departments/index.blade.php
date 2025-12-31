@extends('layouts.dashboard')
@php
use App\Models\NonBankingService\Department;
use App\Helpers\HArr;
@endphp
@section('css')
<link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/custom/css/financial-planning/common.css">
<style>
	.bg-white-hover:hover {
		color:white !important;
	}
	.new-study-item i {
		color:#055dac !important
	}
	.new-study-item:hover i {
		color:white !important;
	}
</style>
@endsection
@section('sub-header')
{{ $title }}
@endsection
@section('content')

<div class="kt-portlet kt-portlet--tabs">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-toolbar justify-content-between flex-grow-1">
            <ul class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ !Request('active') || Request('active') == Department::GENERAL ?'active':'' }}" data-toggle="tab" href="#{{Department::GENERAL  }}" role="tab">
                        <i class="fa fa-money-check-alt"></i> {{ __('Departments') }}
                    </a>
                </li>
				
				 <li class="nav-item">
                    <a class="nav-link {{  Request('active') == Department::MICROFINANCE ?'active':'' }}" href="{{route('edit.departments',['company'=>$company->id,'department'=>$microfinanceDepartment->id,'type'=>Department::MICROFINANCE ])}}" role="tab">
                        <i class="fa fa-money-check-alt"></i> {{ __('Microfinance Departments') }}
                    </a>
                </li>
				
				


            </ul>
            {{-- @if(auth()->user()->can('create study info')) --}}
			
			 <div class="flex-tabs">
                 
				
                <a href="{{ route('create.departments',['company'=>$company->id,'type'=>'general']) }}" class="btn btn-2-bg bg-white-hover new-study-item rounded btn-icon-sm align-self-center">
                    <i class="fas fa-plus white-icon exclude-icon"></i>
                    {{ __('New General Department') }}
                </a>
				
				   {{-- <a href="{{ route('create.microfinance-departments',['company'=>$company->id]) }}" class="btn btn-2-bg bg-white-hover new-study-item rounded btn-icon-sm align-self-center">
                    <i class="fas fa-plus white-icon exclude-icon"></i>
                    {{ __('New Microfinance Department') }}
                </a> --}}
				
				
            </div>
			
            {{-- <div class="flex-tabs">
			
                <a href="{{ $createRoute }}" class="btn new-record-class rounded btn-icon-sm align-self-center">
                    <i class="fas fa-plus white-icon"></i>
                    {{ __('New Study') }}
                </a>
            </div> --}}
            {{-- @endif  --}}

        </div>
    </div>
    <div class="kt-portlet__body">
        <div class="tab-content  kt-margin-t-20">
		
			  @php
            $currentType = Department::GENERAL ;
            @endphp
            <!--Begin:: Tab Content-->
            <div class="tab-pane {{  !Request('active') || Request('active') == $currentType ?'active':'' }}" id="{{ $currentType }}" role="tabpanel">
                <div class="kt-portlet kt-portlet--mobile">
						@php
                        $rowIndex = 0;
                        @endphp
                        <x-tables.repeater-table :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                            <x-slot name="ths">
                                <x-tables.repeater-table-th class="  header-border-down first-column-th-class" :title="__('Name')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="  header-border-down first-column-th-class" :title="__('Expense Type')"></x-tables.repeater-table-th>
                                {{-- <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Start Date')"></x-tables.repeater-table-th> --}}
                                {{-- <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('End Date')"></x-tables.repeater-table-th> --}}
                                {{-- <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Financial Statement')"></x-tables.repeater-table-th> --}}
                                {{-- <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Balance Sheet')"></x-tables.repeater-table-th> --}}
                                {{-- <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Cash Flow')"></x-tables.repeater-table-th> --}}
                                {{-- <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Dashboard')"></x-tables.repeater-table-th> --}}
                                <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Actions')"></x-tables.repeater-table-th>
                            </x-slot>
                            <x-slot name="trs">

                                @php
                                $currentLoanTotalPerYear = [];
                                @endphp

                                @foreach ($models[$currentType] as $index=>$model)

                                <tr data-repeat-formatting-decimals="0" data-repeater-style>

                                    <td>
                                        <div class="">

                                            <input value="{{ $model->getName() }}" disabled class="form-control text-left " type="text">
                                        </div>
                                    </td>
									
									 <td>
                                        <div class="">

                                            <input value="{{ $model->getExpenseTypeName()  }}" disabled class="form-control text-left " type="text">
                                        </div>
                                    </td>
                                    {{-- <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :removeThreeDots="true" :removeCurrency="true" :mark="' '" :is-number="false" :removeThreeDotsClass="true" :number-format-decimals="0" :currentVal="$model->getStudyStartDateFormattedForView()" :classes="''" :is-percentage="false" :name="''" :columnIndex="0"></x-repeat-right-dot-inputs>

                                        </div>
                                    </td> --}}
									
									  <td class="kt-datatable__cell--left kt-datatable__cell " data-field="Actions" data-autohide-disabled="false">
                                        <span style="overflow: visible; position: relative; width: 110px;">
											{{-- @if(hasAuthFor('update lc settlement internal transfer')) --}}
                                            <a type="button" class="btn btn-secondary btn-outline-hover-brand btn-icon edit-btn-class" title="{{ __('Edit') }}" href="{{ route('edit.departments',['company'=>$company->id,'department'=>$model->id,'type'=>$currentType]) }}"><i class="fa fa-pen-alt exclude-icon default-icon-color" ></i></a>
                                            {{-- <a type="button" class="btn btn-secondary btn-outline-hover-brand btn-icon copy-btn-class" title="{{ __('Copy') }}" href="{{ route('edit.study',['company'=>$company->id,'study'=>$model->id]) }}"><i class="fa fa-layer-group exclude-icon default-icon-color" ></i></a> --}}
											{{-- @endif  --}}
											{{-- @if(hasAuthFor('delete lc settlement internal transfer')) --}}
                                            <a data-toggle="modal" data-target="#delete-department-{{ $model->id }}" type="button" class="btn delete-btn-class btn-secondary btn-outline-hover-danger btn-icon" title="Delete" href="#"><i class="fa fa-trash-alt exclude-icon default-icon-color"></i></a>
                                            <div class="modal fade" id="delete-department-{{ $model->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content">
                                                        <form action="{{ route('departments.destroy',['company'=>$company->id,'department'=>$model->id ]) }}" method="post">
                                                            @csrf
                                                            @method('delete')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Do You Want To Delete This Item ?') }}</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                                                                <button type="submit" class="btn btn-danger">{{ __('Confirm Delete') }}</button>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
											{{-- @endif  --}}
                                        </span>
                                    </td>



                                </tr>




                                @endforeach


                          




                            </x-slot>




                        </x-tables.repeater-table>
						
                    
                </div>
            </div>
			
			
			
			
			
			
			
			
			
			  @php
            $currentType = Department::MICROFINANCE ;
            @endphp
            <!--Begin:: Tab Content-->
            {{-- <div class="tab-pane {{  Request('active') == $currentType ?'active':'' }}" id="{{ $currentType }}" role="tabpanel">
                <div class="kt-portlet kt-portlet--mobile">
						@php
                        $rowIndex = 0;
                        @endphp
                        <x-tables.repeater-table :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                            <x-slot name="ths">
                                <x-tables.repeater-table-th class="  header-border-down first-column-th-class" :title="__('Name')"></x-tables.repeater-table-th>
                  
                                <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Actions')"></x-tables.repeater-table-th>
                            </x-slot>
                            <x-slot name="trs">

                                @php
                                $currentLoanTotalPerYear = [];
                                @endphp

                                @foreach ($models[$currentType] as $index=>$model)

                                <tr data-repeat-formatting-decimals="0" data-repeater-style>

                                    <td>
                                        <div class="">
                                            <input value="{{ $model->getName() }}" disabled class="form-control text-left " type="text">
                                        </div>
                                    </td>
									
									
									
									  <td class="kt-datatable__cell--left kt-datatable__cell " data-field="Actions" data-autohide-disabled="false">
                                        <span style="overflow: visible; position: relative; width: 110px;">
                                            <a type="button" class="btn btn-secondary btn-outline-hover-brand btn-icon edit-btn-class" title="{{ __('Edit') }}" href="{{ route('edit.departments',['company'=>$company->id,'department'=>$model->id,'type'=>$currentType]) }}"><i class="fa fa-pen-alt exclude-icon default-icon-color" ></i></a>
                                        </span>
                                    </td>



                                </tr>




                                @endforeach


                          




                            </x-slot>




                        </x-tables.repeater-table>
						
                    
                </div>
            </div> --}}
			
			
			
          




            <!--End:: Tab Content-->



            <!--End:: Tab Content-->
        </div>
    </div>
</div>

@endsection
@section('js')
<!--begin::Page Scripts(used by this page) -->
<script src="{{ url('assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/custom/js/vendors/bootstrap-datepicker.init.js') }}" type="text/javascript">
</script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-datepicker.js') }}" type="text/javascript">
</script>
<script src="{{ url('assets/vendors/general/bootstrap-select/dist/js/bootstrap-select.js') }}" type="text/javascript">
</script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-select.js') }}" type="text/javascript">
</script>
<script src="{{ url('assets/vendors/general/jquery.repeater/src/lib.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/general/jquery.repeater/src/jquery.input.js') }}" type="text/javascript">
</script>
{{-- <script src="{{ url('assets/vendors/general/jquery.repeater/src/repeater.js') }}" type="text/javascript"></script> --}}
{{-- <script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/form-repeater.js') }}" type="text/javascript"></script> --}}

<script>
    $(document).on('click', '.js-close-modal', function() {
        $(this).closest('.modal').modal('hide');
    })

</script>
<script>
    $(document).on('change', '.js-search-modal', function() {
        const searchFieldName = $(this).val();
        const popupType = $(this).attr('data-type');
        const modal = $(this).closest('.modal');
        if (searchFieldName === 'transfer_date') {
            modal.find('.data-type-span').html('[ {{ __("Transfer Date") }} ]')
            $(modal).find('.search-field').val('').trigger('change').prop('disabled', true);
        } else if (searchFieldName === 'contract_end_date') {
            modal.find('.data-type-span').html('[ {{ __("Contract End Date") }} ]')
            $(modal).find('.search-field').val('').trigger('change').prop('disabled', true);
        } else if (searchFieldName === 'balance_date') {
            modal.find('.data-type-span').html('[ {{ __("Balance Date") }} ]')
            $(modal).find('.search-field').val('').trigger('change').prop('disabled', true);
        } else {
            modal.find('.data-type-span').html('[ {{ __("Contract Start Date") }} ]')
            $(modal).find('.search-field').prop('disabled', false);
        }
    })
    $(function() {

        $('.js-search-modal').trigger('change')

    })

</script>
@endsection
@push('js')
<script src="/custom/js/financial-planning/common.js"></script>

@endpush
