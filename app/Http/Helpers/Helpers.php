<?php

use App\Compras\CuentaProveedor;
use App\Mantenimiento\Tabla\General;
use App\Mantenimiento\Ubigeo\Departamento;
use App\Mantenimiento\Ubigeo\Distrito;
use App\Mantenimiento\Ubigeo\Provincia;
// use App\Parametro;
use Carbon\Carbon;
//Orden de compra
use App\Compras\Documento\Detalle as Detalle_Documento;
use App\Compras\Detalle;
use App\Compras\Orden;
use App\Compras\Documento\Documento;
use App\Compras\Proveedor;
use App\Mantenimiento\Colaborador\Colaborador;
//Bitacora de actividades
use Spatie\Activitylog\Contracts\Activity;
use Illuminate\Support\Facades\Auth;

//Facturacion Electronica
use Illuminate\Support\Facades\Http;
use App\Mantenimiento\Parametro\Parametro;
use GuzzleHttp\Client;
use App\Mantenimiento\Empresa\Facturacion;
use App\Mantenimiento\Vendedor\Vendedor;
use App\Pos\Caja;
use App\Pos\MovimientoCaja;
use App\Ventas\Cliente;
use App\Ventas\TipoPago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mantenimiento\Tabla\Detalle as TablaDetalle;
use App\Ventas\CuentaCliente;
use App\Ventas\Documento\Detalle as DocumentoDetalle;
use App\Ventas\Documento\Documento as DocumentoDocumento;

// TABLAS-DETALLES

if (!function_exists('tipos_moneda')) {
    function tipos_moneda()
    {
        return General::find(1)->detalles;
    }
}

if (!function_exists('bancos')) {
    function bancos()
    {
        return General::find(2)->detalles;
    }
}

if (!function_exists('tipos_documento')) {
    function tipos_documento()
    {
        return General::find(3)->detalles;
    }
}

if (!function_exists('tipos_sexo')) {
    function tipos_sexo()
    {
        return General::find(4)->detalles;
    }
}

if (!function_exists('estados_civiles')) {
    function estados_civiles()
    {
        return General::find(5)->detalles;
    }
}

if (!function_exists('zonas')) {
    function zonas()
    {
        return General::find(6)->detalles;
    }
}

if (!function_exists('areas')) {
    function areas()
    {
        return General::find(7)->detalles;
    }
}

if (!function_exists('cargos')) {
    function cargos()
    {
        return General::find(8)->detalles;
    }
}

if (!function_exists('profesiones')) {
    function profesiones()
    {
        return General::find(9)->detalles;
    }
}

if (!function_exists('presentaciones')) {
    function presentaciones()
    {
        return General::find(10)->detalles;
    }
}

if (!function_exists('personas')) {
    function personas()
    {
        return General::find(11)->detalles;
    }
}

if (!function_exists('grupos_sanguineos')) {
    function grupos_sanguineos()
    {
        return General::find(12)->detalles;
    }
}

if (!function_exists('modo_compra')) {
    function modo_compra()
    {
        return General::find(14)->detalles;
    }
}

if (!function_exists('unidad_medida')) {
    function unidad_medida()
    {
        return General::find(13)->detalles;
    }
}

if (!function_exists('tipo_compra')) {
    function tipo_compra()
    {
        return General::find(16)->detalles;
    }
}

if (!function_exists('tipo_clientes')) {
    function tipo_clientes()
    {
        return General::find(17)->detalles;
    }
}

if (!function_exists('condicion_reparto')) {
    function condicion_reparto()
    {
        return General::find(18)->detalles;
    }
}

if (!function_exists('tipos_venta')) {
    function tipos_venta()
    {
        return General::find(21)->detalles;
    }
}

if (!function_exists('cod_motivos')) {
    function cod_motivos()
    {
        return TablaDetalle::where('tabla_id',33)->wherein('simbolo',['01','07'])->get();
    }
}

if (!function_exists('forma_pago')) {
    function forma_pago()
    {
        return General::find(30)->detalles;
    }
}

