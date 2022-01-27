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
                <div class="ibox-title">
                    <h5>Productos</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content" id="div_productos">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-1">
                            <div class="form-group">
                                <a href="{{ route('almacenes.producto.getExcel') }}" class="btn btn-primary btn-block"><i class="fa fa-file-excel-o"></i></a>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table dataTables-producto table-striped table-bordered table-hover"
                                    style="text-transform:uppercase" id="table_productos">
                                    <thead>
                                        <tr>
                                            <th class="text-center">CÓDIGO</th>
                                            <th class="text-center">CÓDIGO BARRA</th>
                                            <th class="text-center">NOMBRE</th>
                                            <th class="text-center">ALMACEN</th>
                                            <th class="text-center">MARCA</th>
                                            <th class="text-center">CATEGORIA</th>
                                            <th class="text-center">STOCK</th>
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
    </div>
</div>

@include('almacenes.productos.modalIngreso')

@stop
@push('styles')
<!-- DataTable -->
<link href="{{ asset('Inspinia/css/plugins/dataTables/datatables.min.css') }}" rel="stylesheet">
<style>


</style>
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
            "buttons": [
                {
                    extend:    'excelHtml5',
                    text:      '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Excel',
                    title: 'PRODUCTOS'
                },

                {
                    titleAttr: 'Imprimir',
                    extend: 'print',
                    text:      '<i class="fa fa-print"></i> Imprimir',
                    customize: function (win){
                        $(win.document.body).addClass('white-bg');
                        $(win.document.body).css('font-size', '10px');

                        $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                    }
                }
            ],
            "bPaginate": true,
            "serverSide":true,
            "processing":true,
            "bLengthChange": true,
            "bFilter": true,
            "order": [],
            "bInfo": true,
            'bAutoWidth': false,
            "ajax": "{{ route('almacenes.producto.getTable') }}",
            "columns": [{
                    data: 'codigo',
                    className: "text-left",
                    name:"productos.codigo"
                },
                {
                    data: 'codigo_barra',
                    className: "text-left",
                    name:"productos.codigo_barra"
                },
                {
                    data: 'nombre',
                    className: "text-left",
                    name:"productos.nombre"
                },
                {
                    data: 'almacen',
                    className: "text-left",
                    name:"almacenes.descripcion"
                },
                {
                    data: 'marca',
                    className: "text-left",
                    name:"marcas.marca"
                },
                {
                    data: 'categoria',
                    className: "text-left",
                    name:"categorias.descripcion"
                },
                {
                    data: 'stock',
                    className: "text-center",
                    name:"productos.stock"
                },
                {
                    data: null,
                    defaultContent: "",
                    searchable: false,
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
            createdRow: function(row, data, dataIndex, cells) {
                $(row).addClass('fila_lote');
                $(row).attr('data-href', "");
            },
        });

        $('buttons-html5').removeClass('.btn-default');
        $('#table_productos_wrapper').removeClass('');
        $('.dataTables-productos tbody').on( 'click', 'tr', function () {
                $('.dataTables-productos').DataTable().$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
        } );

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
