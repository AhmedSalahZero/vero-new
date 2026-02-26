@extends('layouts.dashboard')
@section('css')
<x-styles.commons></x-styles.commons>
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">


@section('sub-header')
<x-main-form-title :id="'main-form-title'" :class="''">{{ $title }}</x-main-form-title>

@endsection
@section('content')


<div id="app-properties">
</div>

<script>
window.translations = @json(\Lang::get('messages'));
console.log(window.translations)
	  </script>
	  	  @vite('resources/js/Trading/Views/Properties/properties.ts')
		  
@endsection