if (!function_exists('modos_pago')) {
    function modos_pago()
    {
        return TipoPago::where('estado', 'ACTIVO')->get();
    }
}

if (!function_exists('vendedores')) {
    function vendedores()
    {
        return Vendedor::cursor()->filter(function ($vendedor) {
            return $vendedor->persona->estado == "ACTIVO" ? true : false;
        });
    }
}

// DOCUMENTO VALIDO PARA NOTA DE CRÉDITO
if (!function_exists('docValido')) {
    function docValido($id)
    {
        $documento = DocumentoDocumento::find($id);
        $detalles = DocumentoDetalle::where('documento_id', $id)->get();
        $cont = 0 ;

        if($documento->sunat === '2')
        {
            return false;
        }

        foreach($detalles as $detalle)
        {
            if($detalle->cantidad === $detalle->detalles->sum('cantidad'))
            {
                $cont = $cont + 1;
            }
        }

        if(count($detalles) === $cont)
        {

            $documento->sunat = '2';
            $documento->update();
            return false;
        }
        else
        {
            return true;
        }
    }
}

// UBIGEO
if (!function_exists('departamentos')) {
    function departamentos($id = null)
    {
        if (is_null($id)) {
            return Departamento::all();
        } else {
            $departamento_id = str_pad($id, 2, "0", STR_PAD_LEFT);
            return Departamento::where('id', $id)->get();
        }
    }
}

if (!function_exists('provincias')) {
    function provincias($id = null)
    {
        if (is_null($id)) {
            return Provincia::all();
        } else {
            $provincia_id = str_pad($id, 4, "0", STR_PAD_LEFT);
            return Provincia::where('id', $provincia_id)->get();
        }
    }
}

if (!function_exists('getProvinciasByDepartamento')) {
    function getProvinciasByDepartamento($departamento_id)
    {
        if (is_null($departamento_id)) {
            return collect([]);
        } else {
            $departamento_id = str_pad($departamento_id, 2, "0", STR_PAD_LEFT);
            return Provincia::where('departamento_id', $departamento_id)->get();
        }
    }
}

if (!function_exists('distritos')) {
    function distritos($id = null)
    {
        if (is_null($id)) {
            return Distrito::all();
        } else {
            $distrito_id = str_pad($id, 6, "0", STR_PAD_LEFT);
            return Distrito::where('id', $distrito_id)->get();
        }
    }
}

if (!function_exists('getDistritosByProvincia')) {
    function getDistritosByProvincia($provincia_id)
    {
        if (is_null($provincia_id)) {
            return collect([]);
        } else {
            $provincia_id = str_pad($provincia_id, 4, "0", STR_PAD_LEFT);
            return Distrito::where('provincia_id', $provincia_id)->get();
        }
    }
}

//Consultas a la Api
if (!function_exists('consultaRuc')) {
    function consultaRuc()
    {
        return Parametro::findOrFail(1);
    }
}
if (!function_exists('consultaDni')) {
    function consultaDni()
    {
        return Parametro::findOrFail(2);
    }
}

if (!function_exists('getFechaFormato')) {
    function getFechaFormato($fecha, $formato)
    {
        if (is_null($fecha) || empty($fecha))
            return "-";

        $fecha_formato = Carbon::parse($fecha)->format($formato);
        return ($fecha_formato) ? $fecha_formato : $fecha;
    }
}

// Documento tributarios
if (!function_exists('tipos_documentos_tributarios')) {
    function tipos_documentos_tributarios()
    {
        return General::findOrFail(15)->detalles;
    }
}

// Tipos de pago caja chica
if (!function_exists('tipos_pago_caja')) {
    function tipos_pago_caja()
    {
        return General::findOrFail(19)->detalles;
    }
}

// Tipo: Maquina o equipo
if (!function_exists('tipos_maq_eq')) {
    function tipos_maq_eq()
    {
        return General::find(20)->detalles;
    }
}

// Tipo: tienda
if (!function_exists('tipos_tienda')) {
    function tipos_tienda()
    {
        return General::find(22)->detalles;
    }
}

