@extends('layout') @section('content')
    @include('pos.MovimientoCaja.create')
    @include('pos.MovimientoCaja.cerrar')
@section('caja-movimiento-active', 'active')
@section('caja-chica-active', 'active')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-10">
        <h2 style="text-transform:uppercase"><b>LISTADO DE MOVIMIENTOS DE CAJAS</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Movimiento Caja</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2 col-md-2">
        @can('haveaccess', 'movimiento_caja.create')
        <a class="btn btn-block btn-w-m btn-modal btn-primary m-t-md" href="#">
            <i class="fa fa-plus-square"></i> Añadir nuevo
        </a>
        @endcan
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table dataTables-cajas table-striped table-bordered table-hover"
                            style="text-transform:uppercase">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="text-center">CAJA</th>
                                    <th class="text-center">CANTIDAD INICIAL</th>
                                    <th class="text-center">CANTIDAD CIERRE</th>
                                    <th class="text-center">FECHA APERTURA</th>
                                    <th class="text-center">FECHA CIERRE</th>
                                    <th class="text-center">ACCIONES</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@push('styles')
<!-- DataTable -->
<link href="{{ asset('Inspinia/css/plugins/dataTables/datatables.min.css') }}" rel="stylesheet">
<style>
    .my-swal {
        z-index: 3000 !important;
    }

</style>
@endpush
@push('scripts')
<!-- DataTable -->
<script src="{{ asset('Inspinia/js/plugins/dataTables/datatables.min.js') }}"></script>
<script src="{{ asset('Inspinia/js/plugins/dataTables/dataTables.bootstrap4.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    $('.dataTables-cajas').DataTable({
        "dom": '<"html5buttons"B>lTfgitp',
        "buttons": [{
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel-o"></i> Excel',
                titleAttr: 'Excel',
                title: 'Tablas Generales'
            },
            {
                titleAttr: 'Imprimir',
                extend: 'print',
                text: '<i class="fa fa-print"></i> Imprimir',
                customize: function(win) {
                    $(win.document.body).addClass('white-bg');
                    $(win.document.body).css('font-size', '10px');
                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                }
            }
        ],
        "bPaginate": true,
        "bLengthChange": true,
        "bFilter": true,
        "bInfo": true,
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": '{{ route('Caja.get_movimientos_cajas') }}',
        "columns": [
            //Caja chica
            {
                data: 'id',
                className: "text-center",
                "visible": false
            },
            {
                data: 'caja',
                className: "text-center"
            },
            {
                data: 'cantidad_inicial',
                className: "text-center"
            },
            {
                data: 'cantidad_final',
                className: "text-center"
            },
            {
                data: 'fecha_Inicio',
                className: "text-center"
            },
            {
                data: 'fecha_Cierre',
                className: "text-center"
            },
            {
                data: null,
                className: "text-center",
                "render": function(data, type, row, meta) {
                    var html =
                        "<div class='btn-group'><a class='btn btn-primary btn-sm' href='#' title='Caja Cerrada'><i class='fa fa-check'> Caja Cerrada</i></a></div>";
                    if (data.fecha_Cierre == "-") {
                        html =
                            "<div class='btn-group'><a class='btn btn-warning btn-sm' href='#'  onclick='cerrarCaja(" +
                            data.id +
                            ")' title='Modificar'><i class='fa fa-lock'> cerrar</i></a><a class='btn btn-danger btn-sm' href='#'  onclick='reporte(" +
                            data.id +
                            ")' title='Modificar'><i class='fa fa-file-pdf-o'></i></a></div>"
                    }
                    return html;
                }
            }
        ],
        "language": {
            "url": "{{ asset('Spanish.json') }}"
        },
        "order": [
            [0, "desc"]
        ],
    });
    function reporte(id)
    {
        var url="{{route('Caja.reporte.movimiento',':id')}}"
        window.location.href=url.replace(":id", id)
    }
    function cerrarCaja(id) {
        axios.get("{{ route('Caja.datos.cierre') }}", {
            params: {
                id: id
            }
        }).then((value) => {
            var datos = value.data;
            $("#modal_cerrar_caja #movimiento_id").val(id);
            $("#modal_cerrar_caja #caja").val(datos.caja);
            $("#modal_cerrar_caja #colaborador").val(datos.colaborador);
            $("#modal_cerrar_caja #monto_inicial").val(datos.monto_inicial);
            $("#modal_cerrar_caja #ingreso").val(datos.ingresos);
            $("#modal_cerrar_caja #egreso").val(datos.egresos);
            $("#modal_cerrar_caja #saldo").val(datos.saldo);
            $("#modal_cerrar_caja").modal("show");
        }).catch((value) => {})
    }
    $(".btn-modal").click(function(e) {
        e.preventDefault();
        $("#modal_crear_caja").modal("show");
    });
</script>
@endpush
