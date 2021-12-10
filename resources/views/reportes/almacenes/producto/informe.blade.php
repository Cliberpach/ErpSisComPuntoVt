@extends('layout') @section('content')


<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-12 col-md-12">
       <h2  style="text-transform:uppercase"><b>Productos</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('home')}}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Informe de producto: compra y venta</strong>
            </li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight" id="div_productos">
    <div class="row">
        <div class="col-12 text-warning">
            <span><b>Instrucciones:</b> Doble click en el registro del producto a ver informacion.</span>
        </div>
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
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
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <h2>COMPRAS</h2>
        </div>
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table dataTables-compras table-striped table-bordered table-hover"
                            style="text-transform:uppercase">
                            <thead>
                                <tr>
                                    <th class="text-center">PROVEEDOR</th>
                                    <th class="text-center">DOCUMENTO</th>
                                    <th class="text-center">NUMERO</th>
                                    <th class="text-center">FECHA</th>
                                    <th class="text-center">CANTIDAD</th>
                                    <th class="text-center">PRECIO</th>
                                    <th class="text-center">LOTE</th>
                                    <th class="text-center">FECHA VENC.</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <h2>VENTAS</h2>
        </div>
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table dataTables-ventas table-striped table-bordered table-hover"
                            style="text-transform:uppercase">
                            <thead>
                                <tr>
                                    <th class="text-center">CLIENTE</th>
                                    <th class="text-center">DOCUMENTO</th>
                                    <th class="text-center">NUMERO</th>
                                    <th class="text-center">FECHA</th>
                                    <th class="text-center">CANTIDAD</th>
                                    <th class="text-center">PRECIO</th>
                                    <th class="text-center">LOTE</th>
                                    <th class="text-center">FECHA VENC.</th>
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
<!-- DataTable -->
<link href="{{asset('Inspinia/css/plugins/dataTables/datatables.min.css')}}" rel="stylesheet">
<style>

    @media (min-width: 992px){
        .modal-lg {
            max-width: 1200px;
        }
    }

    #table_productos div.dataTables_wrapper div.dataTables_filter{
        text-align: left !important;
    }
    #table_productos tr[data-href] {
        cursor: pointer;
    }
    #table_productos tbody .fila_lote.selected {
        /* color: #151515 !important;*/
        font-weight: 400;
        color: white !important;
        background-color: #18a689 !important;
        /* background-color: #CFCFCF !important; */
    }

    #div_productos  div.dataTables_wrapper div.dataTables_filter{
        text-align:left !important;
    }


    @media only screen and (max-width: 992px) {

        #table_tabla_registro_filter{
            text-align:left;
        }

        #table_productos_filter{
            text-align: left;
        }
        #table_productos  div.dataTables_wrapper div.dataTables_paginate ul.pagination {
            float:left;
            margin: 10px 0;
            white-space: nowrap;
        }

    }

    @media only screen and (min-width: 428px) and (max-width: 1190px) {
        /* Para tables: */
        #div_productos div.dataTables_filter input {
            width: 175% !important;
            display: inline-block !important;
        }
    }

    @media only screen and (max-width: 428px) {
        /* Para celular: */
        #div_productos  div.dataTables_filter input {
            width: 100% !important;
            display: inline-block !important;
        }
    }

    @media only screen and (min-width: 1190px) {

        #div_productos div.dataTables_filter input {
            width: 363% !important;
            display: inline-block !important;
        }
    }
</style>
@endpush

@push('scripts')
<!-- DataTable -->
<script src="{{asset('Inspinia/js/plugins/dataTables/datatables.min.js')}}"></script>
<script src="{{asset('Inspinia/js/plugins/dataTables/dataTables.bootstrap4.min.js')}}"></script>

