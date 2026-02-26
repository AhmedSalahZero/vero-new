@extends('layouts.dashboard')
@php
use App\Models\PropertyManagement\Study;
@endphp
@section('css')
<link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/custom/css/financial-planning/common.css">
<style>
    .header-border {
        border-bottom: 1px solid #007bff;
    }

    .w-200px {
        width: 200px !important;
        font-size: 0.875rem !important
    }

    .w-50-prc {
        max-width: 50% !important;
        min-width: 50% !important;
        width: 50% !important;
    }

    .w-40-prc {
        max-width: 40% !important;
        min-width: 40% !important;
        width: 40% !important;
    }

    .w-10-prc {
        max-width: 10% !important;
        min-width: 10% !important;
        width: 10% !important;
    }

    .w-5-prc {
        max-width: 5% !important;
        min-width: 5% !important;
        width: 5% !important;
    }

</style>
<style>
    .multi-flex-tabs {
        margin-top: 5px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 10px;
    }

    .bg-white-hover:hover {
        color: white !important;
    }

    .new-study-item i {
        color: #055dac !important
    }

    .new-study-item:hover i {
        color: white !important;
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
                    <a class="nav-link {{ !Request('active') || Request('active') == Study::ANNUALLY_STUDY ?'active':'' }}" data-toggle="tab" href="#{{Study::ANNUALLY_STUDY  }}" role="tab">
                        <i class="fa fa-money-check-alt"></i> {{ __('Annually Study') }}
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{  Request('active') == Study::BUSINESS_PLAN ?'active':'' }}" data-toggle="tab" href="#{{Study::BUSINESS_PLAN  }}" role="tab">
                        <i class="fa fa-money-check-alt"></i> {{ __('Business Plan') }}
                    </a>
                </li>







            </ul>








            <div class="multi-flex-tabs">
                {{-- <div class="flex-tabs">
			   	
					
			   </div> --}}
                <div class="flex-tabs mt-2">
                    <a href="{{ route('view.property.management.foreign.exchange.rate',['company'=>$company->id]) }}" class="btn w-200px new-record-class new-study-item rounded btn-icon-sm align-self-center">
                        <i class="fas fa-plus white-icon exclude-icon"></i>
                        {{ __('Foreign Exchange Rate') }}
                    </a>

                    <a href="{{ route('property.management.create.study',['company'=>$company->id,'is_business_plan'=>0]) }}" class="btn 
					 {{-- @if(!$company->hasAtLeastOneOfEachMainModels())
					 visibility-hidden 
                    @endif --}}
					w-200px btn-2-bg bg-white-hover new-study-item rounded btn-icon-sm align-self-center">
                        <i class="fas fa-plus white-icon exclude-icon "></i>
                        {{ __('New Financial Plan') }}
                    </a>


                    <a href="{{ route('property.management.view.properties',['company'=>$company->id]) }}" class="btn w-200px new-record-class new-study-item rounded btn-icon-sm align-self-center">
                        <i class="fas fa-plus white-icon exclude-icon"></i>
                        {{ __('Properties') }}
                    </a>








                    <a href="{{ route('property.management.view.departments',['company'=>$company->id]) }}" class="btn w-200px new-record-class new-study-item rounded btn-icon-sm align-self-center">
                        <i class="fas fa-plus white-icon exclude-icon"></i>
                        {{ __('Manpower Structure') }}
                    </a>

                    <a href="{{ route('property.management.view.expense.names',['company'=>$company->id]) }}" class="btn w-200px new-record-class new-study-item rounded btn-icon-sm align-self-center">
                        <i class="fas fa-plus white-icon exclude-icon"></i>
                        {{ __('Cost & Expenses') }}
                    </a>
					 <a href="{{ route('property.management.view.tenants',['company'=>$company->id]) }}" class="btn w-200px new-record-class new-study-item rounded btn-icon-sm align-self-center">
                        <i class="fas fa-plus white-icon exclude-icon"></i>
                        {{ __('Tenants') }}
                    </a>
                    <a href="{{ route('property.management.view.fixed.asset.names',['company'=>$company->id]) }}" class="btn w-200px new-record-class new-study-item rounded btn-icon-sm align-self-center">
                        <i class="fas fa-plus white-icon exclude-icon"></i>
                        {{ __('Fixed Assets') }}
                    </a>
                </div>
            </div>




        </div>
    </div>
    <div class="kt-portlet__body pt-0">
        <div class="tab-content  kt-margin-t-20">

            @php
            $currentType = Study::BUSINESS_PLAN ;
            @endphp
            <!--Begin:: Tab Content-->
            <div class="tab-pane {{   Request('active') == $currentType ?'active':'' }}" id="{{ $currentType }}" role="tabpanel">
                <div class="kt-portlet kt-portlet--mobile">
                    @php
                    $rowIndex = 0;
                    @endphp
                    <x-tables.repeater-table :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                        <x-slot name="ths">
                            <x-tables.repeater-table-th class="  header-border-down first-column-th-class" :title="__('Name')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Start Date')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('End Date')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Financial Statement')"></x-tables.repeater-table-th>
                            {{-- <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Balance Sheet')"></x-tables.repeater-table-th> --}}
                            {{-- <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Cash Flow')"></x-tables.repeater-table-th> --}}
                            <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Dashboard')"></x-tables.repeater-table-th>
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
                                    <div class="d-flex align-items-center justify-content-center">
                                        <x-repeat-right-dot-inputs :removeThreeDots="true" :removeCurrency="true" :mark="' '" :is-number="false" :removeThreeDotsClass="true" :number-format-decimals="0" :currentVal="$model->getStudyStartDateFormattedForView()" :classes="''" :is-percentage="false" :name="''" :columnIndex="0"></x-repeat-right-dot-inputs>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <x-repeat-right-dot-inputs :removeThreeDots="true" :removeCurrency="true" :mark="' '" :is-number="false" :removeThreeDotsClass="true" :number-format-decimals="0" :currentVal="$model->getStudyEndDateFormattedForView()" :classes="''" :is-percentage="false" :name="''" :columnIndex="0"></x-repeat-right-dot-inputs>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center flex-column " style="gap:10px;">
                                        <div class="d-flex mr-auto" style="gap:10px;">
                                            <a href="{{ route('view.property.management.forecast.income.statement',['company'=>$company->id,'study'=>$model->id]) }}" class="btn btn-md-width btn-1-bg btn-sm btn-brand btn-pill">{{ __('Income Statement') }}</a>
                                            <a href="{{ route('property.management.balance.sheet.result',['company'=>$company->id,'study'=>$model->id]) }}" class="btn btn-md-width btn-2-bg btn-sm btn-brand btn-pill">{{ __('Balance Sheet') }}</a>
                                        </div>
                                        <div class="d-flex mr-auto" style="gap:10px;">
                                            <a href="{{ route('property.management.cash.in.out.flow.result',['company'=>$company->id,'study'=>$model->id]) }}" class="btn btn-md-width btn-3-bg btn-sm btn-brand btn-pill">{{ __('Cash Flow') }}</a>
                                            <a href="#" class="btn btn-md-width btn-4-bg btn-sm btn-brand btn-pill">{{ __('Ratio Analysis') }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex mr-auto" style="gap:10px;">
                                        <a href="{{ route('property.management.view.results.dashboard',['company'=>$company->id,'study'=>$model->id]) }}" class="btn btn-sm-width btn-1-bg btn-sm btn-brand btn-pill">{{ __('Result') }}</a>
                                        <a href="{{ route('view.property.management.valuation',['company'=>$company->id,'study'=>$model->id]) }}" class="btn btn-sm-width btn-2-bg btn-sm btn-brand btn-pill">{{ __('Valuation') }}</a>
                                    </div>
                                </td>
                                <td class="kt-datatable__cell--left kt-datatable__cell " data-field="Actions" data-autohide-disabled="false">
                                    <span style="overflow: visible; position: relative; width: 110px;">
                                        <a type="button" class="btn btn-secondary btn-outline-hover-brand btn-icon edit-btn-class" title="{{ __('Edit') }}" href="{{ route('property.management.edit.study',['company'=>$company->id,'study'=>$model->id]) }}"><i class="fa fa-pen-alt exclude-icon default-icon-color"></i></a>
                                        <a data-toggle="modal" data-target="#copy{{ $model->id }}" type="button" class="btn btn-secondary btn-outline-hover-brand btn-icon copy-btn-class" title="{{ __('Copy') }}" href="#"><i class="fa fa-copy exclude-icon default-icon-color"></i></a>
                                        <div class="modal fade" id="copy{{ $model->id }}" tabindex="-1" role="dialog" aria-labelledby="copyModalLabel{{ $model->id }}" aria-hidden="true" style="color: black">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="copyModalLabel{{ $model->id }}">{{ __('Copy') }}</h5>
                                                    </div>
                                                    <form action="{{ route('property.management.copy.study', ['study'=>$model->id,'company'=>$company->id]) }}" id="form{{ $model->id }}" method="POST">
                                                        <div class="modal-body">

                                                            {{ csrf_field() }}
                                                            <div class="form-group">
                                                                <label for="name{{ $model->id }}" class="col-form-label">{{ __('New Name') }}</label>

                                                                <input type="text" name="name" class="form-control" id="name{{ $model->id }}">
                                                            </div>

                                                        </div>
                                                        <div class="modal-footer d-flex">
                                                            <button type="button" class="btn btn-sm btn-secondary p-2" data-dismiss="modal">{{__('Close')}}</button>
                                                            <button type="submit" class="btn btn-sm btn-info p-2 "><span class="tooltiptext submit-copy-btn"><i class="far fa-copy"> {{__('Copy')}}</i> </span>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <a data-toggle="modal" data-target="#delete-study-{{ $model->id }}" type="button" class="btn delete-btn-class btn-secondary btn-outline-hover-danger btn-icon" title="Delete" href="#"><i class="fa fa-trash-alt exclude-icon default-icon-color"></i></a>
                                        <div class="modal fade" id="delete-study-{{ $model->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <form action="{{ route('property.management.study.destroy',['company'=>$company->id,'study'=>$model->id ]) }}" method="post">
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



                                    </span>
                                </td>



                            </tr>




                            @endforeach







                        </x-slot>




                    </x-tables.repeater-table>


                </div>
            </div>



            @php
            $currentType = Study::ANNUALLY_STUDY ;
            @endphp
            <!--Begin:: Tab Content-->
            <div class="tab-pane {{  !Request('active') || Request('active') == $currentType ?'active':'' }}" id="{{ $currentType }}" role="tabpanel">
                <div class="kt-portlet kt-portlet--mobile">
                    @php
                    $rowIndex = 0;
                    @endphp
                    <x-tables.repeater-table :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                        <x-slot name="ths">
                            <x-tables.repeater-table-th class="  header-border-down first-column-th-class" :title="__('Study Name')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Start Date')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('End Date')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Income Statement')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Balance Sheet')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Cash Flow')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Dashboard')"></x-tables.repeater-table-th>
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
                                    <div class="d-flex align-items-center justify-content-center">
                                        <x-repeat-right-dot-inputs :removeThreeDots="true" :removeCurrency="true" :mark="' '" :is-number="false" :removeThreeDotsClass="true" :number-format-decimals="0" :currentVal="$model->getStudyStartDateFormattedForView()" :classes="''" :is-percentage="false" :name="''" :columnIndex="0"></x-repeat-right-dot-inputs>

                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <x-repeat-right-dot-inputs :removeThreeDots="true" :removeCurrency="true" :mark="' '" :is-number="false" :removeThreeDotsClass="true" :number-format-decimals="0" :currentVal="$model->getStudyEndDateFormattedForView()" :classes="''" :is-percentage="false" :name="''" :columnIndex="0"></x-repeat-right-dot-inputs>

                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center flex-column " style="gap:10px;">
                                        <div class="d-flex mr-auto" style="gap:10px;">
                                            <a href="{{ route('view.property.management.forecast.income.statement',['company'=>$company->id,'study'=>$model->id]) }}" class="btn btn-sm-width btn-1-bg btn-sm btn-brand btn-pill">{{ __('Forecast') }}</a>
                                            <a href="#" class="btn btn-sm-width btn-2-bg btn-sm btn-brand btn-pill">{{ __('Actual') }}</a>
                                        </div>
                                        <div class="d-flex mr-auto" style="gap:10px;">
                                            <a href="#" class="btn btn-sm-width btn-3-bg btn-sm btn-brand btn-pill">{{ __('Adjusted') }}</a>
                                            <a href="#" class="btn btn-sm-width btn-4-bg btn-sm btn-brand btn-pill">{{ __('Modified') }}</a>
                                        </div>
                                    </div>

                                </td>
                                <td>
                                    <div class="d-flex mr-auto" style="gap:10px;">
                                        <a href="{{ route('property.management.balance.sheet.result',['company'=>$company->id,'study'=>$model->id]) }}" class="btn btn-sm-width btn-1-bg btn-sm btn-brand btn-pill">{{ __('Forecast') }}</a>
                                        <a href="#" class="btn btn-sm-width btn-2-bg btn-sm btn-brand btn-pill">{{ __('Actual') }}</a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex mr-auto" style="gap:10px;">
                                        <a href="{{ route('property.management.cash.in.out.flow.result',['company'=>$company->id,'study'=>$model->id]) }}" class="btn btn-sm-width btn-1-bg btn-sm btn-brand btn-pill">{{ __('Forecast') }}</a>
                                        <a href="#" class="btn btn-sm-width btn-2-bg btn-sm btn-brand btn-pill">{{ __('Actual') }}</a>
                                    </div>

                                </td>
                                <td>
                                    <div class="d-flex mr-auto" style="gap:10px;">
                                        <a href="{{ route('property.management.view.results.dashboard',['company'=>$company->id,'study'=>$model->id]) }}" class="btn btn-sm-width btn-1-bg btn-sm btn-brand btn-pill">{{ __('Forecast') }}</a>
                                        <a href="#" class="btn btn-sm-width btn-2-bg btn-sm btn-brand btn-pill">{{ __('Actual') }}</a>
                                    </div>
                                </td>
                                <td class="kt-datatable__cell--left kt-datatable__cell " data-field="Actions" data-autohide-disabled="false">
                                    <span style="overflow: visible; position: relative; width: 110px;">
                                        {{-- @if(hasAuthFor('update lc settlement internal transfer')) --}}
                                        {{-- <a type="button" class="btn btn-secondary btn-outline-hover-brand btn-icon edit-btn-class" title="Edit" href="{{ route('property.management.edit.study',['company'=>$company->id,'study'=>$model->id]) }}"><i class="fa fa-pen-alt exclude-icon default-icon-color"></i></a> --}}
                                        {{-- @endif  --}}

                                        <a type="button" class="btn btn-secondary btn-outline-hover-brand btn-icon edit-btn-class" title="{{ __('Edit') }}" href="{{ route('property.management.edit.study',['company'=>$company->id,'study'=>$model->id]) }}"><i class="fa fa-pen-alt exclude-icon default-icon-color"></i></a>
                                        <a data-toggle="modal" data-target="#copy{{ $model->id }}" type="button" class="btn btn-secondary btn-outline-hover-brand btn-icon copy-btn-class" title="{{ __('Copy') }}" href="#"><i class="fa fa-copy exclude-icon default-icon-color"></i></a>
                                        <div class="modal fade" id="copy{{ $model->id }}" tabindex="-1" role="dialog" aria-labelledby="copyModalLabel{{ $model->id }}" aria-hidden="true" style="color: black">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="copyModalLabel{{ $model->id }}">{{ __('Copy') }}</h5>
                                                    </div>
                                                    <form action="{{ route('property.management.copy.study', ['study'=>$model->id,'company'=>$company->id]) }}" id="form{{ $model->id }}" method="POST">
                                                        <div class="modal-body">

                                                            {{ csrf_field() }}
                                                            <div class="form-group">
                                                                <label for="name{{ $model->id }}" class="col-form-label">{{ __('New Name') }}</label>

                                                                <input type="text" name="name" class="form-control" id="name{{ $model->id }}">
                                                            </div>

                                                        </div>
                                                        <div class="modal-footer d-flex">
                                                            <button type="button" class="btn btn-sm btn-secondary p-2" data-dismiss="modal">{{__('Close')}}</button>
                                                            <button type="submit" class="btn btn-sm btn-info p-2 "><span class="tooltiptext submit-copy-btn"><i class="far fa-copy"> {{__('Copy')}}</i> </span>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- @if(hasAuthFor('delete lc settlement internal transfer')) --}}
                                        <a data-toggle="modal" data-target="#delete-study-{{ $model->id }}" type="button" class="btn btn-secondary btn-outline-hover-danger btn-icon delete-btn-class" title="Delete" href="#"><i class="fa fa-trash-alt exclude-icon default-icon-color"></i></a>
                                        <div class="modal fade" id="delete-study-{{ $model->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <form action="{{ route('property.management.study.destroy',['company'=>$company->id,'study'=>$model->id ]) }}" method="post">
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














            <!--End:: Tab Content-->



            <!--End:: Tab Content-->
        </div>

    </div>


</div>


@if(!$company->hasAtLeastOneOfEachMainModels())
<div class="kt-portlet">
    <div class="kt-portlet__body">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-red">
                    Heads up!!!
                </h1>
                <h4>
                    Before you can create a new financial plan, please make sure you’ve added:
                </h4>
                <ul>
                    @foreach([
                    'At least one property (click Properties Button).',
                    'At least one manpower department and position (click Manpower Structure Button).',
                    'At least one expense item (click Cost & Expense Button).',
                    'At least one fixed asset (click Fixed Asset Button).'
                    ] as $text)
                    <li>
                        <h5 class="text-green mb-4 mt-4">{{ $text }}</h5>
                    </li>
                    @endforeach
                </ul>
                <h4>
                    Once these are set, you’re all ready to go! 😊

                </h4>
            </div>
        </div>
    </div>
</div>
@endif

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
