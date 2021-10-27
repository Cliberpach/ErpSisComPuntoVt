<?php

namespace App\Http\Controllers\Ventas;

use App\Almacenes\Producto;
use App\Events\GuiaRegistrado;
use App\Events\NumeracionGuiaRemision;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Empresa\Empresa;
use App\Ventas\Cliente;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\ErrorGuia;
use App\Ventas\Guia;
use App\Ventas\Tienda;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade as PDF;
use Exception;

class GuiaController extends Controller
{
    public function index()
    {
        return view('ventas.guias.index');
    }

    public function create($id)
    {
        
        $empresas = Empresa::where('estado','ACTIVO')->get();
        $documento = Documento::findOrFail($id);
        $detalles = Detalle::where('documento_id',$id)->get();
        $clientes = Cliente::where('estado', 'ACTIVO')->get();
        $productos = Producto::where('estado', 'ACTIVO')->get();
        $direccion_empresa = Empresa::findOrFail($documento->empresa_id);

        /*$pesos_productos =  DB::table('cotizacion_documento_detalles')
                    ->join('lote_productos','lote_productos.id','=','cotizacion_documento_detalles.lote_id')
                    ->join('productos','productos.id','=','lote_productos.producto_id')
                    ->select('productos.*','cotizacion_documento_detalles.*')
                    ->where('cotizacion_documento_detalles.documento_id','=',$id)
                    ->sum("productos.peso_producto");*/
        $pesos_productos = 0.00;
        foreach($detalles as $detalle)
        {
            $peso_item = $detalle->cantidad * $detalle->lote->producto->peso_producto;
            $pesos_productos = $pesos_productos + $peso_item;
        }
        
        
        $cantidad_productos =  DB::table('cotizacion_documento_detalles')
                    ->where('cotizacion_documento_detalles.documento_id','=',$id)
                    ->sum("cotizacion_documento_detalles.cantidad");

        return view('ventas.guias.create',[

            'documento' => $documento,
            'detalles' => $detalles,
            'empresas' => $empresas,
            'direccion_empresa' => $direccion_empresa,
            'clientes' => $clientes,
            'productos' => $productos,
            'pesos_productos' => $pesos_productos,
            'cantidad_productos' => $cantidad_productos

        ]);



    }

    public function getGuias()
    {
        $guias = Guia::orderBy('id','DESC')->get();
        $coleccion = collect([]);
        foreach($guias as $guia){
            $coleccion->push([
                'id' => $guia->id,
                "numero" =>  ($guia->documento->serie && $guia->documento->correlativo) ? $guia->documento->serie.'-'.$guia->documento->correlativo : '-',
                'tipo_venta' => ($guia->documento->sunat == '1') ? $guia->documento->descripcionTipo() : $guia->documento->nombreTipo()  ,
                'tipo_pago' => $guia->documento->tipo_pago,
                'cliente' => $guia->documento->tipo_documento_cliente.': '.$guia->documento->documento_cliente.' - '.$guia->documento->cliente,
                'fecha_documento' =>  Carbon::parse($guia->documento->fecha_documento)->format( 'd/m/Y'),
                'estado' => $guia->estado,
                "serie_guia" => $guia->serie.'-'.$guia->correlativo,
                'cantidad' => $guia->cantidad_productos. ' NIU',
                'peso' => $guia->peso_productos.' kG',
                'ruta_comprobante_archivo' => $guia->ruta_comprobante_archivo,
                'nombre_comprobante_archivo' => $guia->nombre_comprobante_archivo,
                'sunat' => $guia->sunat,
            ]);
        }

        return DataTables::of($coleccion)->toJson();
  
    }

