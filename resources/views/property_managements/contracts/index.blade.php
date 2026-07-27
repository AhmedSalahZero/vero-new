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
   <div id="app-contracts-index"></div>

    <script>
        window.propertyId = {{ $property->id }};
        window.contractsData = @json($contractsData);
    </script>
@endsection
 @vite('resources/js/PropertyManagement/Views/Contracts/index.ts')
