<?php

namespace App\Http\Controllers\Consultas\Ventas;

use App\Events\DocumentoNumeracion;
use App\Events\NotifySunatEvent;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Condicion;
use App\Mantenimiento\Empresa\Empresa;
use App\Ventas\DetalleGuia;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\Guia;
use App\Ventas\Nota;
use App\Ventas\NotaDetalle;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Luecano\NumeroALetras\NumeroALetras;
use stdClass;
use Barryvdh\DomPDF\Facade as PDF;

class AlertaController extends Controller
{
    public function envio()
    {
        $dato = "Message";
        broadcast(new NotifySunatEvent($dato));
        return view('consultas.ventas.alertas.envio');
    }

    public function getTableEnvio()
    {
        $fecha_hoy = Carbon::now()->toDateString();
        $consulta =  DB::table('cotizacion_documento')
            ->join('tabladetalles', 'tabladetalles.id', '=', 'cotizacion_documento.tipo_venta')
            ->join('clientes', 'clientes.id', '=', 'cotizacion_documento.cliente_id')
            ->select(
                DB::raw('(CONCAT(cotizacion_documento.serie, "-" , cotizacion_documento.correlativo)) as numero_doc'),
                'cotizacion_documento.id',
                'cotizacion_documento.serie',
                'cotizacion_documento.correlativo',
                'cotizacion_documento.fecha_documento',
                'cotizacion_documento.estado',
                'tabladetalles.descripcion as tipo',
                'clientes.nombre as cliente',
                'cotizacion_documento.total as monto',
                DB::raw('DATEDIFF( now(),cotizacion_documento.fecha_documento) as dias'),
                'cotizacion_documento.sunat',
                'cotizacion_documento.getCdrResponse',
                DB::raw('ifnull((json_unquote(json_extract(cotizacion_documento.getCdrResponse, "$.code"))),"-") as code'),
                DB::raw('ifnull((json_unquote(json_extract(cotizacion_documento.getCdrResponse, "$.description"))),"-") as description')
            )
            ->orderBy('cotizacion_documento.id', 'DESC')
            ->whereIn('cotizacion_documento.tipo_venta', ['127', '128'])
            ->where('cotizacion_documento.estado', '!=', 'ANULADO')
            ->where('cotizacion_documento.sunat', '0')
            ->where('cotizacion_documento.contingencia', '0')
            ->whereRaw('ifnull((json_unquote(json_extract(cotizacion_documento.getRegularizeResponse, "$.code"))),"0000") != "1033"');

        if (!PuntoVenta() && !FullAccess()) {
            $consulta = $consulta->where('user_id', Auth::user()->id);
        }

        return datatables()->query(
            $consulta
        )->toJson();
    }

    public function regularize()
    {
        return view('consultas.ventas.alertas.regularize');
    }

    public function getTableRegularize()
    {
        $fecha_hoy = Carbon::now()->toDateString();
        $consulta =  DB::table('cotizacion_documento')
            ->join('tabladetalles', 'tabladetalles.id', '=', 'cotizacion_documento.tipo_venta')
            ->join('clientes', 'clientes.id', '=', 'cotizacion_documento.cliente_id')
            ->select(
                DB::raw('(CONCAT(cotizacion_documento.serie, "-" , cotizacion_documento.correlativo)) as numero_doc'),
                'cotizacion_documento.id',
                'cotizacion_documento.serie',
                'cotizacion_documento.correlativo',
                'cotizacion_documento.fecha_documento',
                'cotizacion_documento.estado',
                'tabladetalles.descripcion as tipo',
                'clientes.nombre as cliente',
                'cotizacion_documento.total as monto',
                DB::raw('DATEDIFF( now(),cotizacion_documento.fecha_documento) as dias'),
                'cotizacion_documento.sunat',
                'cotizacion_documento.getRegularizeResponse',
                DB::raw('json_unquote(json_extract(cotizacion_documento.getRegularizeResponse, "$.code")) as code'),
                DB::raw('json_unquote(json_extract(cotizacion_documento.getRegularizeResponse, "$.description")) as description')
            )
            ->orderBy('cotizacion_documento.id', 'DESC')
            ->whereIn('cotizacion_documento.tipo_venta', ['127', '128'])
            ->where('cotizacion_documento.estado', '!=', 'ANULADO')
            ->where('cotizacion_documento.sunat', '!=', '2')
            ->where('cotizacion_documento.contingencia', '0')
            ->where(DB::raw('JSON_EXTRACT(cotizacion_documento.getRegularizeResponse, "$.code")'), '1033')
            ->where('cotizacion_documento.regularize', '1');

        if (!PuntoVenta() && !FullAccess()) {
            $consulta = $consulta->where('user_id', Auth::user()->id);
        }

        return datatables()->query(
            $consulta
        )->toJson();
    }

    public function obtenerLeyenda($documento)
    {
        $formatter = new NumeroALetras();
        $convertir = $formatter->toInvoice($documento->total, 2, 'SOLES');

        //CREAR LEYENDA DEL COMPROBANTE
        $arrayLeyenda = array();
        $arrayLeyenda[] = array(
            "code" => "1000",
            "value" => $convertir
        );
        return $arrayLeyenda;
    }

