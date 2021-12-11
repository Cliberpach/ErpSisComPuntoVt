@extends('layout') @section('content')

@section('utilidad-active', 'active')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-8">
       <h2  style="text-transform:uppercase"><b>Utilidad en el mes actual</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('home')}}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Panel</strong>
            </li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row align-items-end">
                        <div class="col-12 col-md-4 text-danger text-center"><h1><b>INVERSIÓN</b></h1></div>
                        <div class="col-12 col-md-4">
                            <div class="row">
                                <div class="col-12 text-center text-primary"><h1><b>DOLARES</b></h1></div>
                                <div class="col-12 text-center text-primary"><h1><b>{{ $inversion_mensual_dolares }}</b></h1></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="row">
                                <div class="col-12 text-center"><h1><b>SOLES</b></h1></div>
                                <div class="col-12 text-center"><h1><b>{{ $inversion_mensual }}</b></h1></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-4 text-danger text-center"><h1><b>VENTAS</b></h1></div>
                        <div class="col-12 col-md-4">
                            <div class="row">
                                <div class="col-12 text-center text-primary"><h1><b>{{ $ventas_mensual_dolares }}</b></h1></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="row">
                                <div class="col-12 text-center"><h1><b>{{ $ventas_mensual }}</b></h1></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-4 text-danger text-center"><h1><b>UTILIDAD</b></h1></div>
                        <div class="col-12 col-md-4">
                            <div class="row">
                                <div class="col-12 text-center text-primary"><h1><b>{{ $utilidad_mensual_dolares }}</b></h1></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="row">
                                <div class="col-12 text-center"><h1><b>{{ $utilidad_mensual }}</b></h1></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-4 text-danger text-center"><h1><b>PORCENTAJE</b></h1></div>
                        <div class="col-12 col-md-4">
                            <div class="row">
                                <div class="col-12 text-center text-primary"><h1><b>{{ $porcentaje }}%</b></h1></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="row">
                                <div class="col-12 text-center"><h1><b>{{ $porcentaje }}%</b></h1></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@stop
@push('styles')
<!-- DataTable -->
<link href="{{asset('Inspinia/css/plugins/dataTables/datatables.min.css')}}" rel="stylesheet">
<style>
    .letrapequeña {
        font-size: 11px;
    }

</style>
@endpush

@push('scripts')
<!-- DataTable -->
<script src="{{asset('Inspinia/js/plugins/dataTables/datatables.min.js')}}"></script>
<script src="{{asset('Inspinia/js/plugins/dataTables/dataTables.bootstrap4.min.js')}}"></script>

@endpush
