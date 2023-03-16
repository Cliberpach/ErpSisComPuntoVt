@extends('layout') @section('content')

@section('compras-active', 'active')
@section('documento-compra-active', 'active')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-12">
       <h2  style="text-transform:uppercase"><b>MODIFICAR DOCUMENTO DE COMPRA # {{$documento->id}}</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('home')}}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('compras.documento.index')}}">Documentos de Compra</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Modificar</strong>
            </li>

        </ol>
    </div>
</div>
<compras-edit-component id="{{ $documento->id }}"></compras-edit-component>

{{--<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">

                <div class="ibox-content">

                    <div class="row">
                        <div class="col-12">
                            <form action="{{route('compras.documento.update', $documento->id)}}" method="POST" id="enviar_orden">
                                @csrf @method('PUT')

                                <div class="row">
                                    <div class="col-sm-6 b-r">
                                        <h4 class=""><b>Documento de compra</b></h4>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p>Modificar datos del documento de compra:</p>
                                            </div>
                                        </div>

                                        <div class="form-group row">

                                            <div class="col-lg-6 col-xs-12" id="fecha_documento">
                                                <label class="required">Fecha de Emisión</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon">
                                                        <i class="fa fa-calendar"></i>
                                                    </span>
                                                    <input type="text" id="fecha_documento" name="fecha_emision"
                                                        class="form-control {{ $errors->has('fecha_emision') ? ' is-invalid' : '' }}"
                                                        value="{{old('fecha_emision', getFechaFormato($documento->fecha_emision, 'd/m/Y'))}}"
                                                        autocomplete="off" readonly required>
                                                    @if ($errors->has('fecha_emision'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('fecha_emision') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-lg-6 col-xs-12" id="fecha_entrega">
                                                <label class="required">Fecha de Entrega</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon">
                                                        <i class="fa fa-calendar"></i>
                                                    </span>
                                                    <input type="text" id="fecha_entrega_campo" name="fecha_entrega"
                                                        class="form-control {{ $errors->has('fecha_entrega') ? ' is-invalid' : '' }}"
                                                        value="{{old('fecha_entrega', getFechaFormato($documento->fecha_entrega, 'd/m/Y'))}}"
                                                        autocomplete="off" required readonly>
                                                    @if ($errors->has('fecha_entrega'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('fecha_entrega') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>

                                        <div class="form-group">
                                            <label class="required">Empresa: </label>
                                            <select
                                                class="select2_form form-control {{ $errors->has('empresa_id') ? ' is-invalid' : '' }}"
                                                style="text-transform: uppercase; width:100%" value="{{old('empresa_id')}}"
                                                name="empresa_id" id="empresa_id"  disabled>
                                                <option></option>
                                                @foreach ($empresas as $empresa)
                                                <option value="{{$empresa->id}}"  @if($empresa->id == '1' ) {{'selected'}} @endif >{{$empresa->razon_social}}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <hr>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <p>Modificar Proveedor:</p>
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="required">Ruc / Dni: </label>

                                            <select
                                                class="select2_form form-control {{ $errors->has('proveedor_id') ? ' is-invalid' : '' }}"
                                                style="text-transform: uppercase; width:100%" value="{{old('proveedor_id',$documento->proveedor_id)}}"
                                                name="proveedor_id" id="proveedor_id" required @if (!empty($documento->orden_id)) disabled @endif>
                                                <option></option>
                                                @foreach ($proveedores as $proveedor)
                                                @if($proveedor->ruc)
                                                <option value="{{$proveedor->id}}" @if(old('proveedor_id',$documento->proveedor_id)
                                                    == $proveedor->id ) {{'selected'}} @endif
                                                    >{{$proveedor->ruc}}</option>
                                                @else
                                                @if($proveedor->dni)
                                                <option value="{{$proveedor->id}}" @if(old('proveedor_id',$documento->proveedor_id)
                                                    == $proveedor->id ) {{'selected'}} @endif
                                                    >{{$proveedor->dni}}</option>
                                                @endif
                                                @endif
                                                @endforeach
                                            </select>

                                        </div>

                                        <div class="form-group">
                                            <label class="required">Razon Social: </label>
                                            <select
                                                class="select2_form form-control {{ $errors->has('proveedor_razon') ? ' is-invalid' : '' }}"
                                                style="text-transform: uppercase; width:100%" value="{{old('proveedor_razon')}}"
                                                name="proveedor_razon" id="proveedor_razon" required @if (!empty($documento->orden_id)) disabled @endif>
                                                <option></option>
                                                @foreach ($proveedores as $proveedor)
                                                    @if($proveedor->ruc)
                                                    <option value="{{$proveedor->id}}" @if(old('proveedor_id',$documento->proveedor_id)==$proveedor->id )
                                                        {{'selected'}} @endif >{{$proveedor->descripcion}}
                                                    </option>
                                                    @else
                                                    @if($proveedor->dni)
                                                    <option value="{{$proveedor->id}}" @if(old('proveedor_id',$documento->proveedor_id)==$proveedor->id )
                                                        {{'selected'}} @endif >{{$proveedor->descripcion}}
                                                    </option>
                                                    @endif
                                                @endif
                                                @endforeach
                                            </select>
                                        </div>




                                    </div>

                                    <div class="col-sm-6">


                                        <div class="form-group row">
                                            <div class="col-md-6">
                                                <label class="required">Condición: </label>
                                                <select id="condicion_id" name="condicion_id"
                                                        class="select2_form form-control {{ $errors->has('condicion_id') ? ' is-invalid' : '' }}"
                                                        required>
                                                        <option></option>
                                                        @foreach ($condiciones as $condicion)
                                                            <option value="{{ $condicion->id }}"
                                                            @if(old('condicion_id',$documento->condicion_id) == $condicion->id ) {{'selected'}} @endif>
                                                                {{ $condicion->descripcion }} {{ $condicion->dias > 0 ? $condicion->dias.' dias' : '' }}
                                                            </option>
                                                        @endforeach
                                                </select>
                                                @if ($errors->has('condicion_id'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('condicion_id') }}</strong>
                                                </span>
                                                @endif

                                            </div>
                                            <div class="col-md-6">
                                                <label class="required">Moneda: </label>
                                                <select
                                                    class="select2_form form-control {{ $errors->has('moneda') ? ' is-invalid' : '' }}"
                                                    style="text-transform: uppercase; width:100%"
                                                    value="{{old('moneda',$documento->moneda)}}" name="moneda" id="moneda" required @if (!empty($documento->orden_id)) disabled @endif>
                                                    <option></option>
                                                    @foreach (tipos_moneda() as $moneda)
                                                    <option value="{{$moneda->descripcion}}" @if(old('moneda',$documento->
                                                        moneda)==$moneda->
                                                        descripcion ) {{'selected'}} @endif
                                                        >{{$moneda->simbolo.' - '.$moneda->descripcion}}</option>
                                                    @endforeach
                                                    @if ($errors->has('moneda'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('moneda') }}</strong>
                                                    </span>
                                                    @endif
                                                </select>


                                            </div>


                                        </div>

                                        <div class="form-group row">
                                            <div class="col-md-6">
                                                <label class="required">Tipo: </label>
                                                <select
                                                    class="select2_form form-control {{ $errors->has('tipo_compra') ? ' is-invalid' : '' }}"
                                                    style="text-transform: uppercase; width:100%" value="{{old('tipo_compra')}}"
                                                    name="tipo_compra" id="tipo_compra" required onchange="activarNumero()">
                                                    <option></option>
                                                    @foreach (tipo_compra() as $modo)
                                                    <option value="{{$modo->descripcion}}" @if(old('tipo_compra',$documento->tipo_compra)==$modo->
                                                        descripcion ) {{'selected'}} @endif
                                                        >{{$modo->descripcion}}</option>
                                                    @endforeach
                                                    @if ($errors->has('tipo_compra'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('tipo_compra') }}</strong>
                                                    </span>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-12 col-md-4">
                                                        <label class="required" id="serie_comprobante">Serie: </label>
                                                        <input type="text" id="serie_tipo" name="serie_tipo" class="form-control {{ $errors->has('serie_tipo') ? ' is-invalid' : '' }}" value="{{old('serie_tipo',$documento->serie_tipo)}}" required >

                                                        @if ($errors->has('serie_tipo'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('serie_tipo') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="col-12 col-md-8">
                                                        <label class="required" id="numero_comprobante">Nº: </label>
                                                        <input type="text" id="numero_tipo" name="numero_tipo" class="form-control {{ $errors->has('numero_tipo') ? ' is-invalid' : '' }}" value="{{old('numero_tipo',$documento->numero_tipo)}}" required >

                                                        @if ($errors->has('numero_tipo'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('numero_tipo') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="form-group row">
                                            <div class="col-md-6">
                                                <label class=""  id="campo_tipo_cambio">Tipo de Cambio (S/.):</label>
                                                <input type="text" id="tipo_cambio" name="tipo_cambio" class="form-control {{ $errors->has('tipo_cambio') ? ' is-invalid' : '' }}" value="{{old('tipo_cambio',$documento->tipo_cambio)}}" disabled>
                                                @if ($errors->has('tipo_cambio'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('tipo_cambio') }}</strong>
                                                </span>
                                                @endif

                                            </div>

                                            <div class="col-md-6">
                                                <label id="igv_requerido">IGV (%):</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-addon">
                                                            <input type="checkbox" id="igv_check" name="igv_check" @if (!empty($documento->orden_id)) disabled @endif>
                                                        </span>
                                                    </div>
                                                    <input type="text" value="{{old('igv',$documento->igv)}}" maxlength="3"
                                                        class="form-control {{ $errors->has('igv') ? ' is-invalid' : '' }}"
                                                        name="igv" id="igv"  onkeyup="return mayus(this)" required>
                                                        @if ($errors->has('igv'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('igv') }}</strong>
                                                        </span>
                                                        @endif

                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Observación:</label>
                                            <textarea type="text" placeholder=""
                                                class="form-control {{ $errors->has('observacion') ? ' is-invalid' : '' }}"
                                                name="observacion" id="observacion"  onkeyup="return mayus(this)"
                                                value="{{old('observacion', $documento->observacion)}}" @if (!empty($documento->orden_id)) disabled @endif>{{old('observacion',$documento->observacion)}}</textarea>
                                            @if ($errors->has('observacion'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('observacion') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <input type="hidden" id="productos_tabla" name="productos_tabla[]">
                                        <input type="hidden" id="productos_detalle" name="productos_detalle[]" value="{{$detalles}}">

                                    </div>

                                    <input type="hidden" name="monto_sub_total" id="monto_sub_total" value="{{ old('monto_sub_total',$documento->sub_total) }}">
                                    <input type="hidden" name="monto_total_igv" id="monto_total_igv" value="{{ old('monto_total_igv',$documento->total_igv) }}">
                                    <input type="hidden" name="monto_total" id="monto_total" value="{{ old('monto_total',$documento->total) }}">
                                    <input type="hidden" name="monto_percepcion" id="monto_percepcion" value="{{ old('monto_percepcion',$documento->percepcion) }}">

                                </div>
                            </form>
                        </div>
                    </div>

                    <hr>

                    <div class="row">

                        <div class="col-lg-12">
                            <div class="panel panel-primary" id="panel_detalle">
                                <div class="panel-heading">
                                    <h4 class=""><b>Detalle de Documento de Compra</b></h4>
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
                                        <div class="col-lg-6 col-xs-12 b-r">
                                            <div class="form-group row align-items-end">
                                                <div class="col-10 col-md-10">
                                                    <label class="required">Producto:</label>
                                                    <select class="select2_form form-control"
                                                        style="text-transform: uppercase; width:100%" name="producto_id"
                                                        id="producto_id">
                                                    </select>
                                                    <div class="invalid-feedback"><b><span id="error-producto"></span></b>
                                                    </div>
                                                </div>
                                                <div class="col-2 col-md-2">
                                                    <button type="button" class="btn btn-secondary" onclick="obtenerProducts()"><i class="fa fa-refresh"></i></button>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                    <label class="required">Costo Flete:</label>
                                                    <input type="text" id="costo_flete" name="costo_flete" class="form-control" onkeydown="nextFocus(event,'btn_enviar_detalle')">
                                                    <div class="invalid-feedback"><b><span id="error-costo-flete"></span></b></div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="col-lg-6 col-xs-12">
                                            <div class="form-group row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label required" for="amount">Importe:</label>
                                                        <input type="text" id="precio" name="precio" class="form-control" onkeydown="nextFocus(event,'cantidad')">
                                                        <div class="invalid-feedback"><b><span id="error-precio"></span></b>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">

                                                    <label class="col-form-label required">Cantidad:</label>
                                                    <input type="text" id="cantidad" class="form-control" onkeydown="nextFocus(event,'costo_flete')">
                                                    <div class="invalid-feedback"><b><span id="error-cantidad"></span></b>
                                                    </div>


                                                </div>
                                            </div>
                                            <div class="form-group row d-none">
                                                <div class="col-md-6" id="fecha_vencimiento_campo">
                                                    <label class="required">Fecha de vencimiento:</label>
                                                    <div class="input-group date">
                                                        <span class="input-group-addon">
                                                            <i class="fa fa-calendar"></i>
                                                        </span>
                                                        <input type="text" id="fecha_vencimiento" name="fecha_vencimiento" class="form-control"  autocomplete="off" readonly>
                                                        <div class="invalid-feedback"><b><span id="error-fecha_vencimiento"></span></b></div>

                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="required">Lote:</label>
                                                    <input type="text" id="lote" name="lote" class="form-control" onkeypress="return mayus(this);">
                                                    <div class="invalid-feedback"><b><span id="error-lote"></span></b></div>
                                                </div>
                                            </div>



                                            <div class="form-group row">
                                                <div class="col-lg-6 col-xs-12">
                                                    <label class="col-form-label" for="amount">&nbsp;</label> <a class="btn btn-block btn-success " onclick="limpiarDetalle()" style='color:white;'> <i class="fa fa-paint-brush"></i> LIMPIAR</a>
                                                </div>

                                                <div class="col-lg-6 col-xs-12">
                                                    <label class="col-form-label" for="amount">&nbsp;</label>
                                                    <button class="btn btn-block btn-warning enviar_producto" style='color:white;' id="btn_enviar_detalle"> <i class="fa fa-plus"></i> AGREGAR</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="table-responsive">
                                        <table
                                            class="table dataTables-orden-detalle table-striped table-bordered table-hover"
                                            style="text-transform:uppercase">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th class="text-center">ACCIONES</th>
                                                    <th class="text-center">CANTIDAD</th>
                                                    <th class="text-center">PRODUCTO</th>
                                                    <th class="text-center">FECHA. VENC</th>
                                                    <th class="text-center">COSTO FLETE</th>
                                                    <th class="text-center">PRECIO</th>
                                                    <th class="text-center">TOTAL</th>

                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                            <tfoot style="text-transform:uppercase">
                                                <tr>
                                                    <th colspan="7" style="text-align:right">Sub Total:</th>
                                                    <th class="text-center"><span id="subtotal">0.0</span></th>

                                                </tr>
                                                <tr>
                                                    <th colspan="7" class="text-center">IGV <span
                                                            id="igv_int"></span>:</th>
                                                    <th class="text-center"><span id="igv_monto"></span></th>

                                                </tr>
                                                <tr>
                                                    <th colspan="7" class="text-center">Percepcion:</th>
                                                    <th class="text-center">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" value="{{old('monto_percepcion',$documento->percepcion)}}" id="percepcion" onkeypress="return filterFloat(event, this, false);">
                                                        </div>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="7" class="text-center">TOTAL:</th>
                                                    <th class="text-center"><span id="total"></span></th>

                                                </tr>
                                            </tfoot>
                                        </table>
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
                            <a href="{{route('compras.orden.index')}}" id="btn_cancelar"
                                class="btn btn-w-m btn-default">
                                <i class="fa fa-arrow-left"></i> Regresar
                            </a>
                            <button type="submit" id="btn_grabar" form="enviar_orden" class="btn btn-w-m btn-primary">
                                <i class="fa fa-save"></i> Grabar
                            </button>
                        </div>

                    </div>

                </div>


            </div>
        </div>

    </div>
</div>
--}}

{{-- @include('compras.documentos.modalEdit'); --}}

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
@endpush
