@extends('layouts.dashboard')
@section('css')
<link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet" type="text/css" />
<style>
    .kt-portlet {
        overflow: visible !important;
    }

</style>
@endsection
@section('sub-header')
{{ __('Cash Expense Statement') }}
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">



        <!--begin::Form-->
        <form class="kt-form kt-form--label-right" method="POST" action="{{ route('result.cash.expense.statement',['company'=>$company->id ]) }}" enctype="multipart/form-data">
            @csrf
            <div class="kt-portlet" style="overflow-x:hidden">
                <div class="kt-portlet__body">
                    <div class="form-group row">
                        <div class="col-md-2 mb-4">
                            <label>{{ __('Start Date') }} <span class="multi_selection"></span> </label>
                            <div class="kt-input-icon">
                                <div class="input-group date">
                                    <input required type="date" class="form-control" name="start_date" value="{{ now() }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 mb-4">
                            <label>{{ __('End Date') }} <span class="multi_selection"></span> </label>
                            <div class="kt-input-icon">
                                <div class="input-group date">
                                    <input required type="date" class="form-control" name="end_date" value="{{ now()->addYear() }}">
                                </div>
                            </div>
                        </div>




                        <div class="col-md-2 mb-4">
                            <label>{{ __('Select Currency') }} </label>
                            <div class="kt-input-icon">
                                <div class="input-group date">
                                    <select  data-live-search="true" data-actions-box="true" name="currency" required class="form-control  kt-bootstrap-select select2-select kt_bootstrap_select ajax-currency-name">
                                        @foreach(getCurrency() as $currency=>$currencyName)
                                        <option value="{{ $currency }}">{{ touppercase($currencyName) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                         <div class="col-md-3 mb-4">
							<x-form.select :multiple="true"  :add-new-modal="true" :add-new-modal-modal-type="''" :add-new-modal-modal-name="'CashExpenseCategory'" :add-new-modal-modal-title="__('Expense Category')" :options="$cashExpenseCategories" :add-new="false" :label="__('Expense Category')" class="select2-select expense_category  " data-update-category-name-based-on-category data-filter-type="{{ 'create' }}" :all="false" name="expense_category_id[]" id="expense_category_id" :selected-value="isset($model) ? $model->getExpenseCategoryId() : 0"></x-form.select>
						</div>


					<div class="col-md-3 mb-4">
						<x-form.select :multiple="true" :add-new-modal="true" :add-new-modal-modal-type="''" :add-new-modal-modal-name="'CashExpenseCategoryName'" :add-new-modal-modal-title="__('Expense Name')" :previous-select-name-in-dB="'cash_expense_category_id'" :previous-select-must-be-selected="true"  :previous-select-selector="'select.expense_category'" :previous-select-title="__('Expense Name')" :options="[]" :add-new="false" :label="__('Expense Name')" class="select2-select category_name  " data-filter-type="{{ 'create' }}" :all="false" name="cash_expense_category_name_id[]" id="{{'cash_expense_category_name_id' }}" :selected-value="isset($model) ? $model->getCashExpenseCategoryNameId() : 0" data-current-selected="{{ isset($model) ? $model->getCashExpenseCategoryNameId() : 0 }}"></x-form.select>
					</div>







                        <x-submitting />







                    </div>

                </div>
            </div>





        </form>

        <!--end::Form-->

        <!--end::Portlet-->
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
<script src="{{ url('assets/vendors/general/jquery.repeater/src/repeater.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/form-repeater.js') }}" type="text/javascript"></script>
<script>
 $(document).on('change', '[data-update-category-name-based-on-category]', function(e) {
                    const expenseCategoryId = $('select.expense_category').val()
                    if (!expenseCategoryId) {
                        return;
                    }
                    $.ajax({
                        url: "{{route('update.expense.category.name.based.on.category',['company'=>$company->id])}}"
                        , data: {
                            expenseCategoryId
                        , }
                        , type: "GET"
                        , success: function(res) {
                            var options = '';
                            var currentSelectedId = $('select.category_name').attr('data-current-selected')
						
                            for (var categoryName in res.categoryNames) {
                                var categoryNameId = res.categoryNames[categoryName];
                                options += `<option ${currentSelectedId == categoryNameId ? 'selected' : '' } value="${categoryNameId}"> ${categoryName}  </option> `;
                            }
                            $('select.category_name').empty().append(options).selectpicker("refresh");
                            $('select.category_name').trigger('change')
                        }
                    })
                })
                $('[data-update-category-name-based-on-category]').trigger('change')
				
</script>
@endsection
