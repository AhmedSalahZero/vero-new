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
{{ __('Cheques Report') }}
@endsection
@section('content')
<div class="">
    <div class="row">
        <div class="col-md-12">

            <!--begin::Form-->
            <form class="kt-form kt-form--label-right" method="get" action="{{ route('result.cheques.report',['company'=>$company->id ]) }}">
                <div class="kt-portlet" style="overflow-x:hidden">
                    <div class="kt-portlet__body">
                        <p class="text-muted mb-4">{{ __('Received or payable cheques whose due date is on or after the date you pick.') }}</p>
                        <div class="form-group row">

                            <div class="col-md-3 mb-4">
                                <label>{{ __('Type') }} @include('star')</label>
                                <div class="kt-input-icon">
                                    <div class="input-group date">
                                        <select required name="type" class="form-control">
                                            @foreach($types as $typeValue => $typeLabel)
                                            <option value="{{ $typeValue }}" @if(old('type', request('type')) == $typeValue) selected @endif>{{ $typeLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 mb-4">
                                <label>{{ __('Due Date From') }} @include('star')</label>
                                <div class="kt-input-icon">
                                    <div class="input-group date">
                                        <input required type="date" class="form-control" name="due_date" value="{{ old('due_date', now()->format('Y-m-d')) }}">
                                    </div>
                                </div>
                                @error('due_date')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-4 mb-4">
                                <label>{{ __('Currency') }} <small class="text-muted">{{ __('(leave empty for all)') }}</small></label>
                                <div class="kt-input-icon">
                                    <div class="input-group date">
                                        <select multiple data-live-search="true" data-actions-box="true" name="currencies[]" class="form-control select2-select">
                                            @foreach($currencies as $currencyCode => $currencyName)
                                            <option value="{{ $currencyCode }}" @if(in_array($currencyCode, (array) old('currencies', []))) selected @endif>{{ touppercase($currencyName) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 mb-4 d-flex align-items-end">
                                <button type="submit" class="btn active-style">{{ __('View Report') }}</button>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
            <!--end::Form-->

        </div>
    </div>
</div>
@endsection
@section('js')
<script src="{{ url('assets/vendors/general/bootstrap-select/dist/js/bootstrap-select.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-select.js') }}" type="text/javascript"></script>
@endsection
