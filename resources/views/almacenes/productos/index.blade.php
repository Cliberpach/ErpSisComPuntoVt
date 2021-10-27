@extends('layout') @section('content')
@section('almacenes-active', 'active')
@section('producto-active', 'active')
@include('almacenes.productos.modalfile')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-10">
        <h2 style="text-transform:uppercase"><b>Listado de Productos Terminados</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Productos</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2 col-md-2">
        <button id="btn_añadir_producto" class="btn btn-block btn-w-m btn-primary m-t-md">
            <i class="fa fa-plus-square"></i> Añadir nuevo
        </button>
        <a class="btn btn-block btn-w-m btn-primary m-t-md btn-modal-file" href="#">
            <i class="fa fa-plus-square"></i> Importar Excel
        </a>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table dataTables-producto table-striped table-bordered table-hover"
                            style="text-transform:uppercase">
                            <thead>
                                <tr>
                                    <th class="text-center">CÓDIGO</th>
                                    <th class="text-center">CÓDIGO BARRA</th>
                                    <th class="text-center">NOMBRE</th>
                                    <th class="text-center">ALMACEN</th>
                                    <th class="text-center">MARCA</th>
                                    <th class="text-center">CATEGORIA</th>
                                    <th class="text-center">STOCK</th>
                                    <th class="text-center">P.V. MIN</th>
                                    <th class="text-center">P.V. MAX</th>
                                    <th class="text-center">ACCIONES</th>
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

@include('almacenes.productos.modalIngreso')

@stop
@push('styles')
<!-- DataTable -->
<link href="{{ asset('Inspinia/css/plugins/dataTables/datatables.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<!-- DataTable -->
<script src="{{ asset('Inspinia/js/plugins/dataTables/datatables.min.js') }}"></script>
<script src="{{ asset('Inspinia/js/plugins/dataTables/dataTables.bootstrap4.min.js') }}"></script>

<script>
    $(document).ready(function() {

        // DataTables
        $('.dataTables-producto').DataTable({
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
            "ajax": "{{ route('almacenes.producto.getTable') }}",
            "columns": [{
                    data: 'codigo',
                    className: "text-left"
                },
                {
                    data: 'codigo_barra',
                    className: "text-left"
                },
                {
                    data: 'nombre',
                    className: "text-left"
                },
                {
                    data: 'almacen',
                    className: "text-left"
                },
                {
                    data: 'marca',
                    className: "text-left"
                },
                {
                    data: 'categoria',
                    className: "text-left"
                },
                {
                    data: 'stock',
                    className: "text-center"
                },
                {
                    data: 'precio_venta_minimo',
                    className: "text-center"
                },
                {
                    data: 'precio_venta_maximo',
                    className: "text-center"
                },
                {
                    data: null,
                    className: "text-center",
                    render: function(data) {
                        //Ruta Detalle
                        var url_detalle = '{{ route('almacenes.producto.show', ':id') }}';
                        url_detalle = url_detalle.replace(':id', data.id);

                        //Ruta Modificar
                        var url_editar = '{{ route('almacenes.producto.edit', ':id') }}';
                        url_editar = url_editar.replace(':id', data.id);

                        return "<div class='btn-group' style='text-transform:capitalize;'><button data-toggle='dropdown' class='btn btn-primary btn-sm  dropdown-toggle'><i class='fa fa-bars'></i></button><ul class='dropdown-menu'>" +

                            "<li><a class='dropdown-item' href='" + url_detalle +"' title='Detalle'><i class='fa fa-eye'></i> Ver</a></b></li>" +
                            "<li><a class='dropdown-item modificarDetalle' href='" + url_editar + "' title='Modificar'><i class='fa fa-edit'></i> Editar</a></b></li>" +
                            "<li><a class='dropdown-item' href='#' onclick='eliminar(" + data.id + ")' title='Eliminar'><i class='fa fa-trash'></i> Eliminar</a></b></li>" +
                            "<li class='dropdown-divider'></li>" +

                            "<li><a class='dropdown-item nuevo-ingreso' href='#' title='Ingreso'><i class='fa fa-save'></i> Ingreso</a></b></li>" +

                        "</ul></div>";
                    }
                }

            ],
            "language": {
                "url": "{{ asset('Spanish.json') }}"
            },
            "order": [],
        });

        // Eventos
        $('#btn_añadir_producto').on('click', añadirProducto);
    });

    $(".dataTables-producto").on('click','.nuevo-ingreso',function(){
        var data = $(".dataTables-producto").dataTable().fnGetData($(this).closest('tr'));

        $('#modal_ingreso').modal('show');
        $('#cantidad_fast').val('');
        $('#producto_id_fast').val(data.id);
        setTimeout(function() { $('#cantidad_fast').focus() }, 10);

    });




    //Controlar Error
    $.fn.DataTable.ext.errMode = 'throw';

    //Modal Eliminar
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger',
        },
        buttonsStyling: false
    });

    // Funciones de Eventos
    function añadirProducto() {
        window.location = "{{ route('almacenes.producto.create') }}";
    }

    function editarCliente(url) {
        window.location = url;
    }

    function eliminar(id) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger',
            },
            buttonsStyling: false
        })
        Swal.fire({
            title: 'Opción Eliminar',
            text: "¿Seguro que desea guardar cambios?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: "#1ab394",
            confirmButtonText: 'Si, Confirmar',
            cancelButtonText: "No, Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                //Ruta Eliminar
                var url_eliminar = '{{ route('almacenes.producto.destroy', ':id') }}';
                url_eliminar = url_eliminar.replace(':id', id);
                $(location).attr('href', url_eliminar);

            } else if (result.dismiss === Swal.DismissReason.cancel) {
                swalWithBootstrapButtons.fire(
                    'Cancelado',
                    'La Solicitud se ha cancelado.',
                    'error'
                )
            }
        })
    }
    $(".btn-modal-file").on('click', function() {
        $("#modal_file").modal("show");
    });
</script>
@endpush