// Tipo: tienda
if (!function_exists('tipos_negocio')) {
    function tipos_negocio()
    {
        return General::find(23)->detalles;
    }
}

// Modo: Responsable
if (!function_exists('modo_responsables')) {
    function modo_responsables()
    {
        return General::find(24)->detalles;
    }
}
//Linea Comercial
if (!function_exists('lineas_comerciales')) {
    function lineas_comerciales()
    {
        return General::find(25)->detalles;
    }
}


// Monto a Pagar Orden de compra
if (!function_exists('calcularMonto')) {
    function calcularMonto($id)
    {

        $detalles = Detalle::where('orden_id', $id)->get();
        $orden = Orden::findOrFail($id);
        $subtotal = 0;
        $igv = '';
        $tipo_moneda = '';
        foreach ($detalles as $detalle) {
            $subtotal = ($detalle->cantidad * $detalle->precio) + $subtotal;
        }

        if (!$orden->igv) {
            $igv = $subtotal * 0.18;
            $total = $subtotal + $igv;
            $decimal_subtotal = number_format($subtotal, 2, '.', '');
            $decimal_total = number_format($total, 2, '.', '');
            $decimal_igv = number_format($igv, 2, '.', '');
        } else {
            $calcularIgv = $orden->igv / 100;
            $base = $subtotal / (1 + $calcularIgv);
            $nuevo_igv = $subtotal - $base;
            $decimal_subtotal = number_format($base, 2, '.', '');
            $decimal_total = number_format($subtotal, 2, '.', '');
            $decimal_igv = number_format($nuevo_igv, 2, '.', '');
        }
        return $decimal_total;
    }
}

//Obtner Simbbolo de la moneda
if (!function_exists('simbolo_monedas')) {
    function simbolo_monedas($moneda_descripcion)
    {
        $simbolo = '';
        foreach (tipos_moneda() as $moneda) {
            if ($moneda->descripcion == $moneda_descripcion) {
                $simbolo = $moneda->simbolo;
            }
        }
        return $simbolo;
    }
}

// Monto a Pagar Documento de compra
if (!function_exists('calcularMontoDocumento')) {
    function calcularMontoDocumento($id)
    {

        $detalles = Detalle_Documento::where('documento_id', $id)->get();
        $documento = Documento::findOrFail($id);
        $subtotal = 0;
        $igv = '';
        $tipo_moneda = '';
        foreach ($detalles as $detalle) {
            $subtotal = ($detalle->cantidad * $detalle->precio) + $subtotal;
        }

        if (!$documento->igv) {
            $igv = $subtotal * 0.18;
            $total = $subtotal + $igv;
            $decimal_subtotal = number_format($subtotal, 2, '.', '');
            $decimal_total = number_format($total, 2, '.', '');
            $decimal_igv = number_format($igv, 2, '.', '');
        } else {
            $calcularIgv = $documento->igv / 100;
            $base = $subtotal / (1 + $calcularIgv);
            $nuevo_igv = $subtotal - $base;
            $decimal_subtotal = number_format($base, 2, '.', '');
            $decimal_total = number_format($subtotal, 2, '.', '');
            $decimal_igv = number_format($nuevo_igv, 2, '.', '');
        }
        return $decimal_total;
    }
}


// Monto a Pagar Documento de compra
if (!function_exists('calcularMontosAcuentaDocumentos')) {
    function calcularMontosAcuentaDocumentos($id)
    {

        $suma_detalle_pago = DB::table('documento_pago_detalle')
            ->join('compra_documento_pagos', 'compra_documento_pagos.id', '=', 'documento_pago_detalle.pago_id')
            ->join('compra_documento_pago_detalle', 'compra_documento_pago_detalle.id', '=', 'documento_pago_detalle.detalle_id')
            ->join('compra_documentos', 'compra_documentos.id', '=', 'compra_documento_pagos.documento_id')
            ->select('compra_documento_pagos.*', 'compra_documentos.*')
            ->where('compra_documentos.id', '=', $id)
            ->where('compra_documento_pagos.estado', 'ACTIVO')
            ->sum('compra_documento_pago_detalle.monto');

        return $suma_detalle_pago;
    }
}