    public function obtenerProductos($id)
    {
        $detalles = Detalle::where('documento_id', $id)->where('eliminado', '0')->where('estado', 'ACTIVO')->get();
        $arrayProductos = array();
        for ($i = 0; $i < count($detalles); $i++) {

            $arrayProductos[] = array(
                "codProducto" => $detalles[$i]->codigo_producto,
                "unidad" => $detalles[$i]->unidad,
                "descripcion" => $detalles[$i]->nombre_producto . ' - ' . $detalles[$i]->codigo_lote,
                "cantidad" => (float)$detalles[$i]->cantidad,
                "mtoValorUnitario" => (float)($detalles[$i]->precio_nuevo / 1.18),
                "mtoValorVenta" => (float)($detalles[$i]->valor_venta / 1.18),
                "mtoBaseIgv" => (float)($detalles[$i]->valor_venta / 1.18),
                "porcentajeIgv" => 18,
                "igv" => (float)($detalles[$i]->valor_venta - ($detalles[$i]->valor_venta / 1.18)),
                "tipAfeIgv" => 10,
                "totalImpuestos" =>  (float)($detalles[$i]->valor_venta - ($detalles[$i]->valor_venta / 1.18)),
                "mtoPrecioUnitario" => (float)$detalles[$i]->precio_nuevo

            );
        }

        return $arrayProductos;
    }

    public function obtenerCuotas($id)
    {
        $documento = Documento::find($id);
        $arrayCuotas = array();
        $condicion = Condicion::find($documento->condicion_id);
        if (strtoupper($condicion->descripcion) == 'CREDITO' || strtoupper($condicion->descripcion) == 'CRÉDITO') {
            $arrayCuotas[] = array(
                "moneda" => "PEN",
                "monto" => (float)$documento->total,
                "fechaPago" => self::obtenerFechaVencimiento($documento)

            );
        }
        /*if($documento->cuenta)
        {
            foreach($documento->cuenta->detalles as $item)
            {
                $arrayCuotas[] = array(
                    "moneda" => "PEN",
                    "monto" => (float)$item->monto,
                    "fechaPago" => self::obtenerFechaCuenta($item->fecha)

                );
            }
        }*/

        return $arrayCuotas;
    }

    public function obtenerFechaCuenta($fecha)
    {
        $date = strtotime($fecha);
        $fecha_emision = date('Y-m-d', $date);
        $hora_emision = date('H:i:s', $date);
        $fecha = $fecha_emision . 'T' . $hora_emision . '-05:00';

        return $fecha;
    }

    public function obtenerFechaEmision($documento)
    {
        $date = strtotime($documento->fecha_documento);
        $fecha_emision = date('Y-m-d', $date);
        $hora_emision = date('H:i:s', $date);
        $fecha = $fecha_emision . 'T' . $hora_emision . '-05:00';

        return $fecha;
    }

    public function obtenerFechaVencimiento($documento)
    {
        $date = strtotime($documento->fecha_vencimiento);
        $fecha_emision = date('Y-m-d', $date);
        $hora_emision = date('H:i:s', $date);
        $fecha = $fecha_emision . 'T' . $hora_emision . '-05:00';

        return $fecha;
    }

    public function sunat($id)
    {
        try {
            $documento = Documento::findOrFail($id);
            //OBTENER CORRELATIVO DEL COMPROBANTE ELECTRONICO
            $existe = event(new DocumentoNumeracion($documento));
            if ($existe[0]) {
                if ($existe[0]->get('existe') == true) {
                    if ($documento->sunat != '1') {
                        //ARREGLO COMPROBANTE
                        $arreglo_comprobante = array(
                            "tipoOperacion" => $documento->tipoOperacion(),
                            "tipoDoc" => $documento->tipoDocumento(),
                            "serie" => $existe[0]->get('numeracion')->serie,
                            "correlativo" => $documento->correlativo,
                            "fechaEmision" => self::obtenerFechaEmision($documento),
                            "fecVencimiento" => self::obtenerFechaVencimiento($documento),
                            "observacion" => $documento->observacion,
                            "formaPago" => array(
                                "moneda" =>  $documento->simboloMoneda(),
                                "tipo" =>  $documento->forma_pago(),
                                "monto" => (float)$documento->total,
                            ),
                            "cuotas" => self::obtenerCuotas($documento->id),
                            "tipoMoneda" => $documento->simboloMoneda(),
                            "client" => array(
                                "tipoDoc" => $documento->tipoDocumentoCliente(),
                                "numDoc" => $documento->documento_cliente,
                                "rznSocial" => $documento->cliente,
                                "address" => array(
                                    "direccion" => $documento->direccion_cliente,
                                )
                            ),
                            "company" => array(
                                "ruc" =>  $documento->ruc_empresa,
                                "razonSocial" => $documento->empresa,
                                "address" => array(
                                    "direccion" => $documento->direccion_fiscal_empresa,
                                )
                            ),
                            "mtoOperGravadas" => (float)$documento->sub_total,
                            "mtoOperExoneradas" => 0,
                            "mtoIGV" => (float)$documento->total_igv,

                            "valorVenta" => (float)$documento->sub_total,
                            "totalImpuestos" => (float)$documento->total_igv,
                            "subTotal" => (float)$documento->total,
                            "mtoImpVenta" => (float)$documento->total,
                            "ublVersion" => "2.1",
                            "details" => self::obtenerProductos($documento->id),
                            "legends" =>  self::obtenerLeyenda($documento),
                        );

                        //return $arreglo_comprobante;
                        //OBTENER JSON DEL COMPROBANTE EL CUAL SE ENVIARA A SUNAT
                        $data = enviarComprobanteapi(json_encode($arreglo_comprobante), $documento->empresa_id);

                        //RESPUESTA DE LA SUNAT EN JSON
                        $json_sunat = json_decode($data);

                        if ($json_sunat->sunatResponse->success == true) {
                            if ($json_sunat->sunatResponse->cdrResponse->code == "0") {
                                $documento->sunat = '1';
                                $respuesta_cdr = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                                $respuesta_cdr = json_decode($respuesta_cdr, true);
                                $documento->getCdrResponse = $respuesta_cdr;

                                $data_comprobante = generarComprobanteapi(json_encode($arreglo_comprobante), $documento->empresa_id);
                                $name = $documento->serie . "-" . $documento->correlativo . '.pdf';

                                $data_cdr = base64_decode($json_sunat->sunatResponse->cdrZip);
                                $name_cdr = 'R-' . $documento->serie . "-" . $documento->correlativo . '.zip';

                                if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sunat'))) {
                                    mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sunat'));
                                }

                                if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'cdr'))) {
                                    mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'cdr'));
                                }

                                $pathToFile = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sunat' . DIRECTORY_SEPARATOR . $name);
                                $pathToFile_cdr = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'cdr' . DIRECTORY_SEPARATOR . $name_cdr);