<script>
    $(document).ready(function() {
        // DataTables
        var productos = [];
        $('.dataTables-compras').dataTable({
            "dom": '<"html5buttons"B>lTfgitp',
            "buttons": [{
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Excel',
                    title: 'CONSULTA COMPRAS'
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
            "language": {
                        "url": "{{asset('Spanish.json')}}"
            },
            "order": [[ 0, "desc" ]],
        });
        $('.dataTables-ventas').dataTable({
            "dom": '<"html5buttons"B>lTfgitp',
            "buttons": [{
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Excel',
                    title: 'CONSULTA VENTAS'
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
            "language": {
                        "url": "{{asset('Spanish.json')}}"
            },
            "order": [[ 0, "desc" ]],
        });

        loadTable();

        $('buttons-html5').removeClass('.btn-default');

        $('.dataTables-producto tbody').on( 'click', 'tr', function () {
                $('.dataTables-producto').DataTable().$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
        } );

        //DOBLE CLICK EN LOTES
        $ ('.dataTables-producto'). on ('dblclick', 'tbody td', function () {
            var lote =  $('.dataTables-producto').DataTable();
            var data = lote.row(this).data();
            llenarCompras(data.id);
            llenarVentas(data.id);
        });

    });

    function llenarCompras(id)
    {
        $('.dataTables-compras').dataTable().fnDestroy();
        let url = '{{ route("reporte.producto.llenarCompras", ":id")}}';
        url = url.replace(":id", id);
        $('.dataTables-compras').DataTable({
            "dom": '<"html5buttons"B>lTfgitp',
            "buttons": [{
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Excel',
                    title: 'CONSULTA COMPRAS'
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
            "ajax": url,
            "columns": [

                {data: 'proveedor', name:'proveedor', className: "letrapequeña"},
                {data: 'documento', name:'documento', className: "letrapequeña"},
                {data: 'numero',name:'numero', className: "letrapequeña"},
                {data: 'fecha_emision',name:'fecha_emision', className: "letrapequeña"},
                {data: 'cantidad',name:'cantidad', className: "letrapequeña"},
                {data: 'precio',name:'precio', className: "letrapequeña"},
                {data: 'lote',name:'lote', className: "letrapequeña"},
                {data: 'fecha_vencimiento',name:'fecha_vencimiento', className: "letrapequeña"}
            ],
            "language": {
                        "url": "{{asset('Spanish.json')}}"
            },
            "order": [[ 0, "desc" ]],


        });
    }

    function llenarVentas(id)
    {
        $('.dataTables-ventas').dataTable().fnDestroy();
        let url = '{{ route("reporte.producto.llenarVentas", ":id")}}';
        url = url.replace(":id", id);
        $('.dataTables-ventas').DataTable({
            "dom": '<"html5buttons"B>lTfgitp',
            "buttons": [{
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Excel',
                    title: 'CONSULTA VENTAS'
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
            "ajax": url,
            "columns": [

                {data: 'cliente', name:'cliente', className: "letrapequeña"},
                {data: 'documento', name:'documento', className: "letrapequeña"},
                {data: 'numero',name:'numero', className: "letrapequeña"},
                {data: 'fecha_emision',name:'fecha_emision', className: "letrapequeña"},
                {data: 'cantidad',name:'cantidad', className: "letrapequeña"},
                {data: 'precio',name:'precio', className: "letrapequeña"},
                {data: 'lote',name:'lote', className: "letrapequeña"},
                {data: 'fecha_vencimiento',name:'fecha_vencimiento', className: "letrapequeña"}
            ],
            "language": {
                        "url": "{{asset('Spanish.json')}}"
            },
            "order": [[ 0, "desc" ]],


        });
    }

    function loadTable()
    {
        $('.dataTables-producto').DataTable({
            "dom":
                "<'row'<'col-sm-12 col-md-12 col-lg-12'f>>" +
                "<'row'<'col-sm-12'tr>>"+
                "<'row justify-content-between'<'col information-content p-0'i><''p>>",
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
        return false;
    }
</script>
@endpush