// Calcular Montos Acuentas
if (!function_exists('calcularMontosAcuentaVentas')) {
    function calcularMontosAcuentaVentas($id)
    {

        $montos = DB::table('cotizacion_documento_pago_detalle_cajas')
            ->join('cotizacion_documento_pagos', 'cotizacion_documento_pagos.id', '=', 'cotizacion_documento_pago_detalle_cajas.pago_id')
            ->join('cotizacion_documento', 'cotizacion_documento.id', '=', 'cotizacion_documento_pagos.documento_id')
            ->join('cotizacion_documento_pago_cajas', 'cotizacion_documento_pago_cajas.id', '=', 'cotizacion_documento_pago_detalle_cajas.caja_id')
            ->join('pos_caja_chica', 'pos_caja_chica.id', '=', 'cotizacion_documento_pago_cajas.caja_id')

            ->select('cotizacion_documento.id as id_documento', 'cotizacion_documento_pago_detalle_cajas.id', 'cotizacion_documento_pago_cajas.monto as caja_monto', 'cotizacion_documento_pagos.tipo_pago', 'cotizacion_documento_pago_detalle_cajas.created_at')
            ->where('cotizacion_documento.id', '=', $id)
            //ANULAR
            ->where('cotizacion_documento_pago_cajas.estado', '!=', 'ANULADO')
            ->sum('cotizacion_documento_pago_cajas.monto');

        return $montos;
    }
}


// Monto a Pagar Documento de venta
if (!function_exists('calcularMontosAcuentaDocumentosVentas')) {
    function calcularMontosAcuentaDocumentosVentas($id)
    {

        $suma_detalle_pago = DB::table('cotizacion_documento_pago_detalles')
            ->join('cotizacion_documento_pagos', 'cotizacion_documento_pagos.id', '=', 'cotizacion_documento_pago_detalles.pago_id')

            ->join('cotizacion_documento', 'cotizacion_documento.id', '=', 'cotizacion_documento_pagos.documento_id')

            ->select('compra_documento_pagos.*', 'compra_documentos.*')
            ->where('cotizacion_documento.id', '=', $id)
            ->where('cotizacion_documento_pago_detalles.estado', 'ACTIVO')
            ->sum('cotizacion_documento_pago_detalles.monto');

        return $suma_detalle_pago;
    }
}


// Calcular monto restante caja chica
if (!function_exists('calcularMontoRestanteCaja')) {
    function calcularMontoRestanteCaja($id)
    {

        $restante = DB::table('compra_documento_pago_detalle')
            ->join('documento_pago_detalle', 'documento_pago_detalle.detalle_id', '=', 'compra_documento_pago_detalle.id')
            ->join('compra_documento_pagos', 'compra_documento_pagos.id', '=', 'documento_pago_detalle.pago_id')
            ->select('compra_documento_pago_detalle.*', 'compra_documentos.*')
            ->where('compra_documento_pagos.estado', '!=', 'ANULADO')
            ->where('compra_documento_pago_detalle.caja_id', $id)
            ->sum('compra_documento_pago_detalle.monto');


        return $restante;
    }
}

// Calcular monto restante caja chica
if (!function_exists('calcularSumaMontosPagosVentas')) {
    function calcularSumaMontosPagosVentas($id)
    {

        $restante = DB::table('cotizacion_documento_pago_cajas')
            ->select('cotizacion_documento_pago_cajas.*')
            ->where('cotizacion_documento_pago_cajas.estado', '!=', 'ANULADO')
            ->where('cotizacion_documento_pago_cajas.caja_id', $id)
            ->sum('cotizacion_documento_pago_cajas.monto');


        return $restante;
    }
}