    public function store(Request $request)
    {
        try
        {
            DB::beginTransaction();
            $data = $request->all();
            $rules = [
                'documento_id'=> 'required',
                'cantidad_productos'=> 'required',
                'peso_productos'=> 'required',
                'tienda'=> 'nullable',
                'observacion' => 'nullable',
                'direccion_empresa' => 'required',
                'ubigeo_llegada'=> 'required',
                'ubigeo_partida'=> 'required',
                
                
            ];
            $message = [
                'documento_id.required' => 'El campo Documento es obligatorio.',
                'direccion_empresa.required' => 'El campo direccion de llegada es obligatorio.',
                'cantidad_productos.required' => 'El campo Cantidad de Productos es obligatorio.',
                'peso_productos.required' => 'El campo Peso de Productos es obligatorio.',
                'ubigeo_llegada.required' => 'El campo Ubigeo es obligatorio.',
                'ubigeo_partida.required' => 'El campo Ubigeo es obligatorio.',


            ];
            Validator::make($data, $rules, $message)->validate();

            $guia = Guia::where('documento_id',$request->get('documento_id'))->get();

            if (count($guia) == 0) {
                $guia = new Guia();
                $guia->documento_id = $request->get('documento_id');

                $guia->tienda = $request->get('tienda');

                $guia->ruc_transporte_oficina = '-';
                $guia->nombre_transporte_oficina = '-';

                $guia->ruc_transporte_domicilio = '-';
                $guia->nombre_transporte_domicilio = '-';
                $guia->direccion_llegada = $request->get('direccion_tienda');

                $guia->cantidad_productos = $request->get('cantidad_productos');
                $guia->peso_productos = $request->get('peso_productos');
                $guia->observacion = $request->get('observacion');
                $guia->ubigeo_llegada = $request->get('ubigeo_llegada');
                $guia->ubigeo_partida = $request->get('ubigeo_partida');
                $guia->dni_conductor = $request->get('dni_conductor');
                $guia->placa_vehiculo = $request->get('placa_vehiculo');
                $guia->save(); 

                $envio_prev = self::sunat_prev($guia->id);

                if(!$envio_prev['success'])
                {
                    DB::rollBack();
                    Session::flash('error',$envio_prev['mensaje']);
                    return back()->with('sunat_error', 'error');
                }
                
                
                DB::commit();
                $envio_post = self::sunat_post($guia->id);
                $guia_pdf = self::guia_pdf($guia->id);
                Session::flash('success','Guia de Remision creada.');
                return redirect()->route('ventas.guiasremision.index')->with('guardar', 'success');
            }else{
                Session::flash('error','Guia de Remision ya ha sido creado.');
                return redirect()->route('ventas.guiasremision.index');
            }
        }  
        catch(Exception $e)
        {
            DB::rollBack();
            return back()->with('error' , $e->getMessage());
        }      
    }

    public function obtenerFecha($guia)
    {
        $date = strtotime($guia->documento->fecha_documento);
        $fecha_emision = date('Y-m-d', $date); 
        $hora_emision = date('H:i:s', $date); 
        $fecha = $fecha_emision.'T'.$hora_emision.'-05:00';

        return $fecha;
    }

    public function obtenerProductos($guia)
    {
        $detalles = Detalle::where('documento_id',$guia->documento_id)->get();
        
        $arrayProductos = Array();
        for($i = 0; $i < count($detalles); $i++){

            $arrayProductos[] = array(
                "codigo" => $detalles[$i]->codigo_producto,
                "unidad" => $detalles[$i]->unidad,
                "descripcion"=> $detalles[$i]->nombre_producto.' - '.$detalles[$i]->codigo_lote,
                "cantidad" => $detalles[$i]->cantidad,
                "codProdSunat" => '10',
            );
        }

        return $arrayProductos;
    }

    public function condicionReparto($guia)
    {
        $Transportista = array(  
            "tipoDoc"=> "6",
            "numDoc"=> $guia->ruc_transporte_domicilio,
            "rznSocial"=> $guia->nombre_transporte_domicilio,
            "placa"=> $guia->placa_vehiculo,
            "choferTipoDoc"=> "1",
            "choferDoc"=> $guia->dni_conductor
        );

        return $Transportista;
    }

    public function limitarDireccion($cadena, $limite, $sufijo){
        
        if(strlen($cadena) > $limite){
            return substr($cadena, 0, $limite) . $sufijo;
        }
        
        return $cadena;
    }

