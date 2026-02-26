@extends('layouts.dashboard')

@section('css')
<link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
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
	.btn.btn-icon{
		height: 3rem !important;
    width: 3rem !important;
	}
</style>
@endsection

@section('sub-header')
{{ $title }}
@endsection

@section('content')

<div id="app-properties-index"></div>

@endsection

@section('js')
{{-- <script src="{{ url('assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/custom/js/vendors/bootstrap-datepicker.init.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-datepicker.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/general/bootstrap-select/dist/js/bootstrap-select.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-select.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/general/jquery.repeater/src/lib.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/general/jquery.repeater/src/jquery.input.js') }}" type="text/javascript"></script> --}}

<script>
	window.propertiesData = @json($properties);
    window.emptyRows = @json($empty_rows);
   
</script>
<script>
 $(document).on('click', '.js-close-modal', function() {
        $(this).closest('.modal').modal('hide');
    })
	</script>
@vite('resources/js/Trading/Views/Properties/index.ts')
@endsection

@push('js')
<script src="/custom/js/non-banking-services/common.js"></script>
@endpush
