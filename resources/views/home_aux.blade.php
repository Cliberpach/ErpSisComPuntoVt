@extends('layout') 
@section('content')
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <span class="label label-success float-right">{{ mes() }}</span>
                    <h5>Ventas</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins">{{ number_format(ventas_mensual(), 2) }}</h1>
                    <div class="stat-percent font-bold text-success d-none">98% <i class="fa fa-bolt"></i></div>
                    <small>Total de ventas:</small>
                </div>
            </div>
        </div>
    </div>
</div>
@stop