    public function show($id)
    {
        $guia = Guia::with(['documento','documento.detalles','documento.detalles.lote','documento.detalles.lote.producto'])->findOrFail($id);
        if ($guia->sunat == '0' || $guia->sunat == '2' ) {
            //ARREGLO GUIA
            $arreglo_guia = array(
                    "tipoDoc" => "09",
                    "serie" => "000",
                    "correlativo"=> "000",
                    "fechaEmision" => self::obtenerFecha($guia),

                    "company" => array(  
                        "ruc" => $guia->documento->ruc_empresa,
                        "razonSocial" => $guia->documento->empresa,
                        "address" => array(
                            "direccion" => $guia->documento->direccion_fiscal_empresa,
                        )),


                    "destinatario" => array(  
                        "tipoDoc" =>  $guia->documento->tipoDocumentoCliente(),
                        "numDoc" => $guia->documento->documento_cliente,
                        "rznSocial" => $guia->documento->cliente,
                        "address" => array(
                            "direccion" => $guia->documento->direccion_cliente,
                        )
                    ),

                    "observacion" => $guia->observacion,
                    
                    "envio" => array(
                        "modTraslado" =>  "01",
                        "codTraslado" =>  "01",
                        "desTraslado" =>  "VENTA",
                        "fecTraslado" =>  self::obtenerFecha($guia),//FECHA DEL TRANSLADO
                        "codPuerto" => "123",
                        "indTransbordo"=> false,
                        "pesoTotal" => $guia->peso_productos,
                        "undPesoTotal"=> "KGM",
                        "numBultos" => $guia->cantidad_productos,
                        "llegada" => array(
                            "ubigueo" =>  $guia->ubigeo_llegada,
                            "direccion" => self::limitarDireccion($guia->direccion_llegada,50,"..."),
                        ),
                        "partida" => array(
                            "ubigueo" => $guia->ubigeo_partida,
                            "direccion" => self::limitarDireccion($guia->documento->direccion_fiscal_empresa,50,"..."),
                        ),
                        "transportista"=> self::condicionReparto($guia)
                    ),

                    "details" =>  self::obtenerProductos($guia),
            );
            
            
            $numeracion= json_encode($arreglo_guia);
            $data = pdfGuiaapi($numeracion);
            $name = $guia->id.'.pdf';
            $pathToFile = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'guias'.DIRECTORY_SEPARATOR.$name);
            if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'guias'))) {
                mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'guias'));
            }
            file_put_contents($pathToFile, $data);
            $empresa = Empresa::first();
            $pdf = PDF::loadview('ventas.guias.reportes.guia',[
                'guia' => $guia,
                'empresa' => $empresa,
                ])->setPaper('a4')->setWarnings(false);
            return $pdf->stream('guia.pdf');

            //return response()->file($pathToFile);
        }else{
            $existe = event(new NumeracionGuiaRemision($guia));
            //OBTENER CORRELATIVO DE LA GUIA DE REMISION
            $numeracion = event(new GuiaRegistrado($guia, $existe[0]->get('numeracion')->serie));
            //ENVIAR GUIA PARA LUEGO GENERAR PDF
            $data = pdfGuiaapi($numeracion[0]);
            $name = $guia->id.'.pdf';
            $pathToFile = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'guias'.DIRECTORY_SEPARATOR.$name);
            if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'guias'))) {
                mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'guias'));
            }
            file_put_contents($pathToFile, $data);

            $empresa = Empresa::first();
            $pdf = PDF::loadview('ventas.guias.reportes.guia',[
                'guia' => $guia,
                'empresa' => $empresa,
                ])->setPaper('a4')->setWarnings(false);
            return $pdf->stream('guia.pdf');

            //return response()->file($pathToFile);
        }


    }
    
    public function sunat($id)
    {
        $guia = Guia::findOrFail($id);
        //OBTENER CORRELATIVO DE LA GUIA DE REMISION
        $existe = event(new NumeracionGuiaRemision($guia));
        if($existe[0]){
            if ($existe[0]->get('existe') == true) {
                if ($guia->sunat != '1') {
                    //ARREGLO GUIA
                    $arreglo_guia = array(
                            "tipoDoc" => "09",
                            "serie" => $existe[0]->get('numeracion')->serie,
                            "correlativo"=> $guia->correlativo,
                            "fechaEmision" => self::obtenerFecha($guia),

                            "company" => array(  
                                "ruc" => $guia->documento->ruc_empresa,
                                "razonSocial" => $guia->documento->empresa,
                                "address" => array(
                                    "direccion" => $guia->documento->direccion_fiscal_empresa,
                                )),


                            "destinatario" => array(  
                                "tipoDoc" =>  $guia->documento->tipoDocumentoCliente(),
                                "numDoc" => $guia->documento->documento_cliente,
                                "rznSocial" => $guia->documento->cliente,
                                "address" => array(
                                    "direccion" => $guia->documento->direccion_cliente,
                                )
                            ),

                            "observacion" => $guia->observacion,
                            
                            "envio" => array(
                                "modTraslado" =>  "01",
                                "codTraslado" =>  "01",
                                "desTraslado" =>  "VENTA",
                                "fecTraslado" =>  self::obtenerFecha($guia),//FECHA DEL TRANSLADO
                                "codPuerto" => "123",
                                "indTransbordo"=> false,
                                "pesoTotal" => $guia->peso_productos,
                                "undPesoTotal"=> "KGM",
                                "numBultos" => $guia->cantidad_productos,
                                "llegada" => array(
                                    "ubigueo" =>  $guia->ubigeo_llegada,
                                    "direccion" => self::limitarDireccion($guia->direccion_llegada,50,"..."),
                                ),
                                "partida" => array(
                                    "ubigueo" => $guia->ubigeo_partida,
                                    "direccion" => self::limitarDireccion($guia->documento->direccion_fiscal_empresa,50,"..."),
                                ),
                                "transportista"=> self::condicionReparto($guia)
                            ),

                            "details" =>  self::obtenerProductos($guia),
                    );
                    
                    $data = enviarGuiaapi(json_encode($arreglo_guia));
                    //RESPUESTA DE LA SUNAT EN JSON
                    $json_sunat = json_decode($data);

                    if ($json_sunat->sunatResponse->success == true) {
                
                        $guia->sunat = '1';
                        $data = pdfGuiaapi(json_encode($arreglo_guia));
                        $name = $existe[0]->get('numeracion')->serie."-".$guia->correlativo.'.pdf';
                        $pathToFile = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'sunat'.DIRECTORY_SEPARATOR.'guia'.DIRECTORY_SEPARATOR.$name);
                        if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'sunat'.DIRECTORY_SEPARATOR.'guia'))) {
                            mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'sunat'.DIRECTORY_SEPARATOR.'guia'));
                        }

                        //file_put_contents($pathToFile, $data);
                        $empresa = Empresa::first();
                        PDF::loadview('ventas.guias.reportes.guia',[
                            'guia' => $guia,
                            'empresa' => $empresa,
                            ])->setPaper('a4')->setWarnings(false)
                            ->save(public_path().'/storage/sunat/guia/'.$name);

                        $guia->nombre_comprobante_archivo = $name;
                        $guia->ruta_comprobante_archivo = 'public/sunat/guia/'.$name;
                        $guia->update(); 

                        //Registro de actividad
                        $descripcion = "SE AGREGÓ LA GUIA DE REMISION ELECTRONICA: ". $existe[0]->get('numeracion')->serie."-".$guia->correlativo;
                        $gestion = "GUIA DE REMISION ELECTRONICA";
                        crearRegistro($guia , $descripcion , $gestion);
                        
                        Session::flash('success','Guia de remision enviada a Sunat con exito.');
                        return view('ventas.guias.index',[
                            
                            'id_sunat' => $json_sunat->sunatResponse->cdrResponse->id,
                            'descripcion_sunat' => $json_sunat->sunatResponse->cdrResponse->description,
                            'notas_sunat' => $json_sunat->sunatResponse->cdrResponse->notes,
                            'sunat_exito' => true

                        ])->with('sunat_exito', 'success');

                    }else{

                        //COMO SUNAT NO LO ADMITE VUELVE A SER 0 
                        $guia->sunat = '0';
                        $guia->update(); 
                        
                        if ($json_sunat->sunatResponse->error) {
                            $id_sunat = $json_sunat->sunatResponse->error->code;
                            $descripcion_sunat = $json_sunat->sunatResponse->error->message;

                        
                        }else {
                            $id_sunat = $json_sunat->sunatResponse->cdrResponse->id;
                            $descripcion_sunat = $json_sunat->sunatResponse->cdrResponse->description;
                            
                        };


                        Session::flash('error','Guia de remision sin exito en el envio a sunat.');
                        return view('ventas.guias.index',[
                            'id_sunat' =>  $id_sunat,
                            'descripcion_sunat' =>  $descripcion_sunat,
                            'sunat_error' => true,

                        ])->with('sunat_error', 'error');
                    }
                }else{
                    $guia->sunat = '1';
                    $guia->update();
                    Session::flash('error','Guia de remision fue enviado a Sunat.');
                    return redirect()->route('ventas.guiasremision.index')->with('sunat_existe', 'error');
                }

            }else{
                Session::flash('error','Guia de remision no se encuentra registrado en la empresa.');
                return redirect()->route('ventas.guiasremision.index')->with('sunat_existe', 'error');
            }
        }else{
            Session::flash('error','Empresa sin parametros para emitir Guia de remisión remitente electrónica.');
            return redirect()->route('ventas.guiasremision.index');
        }
    }

    public function sunat_prev($id)
    {
        try
        {
            $guia = Guia::findOrFail($id);
            //OBTENER CORRELATIVO DE LA GUIA DE REMISION
            $existe = event(new NumeracionGuiaRemision($guia));
            if($existe[0]){
                if ($existe[0]->get('existe') == true) {
                    return array('success' => true,'mensaje' => 'Guia validada.');
                }else{
                    $errorGuia = new ErrorGuia();
                    $errorGuia->guia_id = $guia->id;
                    $errorGuia->tipo = 'sunat-existe';
                    $errorGuia->descripcion = 'Error al crear serie y correlativo';
                    $errorGuia->ecxepcion = 'Guia de remision no se encuentra registrado en la empresa.';
                    $errorGuia->save();
                    return array('success' => false,'mensaje' => 'Guia de remision no se encuentra registrado en la empresa.');
                    // Session::flash('error','Guia de remision no se encuentra registrado en la empresa.');
                    // return redirect()->route('ventas.guiasremision.index')->with('sunat_existe', 'error');
                }
            }else{
                $errorGuia = new ErrorGuia();
                $errorGuia->guia_id = $guia->id;
                $errorGuia->tipo = 'sunat-existe';
                $errorGuia->descripcion = 'Error al crear serie y correlativo';
                $errorGuia->ecxepcion = 'Empresa sin parametros para emitir Guia de remisión remitente electrónica.';
                $errorGuia->save();
                return array('success' => false,'mensaje' => 'Empresa sin parametros para emitir Guia de remisión remitente electrónica.');
                // Session::flash('error','Empresa sin parametros para emitir Guia de remisión remitente electrónica.');
                // return redirect()->route('ventas.guiasremision.index');
            }
        }
        catch(Exception $e)
        {
            $guia = Guia::findOrFail($id);

            $errorGuia = new ErrorGuia();
            $errorGuia->guia_id = $guia->id;
            $errorGuia->tipo = 'sunat-existe';
            $errorGuia->descripcion = 'Error crear serie y correlativo';
            $errorGuia->ecxepcion = $e->getMessage();
            $errorGuia->save();
            return array('success' => false,'mensaje' => $e->getMessage());
        }
    }

    public function sunat_post($id)
    {
        try{
            $guia = Guia::findOrFail($id);
            if ($guia->sunat != '1') {
                //ARREGLO GUIA
                $arreglo_guia = array(
                        "tipoDoc" => "09",
                        "serie" => $guia->serie,
                        "correlativo"=> $guia->correlativo,
                        "fechaEmision" => self::obtenerFecha($guia),

                        "company" => array(  
                            "ruc" => $guia->documento->ruc_empresa,
                            "razonSocial" => $guia->documento->empresa,
                            "address" => array(
                                "direccion" => $guia->documento->direccion_fiscal_empresa,
                            )),


                        "destinatario" => array(  
                            "tipoDoc" =>  $guia->documento->tipoDocumentoCliente(),
                            "numDoc" => $guia->documento->documento_cliente,
                            "rznSocial" => $guia->documento->cliente,
                            "address" => array(
                                "direccion" => $guia->documento->direccion_cliente,
                            )
                        ),

                        "observacion" => $guia->observacion,
                        
                        "envio" => array(
                            "modTraslado" =>  "01",
                            "codTraslado" =>  "01",
                            "desTraslado" =>  "VENTA",
                            "fecTraslado" =>  self::obtenerFecha($guia),//FECHA DEL TRANSLADO
                            "codPuerto" => "123",
                            "indTransbordo"=> false,
                            "pesoTotal" => $guia->peso_productos,
                            "undPesoTotal"=> "KGM",
                            "numBultos" => $guia->cantidad_productos,
                            "llegada" => array(
                                "ubigueo" =>  $guia->ubigeo_llegada,
                                "direccion" => self::limitarDireccion($guia->direccion_llegada,50,"..."),
                            ),
                            "partida" => array(
                                "ubigueo" => $guia->ubigeo_partida,
                                "direccion" => self::limitarDireccion($guia->documento->direccion_fiscal_empresa,50,"..."),
                            ),
                            "transportista"=> self::condicionReparto($guia)
                        ),

                        "details" =>  self::obtenerProductos($guia),
                );
                
                $data = enviarGuiaapi(json_encode($arreglo_guia));
                //RESPUESTA DE LA SUNAT EN JSON
                $json_sunat = json_decode($data);

                if ($json_sunat->sunatResponse->success == true) {
            
                    $guia->sunat = '1';
                    $data = pdfGuiaapi(json_encode($arreglo_guia));
                    $name = $guia->serie."-".$guia->correlativo.'.pdf';
                    $pathToFile = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'sunat'.DIRECTORY_SEPARATOR.'guia'.DIRECTORY_SEPARATOR.$name);
                    if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'sunat'.DIRECTORY_SEPARATOR.'guia'))) {
                        mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'sunat'.DIRECTORY_SEPARATOR.'guia'));
                    }

                    //file_put_contents($pathToFile, $data);
                    

                    $guia->nombre_comprobante_archivo = $name;
                    $guia->ruta_comprobante_archivo = 'public/sunat/guia/'.$name;
                    $guia->update(); 

                    //Registro de actividad
                    $descripcion = "SE AGREGÓ LA GUIA DE REMISION ELECTRONICA: ". $guia->serie."-".$guia->correlativo;
                    $gestion = "GUIA DE REMISION ELECTRONICA";
                    crearRegistro($guia , $descripcion , $gestion);
                    
                    // Session::flash('success','Guia de remision enviada a Sunat con exito.');
                    // return view('ventas.guias.index',[
                        
                    //     'id_sunat' => $json_sunat->sunatResponse->cdrResponse->id,
                    //     'descripcion_sunat' => $json_sunat->sunatResponse->cdrResponse->description,
                    //     'notas_sunat' => $json_sunat->sunatResponse->cdrResponse->notes,
                    //     'sunat_exito' => true

                    // ])->with('sunat_exito', 'success');

                    return array('success' => true,'mensaje' => 'Guia de remisión enviada a Sunat con exito.');

                }else{

                    //COMO SUNAT NO LO ADMITE VUELVE A SER 0 
                    $guia->sunat = '0';
                    $guia->update(); 
                    
                    if ($json_sunat->sunatResponse->error) {
                        $id_sunat = $json_sunat->sunatResponse->error->code;
                        $descripcion_sunat = $json_sunat->sunatResponse->error->message;

                    
                    }else {
                        $id_sunat = $json_sunat->sunatResponse->cdrResponse->id;
                        $descripcion_sunat = $json_sunat->sunatResponse->cdrResponse->description;
                        
                    };


                    $errorGuia = new ErrorGuia();
                    $errorGuia->guia_id = $guia->id;
                    $errorGuia->tipo = 'sunat-envio';
                    $errorGuia->descripcion = 'Error al enviar a sunat';
                    $errorGuia->ecxepcion = $descripcion_sunat;
                    $errorGuia->save();

                    return array('success' => false, 'mensaje' => $descripcion_sunat);
                }
            }else{
                $guia->sunat = '1';
                $guia->update();
                return array('success' => false, 'mensaje' => 'Guia de remision ya fue enviado a Sunat.');
            }
        }
        catch(Exception $e)
        {
            $guia = Guia::find($id);

            $errorGuia = new ErrorGuia();
            $errorGuia->guia_id = $guia->id;
            $errorGuia->tipo = 'sunat-envio';
            $errorGuia->descripcion = 'Error al enviar a sunat';
            $errorGuia->ecxepcion = $e->getMessage();
            $errorGuia->save();
            return array('success' => false, 'mensaje' => $e->getMessage());
        }
    }

    public function guia_pdf($id)
    {
        try
        {
            $guia = Guia::find($id);
            $empresa = Empresa::first();
            PDF::loadview('ventas.guias.reportes.guia',[
                'guia' => $guia,
                'empresa' => $empresa,
                ])->setPaper('a4')->setWarnings(false)
                ->save(public_path().'/storage/sunat/guia/'.$guia->nombre_comprobante_archivo);
            return array('success' => true,'mensaje' => 'Guia de remision validado.');
        }
        catch(Exception $e)
        {
            $guia = Guia::find($id);

            $errorGuia = new ErrorGuia();
            $errorGuia->guia_id = $guia->id;
            $errorGuia->tipo = 'pdf';
            $errorGuia->descripcion = 'Error al generar pdf';
            $errorGuia->ecxepcion = $e->getMessage();
            $errorGuia->save();
            return array('success' => false,'mensaje' => 'Guia de remision no validado.');
        }
    }

}
