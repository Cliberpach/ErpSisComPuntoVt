<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>{{ $documento->nombreDocumento() }}</title>
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
                width: 25%;
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
                width: 45%;
                position: absolute;
                left: 25%;
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
                border: 1px solid #8f8f8f;
                font-size: 14px;
            }

            .informacion{
                width: 100%;
                position: relative;
            }

            .tbl-informacion {
                width: 100%;
                font-size: 12px;
            }

            .cuerpo{
                width: 100%;
                position: relative;
            }

            .tbl-detalles {
                width: 100%;
                font-size: 12px;
            }

            .tbl-detalles thead{
                border-top: 1px solid;
                background-color: rgb(241, 239, 239);
            }

            .tbl-detalles tbody{
                border-top: 1px solid;
                border-bottom: 1px solid;
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
                    @if($empresa->ruta_logo)
                    <img src="{{ base_path() . '/storage/app/'.$empresa->ruta_logo }}" class="img-fluid">
                    @else
                    <img src="{{ public_path() . '/img/default.png' }}" class="img-fluid">
                    @endif
                </div>
            </div>
            <div class="empresa">
                <div class="empresa-info">
                    <p class="m-0 p-0 text-uppercase nombre-empresa">{{ DB::table('empresas')->count() == 0 ? 'SISCOM ' : DB::table('empresas')->first()->razon_social }}</p>
                    <p class="m-0 p-0 text-uppercase ruc-empresa">RUC {{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->ruc }}</p>
                    <p class="m-0 p-0 text-uppercase direccion-empresa">{{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->direccion_fiscal }}</p>

                    <p class="m-0 p-0 text-info-empresa">Central telefónica: {{ DB::table('empresas')->count() == 0 ? '-' : DB::table('empresas')->first()->celular }}</p>
                    <p class="m-0 p-0 text-info-empresa">Email: {{ DB::table('empresas')->count() == 0 ? '-' : DB::table('empresas')->first()->correo }}</p>
                </div>
            </div>
            <div class="comprobante">
                <div class="comprobante-info">
                    <div class="numero-documento">
                        <p class="m-0 p-0 text-uppercase">{{ $documento->nombreDocumento() }}</p>
                        <p class="m-0 p-0 text-uppercase">{{$documento->serie.'-'.$documento->correlativo}}</p>
                    </div>
                </div>
            </div>
        </div><br>
        <div class="informacion">
            <table class="tbl-informacion">
                <tr>
                    <td>FECHA DE EMISIÓN</td>
                    <td>:</td>
                    <td>{{ getFechaFormato( $documento->fecha_documento ,'d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>FECHA DE VENCIMIENTO</td>
                    <td>:</td>
                    <td>{{ getFechaFormato( $documento->fecha_vencimiento ,'d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>CLIENTE</td>
                    <td>:</td>
                    <td>{{ $documento->clienteEntidad->nombre }}</td>
                </tr>
                <tr>
                    <td class="text-uppercase">{{ $documento->tipo_documento_cliente}}</td>
                    <td>:</td>
                    <td>{{ $documento->clienteEntidad->documento }}</td>
                </tr>
                <tr>
                    <td>DIRECCIÓN</td>
                    <td>:</td>
                    <td>{{ $documento->clienteEntidad->direccion }}</td>
                </tr>
                <tr>
                    <td>MODO DE PAGO</td>
                    <td>:</td>
                    <td class="text-uppercase">{{ $documento->formaPago() }}</td>
                </tr>
            </table>
        </div><br>
        <div class="cuerpo">
            <table class="tbl-detalles text-uppercase" cellpadding="5" cellspacing="0">
                <thead>
                    <tr >
                        <th style="text-align: left; width: 10%;">CANT</th>
                        <th style="text-align: left;  width: 60%;">DESCRIPCIÓN</th>
                        <th style="text-align: left;  width: 10%;">P. UNIT.</th>
                        <th style="text-align: left;  width: 10%;">DESC.</th>
                        <th style="text-align: right;  width: 10%;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detalles as $item)
                    <tr>
                        <td style="text-align: left">{{ $item->cantidad }}</td>
                        <td style="text-align: left">{{ $item->nombre_producto }}</td>
                        <td style="text-align: left">{{ $item->precio_unitario }}</td>
                        <td style="text-align: left">{{ $item->dinero }}</td>
                        <td style="text-align: right">{{ $item->valor_venta }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @if($documento->tipo_venta != 129)
                    <tr>
                        <th colspan="3" hidden></th>
                        <th style="text-align:right">Sub Total: S/.</th>
                        <th style="text-align:right">{{ number_format($documento->sub_total, 2) }}</th>
                    </tr>
                    <tr>
                        <th colspan="3" hidden></th>
                        <th style="text-align:right">IGV {{ $documento->igv }}: S/.</th>
                        <th style="text-align:right">{{ number_format($documento->total_igv, 2) }}</th>
                    </tr>
                    @endif
                    <tr>
                        <th colspan="3" hidden></th>
                        <th style="text-align:right">Total a pagar: S/.</th>
                        <th style="text-align:right">{{ number_format($documento->total, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
            <br>
            <p class="p-0 m-0 text-uppercase text-cuerpo">SON: <b>{{ $legends[0]['value'] }}</b></p>
            <br>
            <table class="tbl-qr">
                <tr>
                    <td style="width: 60%">
                        @foreach($empresa->bancos as $banco)
                            <p class="m-0 p-0 text-cuerpo"><b class="text-uppercase">{{ $banco->descripcion}}</b> {{ $banco->tipo_moneda}} <b>N°: </b> {{ $banco->num_cuenta}} <b>CCI:</b> {{ $banco->cci}}</p>
                        @endforeach
                    </td>
                    <td style="text-align: right;">
                        @if($documento->ruta_qr)
                            <img src="{{ base_path() . '/storage/app/'.$documento->ruta_qr }}">
                        @endif
                        @if($documento->hash)
                            <p class="m-0 p-0" style="font-size: 9px;">Código Hash: {{ $documento->hash }}</p>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