// Calcular monto restante caja chica
if (!function_exists('calcularMontosAcuentaVentasTransferencia')) {
    function calcularMontosAcuentaVentasTransferencia($id)
    {

        $restante = DB::table('cotizacion_documento_pago_transferencias')
            ->select('cotizacion_documento_pago_transferencias.*')
            ->where('cotizacion_documento_pago_transferencias.estado', '!=', 'ANULADO')
            ->where('cotizacion_documento_pago_transferencias.documento_id', $id)
            ->sum('cotizacion_documento_pago_transferencias.monto');


        return $restante;
    }
}

/////////////////////////////////////////////////////////////////////////////
// REGISTRO DE ACTIVIDADES

if (!function_exists('crearRegistro')) {
    function crearRegistro($modelo, $descripcion, $gestion)
    {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($modelo)
            ->withProperties(
                [
                    'operacion' => 'AGREGAR',
                    'gestion' => $gestion
                ]
            )
            ->log($descripcion);
    }
}

if (!function_exists('modificarRegistro')) {
    function modificarRegistro($modelo, $descripcion, $gestion)
    {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($modelo)
            ->withProperties(
                [
                    'operacion' => 'MODIFICAR',
                    'gestion' => $gestion
                ]
            )
            ->log($descripcion);
    }
}

if (!function_exists('eliminarRegistro')) {
    function eliminarRegistro($modelo, $descripcion, $gestion)
    {
        activity()
            ->causedBy(Auth::user())
            ->performedOn($modelo)
            ->withProperties(
                [
                    'operacion' => 'ELIMINAR',
                    'gestion' => $gestion
                ]
            )
            ->log($descripcion);
    }
}



/////////////////////////////////////////////////////////////////////////////
// FACTURACION ELECTRONICA
//LOGUEO
// GENERAR TOKEN
if (!function_exists('obtenerTokenapi')) {
    function obtenerTokenapi()
    {
        $parametro = Parametro::findOrFail(3);
        $response = Http::post('https://facturacion.apisperu.com/api/v1/auth/login', [
            'username' => $parametro->usuario_proveedor,
            'password' => $parametro->contra_proveedor,
        ]);
        $estado = $response->getStatusCode();
        // dd($response);
        if ($estado == '200') {

            $resultado = $response->getBody()->getContents();
            $resultado = json_decode($resultado);
            return $resultado->token;
        }
    }
}

////////////////////////////////////////////////

//EMPRESA
// AGREGAR EMPRESA
if (!function_exists('agregarEmpresaapi')) {
    function agregarEmpresaapi($empresa)
    {
        $url = "https://facturacion.apisperu.com/api/v1/companies";
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $token = obtenerTokenapi();
        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}"
            ],
            'body'    => $empresa
        ]);

        $estado = $response->getStatusCode();

        if ($estado == '200') {

            $resultado = $response->getBody()->getContents();
            json_decode($resultado);
            return $resultado;
        }
    }
}
// BORRAR EMPRESA
if (!function_exists('borrarEmpresaapi')) {
    function borrarEmpresaapi($id)
    {
        $url = "https://facturacion.apisperu.com/api/v1/companies/{$id}";
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $token = obtenerTokenapi();
        $response = $client->delete($url, [
            'headers' => [
                'Authorization' => "Bearer {$token}"
            ],
        ]);

        $estado = $response->getStatusCode();

        return $estado;
    }
}
// MODIFICAR EMPRESA
if (!function_exists('modificarEmpresaapi')) {
    function modificarEmpresaapi($empresa, $id)
    {
        $url = "https://facturacion.apisperu.com/api/v1/companies/{$id}";
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $token = obtenerTokenapi();
        $response = $client->put($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}"
            ],
            'body'    => $empresa
        ]);

        $estado = $response->getStatusCode();

        if ($estado == '200') {

            $resultado = $response->getBody()->getContents();
            // json_decode($resultado);
            return $resultado;
        }
    }
}

// OBTENER TOKEN DE LA EMPRESA TOKEN-CODE
if (!function_exists('tokenEmpresa')) {
    function tokenEmpresa($id)
    {
        $facturacion = Facturacion::findOrFail($id);
        return $facturacion->token_code;
    }
}

////////////////////////////////////////////////

