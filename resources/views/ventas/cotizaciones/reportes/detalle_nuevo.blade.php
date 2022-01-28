<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>COTIZACION</title>
        <link rel="icon" href="{{ base_path() . '/img/siscom.ico' }}" />
        <style>
            body {
                font-family: Arial, Helvetica, sans-serif;
                color: black;
            }

            .cabecera{
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

            .logo .logo-img
            {
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
                width: 40%;
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

            .numero-cotizacion {
                margin: 1px;
                padding-top: 20px;
                padding-bottom: 20px;
                border: 2px solid #52BE80;
                font-size: 14px;
            }

            .nombre-cotizacion{
                margin-top: 5px;
                margin-bottom: 5px;
                margin-left: 0px;
                margin-right: 0px;
                width: 100%;
                background-color: #7DCEA0 ;
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

            .informacion{
                width: 100%;
                position: relative;
                border: 2px solid #52BE80;
            }

            .tbl-informacion {
                width: 100%;
                font-size: 12px;
            }

            .cuerpo{
                width: 100%;
                position: relative;
                border: 1px solid red;
            }

            .tbl-detalles {
                width: 100%;
                font-size: 12px;
            }

            .tbl-detalles thead{
                border-top: 2px solid #52BE80;
                border-left: 2px solid #52BE80;
                border-right: 2px solid #52BE80;
            }

            .tbl-detalles tbody{
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

            .text-cuerpo{
                font-size: 12px
            }

            .tbl-qr {
                width: 100%;
            }
            /*---------------------------------------------*/

            .m-0{
                margin:0;
            }

            .text-uppercase {
                text-transform: uppercase;
            }

            .p-0{
                padding:0;
            }
        </style>
    </head>

    <body>
        <div class="cabecera">
            <div class="logo">
                <div class="logo-img">
                    <img src="{{ base_path() . '/storage/app/'.$empresa->ruta_logo }}" class="img-fluid">
                </div>
            </div>
            <div class="empresa">
                <div class="empresa-info">
                    <p class="m-0 p-0 text-uppercase nombre-empresa">{{ DB::table('empresas')->count() == 0 ? 'SISCOM ' : DB::table('empresas')->first()->razon_social }}</p>
                    <p class="m-0 p-0 text-uppercase direccion-empresa">{{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->direccion_fiscal }}</p>

                    <p class="m-0 p-0 text-info-empresa">Central telefónica: {{ DB::table('empresas')->count() == 0 ? '-' : DB::table('empresas')->first()->telefono }}</p>
                    <p class="m-0 p-0 text-info-empresa">Email: {{ DB::table('empresas')->count() == 0 ? '-' : DB::table('empresas')->first()->correo }}</p>
                </div>
            </div>
            <div class="comprobante">
                <div class="comprobante-info">
                    <div class="numero-cotizacion">
                        <p class="m-0 p-0 text-uppercase ruc-empresa">RUC {{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->ruc }}</p>
                        <div class="nombre-cotizacion">
                            <p class="m-0 p-0 text-uppercase">COTIZACION</p>
                        </div>
                        <p class="m-0 p-0 text-uppercase">{{ 'CO-'.$cotizacion->id}}</p>
                    </div>
                </div>
            </div>
        </div><br>
        @if ($empresa->condicion == 1)
        <div class="logos-empresas">
            <div class="logo-empresa">
                <img src="{{ public_path() . '/img/cifarelli_1.jpg' }}" class="img-logo">
            </div>
            <div class="logo-empresa">
                <img src="{{ public_path() . '/img/motor.png' }}" class="img-logo">
            </div>
            <div class="logo-empresa">
                <img src="{{ public_path() . '/img/motosierra.jpg' }}" class="img-logo">
            </div>
            <div class="logo-empresa">
                <img src="{{ public_path() . '/img/mochila.jpg' }}" class="img-logo">
            </div>
            <div class="logo-empresa">
                <img src="{{ public_path() . '/img/mochila_jacto.jpg' }}" class="img-logo">
            </div>
            <div class="logo-empresa">
                <img src="{{ public_path() . '/img/filtro.jpg' }}" class="img-logo">
            </div>
            <div class="logo-empresa">
                <img src="{{ public_path() . '/img/llaves.jpg' }}" class="img-logo">
            </div>
        </div><br>
        @endif
        <div class="informacion">
            <table class="tbl-informacion">
                <tbody style="padding-top: 5px; padding-bottom: 5px;">
                    <tr>
                        <td style="padding-left: 5px;">FECHA DE DOCUMENTO</td>
                        <td>:</td>
                        <td>{{ getFechaFormato( $cotizacion->fecha_documento ,'d/m/Y')}}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 5px;">FECHA DE ATENCION</td>
                        <td>:</td>
                        <td>{{ getFechaFormato( $cotizacion->fecha_atencion ,'d/m/Y')}}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 5px;">CLIENTE</td>
                        <td>:</td>
                        <td>{{ $cotizacion->cliente->nombre }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 5px;" class="text-uppercase">{{ $cotizacion->cliente->tipo_documento }}</td>
                        <td>:</td>
                        <td>{{ $cotizacion->cliente->documento }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 5px;">DIRECCIÓN</td>
                        <td>:</td>
                        <td>{{ $cotizacion->cliente->direccion }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 5px;">CORREO</td>
                        <td>:</td>
                        <td class="text-uppercase">{{ $cotizacion->cliente->correo_electronico }}</td>
                    </tr>
                </tbody>
            </table>
        </div><br>
        <div class="cuerpo">
            <table class="tbl-detalles text-uppercase" cellpadding="8" cellspacing="0">
                <thead>
                    <tr >
                        <th style="text-align: center; border-right: 2px solid #52BE80; width: 10%;">CANT</th>
                        <th style="text-align: center;border-right: 2px solid #52BE80; width: 10%;">UM</th>
                        <th style="text-align: center; border-right: 2px solid #52BE80; width: 60%;">DESCRIPCIÓN</th>
                        <th style="text-align: center; border-right: 2px solid #52BE80; width: 10%;">P. UNIT.</th>
                        <th style="text-align: right; width: 10%;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detalles as $item)
                    <tr>
                        <td style="text-align: center; border-right: 2px solid #52BE80">{{ $item->cantidad }}</td>
                        <td style="text-align: center; border-right: 2px solid #52BE80">{{ $item->producto->tabladetalle->simbolo }}</td>
                        <td style="text-align: left; border-right: 2px solid #52BE80">{{ $item->producto->codigo . ' - ' . $item->producto->nombre }}</td>
                        <td style="text-align: center; border-right: 2px solid #52BE80">{{ $item->precio_nuevo }}</td>
                        <td style="text-align: right">{{ $item->valor_venta }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div><br>
        <div class="info-total-qr">
            <table class="tbl-qr">
                <tr>
                    <td style="width: 60%">
                        <table class="tbl-qr">
                            <tr>
                                <td>
                                    @foreach($empresa->bancos as $banco)
                                        <p class="m-0 p-0 text-cuerpo"><b class="text-uppercase">{{ $banco->descripcion}}</b> {{ $banco->tipo_moneda}} <b>N°: </b> {{ $banco->num_cuenta}} <b>CCI:</b> {{ $banco->cci}}</p>
                                    @endforeach
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 40%;">
                        <table class="tbl-total text-uppercase">
                            <tr>
                                <td style="text-align:left; padding: 5px;"><p class="p-0 m-0">Total: S/.</p></td>
                                <td style="text-align:right; padding: 5px;"><p class="p-0 m-0">{{ number_format($cotizacion->total, 2) }}</p></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
