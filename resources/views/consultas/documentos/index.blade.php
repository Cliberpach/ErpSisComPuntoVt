@extends('layout') @section('content')

@section('consulta-active', 'active')
@section('consulta-comprobantes-active', 'active')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-10">
        <h2 style="text-transform:uppercase"><b>Listado de Documentos</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Documentos</strong>
            </li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
            <div class="row align-items-end">
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label for="tipo_id">Documento</label>
                        <select name="tipo_id" id="tipo_id" class="select2_form form-control">
                            <option value=""></option>
                            @foreach(comprobantes_empresa() as $doc)
                                <option value="{{$doc->tipo_comprobante}}">{{ $doc->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label for="fecha_desde">Fecha desde</label>
                        <input type="date" id="fecha_desde" class="form-control">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label for="fecha_desde">Fecha hasta</label>
                        <input type="date" id="fecha_hasta" class="form-control">
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <button class="btn btn-primary btn-block" onclick="initTable()"><i class="fa fa-refresh"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table dataTables-documento table-striped table-bordered table-hover"
                            style="text-transform:uppercase">
                            <thead>
                                <tr>
                                    <th class="text-center">TIPO</th>
                                    <th class="text-center"># DOC</th>
                                    <th class="text-center">FECHA</th>
                                    <th class="text-center">MONTO</th>
                                    <th class="text-center">ESTADO</th>
                                    <th class="text-center">VISTA</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@stop
@push('styles')
<link href="{{ asset('Inspinia/css/plugins/select2/select2.min.css') }}" rel="stylesheet">
<!-- DataTable -->
<link href="{{ asset('Inspinia/css/plugins/dataTables/datatables.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('Inspinia/js/plugins/select2/select2.full.min.js') }}"></script>
<!-- DataTable -->
<script src="{{ asset('Inspinia/js/plugins/dataTables/datatables.min.js') }}"></script>
<script src="{{ asset('Inspinia/js/plugins/dataTables/dataTables.bootstrap4.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $(".select2_form").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            height: '200px',
            width: '100%',
        });

        var documentos = [];
        // DataTables
        //initTable();

        $('.dataTables-documento').DataTable({
            "language": {
                        "url": "{{asset('Spanish.json')}}"
            },
        });

    });

    function initTable()
    {
        let verificar = true;
        var fecha_desde = $('#fecha_desde').val();
        var fecha_hasta = $('#fecha_hasta').val();
        var tipo = $('#tipo_id').val();

        if (tipo == '') {
            verificar = false;
            toastr.error('Ingresar documento');
        }

        if (fecha_desde == '' && fecha_desde == null && fecha_hasta == '') {
            verificar = false;
            toastr.error('Ingresar fecha hasta');
        }

        if (fecha_hasta == '' && fecha_hasta == null && fecha_desde == '') {
            verificar = false;
            toastr.error('Ingresar fecha desde');
        }

        if (fecha_desde > fecha_hasta && fecha_hasta == '' && fecha_desde == '') {
            verificar = false;
            toastr.error('Fecha desde debe ser menor que fecha hasta');
        }
        console.log(tipo)

        if(verificar)
        {
            let timerInterval;
            Swal.fire({
                title: 'Cargando...',
                icon: 'info',
                customClass: {
                    container: 'my-swal'
                },
                timer: 10,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    Swal.stopTimer();
                    $.ajax({
                        dataType : 'json',
                        type : 'post',
                        url : '{{ route('consultas.documento.getTable') }}',
                        data : {'_token' : $('input[name=_token]').val(), 'fecha_desde' : fecha_desde, 'fecha_hasta' : fecha_hasta, 'tipo' : tipo},
                        success: function(response) {
                            if (response.success) {
                                documentos = [];
                                documentos = response.documentos;
                                console.log(documentos)
                                loadTable();
                                timerInterval = 0;
                                Swal.resumeTimer();
                                //console.log(colaboradores);
                            } else {
                                Swal.resumeTimer();
                                documentos = [];
                                loadTable();
                            }
                        }
                    });
                },
                willClose: () => {
                    clearInterval(timerInterval)
                }
            });
        }
        return false;
    }

    function loadTable()
    {
        $('.dataTables-documento').dataTable().fnDestroy();
        $('.dataTables-documento').DataTable({
            "dom": '<"html5buttons"B>lTfgitp',
            "buttons": [{
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Excel',
                    title: 'CONSULTA COTIZACION'
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
            "data": documentos,
            "columns": [
                {
                    data: 'tipo_doc'
                },
                {
                    data: 'numero',
                    className: "text-center",
                },
                {
                    data: 'fecha',
                    className: "text-center",
                },
                {
                    data: 'total',
                    className: "text-center",
                },
                {
                    data: null,
                    className: "text-center",
                    render: function(data) {
                        switch (data.estado) {
                            case "PENDIENTE":
                                return "<span class='badge badge-warning' d-block>" + data
                                    .estado +
                                    "</span>";
                                break;
                            case "VENCIDA":
                                return "<span class='badge badge-danger' d-block>" + data
                                    .estado +
                                    "</span>";
                                break;
                            case "ATENDIDA":
                                return "<span class='badge badge-success' d-block>" + data
                                    .estado +
                                    "</span>";
                                break;
                            default:
                                return "<span class='badge badge-success' d-block>" + data
                                    .estado +
                                    "</span>";
                        }
                    },
                },
                {
                    data: null,
                    className: "text-center",
                    render: function(data) {
                        //Ruta Detalle
                        var url_venta = '{{ route("ventas.documento.comprobante", ":id") }}';
                        url_venta = url_venta.replace(':id', data.id+'-100');
                        let url_nota = '{{ route("ventas.notas.show", ":id")}}';
                        url_nota = url_nota.replace(':id', data.id);

                        let cadena = "";

                        if(data.tipo < 130)
                        {
                            cadena =  cadena + "<a class='btn btn-sm btn-info' href='"+ url_venta +"' target='_blank' title='Vista'><i class='fa fa-eye'></i> </a>";
                        }

                        if(data.tipo == 130)
                        {
                            cadena =  cadena + "<a class='btn btn-sm btn-info' href='"+ url_nota +"' target='_blank' title='Vista'><i class='fa fa-eye'></i> </a>";
                        }

                        return cadena;
                    },
                },

            ],
            "language": {
                        "url": "{{asset('Spanish.json')}}"
            },
            "order": [[ 0, "desc" ]],
        });
        return false;
    }
</script>
@endpush