                                file_put_contents($pathToFile, $data_comprobante);
                                file_put_contents($pathToFile_cdr, $data_cdr);

                                $arreglo_qr = array(
                                    "ruc" => $documento->ruc_empresa,
                                    "tipo" => $documento->tipoDocumento(),
                                    "serie" => $documento->serie,
                                    "numero" => $documento->correlativo,
                                    "emision" => self::obtenerFechaEmision($documento),
                                    "igv" => 18,
                                    "total" => (float)$documento->total,
                                    "clienteTipo" => $documento->tipoDocumentoCliente(),
                                    "clienteNumero" => $documento->documento_cliente
                                );

                                /********************************/
                                $data_qr = generarQrApi(json_encode($arreglo_qr), $documento->empresa_id);

                                $name_qr = $documento->serie . "-" . $documento->correlativo . '.svg';

                                if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'qrs'))) {
                                    mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'qrs'));
                                }

                                $pathToFile_qr = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'qrs' . DIRECTORY_SEPARATOR . $name_qr);

                                file_put_contents($pathToFile_qr, $data_qr);

                                /********************************/

                                $data_xml = generarXmlapi(json_encode($arreglo_comprobante), $documento->empresa_id);
                                $name_xml = $documento->serie . '-' . $documento->correlativo . '.xml';
                                $pathToFile_xml = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . $name_xml);
                                if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'xml'))) {
                                    mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'xml'));
                                }
                                file_put_contents($pathToFile_xml, $data_xml);

                                /********************************* */

                                $documento->nombre_comprobante_archivo = $name;
                                $documento->hash = $json_sunat->hash;
                                $documento->xml = $name_xml;
                                $documento->ruta_comprobante_archivo = 'public/sunat/' . $name;
                                $documento->ruta_qr = 'public/qrs/' . $name_qr;
                                $documento->update();


                                //Registro de actividad
                                $descripcion = "SE AGREGÓ EL COMPROBANTE ELECTRONICO: " . $documento->serie . "-" . $documento->correlativo;
                                $gestion = "COMPROBANTES ELECTRONICOS";
                                crearRegistro($documento, $descripcion, $gestion);

                                Session::flash('success', 'Documento de Venta enviada a Sunat con exito.');
                                Session::flash('sunat_exito', '1');
                                Session::flash('id_sunat', $json_sunat->sunatResponse->cdrResponse->id);
                                Session::flash('descripcion_sunat', $json_sunat->sunatResponse->cdrResponse->description);
                                return redirect()->route('consultas.ventas.alerta.envio')->with('sunat_exito', 'success');
                                // return view('consultas.ventas.alertas.envio',[

                                //     'id_sunat' => $json_sunat->sunatResponse->cdrResponse->id,
                                //     'descripcion_sunat' => $json_sunat->sunatResponse->cdrResponse->description,
                                //     'notas_sunat' => $json_sunat->sunatResponse->cdrResponse->notes,
                                //     'sunat_exito' => true

                                // ])->with('sunat_exito', 'success');

                            } else {
                                $documento->sunat = '0';

                                $id_sunat = $json_sunat->sunatResponse->cdrResponse->code;
                                $descripcion_sunat = $json_sunat->sunatResponse->cdrResponse->description;

                                $respuesta_error = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                                $respuesta_error = json_decode($respuesta_error, true);
                                $documento->getCdrResponse = $respuesta_error;

                                $documento->update();
                                Session::flash('error', 'Documento de Venta sin exito en el envio a sunat.');
                                Session::flash('sunat_error', '1');
                                Session::flash('id_sunat', $id_sunat);
                                Session::flash('descripcion_sunat', $descripcion_sunat);
                                return redirect()->route('consultas.ventas.alerta.envio')->with('sunat_error', 'error');
                                // $dato = "Message";
                                // broadcast(new NotifySunatEvent($dato));
                                // return view('consultas.ventas.alertas.envio',[
                                //     'id_sunat' =>  $id_sunat,
                                //     'descripcion_sunat' =>  $descripcion_sunat,
                                //     'sunat_error' => true,

                                // ])->with('sunat_error', 'error');
                            }
                        } else {

                            //COMO SUNAT NO LO ADMITE VUELVE A SER 0
                            $documento->sunat = '0';
                            $documento->regularize = '1';

                            if ($json_sunat->sunatResponse->error) {
                                $id_sunat = $json_sunat->sunatResponse->error->code;
                                $descripcion_sunat = $json_sunat->sunatResponse->error->message;

                                $obj_erro = new stdClass();
                                $obj_erro->code = $json_sunat->sunatResponse->error->code;
                                $obj_erro->description = $json_sunat->sunatResponse->error->message;
                                $respuesta_error = json_encode($obj_erro, true);
                                $respuesta_error = json_decode($respuesta_error, true);
                                $documento->getRegularizeResponse = $respuesta_error;
                            } else {
                                $id_sunat = $json_sunat->sunatResponse->cdrResponse->id;
                                $descripcion_sunat = $json_sunat->sunatResponse->cdrResponse->description;

                                $respuesta_error = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                                $respuesta_error = json_decode($respuesta_error, true);
                                $documento->getCdrResponse = $respuesta_error;
                            };

                            $documento->update();
                            Session::flash('error', 'Documento de Venta sin exito en el envio a sunat.');
                            Session::flash('sunat_error', '1');
                            Session::flash('id_sunat', $id_sunat);
                            Session::flash('descripcion_sunat', $descripcion_sunat);
                            return redirect()->route('consultas.ventas.alerta.envio')->with('sunat_error', 'error');
                            // return view('consultas.ventas.alertas.envio',[
                            //     'id_sunat' =>  $id_sunat,
                            //     'descripcion_sunat' =>  $descripcion_sunat,
                            //     'sunat_error' => true,

                            // ])->with('sunat_error', 'error');
                        }
                    } else {
                        $documento->sunat = '1';
                        $documento->update();
                        Session::flash('error', 'Documento de venta fue enviado a Sunat.');
                        return redirect()->route('consultas.ventas.alerta.envio');
                    }
                } else {
                    Session::flash('error', 'Tipo de Comprobante no registrado en la empresa.');
                    return redirect()->route('consultas.ventas.alerta.envio');
                }
            } else {
                Session::flash('error', 'Empresa sin parametros para emitir comprobantes electronicos');
                return redirect()->route('consultas.ventas.alerta.envio');
            }
        } catch (Exception $e) {
            $documento = Documento::findOrFail($id);
            $documento->regularize = '1';
            $documento->sunat = '0';
            $obj_erro = new stdClass();
            $obj_erro->code = 6;
            $obj_erro->description = $e->getMessage();
            $respuesta_error = json_encode($obj_erro, true);
            $respuesta_error = json_decode($respuesta_error, true);
            $documento->getRegularizeResponse = $respuesta_error;
            $documento->update();

            Session::flash('error', 'No se puede conectar con el servidor, porfavor intentar nuevamente.');
            return redirect()->route('consultas.ventas.alerta.envio');
        }
    }

    public function cdr($id)
    {

        try {
            $documento = Documento::findOrFail($id);
            $comprobante = array(
                'ruc' => $documento->ruc_empresa,
                'tipo' => $documento->tipoDocumento(),
                'serie' => $documento->serie,
                'correlativo' => $documento->correlativo,
                'fecha_emision' => $documento->fecha_documento,
                'total' => $documento->total
            );
            $comprobante = json_encode($comprobante, false);
            $comprobante = json_decode($comprobante, false);
            $data = consultaCrd($comprobante);
            $data = json_decode($data);

            if ($data->success) {
                if ($data->data->comprobante_estado_codigo == '1' && strtoupper($data->data->comprobante_estado_descripcion) == 'ACEPTADO') {
                    $documento->sunat = '1';
                    $documento->regularize = '0';
                    $documento->update();

                    Session::flash('success', 'Documento de Venta enviada a Sunat con exito.');
                    Session::flash('sunat_exito', '1');
                    Session::flash('id_sunat', $documento->serie . '-' . $documento->correlativo);
                    Session::flash('descripcion_sunat', 'CDR regularizado.');
                    return redirect()->route('consultas.ventas.alerta.regularize');
                } else {
                    $documento->sunat = '0';
                    $documento->regularize = '0';
                    $documento->getCdrResponse = null;
                    $documento->getRegularizeResponse = null;
                    $documento->update();
                    Session::flash('error', 'Este documento tiene un error diferente al CDR, intentar enviar a sunat.');
                    return redirect()->route('consultas.ventas.alerta.regularize');
                }
            } else {
                $documento->sunat = '0';
                $documento->regularize = '0';
                $documento->getCdrResponse = null;
                $documento->getRegularizeResponse = null;
                $documento->update();
                Session::flash('error', 'Este documento tiene un error diferente al CDR, intentar enviar a sunat.');
                return redirect()->route('consultas.ventas.alerta.regularize')->with('sunat_error', 'error');
            }
            // $json_data = json_decode($documento->getRegularizeResponse, false);
            // if ($documento->regularize == '1' && $json_data->code == '1033') {
            //     $documento->regularize = '0';
            //     $documento->sunat = '1';
            //     $documento->update();
            //     Session::flash('success', 'Documento de Venta regularizado con exito.');
            //     return view('consultas.ventas.alertas.regularize', [

            //         'id_sunat' => $documento->serie . '-' . $documento->correlativo,
            //         'descripcion_sunat' => 'CDR regularizado.',
            //         'notas_sunat' => '',
            //         'sunat_exito' => true

            //     ])->with('sunat_exito', 'success');
            // } else {
            //     Session::flash('error', 'Este documento tiene un error diferente al CDR, intentar enviar a sunat.');
            //     return redirect()->route('consultas.ventas.alerta.regularize')->with('sunat_existe', 'error');
            // }
        } catch (Exception $e) {
            Session::flash('error', 'No se puede conectar con el servidor, porfavor intentar nuevamente.'); //$e->getMessage()
            return redirect()->route('consultas.ventas.alerta.regularize');
        }
    }

    public function notas()
    {
        $dato = "Message";
        broadcast(new NotifySunatEvent($dato));
        return view('consultas.ventas.alertas.notas');
    }

    public function getTableNotas()
    {
        $consulta = DB::table('nota_electronica')
            ->select(
                'nota_electronica.id',
                'nota_electronica.serie',
                'nota_electronica.correlativo',
                'nota_electronica.desMotivo as motivo',
                'nota_electronica.cliente',
                'nota_electronica.mtoImpVenta as monto',
                'nota_electronica.sunat',
                'nota_electronica.regularize',
                'nota_electronica.getCdrResponse',
                'nota_electronica.fechaEmision as fecha',
                DB::raw('ifnull((json_unquote(json_extract(nota_electronica.getRegularizeResponse, "$.code"))),"-") as code_regularize'),
                DB::raw('ifnull((json_unquote(json_extract(nota_electronica.getCdrResponse, "$.code"))),"-") as code'),
                DB::raw('ifnull((json_unquote(json_extract(nota_electronica.getCdrResponse, "$.description"))),"-") as description')
            )
            ->whereIn('nota_electronica.tipDocAfectado', ['01', '03'])
            ->where('nota_electronica.estado', '!=', 'ANULADO')
            ->where('nota_electronica.sunat', '0');

        if (!PuntoVenta() && !FullAccess()) {
            $consulta = $consulta->where('user_id', Auth::user()->id);
        }

        $consulta = $consulta->orderBy('nota_electronica.id', 'desc');

        return datatables()->query(
            $consulta
        )->toJson();
    }

    public function sunat_notas($id)
    {
        try {
            $nota = Nota::findOrFail($id);
            $detalles = NotaDetalle::where('nota_id', $id)->get();
            //OBTENER CORRELATIVO DE LA NOTA CREDITO
            $nota = Nota::findOrFail($id);
            if ($nota->sunat != '1') {
                //ARREGLO COMPROBANTE
                $arreglo_nota = array(
                    "tipDocAfectado" => $nota->tipDocAfectado,
                    "numDocfectado" => $nota->numDocfectado,
                    "codMotivo" => $nota->codMotivo,
                    "desMotivo" => $nota->desMotivo,
                    "tipoDoc" => $nota->tipoDoc,
                    "fechaEmision" => self::obtenerFecha($nota->fechaEmision),
                    "tipoMoneda" => $nota->tipoMoneda,
                    "serie" => $nota->tipDocAfectado === '03' ? 'BB01' : 'FF01', //$nota->serie,
                    "correlativo" => $nota->correlativo,
                    "company" => array(
                        "ruc" => $nota->ruc_empresa,
                        "razonSocial" => $nota->empresa,
                        "address" => array(
                            "direccion" => $nota->direccion_fiscal_empresa,
                        )
                    ),


                    "client" => array(
                        "tipoDoc" =>  $nota->cod_tipo_documento_cliente,
                        "numDoc" => $nota->documento_cliente,
                        "rznSocial" => $nota->cliente,
                        "address" => array(
                            "direccion" => $nota->direccion_cliente,
                        )
                    ),

                    "mtoOperGravadas" =>  floatval($nota->mtoOperGravadas),
                    "mtoIGV" => floatval($nota->mtoIGV),
                    "totalImpuestos" => floatval($nota->totalImpuestos),
                    "mtoImpVenta" => floatval($nota->mtoImpVenta),
                    "ublVersion" =>  $nota->ublVersion,
                    "details" => self::obtenerProductosNota($detalles),
                    "legends" =>  self::obtenerLeyendaNota($nota),
                );

                // return $arreglo_nota;
                //OBTENER JSON DEL COMPROBANTE EL CUAL SE ENVIARA A SUNAT
                $data = enviarNotaapi(json_encode($arreglo_nota));

                //RESPUESTA DE LA SUNAT EN JSON
                $json_sunat = json_decode($data);

                if ($json_sunat->sunatResponse->success == true) {
                    if ($json_sunat->sunatResponse->cdrResponse->code == "0") {
                        $nota->sunat = '1';
                        $respuesta_cdr = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                        $respuesta_cdr = json_decode($respuesta_cdr, true);
                        $nota->getCdrResponse = $respuesta_cdr;

                        $data_comprobante = pdfNotaapi(json_encode($arreglo_nota));
                        $name = $nota->serie . "-" . $nota->correlativo . '.pdf';

                        if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sunat'))) {
                            mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sunat'));
                        }

                        if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sunat' . DIRECTORY_SEPARATOR . 'nota'))) {
                            mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sunat' . DIRECTORY_SEPARATOR . 'nota'));
                        }

                        $pathToFile = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sunat' . DIRECTORY_SEPARATOR . 'nota' . DIRECTORY_SEPARATOR . $name);

                        /*************************************** */
                        $arreglo_qr = array(
                            "ruc" => $nota->ruc_empresa,
                            "tipo" => $nota->tipoDoc,
                            "serie" => $nota->serie,
                            "numero" => $nota->correlativo,
                            "emision" => self::obtenerFecha($nota->fechaEmision),
                            "igv" => 18,
                            "total" => floatval($nota->mtoImpVenta),
                            "clienteTipo" => $nota->cod_tipo_documento_cliente,
                            "clienteNumero" => $nota->documento_cliente
                        );

                        $data_qr = generarQrApi(json_encode($arreglo_qr), $nota->empresa_id);

                        $name_qr = $nota->serie . "-" . $nota->correlativo . '.svg';

                        if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'qrs_nota'))) {
                            mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'qrs_nota'));
                        }
                        $pathToFile_qr = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'qrs_nota' . DIRECTORY_SEPARATOR . $name_qr);

                        file_put_contents($pathToFile_qr, $data_qr);
                        /*************************************** */

                        file_put_contents($pathToFile, $data_comprobante);
                        $nota->hash = $json_sunat->hash;
                        $nota->ruta_qr = 'public/qrs_nota/' . $name_qr;
                        $nota->nombre_comprobante_archivo = $name;
                        $nota->ruta_comprobante_archivo = 'public/sunat/nota/' . $name;
                        $nota->update();


                        //Registro de actividad
                        $descripcion = "SE AGREGÓ LA NOTA ELECTRONICA: " . $nota->serie . "-" . $nota->correlativo;
                        $gestion = "NOTAS ELECTRONICAS";
                        crearRegistro($nota, $descripcion, $gestion);

                        Session::flash('success', 'Nota enviada a Sunat con exito.');
                        Session::flash('sunat_exito', '1');
                        Session::flash('id_sunat', $json_sunat->sunatResponse->cdrResponse->id);
                        Session::flash('descripcion_sunat', $json_sunat->sunatResponse->cdrResponse->description,);
                        return redirect()->route('consultas.ventas.alerta.notas')->with('sunat_exito', 'success');
                        // return view('consultas.ventas.alertas.notas', [

                        //     'id_sunat' => $json_sunat->sunatResponse->cdrResponse->id,
                        //     'descripcion_sunat' => $json_sunat->sunatResponse->cdrResponse->description,
                        //     'notas_sunat' => $json_sunat->sunatResponse->cdrResponse->notes,
                        //     'sunat_exito' => true
                        // ])->with('sunat_exito', 'success');
                    } else {
                        $nota->sunat = '0';
                        $id_sunat = $json_sunat->sunatResponse->cdrResponse->code;
                        $descripcion_sunat = $json_sunat->sunatResponse->cdrResponse->description;

                        $respuesta_error = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                        $respuesta_error = json_decode($respuesta_error, true);
                        $nota->getCdrResponse = $respuesta_error;

                        $nota->update();
                        Session::flash('error', 'Nota electronica sin exito en el envio a sunat.');
                        Session::flash('sunat_error', '1');
                        Session::flash('id_sunat', $id_sunat);
                        Session::flash('descripcion_sunat', $descripcion_sunat);
                        return redirect()->route('consultas.ventas.alerta.notas')->with('sunat_error', 'error');
                        // $dato = "Message";
                        // broadcast(new NotifySunatEvent($dato));
                        // return view('consultas.ventas.alertas.notas', [
                        //     'id_sunat' =>  $id_sunat,
                        //     'descripcion_sunat' =>  $descripcion_sunat,
                        //     'sunat_error' => true,
                        // ])->with('sunat_error', 'error');
                    }
                } else {

                    //COMO SUNAT NO LO ADMITE VUELVE A SER 0
                    $nota->sunat = '0';
                    $nota->regularize = '1';

                    if ($json_sunat->sunatResponse->error) {
                        $id_sunat = $json_sunat->sunatResponse->error->code;
                        $descripcion_sunat = $json_sunat->sunatResponse->error->message;
                        $obj_erro = new stdClass;
                        $obj_erro->code = $json_sunat->sunatResponse->error->code;
                        $obj_erro->description = $json_sunat->sunatResponse->error->message;
                        $respuesta_error = json_encode($obj_erro, true);
                        $respuesta_error = json_decode($respuesta_error, true);
                        $nota->getRegularizeResponse = $respuesta_error;
                    } else {
                        $id_sunat = $json_sunat->sunatResponse->cdrResponse->id;
                        $descripcion_sunat = $json_sunat->sunatResponse->cdrResponse->description;
                        $respuesta_error = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                        $respuesta_error = json_decode($respuesta_error, true);
                        $nota->getCdrResponse = $respuesta_error;
                    };

                    $nota->update();
                    Session::flash('error', 'Nota electronica sin exito en el envio a sunat.');
                    Session::flash('sunat_error', '1');
                    Session::flash('id_sunat', $id_sunat);
                    Session::flash('descripcion_sunat', $descripcion_sunat);
                    return redirect()->route('consultas.ventas.alerta.notas')->with('sunat_error', 'error');
                    // $dato = "Message";
                    // broadcast(new NotifySunatEvent($dato));
                    // return view('consultas.ventas.alertas.notas', [
                    //     'id_sunat' =>  $id_sunat,
                    //     'descripcion_sunat' =>  $descripcion_sunat,
                    //     'sunat_error' => true,
                    // ])->with('sunat_error', 'error');
                }
            } else {
                $nota->sunat = '1';
                $nota->update();
                Session::flash('error', 'Nota fue enviado a Sunat.');
                return redirect()->route('consultas.ventas.alerta.notas')->with('sunat_existe', 'error');
            }
        } catch (Exception $e) {
            Session::flash('error', 'Ocurrio un error, si el error persiste contactar al administrador del sistema.');
            return redirect()->route('consultas.ventas.alerta.notas');
        }
    }

    public function obtenerProductosNota($detalles)
    {

        $arrayProductos = array();
        for ($i = 0; $i < count($detalles); $i++) {

            $arrayProductos[] = array(
                "codProducto" => $detalles[$i]->codProducto,
                "unidad" => $detalles[$i]->unidad,
                "descripcion" => $detalles[$i]->descripcion,
                "cantidad" => $detalles[$i]->cantidad,

                'mtoBaseIgv' => floatval($detalles[$i]->mtoBaseIgv),
                'porcentajeIgv' => floatval($detalles[$i]->porcentajeIgv),
                'igv' => floatval($detalles[$i]->igv),
                'tipAfeIgv' => floatval($detalles[$i]->tipAfeIgv),

                'totalImpuestos' => floatval($detalles[$i]->totalImpuestos),
                'mtoValorVenta' => floatval($detalles[$i]->mtoValorVenta),
                'mtoValorUnitario' => floatval($detalles[$i]->mtoValorUnitario),
                'mtoPrecioUnitario' => floatval($detalles[$i]->mtoPrecioUnitario),

            );
        }

        return $arrayProductos;
    }

    public function obtenerLeyendaNota($nota)
    {
        //CREAR LEYENDA DEL COMPROBANTE
        $arrayLeyenda = array();
        $arrayLeyenda[] = array(
            "code" => $nota->code,
            "value" => $nota->value
        );
        return $arrayLeyenda;
    }

    public function obtenerFecha($fecha)
    {
        $date = strtotime($fecha);
        $fecha_emision = date('Y-m-d', $date);
        $hora_emision = date('H:i:s', $date);
        $fecha = $fecha_emision . 'T' . $hora_emision . '-05:00';

        return $fecha;
    }

    public function guias()
    {
        $dato = "Message";
        broadcast(new NotifySunatEvent($dato));
        return view('consultas.ventas.alertas.guias');
    }

    public function getTableGuias()
    {
        $consulta =  DB::table('guias_remision')
            ->select(
                'guias_remision.id',
                'guias_remision.serie',
                'guias_remision.correlativo',
                'guias_remision.documento_cliente',
                'guias_remision.cliente',
                'guias_remision.sunat',
                'guias_remision.cantidad_productos',
                'guias_remision.peso_productos',
                'guias_remision.regularize',
                'guias_remision.getCdrResponse',
                DB::raw('DATE_FORMAT(guias_remision.created_at, "%Y-%m-%d") as fecha'),
                DB::raw('ifnull((json_unquote(json_extract(guias_remision.getRegularizeResponse, "$.code"))),"-") as code_regularize'),
                DB::raw('ifnull((json_unquote(json_extract(guias_remision.getCdrResponse, "$.code"))),"-") as code'),
                DB::raw('ifnull((json_unquote(json_extract(guias_remision.getCdrResponse, "$.description"))),"-") as description')
            )
            ->where('guias_remision.estado', '!=', 'NULO')
            ->where('guias_remision.sunat', '0');

        if (!PuntoVenta() && !FullAccess()) {
            $consulta = $consulta->where('user_id', Auth::user()->id);
        }

        $consulta = $consulta->orderBy('guias_remision.id', 'desc');

        return datatables()->query(
            $consulta
        )->toJson();
    }

    public function sunat_guias($id)
    {
        $guia = Guia::findOrFail($id);
        if ($guia->sunat != '1') {
            //ARREGLO GUIA
            $arreglo_guia = array(
                "tipoDoc" => "09",
                "serie" => $guia->serie,
                "correlativo" => $guia->correlativo,
                "fechaEmision" => self::obtenerFechaGuia($guia),

                "company" => array(
                    "ruc" => $guia->ruc_empresa,
                    "razonSocial" => $guia->empresa,
                    "address" => array(
                        "direccion" => $guia->direccion_empresa,
                    )
                ),


                "destinatario" => array(
                    "tipoDoc" =>  $guia->codTraslado() == "04" ? "6" : $guia->tipoDocumentoCliente(),
                    "numDoc" =>  $guia->codTraslado() == "04" ? $guia->ruc_empresa : $guia->documento_cliente,
                    "rznSocial" =>  $guia->codTraslado() == "04" ? $guia->empresa : $guia->cliente,
                    "address" => array(
                        "direccion" =>  $guia->codTraslado() == "04" ? $guia->direccion_empresa : $guia->direccion_cliente,
                    )
                ),

                "observacion" => $guia->observacion,

                "envio" => array(
                    "modTraslado" =>  "01",
                    "codTraslado" =>  $guia->codTraslado(),
                    "desTraslado" =>  $guia->desTraslado(),
                    "fecTraslado" =>  self::obtenerFechaGuia($guia), //FECHA DEL TRANSLADO
                    "codPuerto" => "123",
                    "indTransbordo" => false,
                    "pesoTotal" => $guia->peso_productos,
                    "undPesoTotal" => "KGM",
                    "numContenedor" => "XD-2232",
                    "numBultos" => $guia->cantidad_productos,
                    "llegada" => array(
                        "ubigueo" =>  $guia->ubigeo_llegada,
                        "direccion" => self::limitarDireccion($guia->direccion_llegada, 50, "..."),
                    ),
                    "partida" => array(
                        "ubigueo" => $guia->ubigeo_partida,
                        "direccion" => self::limitarDireccion($guia->direccion_empresa, 50, "..."),
                    ),
                    "transportista" => self::condicionReparto($guia)
                ),

                "details" =>  self::obtenerProductosGuia($guia),
            );

            $data = enviarGuiaapi(json_encode($arreglo_guia));
            //RESPUESTA DE LA SUNAT EN JSON
            $json_sunat = json_decode($data);

            if ($json_sunat->sunatResponse->success == true) {
                if ($json_sunat->sunatResponse->cdrResponse->code == "0") {
                    $guia->sunat = '1';
                    $respuesta_cdr = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                    $respuesta_cdr = json_decode($respuesta_cdr, true);
                    $guia->getCdrResponse = $respuesta_cdr;
                    $data = pdfGuiaapi(json_encode($arreglo_guia));
                    $name = $guia->serie . "-" . $guia->correlativo . '.pdf';
                    $pathToFile = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sunat' . DIRECTORY_SEPARATOR . 'guia' . DIRECTORY_SEPARATOR . $name);
                    if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sunat' . DIRECTORY_SEPARATOR . 'guia'))) {
                        mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sunat' . DIRECTORY_SEPARATOR . 'guia'));
                    }

                    //file_put_contents($pathToFile, $data);
                    $empresa = Empresa::first();
                    PDF::loadview('ventas.guias.reportes.guia', [
                        'guia' => $guia,
                        'empresa' => $empresa,
                    ])->setPaper('a4')->setWarnings(false)
                        ->save(public_path() . '/storage/sunat/guia/' . $name);

                    $guia->nombre_comprobante_archivo = $name;
                    $guia->ruta_comprobante_archivo = 'public/sunat/guia/' . $name;
                    $guia->update();

                    //Registro de actividad
                    $descripcion = "SE AGREGÓ LA GUIA DE REMISION ELECTRONICA: " . $guia->serie . "-" . $guia->correlativo;
                    $gestion = "GUIA DE REMISION ELECTRONICA";
                    crearRegistro($guia, $descripcion, $gestion);

                    Session::flash('success', 'Guia de remision enviada a Sunat con exito.');
                    Session::flash('sunat_exito', '1');
                    Session::flash('id_sunat', $json_sunat->sunatResponse->cdrResponse->id);
                    Session::flash('descripcion_sunat', $json_sunat->sunatResponse->cdrResponse->description,);
                    return redirect()->route('consultas.ventas.alerta.guias')->with('sunat_exito', 'success');
                    // return view('consultas.ventas.alertas.guias', [

                    //     'id_sunat' => $json_sunat->sunatResponse->cdrResponse->id,
                    //     'descripcion_sunat' => $json_sunat->sunatResponse->cdrResponse->description,
                    //     'notas_sunat' => $json_sunat->sunatResponse->cdrResponse->notes,
                    //     'sunat_exito' => true

                    // ])->with('sunat_exito', 'success');
                } else {
                    $guia->sunat = '0';
                    $id_sunat = $json_sunat->sunatResponse->cdrResponse->code;
                    $descripcion_sunat = $json_sunat->sunatResponse->cdrResponse->description;

                    $respuesta_error = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                    $respuesta_error = json_decode($respuesta_error, true);
                    $guia->getCdrResponse = $respuesta_error;

                    $guia->update();

                    Session::flash('error', 'Guia de remision sin exito en el envio a sunat.');
                    Session::flash('sunat_error', '1');
                    Session::flash('id_sunat', $id_sunat);
                    Session::flash('descripcion_sunat', $descripcion_sunat);
                    return redirect()->route('consultas.ventas.alerta.guias')->with('sunat_error', 'error');

                    // Session::flash('error', 'Guia de remision sin exito en el envio a sunat.');
                    // return view('consultas.ventas.alertas.guias', [
                    //     'id_sunat' =>  $id_sunat,
                    //     'descripcion_sunat' =>  $descripcion_sunat,
                    //     'sunat_error' => true,

                    // ])->with('sunat_error', 'error');
                }
            } else {

                //COMO SUNAT NO LO ADMITE VUELVE A SER 0
                $guia->sunat = '0';
                $guia->regularize = '1';

                if ($json_sunat->sunatResponse->error) {
                    $id_sunat = $json_sunat->sunatResponse->error->code;
                    $descripcion_sunat = $json_sunat->sunatResponse->error->message;
                    $obj_erro = new stdClass;
                    $obj_erro->code = $json_sunat->sunatResponse->error->code;
                    $obj_erro->description = $json_sunat->sunatResponse->error->message;
                    $respuesta_error = json_encode($obj_erro, true);
                    $respuesta_error = json_decode($respuesta_error, true);
                    $guia->getRegularizeResponse = $respuesta_error;
                } else {
                    $id_sunat = $json_sunat->sunatResponse->cdrResponse->id;
                    $descripcion_sunat = $json_sunat->sunatResponse->cdrResponse->description;
                    $respuesta_error = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                    $respuesta_error = json_decode($respuesta_error, true);
                    $guia->getCdrResponse = $respuesta_error;
                };

                $guia->update();

                Session::flash('error', 'Guia de remision sin exito en el envio a sunat.');
                Session::flash('sunat_error', '1');
                Session::flash('id_sunat', $id_sunat);
                Session::flash('descripcion_sunat', $descripcion_sunat);
                return redirect()->route('consultas.ventas.alerta.guias')->with('sunat_error', 'error');
                // return view('consultas.ventas.alertas.guias', [
                //     'id_sunat' =>  $id_sunat,
                //     'descripcion_sunat' =>  $descripcion_sunat,
                //     'sunat_error' => 'true',

                // ])->with('sunat_error', 'error');
            }
        } else {
            $guia->sunat = '1';
            $guia->update();
            Session::flash('error', 'Guia de remision fue enviado a Sunat.');
            return redirect()->route('consultas.ventas.alerta.guias')->with('sunat_existe', 'error');
        }
    }

    public function limitarDireccion($cadena, $limite, $sufijo)
    {

        if (strlen($cadena) > $limite) {
            return substr($cadena, 0, $limite) . $sufijo;
        }

        return $cadena;
    }

    public function condicionReparto($guia)
    {
        $Transportista = array(
            "tipoDoc" => "6",
            "numDoc" => $guia->ruc_transporte_domicilio,
            "rznSocial" => $guia->nombre_transporte_domicilio,
            "placa" => $guia->placa_vehiculo,
            "choferTipoDoc" => "1",
            "choferDoc" => $guia->dni_conductor
        );

        return $Transportista;
    }

    public function obtenerFechaGuia($guia)
    {
        $date = strtotime($guia->fecha_emision);
        $fecha_emision = date('Y-m-d', $date);
        $hora_emision = date('H:i:s', $date);
        $fecha = $fecha_emision . 'T' . $hora_emision . '-05:00';

        return $fecha;
    }

    public function obtenerProductosGuia($guia)
    {
        $detalles = DetalleGuia::where('guia_id', $guia->id)->get();

        $arrayProductos = array();
        for ($i = 0; $i < count($detalles); $i++) {

            $arrayProductos[] = array(
                "codigo" => $detalles[$i]->codigo_producto,
                "unidad" => $detalles[$i]->unidad,
                "descripcion" => $detalles[$i]->nombre_producto,
                "cantidad" => $detalles[$i]->cantidad,
                "codProdSunat" => '10',
            );
        }

        return $arrayProductos;
    }
}