//ENVIAR FACTURA O BOLETA
//GENERAR PDF
if (!function_exists('generarComprobanteapi')) {
    function generarComprobanteapi($comprobante, $empresa)
    {
        $url = "https://facturacion.apisperu.com/api/v1/invoice/pdf";
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $token = tokenEmpresa($empresa);
        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}"
            ],
            'body'    => $comprobante
        ]);

        $estado = $response->getStatusCode();

        return $response->getBody()->getContents();

        dd($response->getBody()->getContents());
        if ($estado == '200') {

            $resultado = $response->getBody()->getContents();
            json_decode($resultado);
            return $resultado;
        }
    }
}
//GENERAR XML
if (!function_exists('generarXmlapi')) {
    function generarXmlapi($comprobante, $empresa)
    {
        $url = "https://facturacion.apisperu.com/api/v1/invoice/xml";
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $token = tokenEmpresa($empresa);
        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}"
            ],
            'body'    => $comprobante
        ]);

        $estado = $response->getStatusCode();

        return $response->getBody();



        // dd( $response->getBody()->getContents());
        if ($estado == '200') {

            $resultado = $response->getBody()->getContents();
            json_decode($resultado);
            return $resultado;
        }
    }
}

//GENERAR XML
if (!function_exists('generarQrApi')) {
    function generarQrApi($comprobante, $empresa)
    {
        $url = "https://facturacion.apisperu.com/api/v1/sale/qr";
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $token = tokenEmpresa($empresa);
        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}"
            ],
            'body'    => $comprobante
        ]);

        $estado = $response->getStatusCode();

        return $response->getBody();

        // dd( $response->getBody()->getContents());
        if ($estado == '200') {

            $resultado = $response->getBody()->getContents();
            json_decode($resultado);
            return $resultado;
        }
    }
}

//ENVIAR A  SUNAT
if (!function_exists('enviarComprobanteapi')) {
    function enviarComprobanteapi($comprobante, $empresa)
    {
        $url = "https://facturacion.apisperu.com/api/v1/invoice/send";
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $token = tokenEmpresa($empresa);
        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}"
            ],
            'body'    => $comprobante
        ]);

        $estado = $response->getStatusCode();

        if ($estado == '200') {

            $resultado = $response->getBody()->getContents();
            json_decode($resultado);
            return $resultado;
        }
    }
}

////////////////////////////////////////////////
//GUIA DE REMISION
//GENERAR PDF DE GUIA DE REMISION
if (!function_exists('pdfGuiaapi')) {
    function pdfGuiaapi($guia)
    {
        $url = "https://facturacion.apisperu.com/api/v1/despatch/pdf";
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $token = obtenerTokenapi();
        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}"
            ],
            'body'    => $guia
        ]);

        $estado = $response->getStatusCode();

        // dd($estado);

        // return $response->getBody()->getContents();

        if ($estado == '200') {

            $resultado = $response->getBody()->getContents();
            json_decode($resultado);
            return $resultado;
        }
    }
}
//ENVIAR A  SUNAT
if (!function_exists('enviarGuiaapi')) {
    function enviarGuiaapi($guia)
    {
        $url = "https://facturacion.apisperu.com/api/v1/despatch/send";
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $token = obtenerTokenapi();
        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}"
            ],
            'body'    => $guia
        ]);

        $estado = $response->getStatusCode();

        if ($estado == '200') {
            $resultado = $response->getBody()->getContents();
            json_decode($resultado);
            return $resultado;
        }
    }
}

////////////////////////////////////////////////
//NOTA DE CREDITO Y DEBITO
//GENERAR PDF DE NOTA
if (!function_exists('pdfNotaapi')) {
    function pdfNotaapi($nota)
    {
        $url = "https://facturacion.apisperu.com/api/v1/note/pdf";
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $token = obtenerTokenapi();
        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}"
            ],
            'body'    => $nota
        ]);

        $estado = $response->getStatusCode();

        if ($estado == '200') {
            $resultado = $response->getBody()->getContents();
            json_decode($resultado);
            return $resultado;
        }
    }
}

