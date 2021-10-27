@extends('layout') @section('content')

@section('ventas-active', 'active')
@section('documentos-active', 'active')

<div class="row wrapper border-bottom white-bg page-heading">

    <div class="col-lg-12">
       <h2  style="text-transform:uppercase"><b>MODIFICAR DOCUMENTO DE VENTA # {{$documento->id}}</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('home')}}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('ventas.documento.index')}}">Documentos de Venta</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Modificar</strong>
            </li>

        </ol>
    </div>



</div>


<div class="wrapper wrapper-content animated fadeInRight">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">

                <div class="ibox-content">

                    <form action="{{route('ventas.documento.update', $documento->id)}}" method="POST" id="enviar_documento">
                        @csrf @method('PUT')

                        <div class="row">
                            <div class="col-sm-6 b-r">
                                <h4 class=""><b>Documento de venta</b></h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <p>Modificar datos del documento de venta:</p>
                                    </div>
                                </div>

                                <div class="form-group row">

                                    <div class="col-lg-6 col-xs-12" id="fecha_documento">
                                        <label class="required">Fecha de Documento</label>
                                        <div class="input-group date">
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                            <input type="text" id="fecha_documento_campo" name="fecha_documento_campo"
                                                class="form-control {{ $errors->has('fecha_documento') ? ' is-invalid' : '' }}"
                                                value="{{old('fecha_documento_campo', getFechaFormato($documento->fecha_documento, 'd/m/Y'))}}"
                                                autocomplete="off" readonly required>
                                            @if ($errors->has('fecha_documento'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('fecha_documento') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-xs-12" id="fecha_atencion">
                                        <label class="required">Fecha de Atención</label>
                                        <div class="input-group date">
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                            <input type="text" id="fecha_atencion_campo" name="fecha_atencion_campo"
                                                class="form-control {{ $errors->has('fecha_atencion_campo') ? ' is-invalid' : '' }}"
                                                value="{{old('fecha_atencion_campo', getFechaFormato($documento->fecha_atencion, 'd/m/Y'))}}"
                                                autocomplete="off" required readonly>
                                            @if ($errors->has('fecha_atencion_campo'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('fecha_atencion_campo') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                </div>


                                <div class="form-group row">

                                    <div class="col-md-6">
                                        <label class="required">Tipo: </label>
                                        <select
                                            class="select2_form form-control {{ $errors->has('tipo_venta') ? ' is-invalid' : '' }}"
                                            style="text-transform: uppercase; width:100%" value="{{old('tipo_compra')}}"
                                            name="tipo_venta" id="tipo_venta" required>
                                            <option></option>
                                            @foreach (tipos_venta() as $tipo)
                                            <option value="{{$tipo->descripcion}}" @if(old('tipo_venta',$documento->tipo_venta)==$tipo->descripcion ) {{'selected'}} @endif
                                                >{{$tipo->descripcion}}</option>
                                            @endforeach
                                            @if ($errors->has('tipo_venta'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('tipo_venta') }}</strong>
                                            </span>
                                            @endif
                                        </select>
                                    </div>


                                    <div class="col-md-6">
                                        <label >Moneda:</label>
                                        <select id="moneda" name="moneda" class="select2_form form-control {{ $errors->has('moneda') ? ' is-invalid' : '' }}" disabled >
                                            <option selected>SOLES</option>
                                        </select>
                                    </div>


                                </div>






                            </div>

                            <div class="col-sm-6">

                                <div class="form-group">
                                    <label class="required">Empresa: </label>
                                    <select
                                        class="select2_form form-control {{ $errors->has('empresa_id') ? ' is-invalid' : '' }}"
                                        style="text-transform: uppercase; width:100%" value="{{old('empresa_id', $documento->empresa_id)}}"
                                        name="empresa_id" id="empresa_id" required>
                                        <option></option>
                                        @foreach ($empresas as $empresa)
                                        <option value="{{$empresa->id}}" @if(old('empresa_id', $documento->empresa_id)
                                            == $empresa->id ) {{'selected'}} @endif >{{$empresa->razon_social}}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="form-group">
                                    <label class="required">Cliente: </label>
                                    <input type="hidden" name="tipo_cliente_documento" id="tipo_cliente_documento">
                                    <select
                                        class="select2_form form-control {{ $errors->has('cliente_id') ? ' is-invalid' : '' }}"
                                        style="text-transform: uppercase; width:100%" value="{{old('cliente_id', $documento->cliente_id)}}"
                                        name="cliente_id" id="cliente_id"  onchange="obtenerTipo(this)" required>
                                        <option></option>
                                        @foreach ($clientes as $cliente)
                                       
                                            <option value="{{$cliente->id}}" @if(old('cliente_id',$documento->cliente_id)  == $cliente->id ) {{'selected'}} @endif >{{$cliente->tipo_documento.': '.$cliente->documento.' - '.$cliente->nombre}}</option>
                                        
                                        @endforeach
                                    </select>
                                </div>



                                <div class="form-group">
                                    <label>Observación:</label>
                                    <textarea type="text" placeholder=""
                                        class="form-control {{ $errors->has('observacion') ? ' is-invalid' : '' }}"
                                        name="observacion" id="observacion"  onkeyup="return mayus(this)"
                                        value="{{old('observacion', $documento->observacion)}}">{{old('observacion',$documento->observacion)}}</textarea>
                                    @if ($errors->has('observacion'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('observacion') }}</strong>
                                    </span>
                                    @endif








                                </div>


                                <!-- OBTENER TIPO DE CLIENTE -->
                                <input type="hidden" name="" id="tipo_cliente" value="{{$documento->cliente->detalle->descripcion}}">
                                <!-- OBTENER DATOS DEL PRODUCTO -->
                                <input type="hidden" name="" id="presentacion_producto">
                                <input type="hidden" name="" id="codigo_nombre_producto">
                                <!-- LLENAR DATOS EN UN ARRAY -->
                                <input type="hidden" id="productos_tabla" name="productos_tabla[]">

                            </div>

                        </div>

                        <hr>

                        <div class="row">

                            <div class="col-lg-12">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <h4 class=""><b>Detalle de Documento de Venta</b></h4>
                                    </div>
                                    <div class="panel-body">


                                        <div class="row">

                                            <div class="col-md-6">
                                                <label class="required">Producto:</label>
                                                <select class="select2_form form-control"
                                                    style="text-transform: uppercase; width:100%" name="producto"
                                                    id="producto" onchange="obtenerMonto(this)">
                                                    <option></option>
                                                    @foreach ($productos as $producto)
                                                    <option value="{{$producto->id}}">{{$producto->codigo.' - '.$producto->nombre}}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback"><b><span id="error-producto"></span></b>
                                                </div>
                                            </div>

                                            <div class="col-lg-2 col-xs-12">

                                                <label class="col-form-label required">Cantidad:</label>
                                                <input type="text" id="cantidad" class="form-control">
                                                <div class="invalid-feedback"><b><span id="error-cantidad"></span></b>
                                                </div>
                                            </div>

                                            <div class="col-lg-2 col-xs-12">
                                                <div class="form-group">
                                                    <label class="col-form-label required" for="amount">Precio:</label>
                                                    <input type="text" id="precio" class="form-control">
                                                    <div class="invalid-feedback"><b><span id="error-precio"></span></b>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-2 col-xs-12">

                                                <div class="form-group">
                                                    <label class="col-form-label" for="amount">&nbsp;</label>
                                                    <a class="btn btn-block btn-warning" style='color:white;' id="btn_agregar_detalle"> <i class="fa fa-plus"></i> AGREGAR</a>
                                                </div>

                                            </div>


                                        </div>

                                        <hr>

                                        <div class="table-responsive">
                                            <table
                                                class="table dataTables-detalle-documento table-striped table-bordered table-hover"
                                                style="text-transform:uppercase">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th class="text-center">ACCIONES</th>
                                                        <th class="text-center">CANTIDAD</th>
                                                        <th class="text-center">UNIDAD DE MEDIDA</th>
                                                        <th class="text-center">DESCRIPCION DEL PRODUCTO</th>
                                                        <th class="text-center">PRECIO</th>
                                                        <th class="text-center">TOTAL</th>

                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                                <tfoot style="text-transform:uppercase">
                                                    <tr>
                                                        <th colspan="6" style="text-align:right">Sub Total:</th>
                                                        <th>S/. <span id="subtotal"></span></th>

                                                    </tr>
                                                    <tr>
                                                        <th colspan="6" class="text-center">IGV <span
                                                                id="igv_int"></span>:</th>
                                                        <th class="text-center">S/. <span id="igv_monto"></span></th>

                                                    </tr>
                                                    <tr>
                                                        <th colspan="6" class="text-center">TOTAL:</th>
                                                        <th class="text-center">S/. <span id="total"></span></th>

                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>






                                    </div>
                                </div>
                            </div>

                        </div>

                        <input type="hidden" name="monto_sub_total" id="monto_sub_total" value="{{ old('monto_sub_total') }}">
                        <input type="hidden" name="monto_total_igv" id="monto_total_igv" value="{{ old('monto_total_igv') }}">
                        <input type="hidden" name="monto_total" id="monto_total" value="{{ old('monto_total') }}">

                        <div class="hr-line-dashed"></div>
                        <div class="form-group row">

                            <div class="col-md-6 text-left" style="color:#fcbc6c">
                                <i class="fa fa-exclamation-circle"></i> <small>Los campos marcados con asterisco
                                    (<label class="required"></label>) son obligatorios.</small>
                            </div>

                            <div class="col-md-6 text-right">
                                <a href="{{route('compras.orden.index')}}" id="btn_cancelar"
                                    class="btn btn-w-m btn-default">
                                    <i class="fa fa-arrow-left"></i> Regresar
                                </a>
                                <button type="submit" id="btn_grabar" class="btn btn-w-m btn-primary">
                                    <i class="fa fa-save"></i> Grabar
                                </button>
                            </div>

                        </div>

                    </form>

                </div>


            </div>
        </div>

    </div>
</div>


@include('ventas.documentos.modal')

@stop

@push('styles')
<link href="{{ asset('Inspinia/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') }}"
    rel="stylesheet">
<link href="{{ asset('Inspinia/css/plugins/datapicker/datepicker3.css') }}" rel="stylesheet">
<link href="{{ asset('Inspinia/css/plugins/daterangepicker/daterangepicker-bs3.css') }}" rel="stylesheet">
<link href="{{ asset('Inspinia/css/plugins/select2/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('Inspinia/css/plugins/steps/jquery.steps.css') }}" rel="stylesheet">
<!-- DataTable -->
<link href="{{asset('Inspinia/css/plugins/dataTables/datatables.min.css')}}" rel="stylesheet">
<style>
.select2-container--open {
    z-index: 9999999
}
</style>
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
<!-- Chosen -->
<script src="{{asset('Inspinia/js/plugins/chosen/chosen.jquery.js')}}"></script>
<script>

    $('#cantidad').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

//IGV
$(document).ready(function() {
    if ($("#igv_check").prop('checked')) {
        $('#igv').attr('disabled', false)
        $('#igv_requerido').addClass("required")
    } else {
        $('#igv').attr('disabled', true)
        $('#igv_requerido').removeClass("required")
    }


})

$("#igv_check").click(function() {
    if ($("#igv_check").is(':checked')) {
        $('#igv').attr('disabled', false)
        $('#igv_requerido').addClass("required")
        $('#igv').prop('required', true)
        $('#igv').val('18')
        var igv = ($('#igv').val()) + ' %'
        $('#igv_int').text(igv)
        sumaTotal()

    } else {
        $('#igv').attr('disabled', true)
        $('#igv_requerido').removeClass("required")
        $('#igv').prop('required', false)
        $('#igv').val('')
        $('#igv_int').text('')
    }
});

$("#igv").on("change", function() {
    if ($("#igv_check").is(':checked')) {
        $('#igv').attr('disabled', false)
        $('#igv_requerido').addClass("required")
        $('#igv').prop('required', true)
        var igv = ($('#igv').val()) + ' %'
        $('#igv_int').text(igv)
        sumaTotal()

    } else {
        $('#igv').attr('disabled', true)
        $('#igv_requerido').removeClass("required")
        $('#igv').prop('required', false)
        $('#igv').val('')
        $('#igv_int').text('')
        sumaTotal()
    }
});

if ("{{$documento->igv}}") {
    $('#igv').attr('disabled', false)
        $('#igv_requerido').addClass("required")
        $('#igv').prop('required', true)
        $('#igv').val('18')
        var igv = ($('#igv').val()) + ' %'
        $('#igv_int').text(igv)
        // sumaTotal()
}else{
    $('#igv').attr('disabled', true)
    $('#igv_requerido').removeClass("required")
    $('#igv').prop('required', false)
    $('#igv').val('')
    $('#igv_int').text('')
}







//Select2
$(".select2_form").select2({
    placeholder: "SELECCIONAR",
    allowClear: true,
    height: '200px',
    width: '100%',
});

$('#fecha_documento .input-group.date').datepicker({
    todayBtn: "linked",
    keyboardNavigation: false,
    forceParse: false,
    autoclose: true,
    language: 'es',
    format: "dd/mm/yyyy"
});

$('#fecha_atencion .input-group.date').datepicker({
    todayBtn: "linked",
    keyboardNavigation: false,
    forceParse: false,
    autoclose: true,
    language: 'es',
    format: "dd/mm/yyyy",
})

// Solo campos numericos
$('#precio').keyup(function() {
    var val = $(this).val();
    if (isNaN(val)) {
        val = val.replace(/[^0-9\.]/g, '');
        if (val.split('.').length > 2)
            val = val.replace(/\.+$/, "");
    }
    $(this).val(val);
});


$('#cantidad_editar').on('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});

$('#precio_editar').keyup(function() {
    var val = $(this).val();
    if (isNaN(val)) {
        val = val.replace(/[^0-9\.]/g, '');
        if (val.split('.').length > 2)
            val = val.replace(/\.+$/, "");
    }
    $(this).val(val);
});


$('#cantidad').on('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});

$('#igv').on('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});


function validarFecha() {
    var enviar = false
    var productos = registrosProductos() 

    if ($('#fecha_documento_campo').val() == '') {
        toastr.error('Ingrese Fecha de Documento.', 'Error');
        $("#fecha_documento_campo").focus();
        enviar = true;
    }

    if ($('#fecha_atencion_campo').val() == '') {
        toastr.error('Ingrese Fecha de Atencion.', 'Error');
        $("#fecha_atencion_campo").focus();
        enviar = true;
    }

    if (productos == 0) {
        toastr.error('Ingrese al menos 1  Producto.', 'Error');
        enviar = true;
    }

    return enviar
}

    function validarTipo() {
        var enviar = false

        if ($('#tipo_cliente_documento').val() == '1') {
            toastr.error('El tipo de documento del cliente es diferente a RUC.', 'Error');
            enviar = true;
        }

        return enviar

    }

$('#enviar_documento').submit(function(e) {
    e.preventDefault();
    var correcto = validarFecha()
    if (correcto == false) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger',
            },
            buttonsStyling: false
        })

        Swal.fire({
            title: 'Opción Guardar',
            text: "¿Seguro que desea guardar cambios?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: "#1ab394",
            confirmButtonText: 'Si, Confirmar',
            cancelButtonText: "No, Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {

                var tipo = validarTipo()

                    if (tipo == false) {
                            cargarProductos()
                            //CARGAR DATOS TOTAL
                            $('#monto_sub_total').val($('#subtotal').text())
                            $('#monto_total_igv').val($('#igv_monto').text())
                            $('#monto_total').val($('#total').text())
                            this.submit();
                    }

            } else if (
                /* Read more about handling dismissals below */
                result.dismiss === Swal.DismissReason.cancel
            ) {
                swalWithBootstrapButtons.fire(
                    'Cancelado',
                    'La Solicitud se ha cancelado.',
                    'error'
                )
            }
        })
    }



})

$("#igv_check").click(function() {
    if ($("#igv_check").is(':checked')) {
        $('#igv').attr('disabled', false)
        $('#igv_requerido').addClass("required")
        $('#igv').prop('required', true)
        $('#igv').val('18')
        var igv = ($('#igv').val()) + ' %'
        $('#igv_int').text(igv)
        sumaTotal()

    } else {
        $('#igv').attr('disabled', true)
        $('#igv_requerido').removeClass("required")
        $('#igv').prop('required', false)
        $('#igv').val('')
        $('#igv_int').text('')
        sumaTotal()
    }
});


$(document).ready(function() {

    // DataTables
    $('.dataTables-detalle-documento').DataTable({
        "dom": 'lTfgitp',
        "bPaginate": true,
        "bLengthChange": true,
        "bFilter": true,
        "bInfo": true,
        "bAutoWidth": false,
        "language": {
            "url": "{{asset('Spanish.json')}}"
        },

        "columnDefs": [{
                "targets": [0],
                "visible": false,
                "searchable": false
            },
            {

                "targets": [1],
                className: "text-center",
                render: function(data, type, row) {
                    return "<div class='btn-group'>" +
                        "<a class='btn btn-warning btn-sm modificarDetalle' id='editar-documento' style='color:white;' title='Modificar'><i class='fa fa-edit'></i></a>" +
                        "<a class='btn btn-danger btn-sm' id='eliminar-documento' style='color:white;' title='Eliminar'><i class='fa fa-trash'></i></a>" +
                        "</div>";
                }
            },
            {
                "targets": [2],
                className: "text-center",
            },
            {
                "targets": [3],
                className: "text-center",
            },
            {
                "targets": [4],
               
            },
            {
                "targets": [5],
                className: "text-center",
            },
            {
                "targets": [6],
                className: "text-center",
            },
            {
                "targets": [7],
                "visible": false,
            },


        ],
    });

    @if(old('igv_check', $documento->igv_check))
        $("#igv_check").attr('checked', true);
        $('#igv').attr('disabled', false)
        $('#igv_requerido').addClass("required")
        $('#igv').prop('required', true)        
        var igv = ($('#igv').val()) + ' %'
        $('#igv_int').text(igv)


    @else
        $("#igv_check").attr('checked', false);
        $('#igv').attr('disabled', true)
        $('#igv_requerido').removeClass("required")
        $('#igv').prop('required', false)

    @endif

    obtenerTabla()
    sumaTotal()

})



    function obtenerTabla() {
        var t = $('.dataTables-detalle-documento').DataTable();
        @foreach($detalles as $detalle)
        t.row.add([
            "{{$detalle->producto_id}}",
            '',
            "{{$detalle->cantidad}}",
            "{{$detalle->producto->tabladetalle->simbolo.' - '.$detalle->producto->tabladetalle->descripcion}}",
            "{{$detalle->producto->codigo.' - '.$detalle->producto->nombre}}",
            "{{$detalle->precio}}",
            "{{$detalle->importe}}",
            "{{$detalle->producto->medida}}",
        ]).draw(false);
        @endforeach
    }
    //Editar Registro
    $(document).on('click', '#editar-documento', function(event) {
        var table = $('.dataTables-detalle-documento').DataTable();
        var data = table.row($(this).parents('tr')).data();
        
        console.log(data)
        $('#indice').val(table.row($(this).parents('tr')).index());
        $('#producto_editar').val(data[0]).trigger('change');
        $('#precio_editar').val(data[5]);
        $('#presentacion_producto_editar').val(data[3]);
        $('#codigo_nombre_producto_editar').val(data[4]);
        $('#cantidad_editar').val(data[2]);
        $('#medida_editar').val(data[7]);
        $('#modal_editar_detalle').modal('show');
    })


    //Borrar registro de Producto
    $(document).on('click', '#eliminar-documento', function(event) {

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger',
                },
                buttonsStyling: false
            })

            Swal.fire({
                title: 'Opción Eliminar',
                text: "¿Seguro que desea eliminar Producto?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: "#1ab394",
                confirmButtonText: 'Si, Confirmar',
                cancelButtonText: "No, Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    var table = $('.dataTables-detalle-documento').DataTable();
                    table.row($(this).parents('tr')).remove().draw();
                    sumaTotal()

                } else if (
                    /* Read more about handling dismissals below */
                    result.dismiss === Swal.DismissReason.cancel
                ) {
                    swalWithBootstrapButtons.fire(
                        'Cancelado',
                        'La Solicitud se ha cancelado.',
                        'error'
                    )
                }
            })



    });

    //Validacion al ingresar tablas
    $("#btn_agregar_detalle").click(function() {
        limpiarErrores()
        var enviar = false;
        if ($('#producto').val() == '') {
            toastr.error('Seleccione Producto.', 'Error');
            enviar = true;
            $('#producto').addClass("is-invalid")
            $('#error-producto').text('El campo Producto es obligatorio.')
        } else {
            var existe = buscarProducto($('#producto').val())
            if (existe == true) {
                toastr.error('Producto ya se encuentra ingresado.', 'Error');
                enviar = true;
            }
        }

        if ($('#precio').val() == '') {

            toastr.error('Ingrese el precio del producto.', 'Error');
            enviar = true;

            $("#precio").addClass("is-invalid");
            $('#error-precio').text('El campo Precio es obligatorio.')
        }

        if ($('#cantidad').val() == '') {
            toastr.error('Ingrese cantidad del artículo.', 'Error');
            enviar = true;

            $("#cantidad").addClass("is-invalid");
            $('#error-cantidad').text('El campo Cantidad es obligatorio.')
        }
        if (enviar != true) {
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger',
                },
                buttonsStyling: false
            })

            Swal.fire({
                title: 'Opción Agregar',
                text: "¿Seguro que desea agregar Producto?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: "#1ab394",
                confirmButtonText: 'Si, Confirmar',
                cancelButtonText: "No, Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    obtenerProducto($('#producto').val())

                } else if (
                    /* Read more about handling dismissals below */
                    result.dismiss === Swal.DismissReason.cancel
                ) {
                    swalWithBootstrapButtons.fire(
                        'Cancelado',
                        'La Solicitud se ha cancelado.',
                        'error'
                    )
                }
            })

        }



    })

    function limpiarDetalle() {
        $('#precio').val('')
        $('#cantidad').val('')
        $('#presentacion_producto').val('')
        $('#codigo_nombre_producto').val('')
        $('#producto').val($('#producto option:first-child').val()).trigger('change');

    }

    function limpiarErrores() {
        $('#cantidad').removeClass("is-invalid")
        $('#error-cantidad').text('')

        $('#precio').removeClass("is-invalid")
        $('#error-precio').text('')

        $('#producto').removeClass("is-invalid")
        $('#error-producto').text('')
    }


