@extends('layout') @section('content')

@section('ventas-active', 'active')
@section('guias-remision-active', 'active')

<div class="row wrapper border-bottom white-bg page-heading">

    <div class="col-lg-12">
       <h2  style="text-transform:uppercase"><b>REGISTRAR NUEVA GUIA DE REMISION</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('home')}}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('ventas.guiasremision.index')}}">Guias de Remision</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Registrar</strong>
            </li>

        </ol>
    </div>



</div>


<div class="wrapper wrapper-content animated fadeInRight">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">

                <div class="ibox-content">

                    <div class="row">
                        <div class="col-12">
                            <form action="{{route('ventas.guiasremision.store')}}" method="POST" id="enviar_documento">
                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-sm-6 b-r">
                                        <h4 class=""><b>Guia de Remision</b></h4>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p>Registrar datos de la guia de remision:</p>
                                            </div>
                                        </div>

                                        <div class="form-group row">

                                            <div class="col-lg-6 col-xs-12" id="fecha_documento">
                                                <label class="required">Fecha de Documento</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon">
                                                        <i class="fa fa-calendar"></i>
                                                    </span>

                                                    <input type="date" id="fecha_documento_campo" name="fecha_documento"
                                                        class="form-control {{ $errors->has('fecha_documento') ? ' is-invalid' : '' }}"
                                                        value="{{old('fecha_documento',$hoy)}}"
                                                        autocomplete="off" required readonly>


                                                    @if ($errors->has('fecha_documento'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('fecha_documento') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-lg-6 col-xs-12" id="fecha_entrega">
                                                <label class="required">Fecha de Atención</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon">
                                                        <i class="fa fa-calendar"></i>
                                                    </span>


                                                    <input type="date" id="fecha_atencion_campo" name="fecha_atencion_campo"
                                                        class="form-control{{ $errors->has('fecha_atencion') ? ' is-invalid' : '' }}"
                                                        value="{{old('fecha_atencion',$hoy)}}"
                                                        autocomplete="off" required readonly>



                                                    @if ($errors->has('fecha_atencion'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('fecha_atencion') }}</strong>
                                                    </span>
                                                    @endif

                                                </div>
                                            </div>

                                        </div>

                                        <div class="form-group">
                                            <label class="required">Cliente: </label>
                                            <select class="select2_form form-control {{ $errors->has('cliente_id') ? ' is-invalid' : '' }}" style="text-transform: uppercase; width:100%" value="{{old('cliente_id')}}" name="cliente_id" id="cliente_id" required>
                                            <option></option>
                                                @foreach ($clientes as $cliente)

                                                    <option value="{{$cliente->id}}" @if(old('cliente_id') == $cliente->id )
                                                        {{'selected'}} @endif >{{$cliente->tipo_documento.': '.$cliente->documento.' - '.$cliente->nombre}}
                                                    </option>

                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-lg-6 col-xs-12">
                                                <label class="required">Cantidad de Productos: </label>
                                                <input type="number" name="cantidad_productos" id="cantidad_productos" value="{{old('peso_productos')}}" class="form-control {{ $errors->has('cantidad_productos') ? ' is-invalid' : '' }}" readonly>
                                                @if ($errors->has('cantidad_productos'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('cantidad_productos') }}</strong>
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="col-md-6 col-xs-12">
                                                <label class="required">Peso Total de productos:</label>
                                                <input type="number" name="peso_productos" id="peso_productos" readonly value="{{old('peso_productos')}}" class="form-control {{ $errors->has('peso_productos') ? ' is-invalid' : '' }}">
                                                @if ($errors->has('peso_productos'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('peso_productos') }}</strong>
                                                    </span>
                                                @endif

                                            </div>

                                        </div>

                                        <hr>

                                        <h4 class=""><b>Dirección de Partida</b></h4>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p>Registrar dirección de partida de los productos:</p>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-lg-8 col-xs-12">
                                                <label class="required">Empresa: </label>

                                                <select class="select2_form form-control {{ $errors->has('empresa_id') ? ' is-invalid' : '' }}"
                                                        style="text-transform: uppercase; width:100%" value="{{old('empresa_id')}}"
                                                        name="empresa_id" id="empresa_id" required disabled>
                                                        <option></option>
                                                        @foreach ($empresas as $empresa)
                                                        <option value="{{$empresa->id}}" @if(old('empresa_id', 1)==$empresa->id )
                                                            {{'selected'}} @endif >{{$empresa->razon_social}}</option>
                                                        @endforeach
                                                </select>
                                            </div>

                                            <div class="col-lg-4 col-xs-12">
                                                <label class="required">Ubigeo: </label>
                                                <input type="text" id="ubigeo_partida" class="form-control input-required {{ $errors->has('ubigeo_partida') ? ' is-invalid' : '' }}" required name="ubigeo_partida" value="{{ old('ubigeo_partida',$empresa->ubigeo)}}">
                                                @if ($errors->has('ubigeo_partida'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('ubigeo_partida') }}</strong>
                                                </span>
                                                @endif
                                            </div>

                                        </div>


                                        <div class="form-group">
                                            <label class="required">Dirección de la Empresa (Partida): </label>
                                            <textarea type="text" placeholder=""
                                                class="form-control input-required {{ $errors->has('direccion_tienda') ? ' is-invalid' : '' }}"
                                                name="direccion_empresa" id="direccion_empresa" value="{{old('direccion_empresa',$empresa->direccion_fiscal)}}" required >{{old('direccion_empresa',$empresa->direccion_fiscal)}}</textarea>


                                            @if ($errors->has('direccion_empresa'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('direccion_empresa') }}</strong>
                                            </span>
                                            @endif
                                        </div>



                                    </div>

                                    <div class="col-sm-6">
                                        <h4 class=""><b>Dirección de llegada</b></h4>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p>Registrar dirección de llegada de los productos:</p>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12 col-xs-12">
                                                <div class="form-group">
                                                    <label class="">Tienda (Opcional): </label>
                                                    <input type="text" id="tienda" class="form-control {{ $errors->has('tienda') ? ' is-invalid' : '' }}"  name="tienda" value="{{ old('tienda')}}">
                                                    @if ($errors->has('tienda'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('tienda') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-lg-12 col-xs-12">
                                                <div class="form-group">
                                                    <label class="required">Departamento</label>
                                                    <select id="departamento" name="departamento"
                                                        class="select2_form form-control {{ $errors->has('departamento') ? ' is-invalid' : '' }}"
                                                        style="width: 100%" required>
                                                        <option></option>
                                                        @foreach (departamentos() as $departamento)
                                                            <option value="{{ $departamento->id }}"
                                                                {{ (old('departamento') == $departamento->id ? 'selected' : '') }}>
                                                                {{ $departamento->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                    @if ($errors->has('departamento'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('departamento') }}</strong>
                                                        </span>
                                                    @endif
                                                    {{-- <label class="required">Ubigeo: </label>
                                                    <input type="text" id="ubigeo_llegada" class="form-control input-required {{ $errors->has('ubigeo_llegada') ? ' is-invalid' : '' }}" required  name="ubigeo_llegada" value="{{ old('ubigeo_llegada')}}">
                                                    @if ($errors->has('ubigeo_llegada'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('ubigeo_llegada') }}</strong>
                                                    </span>
                                                    @endif --}}
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-xs-12">
                                                <div class="form-group">
                                                    <label class="required">Provincia</label>
                                                    <select id="provincia" name="provincia"
                                                        class="select2_form form-control {{ $errors->has('provincia') ? ' is-invalid' : '' }}"
                                                        style="width: 100%" required>
                                                        <option></option>
                                                        @foreach (provincias() as $provincia)
                                                            <option value="{{ $provincia->id }}"
                                                                {{ (old('provincia') == $provincia->id ? 'selected' : '') }}>
                                                                {{ $provincia->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                    @if ($errors->has('provincia'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('provincia') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-xs-12">
                                                <div class="form-group">
                                                    <label class="required">Distrito</label>
                                                    <select id="ubigeo_llegada" name="ubigeo_llegada"
                                                        class="select2_form form-control {{ $errors->has('ubigeo_llegada') ? ' is-invalid' : '' }}"
                                                        style="width: 100%" required>
                                                        <option></option>
                                                        @foreach (distritos() as $distrito)
                                                            <option value="{{ $distrito->id }}"
                                                                {{ (old('ubigeo_llegada') == $distrito->id ? 'selected' : '') }}>
                                                                {{ $distrito->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                    @if ($errors->has('ubigeo_llegada'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('ubigeo_llegada') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>

                                        <div class="form-group">
                                            <label class="required">Dirección de la tienda o destino (Llegada): </label>
                                            <textarea type="text" placeholder=""
                                                class="form-control input-required {{ $errors->has('direccion_tienda') ? ' is-invalid' : '' }}"
                                                name="direccion_tienda" id="direccion_tienda" value="{{old('direccion_tienda')}}"  required >{{old('direccion_tienda')}}</textarea>


                                            @if ($errors->has('direccion_tienda'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('direccion_tienda') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                        <hr>

                                        <h4 class=""><b>Detalles del envio</b></h4>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p>Registrar datos adicionales del envio:</p>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-lg-6 col-xs-12">
                                                <label class="">Dni del Conductor: </label>
                                                <input type="text" id="dni_conductor" class="form-control {{ $errors->has('dni_conductor') ? ' is-invalid' : '' }}" maxlength="8" name="dni_conductor" value="{{ old('dni_conductor')}}">
                                                @if ($errors->has('dni_conductor'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('dni_conductor') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                            <div class="col-lg-6 col-xs-12">
                                                <label class="">Placa del Vehículo: </label>
                                                <input type="text" id="placa_vehiculo" class="form-control {{ $errors->has('placa_vehiculo') ? ' is-invalid' : '' }}" name="placa_vehiculo" value="{{ old('placa_vehiculo')}}">
                                                @if ($errors->has('placa_vehiculo'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('placa_vehiculo') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Observación:</label>

                                            <textarea type="text" placeholder=""
                                                class="form-control {{ $errors->has('observacion') ? ' is-invalid' : '' }}"
                                                name="observacion" id="observacion"  onkeyup="return mayus(this)"
                                                value="{{old('observacion')}}"></textarea>


                                            @if ($errors->has('observacion'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('observacion') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <input type="hidden" class="form-control" name="productos_tabla[]" id="productos_tabla">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-primary" id="panel_detalle">
                                <div class="panel-heading">
                                    <h4 class=""><b>Detalle de la Guia de Remision</b></h4>
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
                                            <form id="agregar_producto">
                                                <div class="row align-items-end">
                                                    <div class="col-12 col-md-6">
                                                        <div class="form-group row align-items-end">
                                                            <div class="col-10 col-md-10">
                                                                <label class="required">Producto:</label>
                                                                <select class="select2_form form-control"
                                                                    style="text-transform: uppercase; width:100%" name="producto_id"
                                                                    id="producto_id" required>

                                                                </select>
                                                                <div class="invalid-feedback"><b><span id="error-producto"></span></b>
                                                                </div>
                                                            </div>
                                                            <div class="col-2 col-md-2">
                                                                <button type="button" class="btn btn-secondary" onclick="obtenerProducts()"><i class="fa fa-refresh"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-4">
                                                        <div class="form-group">
                                                            <label for="">Cantidad</label>
                                                            <input type="number" name="cantidad" id="cantidad" class="form-control" placeholder="Cantidad" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-2">
                                                        <div class="form-group">
                                                            <button type="submit" class="btn btn-success btn-block"><i class="fa fa-plus"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-12">
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
                                                            <th class="text-center">PESO (KG)</th>
                                                            <th class="text-center">DESCRIPCION DEL PRODUCTO</th>
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

                    <div class="hr-line-dashed"></div>
                    <div class="form-group row">

                        <div class="col-md-6 text-left" style="color:#fcbc6c">
                            <i class="fa fa-exclamation-circle"></i> <small>Los campos marcados con asterisco
                                (<label class="required"></label>) son obligatorios.</small>
                        </div>

                        <div class="col-md-6 text-right">

                            <a href="{{route('ventas.guiasremision.index')}}" id="btn_cancelar"
                                class="btn btn-w-m btn-default">
                                <i class="fa fa-arrow-left"></i> Regresar
                            </a>

                            <button type="submit" id="btn_grabar" form="enviar_documento" class="btn btn-w-m btn-primary">
                                <i class="fa fa-save"></i> Grabar
                            </button>
                        </div>

                    </div>

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
<!-- DataTable -->
<link href="{{asset('Inspinia/css/plugins/dataTables/datatables.min.css')}}" rel="stylesheet">

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
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    function obtenerProducts()
    {
        $('#panel_detalle').children('.ibox-content').toggleClass('sk-loading');
        $("#producto_id").empty().trigger('change');
        axios.get('{{ route('compras.documento.getProduct') }}').then(response => {
            let data = response.data.data
            console.log(data)
            if (data.length > 0) {
                $('#producto_id').append('<option></option>').trigger('change');
                for(var i = 0;i < data.length; i++)
                {
                    let codigo = data[i].codigo_barra ? (' - ' + data[i].codigo_barra) : '';
                    var newOption = '<option value="'+data[i].id+'" peso="'+data[i].peso_producto+'" unidad="'+data[i].medida_desc+'" descripcion="'+data[i].nombre+'">'+data[i].nombre + codigo + '</option>';
                    $('#producto_id').append(newOption).trigger('change');
                    //departamentos += '<option value="'+result.departamentos[i].id+'">'+result.departamentos[i].nombre+'</option>';
                }

                $('#panel_detalle').children('.ibox-content').toggleClass('sk-loading');

            } else {
                $('#panel_detalle').children('.ibox-content').toggleClass('sk-loading');
                toastr.error('Productos no encontrados.', 'Error');
            }
        })
    }

    $(document).ready(function() {
        obtenerProducts()
        $(".select2_form").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            height: '200px',
            width: '100%',
        });

        $("#departamento").on("change", cargarProvincias);

        $('#provincia').on("change", cargarDistritos);
    });

    function cargarProvincias() {
        var departamento_id = $("#departamento").val();
        if (departamento_id !== "" || departamento_id.length > 0) {
            $.ajax({
                type: 'post',
                dataType: 'json',
                data: {
                    _token: $('input[name=_token]').val(),
                    departamento_id: departamento_id
                },
                url: "{{ route('mantenimiento.ubigeo.provincias') }}",
                success: function(data) {
                    // Limpiamos data
                    $("#provincia").empty();
                    $("#distrito").empty();

                    if (!data.error) {
                        // Mostramos la información
                        if (data.provincias != null) {
                            $("#provincia").select2({
                                data: data.provincias
                            }).val($('#provincia').find(':selected').val()).trigger('change');
                        }
                    } else {
                        toastr.error(data.message, 'Mensaje de Error', {
                            "closeButton": true,
                            positionClass: 'toast-bottom-right'
                        });
                    }
                }
            });
        }
    }

    function cargarDistritos() {
        var provincia_id = $("#provincia").val();
        if (provincia_id !== "" || provincia_id.length > 0) {
            $.ajax({
                type: 'post',
                dataType: 'json',
                data: {
                    _token: $('input[name=_token]').val(),
                    provincia_id: provincia_id
                },
                url: "{{ route('mantenimiento.ubigeo.distritos') }}",
                success: function(data) {
                    // Limpiamos data
                    $("#ubigeo_llegada").empty();

                    if (!data.error) {
                        // Mostramos la información
                        if (data.distritos != null) {
                            var selected = $('#ubigeo_llegada').find(':selected').val();
                            $("#ubigeo_llegada").select2({
                                data: data.distritos
                            });
                        }
                    } else {
                        toastr.error(data.message, 'Mensaje de Error', {
                            "closeButton": true,
                            positionClass: 'toast-bottom-right'
                        });
                    }
                }
            });
        }
    }

    // Solo campos numericos
    $('#ubigeo_partida, #dni_conductor').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    $(document).ready(function() {
        //DATATABLE - COTIZACION
        table = $('.dataTables-detalle-documento').DataTable({
            "dom": 'lTfgitp',
            "bPaginate": true,
            "bLengthChange": true,
            "responsive": true,
            "bFilter": true,
            "bInfo": false,
            "columnDefs": [
                {
                    "targets": 0,
                    "visible": false,
                    "searchable": false
                },
                {
                    searchable: false,
                    "targets": [1],
                    "className": 'text-center',
                    render: function(data){
                        return "<button type='button' class='btn btn-danger btn-delete'><i class='fa fa-trash'></i></button>"
                    }
                },
                {
                    "targets": [2],
                    "className": 'text-center'
                },
                {
                    "targets": [3],
                    "className": 'text-center'
                },
                {
                    "targets": [4],
                    "className": 'text-center'
                },
                {
                    "targets": [5],
                },
            ],
            'bAutoWidth': false,
            "language": {
                url: "{{asset('Spanish.json')}}"
            },
            "order": [[ 0, "desc" ]],
        });

        //DIRECCION DE LA TIENDA OLD

        //Controlar Error
        $.fn.DataTable.ext.errMode = 'throw';
    });

    $('#agregar_producto').on('submit', function(e){
        e.preventDefault();
        let producto_id = $('#producto_id').val();
        let cantidad = $('#cantidad').val();
        let correcto = true;
        if(producto_id)
        {
            if(buscarProducto(producto_id))
            {
                correcto = false;
                toastr.error('Producto', 'Este producto ya esta registrado');
            }
        }
        else{
            correcto = false;
            toastr.error('Producto', 'El campo producto es obligatorio');
        }

        if(cantidad == '' || cantidad == null)
        {
            correcto = false;
            toastr.error('Cantidad', 'El campo cantidad es obligatorio');
        }

        if(correcto)
        {
            llegarDatos();
        }
    })

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

    function llegarDatos() {
        let producto_id = $('#producto_id').val();
        let unidad = $('#producto_id option:selected').attr('unidad');
        let peso = $('#producto_id option:selected').attr('peso');
        let descripcion = $('#producto_id option:selected').attr('descripcion');
        let cantidad = $('#cantidad').val();
        let detalle = {
            id : producto_id,
            cantidad : cantidad,
            unidad : unidad,
            peso : peso,
            descripcion : descripcion
        };
        agregarTabla(detalle);
    }

    //AGREGAR EL DETALLE A LA TABLA
    function agregarTabla($detalle) {
        var t = $('.dataTables-detalle-documento').DataTable();
        t.row.add([
            $detalle.id,
            '',
            $detalle.cantidad,
            $detalle.unidad,
            $detalle.peso,
            $detalle.descripcion
        ]).draw(false);
        limpiarErrores();
        limpiarDetalle();
        cargarDetalle();
        $('#peso_productos').val(sumaPesos().toFixed(2))
        $('#cantidad_productos').val(registrosProductos().toFixed(2))
    }

    function cargarDetalle() {
        var notadetalle = [];
        var table = $('.dataTables-detalle-documento').DataTable();
        var data = table.rows().data();
        data.each(function(value, index) {
            let fila = {
                producto_id: value[0],
                cantidad: value[2],
            };

            notadetalle.push(fila);

        });
        $('#productos_tabla').val(JSON.stringify(notadetalle))
    }

    function limpiarErrores() {
        $('#cantidad').removeClass("is-invalid")
        $('#error-cantidad').text('')

        $('#producto').removeClass("is-invalid")
        $('#error-producto').text('')
    }

    function limpiarDetalle() {
        $('#producto_id').val($('#producto option:first-child').val()).trigger('change');
        $('#cantidad').val('');
    }


    function registrosProductos() {
        var t = $('.dataTables-detalle-documento').DataTable();
        let total = 0.00;
        t.rows().data().each(function(el, index) {
            total = Number(el[2]) + total
        });

        return total
    }


    function validarFecha() {
        var enviar = false
        var productos = registrosProductos()
        if ($('#fecha_documento_campo').val() == '') {
            toastr.error('Ingrese Fecha de Documento.', 'Error');
            $("#fecha_documento_campo").focus();
            enviar = true;
        }

        if ($('#fecha_atencion_campo').val() == '') {
            toastr.error('Ingrese Fecha de Atención.', 'Error');
            $("#fecha_atencion_campo").focus();
            enviar = true;
        }

        if (productos == 0) {
            toastr.error('Ingrese al menos 1 Producto.', 'Error');
            enviar = true;
        }
        return enviar
    }

    function sumaPesos()
    {
        var t = $('.dataTables-detalle-documento').DataTable();
        let total = 0.00;
        t.rows().data().each(function(el, index) {
            total=Number(el[4] * el[2]) + total
        });

        return total
    }

    $('#enviar_documento').submit(function(e) {
        e.preventDefault();
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
                if(registrosProductos() > 0)
                {
                    cargarDetalle();
                    this.submit();
                }
                else
                {
                    toastr.error('Debe tener al menos un detalle')
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
    })

    //Borrar registro de articulos
    $(".dataTables-detalle-documento").on('click','.btn-delete',function(){

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
                $('#peso_productos').val(sumaPesos().toFixed(2))
                $('#cantidad_productos').val(registrosProductos().toFixed(2))

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
</script>
@endpush