//ENVIAR NOTA A SUNAT
if (!function_exists('enviarNotaapi')) {
    function enviarNotaapi($nota)
    {
        $url = "https://facturacion.apisperu.com/api/v1/note/send";
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $token = obtenerTokenapi();
        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}"
            ],
            'body'    => $nota
        ]);

        $estado = $response->getStatusCode();

        if ($estado == '200') {
            $resultado = $response->getBody()->getContents();
            json_decode($resultado);
            return $resultado;
        }
    }
}

//MODIFICAR LOTES CANTIDADES

if (!function_exists('actualizarStockLotes')) {
    function actualizarStockLotes($id = null)
    {
        DB::update('update lote_productos set cantidad_logica = cantidad');
    }
}

if (!function_exists('turnos')) {
    function turnos()
    {
        return General::find(31)->detalles;
    }
}
if (!function_exists('cajas')) {
    function cajas()
    {
        return Caja::where('estado_caja', 'CERRADA')->where('estado', 'ACTIVO')->get();
    }
}
if (!function_exists('colaboradoresDisponibles')) {
    function colaboradoresDisponibles()
    {
        $colaboradores = Colaborador::join('personas as p', 'p.id', '=', 'colaboradores.id')
            //->join('movimiento_caja as mc','mc.colaborador_id','!=','colaboradores.id')
            // ->select('colaboradores.id','p.nombres','p.apellido_paterno','p.apellido_materno')
            ->where('p.estado', 'ACTIVO')
            // ->where('mc.estado_movimiento','CIERRE')
            ->get();
        // $datos=array();
        // foreach ($colaboradores as $key => $value) {
        //
        //     if($consulta->count()==0)
        //     {
        //         array_push($datos,$value);
        //     }
        // }
        //   return $datos;
        $colaboradores = Colaborador::cursor()->filter(function ($colaborador) {
            $consulta = MovimientoCaja::where('colaborador_id', $colaborador->id)->where('estado_movimiento', 'APERTURA');
            return $consulta->count() == 0 ? true : false;
        });
        return $colaboradores;
    }
}
if (!function_exists('cuentas')) {
    function cuentas()
    {
        return General::find(32)->detalles;
    }
}

if (!function_exists('proveedores')) {
    function proveedores()
    {
        return Proveedor::where('estado','!=','ANULADO')->get();
    }
}

if (!function_exists('clientes')) {
    function clientes()
    {
        return Cliente::where('estado','!=','ANULADO')->get();
    }
}
if (!function_exists('movimientoUser')) {
    function movimientoUser()
    {
        if (Auth::user()->usuario == "ADMINISTRADOR") {

            $consulta = MovimientoCaja::where('caja_id', 1)->where('estado_movimiento', 'APERTURA');
            if ($consulta->count() !== 0) {
                return $consulta->first();
            } else {
                return MovimientoCaja::where('estado_movimiento', 'APERTURA')->first();
            }
        } else {
            return MovimientoCaja::where('colaborador_id', Auth::user()->user->persona->colaborador->id)->where('estado_movimiento', 'APERTURA')->first();
        }
    }
}
if (!function_exists('MovimientoCajaIngresos')) {
    function cuadreMovimientoCajaIngresos(MovimientoCaja $movimiento)
    {
        $totalIngresos = 0;
        foreach ($movimiento->detalleMovimientoVentas as $item) {
            if ($item->documento->forma_pago == 160) {
                if ($item->documento->tipo_pago_id == 1) {
                    $totalIngresos = $totalIngresos + $item->documento->importe;
                } else {
                    $totalIngresos = $totalIngresos + $item->documento->efectivo;
                }
            }
        }

        foreach ($movimiento->detalleCuentaCliente as $item) {
            $totalIngresos = $totalIngresos  + $item->efectivo;
        }
        return $totalIngresos;
    }
}