function agregarTabla($detalle) {

    console.log($detalle)
    var t = $('.dataTables-detalle-documento').DataTable();
    t.row.add([
        $detalle.producto_id,
        '',
        $detalle.cantidad,
        obtenerMedida($detalle.presentacion),
        $detalle.producto,
        $detalle.precio,
        ($detalle.cantidad * $detalle.precio).toFixed(2),
        $detalle.presentacion
    ]).draw(false);
    cargarProductos()

}


function obtenerMedida(id) {
    var medida = ""
    @foreach(unidad_medida() as $medida)
        if ("{{$medida->id}}" == id) {
            medida = "{{$medida->simbolo.' - '.$medida->descripcion}}"
        }
    @endforeach
    return medida
}

function buscarProducto(id) {
    var existe = false;
    var t = $('.dataTables-detalle-documento').DataTable();
    t.rows().data().each(function(el, index) {
        if (el[0] == id) {
            existe = true
        }
    });
    return existe
}



    function cargarProductos() {

        var productos = [];
        var table = $('.dataTables-detalle-documento').DataTable();
        var data = table.rows().data();
        data.each(function(value, index) {
            let fila = {
                producto_id: value[0],
                presentacion: value[3],
                precio: value[5],
                cantidad: value[2],
                total: value[6],
            };

            productos.push(fila);

        });

        $('#productos_tabla').val(JSON.stringify(productos));
    }



    function registrosProductos() {
        var table = $('.dataTables-detalle-documento').DataTable();
        var registros = table.rows().data().length;
        return registros
    }


    function sumaTotal() {
        var t = $('.dataTables-detalle-documento').DataTable();
        var subtotal = 0;
        t.rows().data().each(function(el, index) {
            subtotal = Number(el[6]) + subtotal
        });

        var igv = $('#igv').val()
        if (!igv) {
            sinIgv(subtotal)   
        }else{
            conIgv(subtotal)
        }
    }

    function sinIgv(subtotal) {
        // calular igv (calcular la base)
        var igv =  subtotal * 0.18
        var total = subtotal + igv
        $('#igv_int').text('18%')
        $('#subtotal').text(subtotal.toFixed(2))
        $('#igv_monto').text(igv.toFixed(2))
        $('#total').text(total.toFixed(2))

    }

    function conIgv(subtotal) {
        // calular igv (calcular la base)
        var igv = $('#igv').val()
        ///////////////////////////////

        if (igv) {
            var calcularIgv = igv/100
            var base = subtotal / (1 + calcularIgv)
            var nuevo_igv = subtotal - base;
            $('#igv_int').text(igv+'%')
            $('#subtotal').text(base.toFixed(2))
            $('#igv_monto').text(nuevo_igv.toFixed(2))
            $('#total').text(subtotal.toFixed(2))

        }else{
            toastr.error('Ingrese Igv.', 'Error');
        }

    }


    function obtenerTipo(tipo){
            
        if (tipo.value) {
            
            $('#producto').prop('disabled' , false)
            $('#precio').prop('disabled' , false)
            $('#cantidad').prop('disabled' , false)

            @foreach ($clientes as $cliente)

                if ("{{$cliente->id}}" == tipo.value) {
                    $('#tipo_cliente').val("{{$cliente->detalle->descripcion}}")
                    if ("{{$cliente->tipo_documento}}" != "RUC") {
                        $('#tipo_cliente_documento').val("1");
                    }else{
                        $('#tipo_cliente_documento').val("0");
                    }
                }

            @endforeach
        }else{
            $('#producto').prop('disabled' , true)
            $('#precio').prop('disabled' , true)
            $('#cantidad').prop('disabled' , true)
        }
    }

    function obtenerMonto(producto){
        var tipo = $('#tipo_cliente').val()
        // alert(tipo)
        $.get('/almacenes/productos/obtenerProducto/'+ producto.value, function (data) {

            for (var i = 0; i < data.length; i++)
                //SOLO SOLES LOS MONTOS
                if (data[i].cliente == tipo && data[i].moneda == 4  ) {
                    $('#precio').val(data[i].monto)
                }

        });

    }

    function obtenerProducto(id) {
        // Consultamos nuestra BBDD
        var url = '{{ route("almacenes.producto.productoDescripcion", ":id")}}';
        url = url.replace(':id',id);
        $.ajax({
            dataType : 'json',
            type : 'get',
            url : url,
        }).done(function (result){

            $('#presentacion_producto').val(result.medida)
            $('#codigo_nombre_producto').val(result.codigo+' - '+result.nombre)
            llegarDatos()
            sumaTotal()
            limpiarDetalle()
        });
    }


</script>




@endpush