<?php

namespace App\Http\Controllers\Consultas\Ventas;

use App\Events\DocumentoNumeracion;
use App\Events\NotifySunatEvent;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Condicion;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Luecano\NumeroALetras\NumeroALetras;
use stdClass;

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
        ->join('tabladetalles','tabladetalles.id','=','cotizacion_documento.tipo_venta')
        ->join('clientes','clientes.id','=','cotizacion_documento.cliente_id')
        ->select(
            DB::raw('(CONCAT(cotizacion_documento.serie, "-" , cotizacion_documento.correlativo)) as numero_doc'),
            'cotizacion_documento.id',
            'cotizacion_documento.fecha_documento',
            'cotizacion_documento.estado',
            'tabladetalles.descripcion as tipo',
            'clientes.nombre as cliente',
            'cotizacion_documento.total as monto',
            DB::raw('DATEDIFF( now(),cotizacion_documento.fecha_documento) as dias'),
            'cotizacion_documento.sunat',
            DB::raw('json_unquote(json_extract(cotizacion_documento.getRegularizeResponse, "$.code")) as code')
        )
        ->orderBy('cotizacion_documento.id','DESC')
        ->whereIn('cotizacion_documento.tipo_venta',['127','128'])
        ->where('cotizacion_documento.estado', '!=','ANULADO')
        ->where('cotizacion_documento.sunat','0')
        ->whereRaw('ifnull((json_unquote(json_extract(cotizacion_documento.getRegularizeResponse, "$.code"))),"0000") != "1033"');

        if(!PuntoVenta() && !FullAccess())
        {
            $consulta = $consulta->where('user_id',Auth::user()->id);
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
        ->join('tabladetalles','tabladetalles.id','=','cotizacion_documento.tipo_venta')
        ->join('clientes','clientes.id','=','cotizacion_documento.cliente_id')
        ->select(
            DB::raw('(CONCAT(cotizacion_documento.serie, "-" , cotizacion_documento.correlativo)) as numero_doc'),
            'cotizacion_documento.id',
            'cotizacion_documento.fecha_documento',
            'cotizacion_documento.estado',
            'tabladetalles.descripcion as tipo',
            'clientes.nombre as cliente',
            'cotizacion_documento.total as monto',
            DB::raw('DATEDIFF( now(),cotizacion_documento.fecha_documento) as dias'),
            'cotizacion_documento.sunat',
            DB::raw('json_unquote(json_extract(cotizacion_documento.getRegularizeResponse, "$.code")) as code')
        )
        ->orderBy('cotizacion_documento.id','DESC')
        ->whereIn('cotizacion_documento.tipo_venta',['127','128'])
        ->where('cotizacion_documento.estado', '!=','ANULADO')
        ->where('cotizacion_documento.sunat', '!=','2')
        ->where(DB::raw('JSON_EXTRACT(cotizacion_documento.getRegularizeResponse, "$.code")'),'1033')
        ->where('cotizacion_documento.regularize','1');

        if(!PuntoVenta() && !FullAccess())
        {
            $consulta = $consulta->where('user_id',Auth::user()->id);
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
        $arrayLeyenda = Array();
        $arrayLeyenda[] = array(
            "code" => "1000",
            "value" => $convertir
        );
        return $arrayLeyenda;
    }

    public function obtenerProductos($id)
    {
        $detalles = Detalle::where('documento_id',$id)->where('estado', 'ACTIVO')->get();
        $arrayProductos = Array();
        for($i = 0; $i < count($detalles); $i++){

            $arrayProductos[] = array(
                "codProducto" => $detalles[$i]->codigo_producto,
                "unidad" => $detalles[$i]->unidad,
                "descripcion"=> $detalles[$i]->nombre_producto.' - '.$detalles[$i]->codigo_lote,
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
        $arrayCuotas = Array();
        $condicion = Condicion::find($documento->condicion_id);
        if($condicion->descripcion === 'CREDITO' || $condicion->descripcion === 'credito' || $condicion->descripcion === 'CRÉDITO' || $condicion->descripcion === 'crédito')
        {
            $arrayCuotas[] = array(
                "moneda" => "PEN",
                "monto" => (float)1,
                "fechaPago" => self::obtenerFechaEmision($documento)

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
        $fecha = $fecha_emision.'T'.$hora_emision.'-05:00';

        return $fecha;
    }

    public function obtenerFechaEmision($documento)
    {
        $date = strtotime($documento->fecha_documento);
        $fecha_emision = date('Y-m-d', $date);
        $hora_emision = date('H:i:s', $date);
        $fecha = $fecha_emision.'T'.$hora_emision.'-05:00';

        return $fecha;
    }

    public function obtenerFechaVencimiento($documento)
    {
        $date = strtotime($documento->fecha_vencimiento);
        $fecha_emision = date('Y-m-d', $date);
        $hora_emision = date('H:i:s', $date);
        $fecha = $fecha_emision.'T'.$hora_emision.'-05:00';

        return $fecha;
    }

    public function sunat($id)
    {
        try
        {
            $documento = Documento::findOrFail($id);
            //OBTENER CORRELATIVO DEL COMPROBANTE ELECTRONICO
            $existe = event(new DocumentoNumeracion($documento));
            if($existe[0]){
                if ($existe[0]->get('existe') == true) {
                    if ($documento->sunat != '1') {
                        //ARREGLO COMPROBANTE
                        $arreglo_comprobante = array(
                            "tipoOperacion" => $documento->tipoOperacion(),
                            "tipoDoc"=> $documento->tipoDocumento(),
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
                                )),
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
                            if($json_sunat->sunatResponse->cdrResponse->code == "0")
                            {
                                $documento->sunat = '1';
                                $respuesta_cdr = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                                $respuesta_cdr = json_decode($respuesta_cdr, true);
                                $documento->getCdrResponse = $respuesta_cdr;

                                $data_comprobante = generarComprobanteapi(json_encode($arreglo_comprobante), $documento->empresa_id);
                                $name = $documento->serie."-".$documento->correlativo.'.pdf';

                                $data_cdr = base64_decode($json_sunat->sunatResponse->cdrZip);
                                $name_cdr = 'R-'.$documento->serie."-".$documento->correlativo.'.zip';

                                if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'sunat'))) {
                                    mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'sunat'));
                                }

                                if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'cdr'))) {
                                    mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'cdr'));
                                }

                                $pathToFile = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'sunat'.DIRECTORY_SEPARATOR.$name);
                                $pathToFile_cdr = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'cdr'.DIRECTORY_SEPARATOR.$name_cdr);

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

                                $name_qr = $documento->serie."-".$documento->correlativo.'.svg';

                                if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'qrs'))) {
                                    mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'qrs'));
                                }

                                $pathToFile_qr = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'qrs'.DIRECTORY_SEPARATOR.$name_qr);

                                file_put_contents($pathToFile_qr, $data_qr);

                                /********************************/

                                $data_xml = generarXmlapi(json_encode($arreglo_comprobante), $documento->empresa_id);
                                $name_xml = $documento->serie.'-'.$documento->correlativo.'.xml';
                                $pathToFile_xml = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.$name_xml);
                                if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'xml'))) {
                                    mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'xml'));
                                }
                                file_put_contents($pathToFile_xml, $data_xml);

                                /********************************* */

                                $documento->nombre_comprobante_archivo = $name;
                                $documento->hash = $json_sunat->hash;
                                $documento->xml = $name_xml;
                                $documento->ruta_comprobante_archivo = 'public/sunat/'.$name;
                                $documento->ruta_qr = 'public/qrs/'.$name_qr;
                                $documento->update();


                                //Registro de actividad
                                $descripcion = "SE AGREGÓ EL COMPROBANTE ELECTRONICO: ". $documento->serie."-".$documento->correlativo;
                                $gestion = "COMPROBANTES ELECTRONICOS";
                                crearRegistro($documento , $descripcion , $gestion);

                                Session::flash('success','Documento de Venta enviada a Sunat con exito.');
                                $dato = "Message";
                                broadcast(new NotifySunatEvent($dato));
                                return view('consultas.ventas.alertas.envio',[

                                    'id_sunat' => $json_sunat->sunatResponse->cdrResponse->id,
                                    'descripcion_sunat' => $json_sunat->sunatResponse->cdrResponse->description,
                                    'notas_sunat' => $json_sunat->sunatResponse->cdrResponse->notes,
                                    'sunat_exito' => true

                                ])->with('sunat_exito', 'success');

                            }
                            else
                            {
                                $documento->sunat = '0';

                                $id_sunat = $json_sunat->sunatResponse->cdrResponse->code;
                                $descripcion_sunat = $json_sunat->sunatResponse->cdrResponse->description;

                                $respuesta_error = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                                $respuesta_error = json_decode($respuesta_error, true);
                                $documento->getCdrResponse = $respuesta_error;

                                $documento->update();
                                Session::flash('error','Documento de Venta sin exito en el envio a sunat.');
                                $dato = "Message";
                                broadcast(new NotifySunatEvent($dato));
                                return view('consultas.ventas.alertas.envio',[
                                    'id_sunat' =>  $id_sunat,
                                    'descripcion_sunat' =>  $descripcion_sunat,
                                    'sunat_error' => true,

                                ])->with('sunat_error', 'error');
                            }
                        }else{

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


                            }else {
                                $id_sunat = $json_sunat->sunatResponse->cdrResponse->id;
                                $descripcion_sunat = $json_sunat->sunatResponse->cdrResponse->description;

                                $respuesta_error = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                                $respuesta_error = json_decode($respuesta_error, true);
                                $documento->getCdrResponse = $respuesta_error;
                            };

                            $documento->update();
                            Session::flash('error','Documento de Venta sin exito en el envio a sunat.');
                            $dato = "Message";
                            broadcast(new NotifySunatEvent($dato));
                            return view('consultas.ventas.alertas.envio',[
                                'id_sunat' =>  $id_sunat,
                                'descripcion_sunat' =>  $descripcion_sunat,
                                'sunat_error' => true,

                            ])->with('sunat_error', 'error');
                        }
                    }else{
                        $documento->sunat = '1';
                        $documento->update();
                        Session::flash('error','Documento de venta fue enviado a Sunat.');
                        return redirect()->route('consultas.ventas.alerta.envio')->with('sunat_existe', 'error');
                    }
                }else{
                    Session::flash('error','Tipo de Comprobante no registrado en la empresa.');
                    return redirect()->route('consultas.ventas.alerta.envio')->with('sunat_existe', 'error');
                }
            }else{
                Session::flash('error','Empresa sin parametros para emitir comprobantes electronicos');
                return redirect()->route('consultas.ventas.alerta.envio');
            }
        }
        catch(Exception $e)
        {
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

        try
        {
            $documento = Documento::findOrFail($id);
            $json_data = json_decode($documento->getRegularizeResponse, false);
            if($documento->regularize == '1' && $json_data->code == '1033')
            {
                $documento->regularize = '0';
                $documento->sunat = '1';
                $documento->update();
                Session::flash('success','Documento de Venta regularizado con exito.');
                return view('consultas.ventas.alertas.regularize',[

                    'id_sunat' => $documento->serie.'-'.$documento->correlativo,
                    'descripcion_sunat' => 'CDR regularizado.',
                    'notas_sunat' => '',
                    'sunat_exito' => true

                ])->with('sunat_exito', 'success');
            }
            else
            {
                Session::flash('error','Este documento tiene un error diferente al CDR, intentar enviar a sunat.');
                return redirect()->route('consultas.ventas.alerta.regularize')->with('sunat_existe', 'error');
            }
        }
        catch(Exception $e)
        {
            Session::flash('error', 'No se puede conectar con el servidor, porfavor intentar nuevamente.'); //$e->getMessage()
            return redirect()->route('consultas.ventas.alerta.regularize');
        }

    }
}