if (!function_exists('cuadreMovimientoCajaEgresos')) {
    function cuadreMovimientoCajaEgresos($movimiento)
    {

        $totalEgresos = 0;
        foreach ($movimiento->detalleMoviemientoEgresos as $key => $item) {
            $modo = TablaDetalle::where('descripcion', $item->modo_compra)->first();
            // if ($modo->simbolo === 'CREDITO' || $modo->simbolo === 'credito' || $modo->simbolo === 'CRÉDITO' || $modo->simbolo === 'crédito') {
                if ($item->egreso->estado == "ACTIVO") {
                    $totalEgresos = $totalEgresos + $item->egreso->importe;
                }
            // }
        }
        foreach ($movimiento->detalleCuentaProveedor as $key => $item) {
            $totalEgresos = $totalEgresos + $item->efectivo;
        }
        return $totalEgresos;
    }
}

if (!function_exists('precio_dolar')) {
    function precio_dolar()
    {
        $fecha =  Carbon::now()->toDateString();
        $ctx = stream_context_create(array('http'=>
            array(
                'timeout' => 1200,  //1200 Seconds is 20 Minutes
            )
        ));
        $data = file_get_contents("https://api.apis.net.pe/v1/tipo-cambio-sunat?fecha=".$fecha,false,$ctx);
        $infodata = json_decode($data,false);
        return response()->json($infodata);
    }
}

if (!function_exists('mes')) {
    function mes()
    {
        date_default_timezone_set("America/Lima");
        $mes = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"][date("n") - 1];
        return $mes;
    }
}

if (!function_exists('ventas_mensual')) {
    function ventas_mensual()
    {
        $fecha_hoy = Carbon::now();
        $mes = date_format($fecha_hoy,'m');
        $anio = date_format($fecha_hoy,'Y');
        $total = DocumentoDocumento::where('estado','!=','ANULADO')->whereMonth('fecha_documento',$mes)->whereYear('fecha_documento',$anio)->sum('total');
        return number_format($total,3);
    }
}

if (!function_exists('compras_mensual')) {
    function compras_mensual()
    {
        $fecha_hoy = Carbon::now();
        $mes = date_format($fecha_hoy,'m');
        $anio = date_format($fecha_hoy,'Y');
        $total = Documento::where('estado','!=','ANULADO')->whereMonth('fecha_emision',$mes)->whereYear('fecha_emision',$anio)->sum('total_soles');
        return number_format($total,3);
    }
}

if (!function_exists('cuentas_pagar')) {
    function cuentas_pagar()
    {
        $fecha_hoy = Carbon::now();
        $mes = date_format($fecha_hoy,'m');
        $anio = date_format($fecha_hoy,'Y');
        $total = CuentaProveedor::where('estado','!=','ANULADO')->whereMonth('created_at',$mes)->whereYear('created_at',$anio)->sum('saldo');
        return number_format($total,3);
    }
}

if (!function_exists('cuentas_cobrar')) {
    function cuentas_cobrar()
    {
        $fecha_hoy = Carbon::now();
        $mes = date_format($fecha_hoy,'m');
        $anio = date_format($fecha_hoy,'Y');
        $total = CuentaCliente::where('estado','!=','ANULADO')->whereMonth('created_at',$mes)->whereYear('created_at',$anio)->sum('saldo');
        return number_format($total,3);
    }
}


if (!function_exists('ventas_x_mes')) {
    function ventas_x_mes()
    {
        $fecha_actual = Carbon::now();

        $meses_aux = array();
        for ($i = 0; $i < 6; $i++) {
            $f_old = (string)date("d-m-Y", strtotime($fecha_actual . "- " . $i . " month"));
            $m = array("ENE", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DIC");
            $f_old = Carbon::parse($f_old);
            $mes = $m[($f_old->format('n')) - 1];
            $nombre = $mes . ' ' . $f_old->format('Y');
            $ob = new stdClass();
            $ob->fecha = date("d-m-Y", strtotime($fecha_actual . "- " . $i . " month"));
            $ob->nombre = $nombre;
            $ob->anio = date("Y", strtotime($fecha_actual . "- " . $i . " month"));
            $ob->mes = date("m", strtotime($fecha_actual . "- " . $i . " month"));
            array_push($meses_aux, $ob);
        }

        return $meses_aux;
    }
}



