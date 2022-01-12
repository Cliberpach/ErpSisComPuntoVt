@extends('layout') @section('content')

@section('ventas-active', 'active')
@section('documento-active', 'active')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-10">
       <h2  style="text-transform:uppercase"><b>REGISTRAR NUEVA NOTA DE @if(isset($nota_venta)) DEVOLUCIÓN @else CRÉDITO @endif</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('home')}}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('ventas.documento.index')}}">Documentos</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Nota de @if(isset($nota_venta)) devoluciÓn @else crédito @endif</strong>
            </li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">

                <div class="ibox-content">

                    <form id="enviar_documento">
                        @csrf
                        <input type="hidden" name="documento_id" value="{{old('documento_id', $documento->id)}}">
                        <input type="hidden" name="tipo_nota" value="{{ $tipo_nota }}">
                        <input type="hidden" name="productos_tabla" id="productos_tabla">
                        @if(isset($nota_venta))
                        <input type="hidden" name="nota_venta" id="nota_venta" value="1">
                        @endif
                        <div class="row">
                            <div class="col-12 col-md-5 b-r">
                                <div class="row">
                                    <div class="col-12">
                                        <p style="text-transform:uppercase"><strong><i class="fa fa-caret-right"></i> Información de nota de @if(isset($nota_venta)) devolución @else crédito @endif</strong></p>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12 col-md-5">
                                        <label class="required">Tipo Nota de @if(isset($nota_venta)) Devolución @else Crédito @endif</label>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        @if(isset($nota_venta))
                                        <select name="cod_motivo" id="cod_motivo" class="select2_form form-control" onchange="changeTipoNota(this)" disabled>
                                            <option value=""></option>
                                            @foreach(cod_motivos() as $item)
                                                <option value="{{ $item->simbolo }}" {{$item->simbolo === '07' ? 'selected' : ''}}>{{ $item->descripcion }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="cod_motivo" id="cod_motivo" value="07">
                                        @else
                                        <select name="cod_motivo" id="cod_motivo" class="select2_form form-control" onchange="changeTipoNota(this)" required>
                                            <option value=""></option>
                                            @foreach(cod_motivos() as $item)
                                                <option value="{{ $item->simbolo }}">{{ $item->descripcion }}</option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12 col-md-5">
                                        <label class="required">Motivo</label>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <textarea name="des_motivo" id="des_motivo" rows="2" class="form-control" required></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-7">
                                <div class="row">
                                    <div class="col-12">
                                        <p style="text-transform:uppercase"><strong><i class="fa fa-caret-right"></i> Información de cliente</strong></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-3">
                                        <div class="form-group row">
                                            <div class="col-12 col-md-5">
                                                <label class="required">Cliente ID</label>
                                            </div>
                                            <div class="col-12 col-md-7">
                                                <input type="text" class="form-control" value="{{ $documento->clienteEntidad->id }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-9">
                                        <div class=" form-group row">
                                            <div class="col-12 col-md-5">
                                                <label class="required">Tipo Doc. / Nro. Doc</label>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <input type="text" class="form-control" value="{{ $documento->clienteEntidad->tipo_documento }}" readonly>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <input type="text" class="form-control" value="{{ $documento->clienteEntidad->documento }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <div class="col-12 col-md-4">
                                                <label class="required">Nombre / Razón Social</label>
                                            </div>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control" name="cliente" id="cliente" value="{{ $documento->clienteEntidad->nombre }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group row">
                                    <div class="col-12 col-md-6">
                                        <label class="required">{{ $documento->tipo_documento_cliente }}</label>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input type="text" class="form-control" name="documento_cliente" value="{{ $documento->documento_cliente }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row d-none">
                                    <div class="col-12 col-md-6">
                                        <label class="required">Serie Nota</label>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <input type="text" class="form-control" name="serie_nota" value="" readonly>
                                    </div>
                                </div>
                                <div class="form-group row d-none">
                                    <div class="col-12 col-md-6">
                                        <label class="required">Nro. Nota</label>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <input type="text" class="form-control" name="numero_nota" value="" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12 col-md-6">
                                        <label class="required">Emisión de Nota</label>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input type="date" class="form-control" name="fecha_emision" value="{{ $fecha_hoy }}" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12 col-md-6">
                                        <label class="required">Fecha Documento</label>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input type="date" class="form-control" name="fecha_documento" value="{{ $documento->fecha_documento }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group row">
                                    <div class="col-12 col-md-6">
                                        <label class="required">Serie doc. afectado</label>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input type="text" class="form-control" name="serie_doc" value="{{ $documento->serie }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12 col-md-6">
                                        <label class="required">Nro. doc. afectado</label>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input type="text" class="form-control" name="numero_doc" value="{{ $documento->correlativo }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12 col-md-6">
                                        <label class="required">Tipo Pago</label>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input type="text" class="form-control text-uppercase" name="tipo_pago" value="{{ $documento->formaPago() }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group row @if($documento->tipo_venta == '129') d-none @endif">
                                    <div class="col-12 col-md-6">
                                        <label class="required">Sub Total</label>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input type="text" class="form-control" name="sub_total" id="sub_total" value="{{ $documento->sub_total }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row @if($documento->tipo_venta == '129') d-none @endif">
                                    <div class="col-12 col-md-6">
                                        <label class="required">IGV {{$documento->igv }}%</label>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input type="text" class="form-control" name="total_igv" id="total_igv" value="{{ $documento->total_igv }}" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12 col-md-6">
                                        <label class="required">Total</label>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input type="text" class="form-control" name="total" id="total" value="{{ $documento->total }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="panel panel-primary" id="panel_detalle">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <div class="col-10">
                                                <h4><b>Detalles de la nota de @if(isset($nota_venta)) devolución @else crédito @endif</b></h4>
                                            </div>
                                            <div class="col-2 text-right">
                                                <button type="button" class="ladda-button ladda-button-demo btn btn-secondary btn-sm" onclick="actualizarData({{ $documento->id }})" data-style="zoom-in"><i class="fa fa-refresh"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-body ibox-content">
                                        <div class="sk-spinner sk-spinner-wave">
                                            <div class="sk-rect1"></div>
                                            <div class="sk-rect2"></div>
                                            <div class="sk-rect3"></div>
                                            <div class="sk-rect4"></div>
                                            <div class="sk-rect5"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="table-responsive">
                                                    <table id="tbl-detalles" class="table table-hover tbl-detalles" style="width: 100%; text-transform:uppercase;">
                                                        <thead>
                                                            <th></th>
                                                            <th>Cant.</th>
                                                            <th>Descripcion</th>
                                                            <th>P. Unit</th>
                                                            <th>Total</th>
                                                            <th>Opciones</th>
                                                            <th></th>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="col-12 col-md-8"></div>
                                                    <div class="col-12 col-md-4">
                                                        <div class="form-group row @if($documento->tipo_venta == '129') d-none @endif">
                                                            <div class="col-12 col-md-6">
                                                                <label class="required">Sub Total</label>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <input type="text" class="form-control" name="sub_total_nuevo" id="sub_total_nuevo" value="" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row @if($documento->tipo_venta == '129') d-none @endif">
                                                            <div class="col-12 col-md-6">
                                                                <label class="required">IGV {{$documento->igv }}%</label>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <input type="text" class="form-control" name="total_igv_nuevo" id="total_igv_nuevo" value="" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-12 col-md-6">
                                                                <label class="required">Total</label>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <input type="text" class="form-control" name="total_nuevo" id="total_nuevo" value="" readonly>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group row">
                            <div class="col-md-6 text-left" style="color:#fcbc6c">
                                <i class="fa fa-exclamation-circle"></i> <small>Los campos marcados con asterisco
                                    (<label class="required"></label>) son obligatorios.</small>
                            </div>

                            <div class="col-md-6 text-right">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        @if(isset($nota_venta))
                                        <a href="{{route('ventas.notas_dev',$documento->id)}}" id="btn_cancelar"
                                            class="btn btn-w-m btn-block btn-default">
                                            <i class="fa fa-arrow-left"></i> Regresar
                                        </a>
                                        @else
                                        <a href="{{route('ventas.notas',$documento->id)}}" id="btn_cancelar"
                                            class="btn btn-w-m btn-block btn-default">
                                            <i class="fa fa-arrow-left"></i> Regresar
                                        </a>
                                        @endif
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <button type="submit" class="btn btn-w-m btn-block btn-primary">
                                            <i class="fa fa-save"></i> Grabar
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </form>

                </div>


            </div>
        </div>

    </div>

</div>
@include('ventas.notas.credito.modal')

@stop
@push('styles')
<link href="{{ asset('Inspinia/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') }}" rel="stylesheet">
<link href="{{ asset('Inspinia/css/plugins/datapicker/datepicker3.css') }}" rel="stylesheet">
<link href="{{ asset('Inspinia/css/plugins/daterangepicker/daterangepicker-bs3.css') }}" rel="stylesheet">
<link href="{{ asset('Inspinia/css/plugins/select2/select2.min.css') }}" rel="stylesheet">
<!-- DataTable -->
<link href="{{asset('Inspinia/css/plugins/dataTables/datatables.min.css')}}" rel="stylesheet">
<!-- Ladda style -->
<link href="{{asset('Inspinia/css/plugins/ladda/ladda-themeless.min.css')}}" rel="stylesheet">

@endpush

@push('scripts')
<!-- Data picker -->
<script src="{{ asset('Inspinia/js/plugins/datapicker/bootstrap-datepicker.js') }}"></script>
<!-- Date range use moment.js same as full calendar plugin -->
<script src="{{ asset('Inspinia/js/plugins/fullcalendar/moment.min.js') }}"></script>
<!-- Date range picker -->
<script src="{{ asset('Inspinia/js/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- Select2 -->
<script src="{{ asset('Inspinia/js/plugins/select2/select2.full.min.js') }}"></script>

<!-- DataTable -->
<script src="{{asset('Inspinia/js/plugins/dataTables/datatables.min.js')}}"></script>
<script src="{{asset('Inspinia/js/plugins/dataTables/dataTables.bootstrap4.min.js')}}"></script>

 <!-- Ladda -->
 <script src="{{ asset('Inspinia/js/plugins/ladda/spin.min.js') }}"></script>
 <script src="{{ asset('Inspinia/js/plugins/ladda/ladda.min.js') }}"></script>
 <script src="{{ asset('Inspinia/js/plugins/ladda/ladda.jquery.min.js') }}"></script>

<script>

    $(document).ready(function() {

        $(".select2_form").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            height: '200px',
            width: '100%',
        });

        actualizarData('{{ $documento->id }}')
        viewData();

        $('#cantidad_devolver').on('input', function() {
            let max = convertFloat(this.max);
            let valor = convertFloat(this.value);
            if (valor > max) {
                toastr.error('La cantidad ingresada supera al stock del producto Max(' + max + ').', 'Error');
                this.value = max;
            }
        });

    });

    function changeTipoNota(b)
    {
        if(b.value != '')
        {
            if(b.value == '01')
            {
                actualizarData('{{ $documento->id }}')
            }
            else
            {
                actualizarData('{{ $documento->id }}')
            }
        }
    }

    function limpiarForm()
    {
        $("#cantidad_devolver").attr('readonly');
        $("#cantidad_devolver").val('');
        $("#descripcion").attr('readonly');
        $("#descripcion").val('');
        $("#precio_unitario").attr('readonly');
        $("#precio_unitario").val('');
        $("#descuento_dev").attr('readonly');
        $("#descuento_dev").val('');
        $("#monto_igv").attr('readonly');
        $("#monto_igv").val('');
        $("#importe_venta").attr('readonly');
        $("#importe_venta").val('');

    }

    function viewData() {
        $("#tbl-detalles").on('click', '#editar', function() {

            let cod_motivo = $('#cod_motivo').val();

            if(cod_motivo != '')
            {
                var data = $(".tbl-detalles").dataTable().fnGetData($(this).closest('tr'));
                let table = $('#tbl-detalles').DataTable();
                let index = table.row($(this).parents('tr')).index();
                let igv = convertFloat('{{ $documento->igv }}')
                let total_igv = data[3] - (data[3] / (1 + (igv/100)));

                limpiarForm();

                $('#indice').val(index);
                $('#cantidad_devolver').val(data[1]);
                $('#descripcion').val(data[2]);
                $('#precio_unitario').val(data[3]);
                $('#monto_igv').val(total_igv);
                $('#importe_venta').val(data[4]);

                $("#cantidad_devolver").attr({
                        "max": data[1],
                        "min": 1,
                    });

                if(cod_motivo != '01')
                {
                    console.log('o1')
                    $("#cantidad_devolver").removeAttr('readonly');
                    $("#precio_unitario").removeAttr('readonly');
                }
                $('#modal_editar_detalle').modal('show');
            }
            else
            {
                toastr.error('Seleccionar tipo de nota de crédito','Error')
            }
        });
    }

    function cargarProductos() {
        var productos = [];
        var table = $('.tbl-detalles').DataTable();
        var data = table.rows().data();
        data.each(function(value, index) {
            let fila = {
                id: value[0],
                cantidad: value[1],
                precio_unitario: value[3],
                editable: value[6],
            };
            productos.push(fila);
        });

        $('#productos_tabla').val(JSON.stringify(productos));
    }

    function actualizarData(id) {
        $('#panel_detalle').children('.ibox-content').toggleClass('sk-loading');
        let url = '{{ route("ventas.getDetalles",":id") }}';
        url = url.replace(':id',id);

        var l = $( '.ladda-button-demo' ).ladda();
        l.ladda( 'start' );

        dibujarTabla();
        var t = $('.tbl-detalles').DataTable();
        t.clear().draw();
        $.ajax({
            dataType: 'json',
            type: 'get',
            url: url,
        }).done(function(result) {
            let detalles = result.detalles;
            for(let i = 0; i < detalles.length; i++)
            {
                agregarTabla(detalles[i]);
            }
            sumaTotal();
            l.ladda('stop');
            $('#panel_detalle').children('.ibox-content').toggleClass('sk-loading');
        });
    }

    function prueba()
    {
        var t = $('.tbl-detalles').DataTable();
        t.rows().data().each(function(el, index) {
            console.log(el);
        })
    }

    function sumaTotal()
    {
        let t = $('.tbl-detalles').DataTable();
        let total = 0;
        let detalles = [];
        t.rows().data().each(function(el, index) {
            let id = el[0];
            let cantidad = el[1];
            let descripcion = el[2];
            let precio_unitario = el[3];
            let importe_venta = el[1] * el[3];
            let editable = el[6];

            let detalle = {
                id: id,
                cantidad: cantidad,
                descripcion: descripcion,
                precio_unitario: precio_unitario,
                importe_venta: importe_venta,
                editable: editable,
            }

            detalles.push(detalle);
        });

        t.clear().draw();
        if(detalles.length> 0)
        {
            for(let i = 0; i < detalles.length; i++) {
                agregarTabla(detalles[i]);
            }
        }

        t.rows().data().each(function(el, index) {
            if(el[6] == 1 && $('#cod_motivo').val() != '01')
            {
                total = Number(el[4]) + total
            }

            if($('#cod_motivo').val() === '01')
            {
                total = Number(el[4]) + total
            }
        });

        conIgv(convertFloat(total),convertFloat(18))
    }

    function conIgv(total, igv) {
        let subtotal = total / (1 + (igv / 100));
        let igv_calculado = total - subtotal;
        $('#sub_total_nuevo').val((Math.round(subtotal * 10) / 10).toFixed(2));
        $('#total_igv_nuevo').val((Math.round(igv_calculado * 10) / 10).toFixed(2));
        $('#total_nuevo').val((Math.round(total * 10) / 10).toFixed(2));
        //Math.round(fDescuento * 10) / 10
    }

    //AGREGAR EL DETALLE A LA TABLA
    function agregarTabla($detalle) {
        var t = $('.tbl-detalles').DataTable();
        t.row.add([
            $detalle.id,
            Number($detalle.cantidad).toFixed(2),
            $detalle.descripcion,
            Number($detalle.precio_unitario).toFixed(2),
            Number($detalle.importe_venta).toFixed(2),
            '',
            $detalle.editable,
        ]).draw(false);
        //cargarProductos()
    }

    function dibujarTabla()
    {
        $('#tbl-detalles').dataTable().fnDestroy();
        $('#tbl-detalles').DataTable({
            "ordering" : false,
            "bPaginate": false,
            "bLengthChange": false,
            "bFilter": false,
            "bInfo": false,
            "bAutoWidth": false,
            "columnDefs": [{
                    "targets": [0],
                    "visible": false,
                    "searchable": false
                },
                {
                    "targets": [1],
                },
                {
                    "targets": [2],
                },
                {
                    "targets": [3],
                },
                {
                    "targets": [4],
                },
                {
                    "targets": [5],
                    data: null,
                    defaultContent: '<button id="editar" type="button" class="btn btn-sm btn-info btn-rounded">' +
                        '<span class="glyphicon glyphicon-pencil" > </span>' +
                        '</button>',
                    visible: $('#cod_motivo').val() === '01' ? false : true
                },
                {
                    "targets": [6],
                },
            ],
            'bAutoWidth': false,
            'aoColumns': [{
                    sWidth: '0%'
                },
                {
                    sWidth: '15%',
                    sClass: 'cantidad'
                },
                {
                    sWidth: '40%',
                    sClass: 'descripcion'
                },
                {
                    sWidth: '15%',
                    sClass: 'precio_unitario'
                },
                {
                    sWidth: '15%',
                    sClass: 'importe_venta'
                },
                {
                    sWidth: '15%',
                    sClass: 'text-center'
                },
                {
                    sWidth: '0%',
                    visible: false
                },
            ],
            "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                if (aData[6] == 1) {
                    $('td', nRow).css('background-color', '#D1F2EB');
                    $('td', nRow).css('color', '#2980B9');
                    $('td', nRow).css('font-weight', 'bold');
                }
            },
            "language": {
                "url": "/Spanish.json"
            },
            "order": [
                [1, 'asc']
            ],
        });
    }

    $('#enviar_documento').submit(function (e) {
        e.preventDefault();

        let enviar = true;
        let total =  convertFloat($('#total_nuevo').val());

        if(total <= 0)
        {
            enviar = false;
            toastr.error('El monto total de la Nota de Crédito debe ser mayor que 0.')
        }

        if(enviar)
        {
            cargarProductos();
            let formDocumento = document.getElementById('enviar_documento');
            let formData = new FormData(formDocumento);

            var object = {};
            formData.forEach(function(value, key){
                object[key] = value;
            });

            var datos = object;
            var init = {
                // el método de envío de la información será POST
                method: "POST",
                headers: { // cabeceras HTTP
                    // vamos a enviar los datos en formato JSON
                    'Content-Type': 'application/json'
                },
                // el cuerpo de la petición es una cadena de texto
                // con los datos en formato JSON
                body: JSON.stringify(datos) // convertimos el objeto a texto
            };

            var url = '{{ route("ventas.notas.store") }}';
            var textAlert = "¿Seguro que desea guardar cambios?";
            Swal.fire({
                title: 'Opción Guardar',
                text: textAlert,
                icon: 'question',
                customClass: {
                    container: 'my-swal'
                },
                showCancelButton: true,
                confirmButtonColor: "#1ab394",
                confirmButtonText: 'Si, Confirmar',
                cancelButtonText: "No, Cancelar",
                showLoaderOnConfirm: true,
                allowOutsideClick: false,
                preConfirm: (login) => {
                    return fetch(url,init)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText)
                            }
                            return response.json()
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                `Ocurrió un error`
                            );
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.value !== undefined && result.isConfirmed) {
                    if(result.value.errors)
                    {
                        let mensaje = sHtmlErrores(result.value.data.mensajes);
                        toastr.error(mensaje);
                    }
                    else if(result.value.success)
                    {
                        let id = result.value.nota_id;
                        @if(isset($nota_venta))
                        toastr.success('Nota de devolución creada!','Exito')
                        // let url_open_pdf = '{{ route("ventas.notas_dev.show", ":id")}}';
                        // url_open_pdf = url_open_pdf.replace(':id',id);
                        // window.open(url_open_pdf, "Comprobante SISCOM", "width=900, height=600");
                        @else
                        toastr.success('Nota de crédito creada!','Exito')
                        let url_open_pdf = '{{ route("ventas.notas.show", ":id")}}';
                        url_open_pdf = url_open_pdf.replace(':id',id);
                        window.open(url_open_pdf, "Comprobante SISCOM", "width=900, height=600");
                        @endif

                        let ruta = "{{route('ventas.notas', $documento->id)}}"
                        @if(isset($nota_venta))
                        ruta = "{{route('ventas.notas_dev', $documento->id)}}";
                        @endif

                        location = ruta;
                    }
                    else
                    {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: '¡'+ result.value.mensaje +'!',
                            customClass: {
                                container: 'my-swal'
                            },
                            showConfirmButton: false,
                            timer: 2500
                        });
                    }
                }
            });

        }
    });

</script>
@endpush
