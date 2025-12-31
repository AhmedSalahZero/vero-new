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
{{ __('Bank Statement') }}
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">


        <!--begin::Form-->
        <form class="kt-form kt-form--label-right" method="post" action="{{ route('result.expense.statement.reports',['company'=>$company->id,'study'=>$study->id ]) }}" enctype="multipart/form-data">
            @csrf
            <div class="kt-portlet" style="overflow-x:hidden">
                <div class="kt-portlet__body">
                    <div class="form-group row">
                        <div class="col-md-3 mb-4">
                            <label>{{ __('Start Date') }} <span class="multi_selection"></span> </label>
                            <div class="kt-input-icon">
                                <div class="input-group date">
                                    <input required type="date" class="form-control" name="start_date" value="{{ now() }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-4">
                            <label>{{ __('End Date') }} <span class="multi_selection"></span> </label>
                            <div class="kt-input-icon">
                                <div class="input-group date">
                                    <input required type="date" class="form-control" name="end_date" value="{{ now()->addYear() }}">
                                </div>
                            </div>
                        </div>



 <div class="col-md-2 width-12">
                            <label>{{__('Expense Type')}} @include('star')</label>
                            <div class="kt-input-icon">
                                <div class="input-group date">
                                    <select required name="expense_type" class="form-control ">
									@foreach($expenseTypes as $id => $title)
                                        <option value="{{ $id }}" >{{$title}}</option>
										@endforeach 
                                    </select>
                                </div>
                            </div>
                        </div>

                      
                        <div class="col-md-2 width-45">
                            <label>{{__('Expense Category')}} @include('star')</label>
                            <div class="kt-input-icon">
                                <div class="input-group date">
                                    <select id="expense-category-id" name="expense_category_id" class="form-control ">
                                        @foreach($expenseCategories as $id=>$title)
                                        <option value="{{ $id }}" >{{ camelizeWithSpace($title) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
						<div class="col-md-2 width-45">
                            <label>{{__('Expense Name')}} @include('star')</label>
                            <div class="kt-input-icon">
                                <div class="input-group date">
                                    <select id="expense-name-id" name="expense_name_id" class="form-control ">
                                        
                                    </select>
                                </div>
                            </div>
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
	$(document).on('change','select#expense-category-id',function(){
		const val = $(this).val();
		$.ajax({
			url:"{{ route('get.expense.name.for.category',['company'=>$company->id , 'study'=>$study->id]) }}",
			data:{expenseCategoryId:val},
			success:function(res){
				let result = res.data ;
				let options = '';
				for(index in result){
					var row = result[index];
					options += `<option value="${row.id}">${row.name}</option>`;
				}
				$(document).find('select#expense-name-id').empty().append(options).trigger('change');
			}
		})
	})
	$('select#expense-category-id').trigger('change');
</script>

@endsection
