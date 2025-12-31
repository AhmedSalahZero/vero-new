@extends('layouts.dashboard')
@php
use App\Models\NonBankingService\ExpenseName;
use App\Helpers\HArr;
@endphp
@section('css')
<link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/custom/css/financial-planning/common.css">
<style>
i.exclude-icon{
	color:black;
}
.group-color {
	background-color:#f7f8fa !important;
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
                    <a class="nav-link {{ !Request('active') || Request('active') == ExpenseName::EXPENSE ?'active':'' }}" data-toggle="tab" href="#{{ExpenseName::EXPENSE  }}" role="tab">
                        <i class="fa fa-money-check-alt"></i> {{ __('Expenses') }}
                    </a>
                </li>




            </ul>
            {{-- @if(auth()->user()->can('create study info')) --}}

            <div class="flex-tabs">


                <a href="{{ route('create.expense.names',['company'=>$company->id]) }}" class="btn btn-2-bg bg-white-hover new-study-item rounded btn-icon-sm align-self-center">
                    <i class="fas fa-plus white-icon "></i>
                    {{ __('New Expense') }}
                </a>


            </div>



        </div>
    </div>
        <div class="tab-content  kt-margin-t-20">

            <!--Begin:: Tab Content-->
            <div class="tab-pane {{ !Request('active') ?'active':'' }}" id="{{ 'running' }}" role="tabpanel">
                <div class="kt-portlet kt-portlet--mobile">

           


                        <x-table :tableClass="'kt_table_with_no_pagination_no_fixed  removeGlobalStyle ' ">
                            @slot('table_header')


                           
                            @endslot
                            @slot('table_body')
                            <tr class=" text-center first-tr-bg ">
                                <th class=" form-label font-weight-bold  text-center align-middle   header-border-down first-column-th-class">
                                    <div class="d-flex align-items-center justify-content-center ">
                                        <span class="">{{ __('Name') }}</span>
                                    </div>
                                </th>



                                <th class=" form-label font-weight-bold  text-center align-middle   header-border-down first-column-th-class">
                                    <div class="d-flex align-items-center justify-content-center ">
                                        <span class=""> {{ __('Actions') }}</span>
                                    </div>
                                </th>

                            </tr>
                            @php
                            $id = 0 ;
                            @endphp
                            @foreach($items as $mainItemId => $parnetAndSubData )
                            @php
                            $parent =$parnetAndSubData['parent'] ;
                            $subItems =$parnetAndSubData['sub_items'] ?? [];

                            @endphp
                            <tr class="group-color main-row-tr">



                                <td class="black-text " style="cursor: pointer;" onclick="toggleRow('{{ $mainItemId }}')">

                                    <div class="d-flex align-items-center ">
                                        @if(count($subItems))
                                        <i class="row_icon{{ $mainItemId }} exclude-icon flaticon2-up  mr-2  exclude-icon"></i>
                                        @endif
                                        <b class="text-capitalize ">{{ str_to_upper($parent['name']) }}</b>
                                    </div>
                                </td>






                                <td class="text-left text-capitalize">





                                    <b class="ml-3">

                                        <a type="button" class="btn btn-secondary btn-outline-hover-brand btn-icon" title="Edit" href="{{ route('edit.expense.names', ['company'=>$company->id,'expenseType'=>$parent['name']]) }}"><i class="fa exclude-icon fa-pen-alt"></i></a>
                                        <a class="btn btn-secondary btn-outline-hover-danger btn-icon  " href="#" data-toggle="modal" data-target="#modal-delete-{{ $mainItemId }}" title="Delete"><i class="fa exclude-icon fa-trash-alt"></i>
                                        </a>

                                        <div id="modal-delete-{{ $mainItemId }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">{{ __('Delete Cash Expense Category ' . str_to_upper($parent['name']) ) }}</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <h3>{{ __('Are You Sure To Delete This Item ? ') }}</h3>
                                                    </div>
                                                    <form action="{{ route('expense.names.destroy',['company'=>$company->id , 'expenseType'=> $parent['name'] ]) }}" method="post" id="delete_form">
                                                        {{ csrf_field() }}
                                                        {{ method_field('DELETE') }}
                                                        <div class="modal-footer">
                                                            <button class="btn btn-danger">
                                                                {{ __('Delete') }}
                                                            </button>
                                                            <button class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">
                                                                {{ __('Close') }}
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        </span>
                                    </b>
                                </td>








                            </tr>

                            @foreach ($subItems as $subItemId => $titleAndValue)



                            <tr class="row{{ $mainItemId }}  text-center sub-item-row" style="display: none">
                                <td colspan="5" class="text-left  text-capitalize">
                                    <table class="table ml-3 table-borderless">

                                        <tr>

                                            <td class="max-w-20">
                                                <input type="text" class="form-control" disabled value="{{ __('Name') }}">
                                            </td>
                                            <td>

                                                <input type="text" class="form-control" disabled value="{{ $titleAndValue['name'] }}">
                                            </td>

                                        </tr>

                                    </table>
                                </td>









                            </tr>

                            @endforeach


                            <?php $id++ ;?>
                            @endforeach





                            @endslot
                        </x-table>


             
            </div>
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
<script>
    function toggleRow(rowNum) {
        $(".row" + rowNum).toggle();
        $('.row_icon' + rowNum).toggleClass("flaticon2-down flaticon2-up");
        $(".row2" + rowNum).hide();
    }

</script>
@endpush
