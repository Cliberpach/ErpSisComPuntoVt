<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte Caja Movimiento</title>
    <link rel="icon" href="{{ base_path() . '/img/siscom.ico' }}" />
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: black;
        }

        .cabecera {
            width: 100%;
            position: relative;
            height: 100px;
            max-height: 150px;
        }

        .logo {
            width: 30%;
            position: absolute;
            left: 0%;
        }

        .logo .logo-img {
            position: relative;
            width: 95%;
            margin-right: 5%;
            height: 90px;
        }

        .img-fluid {
            width: 100%;
            height: 100%;
        }

        .empresa {
            width: 60%;
            position: absolute;
            left: 30%;
        }

        .empresa .empresa-info {
            position: relative;
            width: 100%;
        }

        .nombre-empresa {
            font-size: 16px;
        }

        .ruc-empresa {
            font-size: 15px;
        }

        .direccion-empresa {
            font-size: 12px;
        }

        .text-info-empresa {
            font-size: 12px;
        }

        .comprobante {
            width: 30%;
            position: absolute;
            left: 70%;
        }

        .comprobante .comprobante-info {
            position: relative;
            width: 100%;
            display: flex;
            align-content: center;
            align-items: center;
            text-align: center;
        }

        .numero-documento {
            margin: 1px;
            padding-top: 20px;
            padding-bottom: 20px;
            border: 2px solid #52BE80;
            font-size: 14px;
        }

        .nombre-documento {
            margin-top: 5px;
            margin-bottom: 5px;
            margin-left: 0px;
            margin-right: 0px;
            width: 100%;
            background-color: #7DCEA0;
        }

        .logos-empresas {
            width: 100%;
            height: 105px;
        }

        .img-logo {
            width: 95%;
            height: 100px;
        }

        .logo-empresa {
            width: 14.2%;
            float: left;
        }

        .informacion {
            width: 100%;
            position: relative;
            border: 2px solid #52BE80;
        }

        .tbl-informacion {
            width: 100%;
            font-size: 12px;
        }

        .cuerpo {
            width: 100%;
            position: relative;
            border: 1px solid #52BE80;
            margin-top: 10px;
        }

        .tbl-detalles {
            width: 100%;
            font-size: 12px;
        }

        .tbl-detalles thead {
            border-top: 2px solid #52BE80;
            border-left: 2px solid #52BE80;
            border-right: 2px solid #52BE80;
        }

        .tbl-detalles tbody {
            border-top: 2px solid #52BE80;
            border-bottom: 2px solid #52BE80;
            border-left: 2px solid #52BE80;
            border-right: 2px solid #52BE80;
        }

        .info-total-qr {
            position: relative;
            width: 100%;
        }

        .tbl-total {
            width: 100%;
            border: 2px solid #229954;
        }

        .qr-img {
            margin-top: 15px;
        }

        .text-cuerpo {
            font-size: 12px
        }

        .tbl-qr {
            width: 100%;
        }

        /*---------------------------------------------*/

        .m-0 {
            margin: 0;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .p-0 {
            padding: 0;
        }

        .cont-check{
            position: relative;
        }

        .checkmark {
            display:inline-block;
            width: 22px;
            height:22px;
            -ms-transform: rotate(45deg); /* IE 9 */
            -webkit-transform: rotate(45deg); /* Chrome, Safari, Opera */
            transform: rotate(45deg);
        }

        .checkmark_stem {
            position: absolute;
            width:3px;
            height:12px;
            background-color:#229954;
            left:11px;
            top:6px;
        }

        .checkmark_kick {
            position: absolute;
            width:3px;
            height:3px;
            background-color:#229954;
            left:8px;
            top:15px;
        }

        .cont-remove{
            position: relative;
        }

        .remove {
            display:inline-block;
            width: 22px;
            height:22px;
            -ms-transform: rotate(45deg); /* IE 9 */
            -webkit-transform: rotate(45deg); /* Chrome, Safari, Opera */
            transform: rotate(45deg);
        }

        .remove_stem {
            position: absolute;
            width:3px;
            height:12px;
            background-color: brown;
            left:11px;
            top:6px;
        }

        .remove_kick {
            position: absolute;
            width:12px;
            height:3px;
            background-color:brown;
            left:7px;
            top:10px;
        }

    </style>
</head>

<body>
    <div class="cabecera">
        <div class="logo">
            <div class="logo-img">
                @if ($empresa->ruta_logo)
                    <img src="{{ base_path() . '/storage/app/' . $empresa->ruta_logo }}" class="img-fluid">
                @else
                    <img src="{{ public_path() . '/img/default.png' }}" class="img-fluid">
                @endif
            </div>
        </div>
        <div class="empresa">
            <div class="empresa-info">
                <p class="m-0 p-0 text-uppercase nombre-empresa">
                    {{ DB::table('empresas')->count() == 0 ? 'SISCOM ' : DB::table('empresas')->first()->razon_social }}
                </p>
                <p class="m-0 p-0 text-uppercase direccion-empresa">
                    {{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->direccion_fiscal }}
                </p>

                <p class="m-0 p-0 text-info-empresa">Central telefónica:
                    {{ DB::table('empresas')->count() == 0 ? '-' : DB::table('empresas')->first()->telefono }}</p>
                <p class="m-0 p-0 text-info-empresa">Email:
                    {{ DB::table('empresas')->count() == 0 ? '-' : DB::table('empresas')->first()->correo }}</p>
            </div>
        </div>

    </div><br>
    <div class="informacion">
        <table class="tbl-informacion">
            <tbody style="padding-top: 5px; padding-bottom: 5px;">
                <tr>
                    <td style="padding-left: 5px;">CAJA</td>
                    <td>:</td>
                    <td>{{ $movimiento->caja->nombre }}</td>
                    {{-- <td>{{ getFechaFormato( $documento->fecha_documento ,'d/m/Y')}}</td> --}}
                </tr>
                <tr>
                    <td style="padding-left: 5px;">Colaborador</td>
                    <td>:</td>
                    <td>{{ $movimiento->colaborador->persona->apellido_paterno . ' ' . $movimiento->colaborador->persona->apellido_materno . ' ' . $movimiento->Colaborador->persona->nombres }}
                    </td>
                    {{-- <td>{{ getFechaFormato( $documento->fecha_documento ,'d/m/Y')}}</td> --}}
                </tr>
                <tr>
                    <td style="padding-left: 5px;">Turno</td>
                    <td>:</td>
                    <td>{{ 'MAÑANA' }}</td>
                    {{-- <td>{{ getFechaFormato( $documento->fecha_documento ,'d/m/Y')}}</td> --}}
                </tr>
                <tr>
                    <td style="padding-left: 5px;">Monto Inicial</td>
                    <td>:</td>
                    <td>{{ $movimiento->monto_inicial }}</td>
                    {{-- <td>{{ getFechaFormato( $documento->fecha_documento ,'d/m/Y')}}</td> --}}
                </tr>
                <tr>
                    <td style="padding-left: 5px;">Fecha</td>
                    <td>:</td>
                    <td>{{ date_format($movimiento->created_at, 'Y/m/d') }}</td>
                </tr>
            </tbody>
        </table>
    </div><br>
    <span style="text-transform: uppercase;font-size:15px">VENTAS</span>
    <br>
    <div class="cuerpo">
        <table class="tbl-detalles text-uppercase" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th style="text-align: center;border-right: 2px solid #52BE80">NUMERO</th>
                    <th style="text-align: center; border-right: 2px solid #52BE80">CLIENTE</th>
                    <th style="text-align: center; border-right: 2px solid #52BE80">DEV</th>
                    <th style="text-align: center; border-right: 2px solid #52BE80">MONTO</th>
                    @php
                        $cont = 0;
                        while($cont < count(tipos_pago()))
                        {
                            if($cont == count(tipos_pago()) - 1)
                            {
                                echo '<th style="text-align: center;">'.tipos_pago()[$cont]->descripcion.'</th>';
                            }
                            else {
                                echo '<th style="text-align: center; border-right: 2px solid #52BE80">'.tipos_pago()[$cont]->descripcion.'</th>';
                            }
                            $cont++;
                        }
                    @endphp
                </tr>
            </thead>
            <tbody>
                @foreach ($movimiento->detalleMovimientoVentas as $ventas)
                {{-- $ventas->documento->sunat != '2' &&  --}}
                    @if ($ventas->documento->condicion_id == 1 && $ventas->documento->estado_pago == 'PAGADA' && ifNoConvertido($ventas->documento->id))
                        <tr>
                            <td style="text-align: center; border-right: 2px solid #52BE80">
                                {{ $ventas->documento->serie . '-' . $ventas->documento->correlativo }}</td>
                            <td style="text-align: center; border-right: 2px solid #52BE80">
                                {{ $ventas->documento->clienteEntidad->nombre }}</td>
                            <td style="text-align: center; border-right: 2px solid #52BE80;">
                                @if (count($ventas->documento->notas) > 0)
                                <div class="cont-check">
                                    <span class="checkmark">
                                        <div class="checkmark_stem"></div>
                                        <div class="checkmark_kick"></div>
                                    </span>
                                </div>
                                @else
                                <div class="cont-remove">
                                    <span class="remove">
                                        <div class="remove_stem"></div>
                                        <div class="remove_kick"></div>
                                    </span>
                                </div>
                                @endif
                            </td>
                            <td style="text-align: center; border-right: 2px solid #52BE80">
                                {{ $ventas->documento->total }}
                            </td>
                            @foreach(tipos_pago() as $tipo)
                                @if($tipo->id == 1)
                                    @if($tipo->id == $ventas->documento->tipo_pago_id)
                                        <td style="text-align: center; border-right: 2px solid #52BE80">{{$ventas->documento->importe}}</td>';
                                    @else
                                        <td style="text-align: center; border-right: 2px solid #52BE80">{{$ventas->documento->efectivo}}</td>';
                                    @endif
                                @else
                                    @if($tipo->id == $ventas->documento->tipo_pago_id)
                                        <td style="text-align: center; border-right: 2px solid #52BE80">{{$ventas->documento->importe}}</td>';
                                    @else
                                        <td style="text-align: center; border-right: 2px solid #52BE80">0.00</td>';
                                    @endif
                                @endif
                            @endforeach
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <td colspan="4" style="text-align: center; border-right: 2px solid #52BE80; border-top: 2px solid #52BE80">TOTAL</td>
                    @foreach (tipos_pago() as $tipo)
                    <td style="text-align: center; border-right: 2px solid #52BE80; border-top: 2px solid #52BE80">{{ number_format(cuadreMovimientoCajaIngresosVentaResum($movimiento,$tipo->id), 2) }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div><br>
    <span style="text-transform: uppercase;font-size:15px">COBRANZA CLIENTES</span>
    <br>
    <div class="cuerpo">
        <table class="tbl-detalles text-uppercase" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th style="text-align: center;border-right: 2px solid #52BE80">NUMERO</th>
                    <th style="text-align: center; border-right: 2px solid #52BE80">CLIENTE</th>
                    <th style="text-align: center; border-right: 2px solid #52BE80">MONTO</th>
                    @php
                        $cont = 0;
                        while($cont < count(tipos_pago()))
                        {
                            if($cont == count(tipos_pago()) - 1)
                            {
                                echo '<th style="text-align: center;">'.tipos_pago()[$cont]->descripcion.'</th>';
                            }
                            else {
                                echo '<th style="text-align: center; border-right: 2px solid #52BE80">'.tipos_pago()[$cont]->descripcion.'</th>';
                            }
                            $cont++;
                        }
                    @endphp
                </tr>
            </thead>
            <tbody>
                @foreach ($movimiento->detalleCuentaCliente as $cuentaCliente)
                    <tr>
                        <td style="text-align: center; border-right: 2px solid #52BE80">
                            {{ $cuentaCliente->cuenta_cliente->documento->serie . '-' . $cuentaCliente->cuenta_cliente->documento->correlativo }}</td>
                        <td style="text-align: center; border-right: 2px solid #52BE80">
                            {{ $cuentaCliente->cuenta_cliente->documento->clienteEntidad->nombre }}</td>
                        <td style="text-align: center; border-right: 2px solid #52BE80">
                            {{ $cuentaCliente->cuenta_cliente->monto }}
                        </td>
                        @foreach(tipos_pago() as $tipo)
                            @if($tipo->id == 1)
                                @if($tipo->id == $cuentaCliente->tipo_pago_id)
                                    <td style="text-align: center; border-right: 2px solid #52BE80">{{$cuentaCliente->efectivo}}</td>';
                                @else
                                    <td style="text-align: center; border-right: 2px solid #52BE80">{{$cuentaCliente->efectivo}}</td>';
                                @endif
                            @else
                                @if($tipo->id == $cuentaCliente->tipo_pago_id)
                                    <td style="text-align: center; border-right: 2px solid #52BE80">{{$cuentaCliente->importe}}</td>';
                                @else
                                    <td style="text-align: center; border-right: 2px solid #52BE80">0.00</td>';
                                @endif
                            @endif
                        @endforeach
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" style="text-align: center; border-right: 2px solid #52BE80; border-top: 2px solid #52BE80">TOTAL</td>
                    @foreach (tipos_pago() as $tipo)
                    <td style="text-align: center; border-right: 2px solid #52BE80; border-top: 2px solid #52BE80">{{ number_format(cuadreMovimientoCajaIngresosCobranzaResum($movimiento,$tipo->id), 2) }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    <br>
    <span style="text-transform: uppercase;font-size:15px">EGRESOS POR CAJA</span>
    <br>
    <div class="cuerpo">
        <table class="tbl-detalles text-uppercase" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th style="text-align: center; border-right: 2px solid #52BE80;">ID RECIBO </th>
                    <th style="text-align: center;border-right: 2px solid #52BE80">DESCRIPCION</th>
                    <th style="text-align: center; border-right: 2px solid #52BE80">IMPORTE</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($movimiento->detalleMoviemientoEgresos as $detalleEgreso)
                    <tr>
                        <td style="text-align: center; border-right: 2px solid #52BE80">
                            {{ $detalleEgreso->egreso->documento }}</td>
                        <td style="text-align: center; border-right: 2px solid #52BE80">
                            {{ $detalleEgreso->egreso->descripcion }}</td>
                        <td style="text-align: center; border-right: 2px solid #52BE80">
                            {{ $detalleEgreso->egreso->importe }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2" style="text-align: center; border-right: 2px solid #52BE80; border-top: 2px solid #52BE80">TOTAL</td>
                    <td style="text-align: center; border-right: 2px solid #52BE80; border-top: 2px solid #52BE80">
                        {{ number_format(cuadreMovimientoCajaEgresosEgreso($movimiento), 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <br>
    <span style="text-transform: uppercase;font-size:15px">PAGOS PROVEEDORES</span>
    <br>
    <div class="cuerpo">
        <table class="tbl-detalles text-uppercase" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th style="text-align: center; border-right: 2px solid #52BE80;">TIPO DOC</th>
                    <th style="text-align: center;border-right: 2px solid #52BE80">NUMERO</th>
                    <th style="text-align: center; border-right: 2px solid #52BE80">CLIENTE</th>
                    <th style="text-align: center; border-right: 2px solid #52BE80">MONTO</th>
                    @php
                        $cont = 0;
                        while($cont < count(tipos_pago()))
                        {
                            if($cont == count(tipos_pago()) - 1)
                            {
                                echo '<th style="text-align: center;">'.tipos_pago()[$cont]->descripcion.'</th>';
                            }
                            else {
                                echo '<th style="text-align: center; border-right: 2px solid #52BE80">'.tipos_pago()[$cont]->descripcion.'</th>';
                            }
                            $cont++;
                        }
                    @endphp
                </tr>
            </thead>
            <tbody>
                @foreach ($movimiento->detalleCuentaProveedor as $detalleProveedor)
                <tr>
                    <td style="text-align: center; border-right: 2px solid #52BE80">
                        {{ $detalleProveedor->cuenta_proveedor->documento->tipo_compra }}</td>
                    <td style="text-align: center; border-right: 2px solid #52BE80">
                        {{ $detalleProveedor->cuenta_proveedor->documento->serie_tipo.' - '.$detalleProveedor->cuenta_proveedor->documento->numero_tipo }}</td>
                    <td style="text-align: center; border-right: 2px solid #52BE80">
                        {{ $detalleProveedor->cuenta_proveedor->documento->proveedor->descripcion }}</td>
                    <td style="text-align: center; border-right: 2px solid #52BE80">
                        {{ $detalleProveedor->efectivo + $detalleProveedor->importe }}
                    </td>
                    @foreach(tipos_pago() as $tipo)
                        @if($tipo->id == 1)
                            @if($tipo->id == $detalleProveedor->tipo_pago_id)
                                <td style="text-align: center; border-right: 2px solid #52BE80">{{$detalleProveedor->efectivo}}</td>';
                            @else
                                <td style="text-align: center; border-right: 2px solid #52BE80">{{$detalleProveedor->efectivo}}</td>';
                            @endif
                        @else
                            @if($tipo->id == $detalleProveedor->tipo_pago_id)
                                <td style="text-align: center; border-right: 2px solid #52BE80">{{$detalleProveedor->importe}}</td>';
                            @else
                                <td style="text-align: center; border-right: 2px solid #52BE80">0.00</td>';
                            @endif
                        @endif
                    @endforeach
                </tr>
                @endforeach
                <tr>
                    <td colspan="4" style="text-align: center; border-top: 2px solid #52BE80 ;border-right: 2px solid #52BE80">TOTAL</td>
                    @foreach (tipos_pago() as $tipo)
                    <td style="text-align: center;border-top: 2px solid #52BE80 ;border-right: 2px solid #52BE80">{{ number_format(cuadreMovimientoCajaEgresosPagoResum($movimiento, $tipo->id), 2) }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div><br>
    <div class="info-total-qr">
        <table class="tbl-qr">
            <tr>
                <td style="width: 100%;">
                    <table class="tbl-total text-uppercase">
                        <thead style="background-color: #52BE80; color: white;">
                            <tr>
                                <th style="text-align:center; padding: 5px;" colspan="2">DETALLES EXTRAS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align:left; padding: 5px;">
                                    <p class="m-0 p-0">Total ingresos ventas:</p>
                                </td>
                                <td style="text-align:right; padding: 5px;">
                                    <p class="p-0 m-0">{{ number_format(cuadreMovimientoCajaIngresosVenta($movimiento), 2) }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:left; padding: 5px;">
                                    <p class="m-0 p-0">Total egresos por caja:</p>
                                </td>
                                <td style="text-align:right; padding: 5px;">
                                    <p class="p-0 m-0">{{ number_format(cuadreMovimientoCajaEgresosEgreso($movimiento), 2) }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:left; padding: 5px;">
                                    <p class="m-0 p-0">Cobranza:</p>
                                </td>
                                <td style="text-align:right; padding: 5px;">
                                    <p class="p-0 m-0">{{ number_format(cuadreMovimientoCajaIngresosCobranza($movimiento), 2) }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:left; padding: 5px;">
                                    <p class="m-0 p-0">Pagos:</p>
                                </td>
                                <td style="text-align:right; padding: 5px;">
                                    <p class="p-0 m-0">{{ number_format(cuadreMovimientoCajaEgresosPago($movimiento), 2) }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
        <br>
        <table class="tbl-qr">
            <tr>
                <td style="width: 100%;">
                    <table class="tbl-total text-uppercase">
                        <thead style="background-color: #52BE80; color: white;">
                            <tr>
                                <th style="text-align:center; padding: 5px;" colspan="2">CUADRE CAJA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align:left; padding: 5px;">
                                    <p class="m-0 p-0">Monto inicial:</p>
                                </td>
                                <td style="text-align:right; padding: 5px;">
                                    <p class="p-0 m-0">{{ number_format($movimiento->monto_inicial, 2) }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:left; padding: 5px;">
                                    <p class="p-0 m-0">Ingresos:</p>
                                </td>
                                <td style="text-align:right; padding: 5px;">
                                    <p class="p-0 m-0">
                                        {{ number_format(cuadreMovimientoCajaIngresosCuadreEfectivo($movimiento), 2) }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:left; padding: 5px;">
                                    <p class="p-0 m-0">Egresos:</p>
                                </td>
                                <td style="text-align:right; padding: 5px;">
                                    <p class="p-0 m-0">
                                        {{ number_format(cuadreMovimientoCajaEgresosCuadreEfectivo($movimiento), 2) }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:left; padding: 5px;">
                                    <p class="p-0 m-0">Saldo Caja:</p>
                                </td>
                                <td style="text-align:right; padding: 5px;">
                                    <p class="p-0 m-0">
                                        {{ number_format($movimiento->monto_inicial + cuadreMovimientoCajaIngresosCuadreEfectivo($movimiento) - cuadreMovimientoCajaEgresosCuadreEfectivo($movimiento), 2) }}
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
        <br>
    </div>
</body>

</html>
