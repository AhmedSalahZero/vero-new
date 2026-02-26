@extends('layouts.dashboard')

@section('css')
    <link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">

	@vite('resources/js/Trading/Views/Contracts/form.ts')
	
@endsection

@section('sub-header')
<x-main-form-title :id="'main-form-title'" :class="''">{{ $title }}</x-main-form-title>

@endsection

@section('content')
     <div id="app-contracts-form"></div>

    <script>
        window.propertyId = {{ $property->id }};
        window.contractId = {{ isset($contract) ? $contract->id : 'null' }};
    </script>
@endsection
