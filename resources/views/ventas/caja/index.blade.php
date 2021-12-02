@extends('layout') @section('content')

@section('ventas-active', 'active')
@section('ventas-caja-active', 'active')
<div id="app">
    <ventas-component :modospago="{{ modos_pago() }}"></ventas-component>
</div>
@stop
@section('vue-css')
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<style>
    .imagen {
        width: 200px;
        height: 200px;
        border-radius: 10%;
    }

</style>
@stop
@section('vue-js')
<script src="{{ asset('js/app.js?v='.rand()) }}"></script>
@stop
