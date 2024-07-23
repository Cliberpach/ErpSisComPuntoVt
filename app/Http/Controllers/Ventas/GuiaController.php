<?php

namespace App\Http\Controllers\Ventas;

use App\Almacenes\DetalleNotaSalidad;
use App\Almacenes\LoteProducto;
use App\Almacenes\NotaSalidad;
use App\Almacenes\Producto;
use App\Events\GuiaRegistrado;
use App\Events\NotifySunatEvent;
use App\Events\NumeracionGuiaRemision;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Empresa\Empresa;
use App\Mantenimiento\Tabla\Detalle as TablaDetalle;
use App\Ventas\Cliente;
use App\Ventas\DetalleGuia;
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
use Illuminate\Support\Facades\Auth;
use stdClass;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use Greenter\Model\Client\Client;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Response\CdrResponse;
use Greenter\Model\Response\SummaryResult;
use Greenter\Ws\Services\SunatEndpoints;
use App\Greenter\Utils\Util;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response; 

class GuiaController extends Controller
{
    public function index()
    {
        $dato = "Message";
        broadcast(new NotifySunatEvent($dato));
        return view('ventas.guias.index');
    }

    public function getGuias()
    {
        $guias = Guia::where('estado', '!=', 'NULO')->orderBy('id','DESC')->get();
        $coleccion = collect([]);
        foreach($guias as $guia){
            $coleccion->push([
                'id' => $guia->id,
                "numero" =>  $guia->documento ? (($guia->documento->serie && $guia->documento->correlativo) ? $guia->documento->serie.'-'.$guia->documento->correlativo : '-') : '-',
                'tipo_venta' => $guia->documento ? (($guia->documento->sunat == '1') ? $guia->documento->descripcionTipo() : $guia->documento->nombreTipo()) : '-'  ,
                'tipo_pago' => $guia->documento ? $guia->documento->tipo_pago : '-',
                'cliente' => $guia->tipo_documento_cliente.': '.$guia->documento_cliente.' - '.$guia->cliente,
                'fecha_documento' =>  Carbon::parse($guia->fecha_emision)->format( 'd/m/Y'),
                'estado' => $guia->estado,
                "serie_guia"    => $guia->serie.'-'.$guia->correlativo,
                'cantidad'      => $guia->cantidad_productos. ' NIU',
                'peso'          => $guia->peso_productos.' kG',
                'ruta_comprobante_archivo'      => $guia->ruta_comprobante_archivo,
                'nombre_comprobante_archivo'    => $guia->nombre_comprobante_archivo,
                'sunat'             =>  $guia->sunat,
                'estado_sunat'      =>  $guia->estado_sunat,
                'regularize'        =>  $guia->regularize,
                'ruta_xml'          =>  $guia->ruta_xml,
                'ruta_cdr'          =>  $guia->ruta_cdr,
                'cdr_response_code' =>  $guia->cdr_response_code,
                'ticket'            =>  $guia->ticket
            ]);
        }

        return DataTables::of($coleccion)->toJson();

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

    public function create_new()
    {

        $empresas = Empresa::where('estado','ACTIVO')->get();
        $clientes = Cliente::where('estado', 'ACTIVO')->get();
        $empresa = Empresa::first();
        $hoy = Carbon::now()->toDateString();

        $fullaccess = false;

        if (count(Auth::user()->roles) > 0) {
            $cont = 0;
            while ($cont < count(Auth::user()->roles)) {
                if (Auth::user()->roles[$cont]['full-access'] == 'SI') {
                    $fullaccess = true;
                    $cont = count(Auth::user()->roles);
                }
                $cont = $cont + 1;
            }
        }

        return view('ventas.guias.create_new',[
            'empresas' => $empresas,
            'empresa' => $empresa,
            'clientes' => $clientes,
            'hoy' => $hoy,
            'fullaccess' => $fullaccess,
        ]);



    }

    public function consulta_ticket($id){
        try {
            //==== obtener la guía por su id =====
            $guia   =   Guia::findOrFail($id);
            $ticket =   $guia->ticket;

            if($ticket){
               
                $util = Util::getInstance();
                //===== iniciar greenter api ====
                $api = $this->controlConfiguracionGreenter($util); 
                //======== CONSULTANDO ESTADO DE LA GUÍA =====
                $res = $api->getStatus($ticket);

            
                //======== response estructura =======
                    /*  code: 99(envío con error)   |   cdrResponse (null o con contenido)
                        code: 98(envío en proceso)  |   cdrResponse(aún sin cdr)
                        code: 0(envío ok)           |   cdrResponse(con contenido)    
                    */
                $code_estado    =   $res->getCode();
                $cdr_response   =   $res->getCdrResponse();
                $descripcion    =   null;

                $guia->response_success =   $res->isSuccess();

                if($code_estado == 0){
                    $descripcion            =   'ACEPTADA';
                    $guia->sunat            =   '1';
                    $guia->regularize       =   '0';
                    $guia->response_code    =   $code_estado;
                    

                    //==== GUARDANDO DATOS DEL CDRZIP =====
                    $guia->cdr_response_id          =   $cdr_response->getId();
                    $guia->cdr_response_code        =   $cdr_response->getCode();
                    $guia->cdr_response_description =   $cdr_response->getDescription();
                    $guia->cdr_response_reference   =   $cdr_response->getReference();



                    //========= GUARDANDO NOTES ======
                    $response_notes =   '';
                    foreach ($cdr_response->getNotes() as $note) {
                       $response_notes.= '|'.$note.'|';
                    }
                    $guia->cdr_response_notes   =   $response_notes;
                    
                    
                    //====== GUARDANDO CDR  =========== 
                    $util->writeCdr(null, $res->getCdrZip(), "GUIA REMISION",$guia->despatch_name);
                    $guia->ruta_cdr      =   'storage/greenter/guías_remisión/cdr/'.$guia->despatch_name.'.zip';
                    

                    //========= GENERANDO QR =========
                    $miQr = QrCode::format('svg')
                    ->size(130) //defino el tamaño
                    ->backgroundColor(0, 0, 0) //defino el fondo
                    ->color(255, 255, 255)
                    ->margin(1) //defino el margen
                    ->generate($cdr_response->getReference());

                    $directoryPath = public_path('storage/greenter/guías_remisión/qrs');

                    $fileName = $guia->despatch_name . '.svg';
                    $pathToFile_qr = $directoryPath . DIRECTORY_SEPARATOR . $fileName;

                    // Crea el directorio si no existe
                    if (!File::exists($directoryPath)) {
                        File::makeDirectory($directoryPath, 0755, true); 
                    }

                    File::put($pathToFile_qr, $miQr);

                    
                    $guia->ruta_qr  =   $pathToFile_qr;   
                    $guia->update();
                }

                if($code_estado == 98){
                    $descripcion            = 'EN PROCESO';
                    $guia->sunat            = '1';
                    $guia->regularize       = '0';
                    $guia->response_code    = $code_estado;
                    $guia->update();
                }

                if($code_estado == 99 && $cdr_response){
                    $descripcion            = 'EN PROCESO';
                    $guia->sunat            = '1';
                    $guia->regularize       = '1';
                    $guia->response_code    = $code_estado;
                   

                    $descripcion    =   'ENVÍO CON ERROR CON GENERACIÓN DE CDR';

                    //==== GUARDANDO DATOS DEL CDRZIP =====
                    $guia->cdr_response_id          =   $cdr_response->getId();
                    $guia->cdr_response_code        =   $cdr_response->getCode();
                    $guia->cdr_response_description =   $cdr_response->getDescription();
                    $guia->cdr_response_reference   =   $cdr_response->getReference();
  
                    //========= GUARDANDO NOTES ======
                    $response_notes =   '';
                    foreach ($cdr_response->getNotes() as $note) {
                        $response_notes.= '|'.$note.'|';
                    }
                    $guia->cdr_response_notes   =   $response_notes;
                      
                   //====== GUARDANDO CDR  =========== 
                   $util->writeCdr(null, $res->getCdrZip(), "GUIA REMISION",$guia->despatch_name);
                   $guia->ruta_cdr      =   'storage/greenter/guías_remisión/cdr/'.$guia->despatch_name.'.zip';
                   $guia->update();
                }

                if($code_estado == '99' && !$cdr_response){
                    $descripcion            =   'ENVÍO CON ERROR SIN GENERACIÓN DE CDR';
                    $guia->sunat            =   '1';
                    $guia->regularize       =   '1';
                    $guia->response_code    =   $code_estado;
                    $guia->update();
                }

                //======= ARCHIVO YA PRESENTADO ANTERIORMENTE ========
                if($guia->cdr_response_code == 2223 ){
                    //======== MARCAR COMO ENVIADO =======
                    $guia->regularize       =   '0';
                    $guia->sunat            =   '1';
                    $guia->update();
                }
            

                $response = [   'code_estado'       =>  $code_estado,
                                'cdr'               =>  $cdr_response?1:0,
                                'descripcion'       =>  $descripcion,
                                'guia_actualizada'  =>  $guia];

                return response()->json([  'type' => 'success','message' => $response ], 200);
            }else{
                return response()->json(['type' => 'error',
                'message' => "La guía no contiene un ticket,debe enviar a sunat previamente" ], 333);
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
           
            return response()->json(['type' => 'error','message' => 'Guía no encontrada'], 404);
        }
        
    }

    /*public function getGuias()
    {
        $guias = Guia::where('estado', '!=', 'NULO')->orderBy('id','DESC')->get();
        $coleccion = collect([]);
        foreach($guias as $guia){
            $coleccion->push([
                'id' => $guia->id,
                "numero" =>  $guia->documento ? (($guia->documento->serie && $guia->documento->correlativo) ? $guia->documento->serie.'-'.$guia->documento->correlativo : '-') : '-',
                'tipo_venta' => $guia->documento ? (($guia->documento->sunat == '1') ? $guia->documento->descripcionTipo() : $guia->documento->nombreTipo()) : '-'  ,
                'tipo_pago' => $guia->documento ? $guia->documento->tipo_pago : '-',
                'cliente' => $guia->tipo_documento_cliente.': '.$guia->documento_cliente.' - '.$guia->cliente,
                'fecha_documento' =>  Carbon::parse($guia->fecha_emision)->format( 'd/m/Y'),
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

    }*/

    public function store(Request $request)
    {
        try
        {
            DB::beginTransaction();
            $data = $request->all();
            $rules = [
                'documento_id'=> 'nullable',
                'cantidad_productos'=> 'required',
                'peso_productos'=> 'required',
                'tienda'=> 'nullable',
                'observacion' => 'nullable',
                'direccion_empresa' => 'required',
                'ubigeo_llegada'=> 'required',
                'ubigeo_partida'=> 'required',
                'motivo_traslado'=> 'required',
            ];
            $message = [
                'direccion_empresa.required' => 'El campo direccion de llegada es obligatorio.',
                'cantidad_productos.required' => 'El campo Cantidad de Productos es obligatorio.',
                'peso_productos.required' => 'El campo Peso de Productos es obligatorio.',
                'ubigeo_llegada.required' => 'El campo Ubigeo es obligatorio.',
                'ubigeo_partida.required' => 'El campo Ubigeo es obligatorio.',
                'motivo_traslado.required' => 'El campo Motivo de traslado es obligatorio.',
            ];
            Validator::make($data, $rules, $message)->validate();

            if($request->documento_id)
            {
                $guia = Guia::where('documento_id',$request->get('documento_id'))->get();
                $documento = Documento::find($request->get('documento_id'));
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
                    $guia->ubigeo_llegada = str_pad($request->get('ubigeo_llegada'), 6, "0", STR_PAD_LEFT);
                    $guia->ubigeo_partida = str_pad($request->get('ubigeo_partida'), 6, "0", STR_PAD_LEFT);
                    $guia->dni_conductor = $request->get('dni_conductor');
                    $guia->placa_vehiculo = $request->get('placa_vehiculo');
                    $guia->placa_vehiculo = $request->get('placa_vehiculo');

                    $guia->motivo_traslado = $request->motivo_traslado;

                    $guia->fecha_emision = $documento->fecha_documento;
                    $guia->ruc_empresa = $documento->ruc_empresa;
                    $guia->empresa_id = $documento->empresa_id;
                    $guia->empresa = $documento->empresa;
                    $guia->direccion_empresa = $documento->direccion_fiscal_empresa;

                    $guia->tipo_documento_cliente = $documento->tipo_documento_cliente;
                    $guia->documento_cliente = $documento->documento_cliente;
                    $guia->direccion_cliente = $documento->direccion_cliente;
                    $guia->cliente = $documento->cliente;
                    $guia->cliente_id = $documento->cliente_id;
                    $guia->user_id = auth()->user()->id;
                    $guia->save();

                    $detalles = Detalle::where('documento_id',$request->documento_id)->get();

                    foreach($detalles as $detalle)
                    {
                        DetalleGuia::create([
                            'guia_id' => $guia->id,
                            'producto_id' => $detalle->lote->producto_id,
                            'lote_id' => $detalle->lote_id,
                            'codigo_producto' => $detalle->codigo_producto,
                            'nombre_producto' => $detalle->nombre_producto,
                            'unidad' => $detalle->unidad,
                            'cantidad' => $detalle->cantidad,
                        ]);
                    }

                    $envio_prev = self::sunat_prev($guia->id);

                    if(!$envio_prev['success'])
                    {
                        DB::rollBack();
                        Session::flash('error',$envio_prev['mensaje']);
                        return back()->with('sunat_error', 'error');
                    }


                    DB::commit();
                    //$envio_post = self::sunat_post($guia->id);
                    $guia_pdf = self::guia_pdf($guia->id);
                    Session::flash('success','Guia de Remision creada.');
                    return redirect()->route('ventas.guiasremision.index')->with('guardar', 'success');
                }else{
                    Session::flash('error','Guia de Remision ya ha sido creado.');
                    return redirect()->route('ventas.guiasremision.index');
                }
            }
            else{
                $productosJSON = $request->get('productos_tabla');
                $detalles = json_decode($productosJSON[0]);

                $fecha_hoy = Carbon::now()->toDateString();
                $fecha = Carbon::createFromFormat('Y-m-d', $fecha_hoy);
                $fecha = str_replace("-", "", $fecha);
                $fecha = str_replace(" ", "", $fecha);
                $fecha = str_replace(":", "", $fecha);
                $ngenerado = $fecha . (DB::table('nota_salidad')->count() + 1);

                $motivo = TablaDetalle::find($request->motivo_traslado);
                $tabladetalle = TablaDetalle::where('descripcion', $motivo->descripcion)->where('tabla_id', 29)->first();

                if(empty($tabladetalle))
                {
                    $destino = new TablaDetalle();
                    $destino->descripcion = $motivo->descripcion;
                    $destino->simbolo = $motivo->simbolo;
                    $destino->estado = 'ACTIVO';
                    $destino->editable = 1;
                    $destino->tabla_id = 29;
                    $destino->save();
                }
                else {
                    $destino = $tabladetalle;
                }

                $notasalidad = new NotaSalidad();
                $notasalidad->numero = $ngenerado;
                $notasalidad->fecha = Carbon::now()->toDateString();
                $notasalidad->destino = $destino->descripcion;
                $notasalidad->origen = $request->origen;
                $notasalidad->usuario = Auth()->user()->usuario;
                $notasalidad->save();

                foreach ($detalles as $fila) {
                    DetalleNotaSalidad::create([
                        'nota_salidad_id' => $notasalidad->id,
                        'lote_id' => $fila->lote_id,
                        'cantidad' => $fila->cantidad,
                        'producto_id' => $fila->producto_id,
                    ]);
                }

                $empresa = Empresa::first();
                $cliente = Cliente::findOrFail($request->cliente_id);
                $guia = new Guia();

                $guia->tienda = $request->get('tienda');
                $guia->nota_salida_id = $notasalidad->id;

                $guia->ruc_transporte_oficina = '-';
                $guia->nombre_transporte_oficina = '-';

                $guia->ruc_transporte_domicilio = '-';
                $guia->nombre_transporte_domicilio = '-';
                $guia->direccion_llegada = $request->get('direccion_tienda');

                $guia->cantidad_productos = $request->get('cantidad_productos');
                $guia->peso_productos = $request->get('peso_productos');
                $guia->observacion = $request->get('observacion');
                $guia->ubigeo_llegada = str_pad($request->get('ubigeo_llegada'), 6, "0", STR_PAD_LEFT);
                $guia->ubigeo_partida = str_pad($request->get('ubigeo_partida'), 6, "0", STR_PAD_LEFT);
                $guia->dni_conductor = $request->get('dni_conductor');
                $guia->placa_vehiculo = $request->get('placa_vehiculo');
                
                $guia->motivo_traslado = $request->motivo_traslado;

                $guia->fecha_emision = $request->get('fecha_documento');
                $guia->ruc_empresa = $empresa->ruc;
                $guia->empresa = $empresa->razon_social;
                $guia->empresa_id = $empresa->id;
                $guia->direccion_empresa = $empresa->direccion_fiscal;

                $guia->tipo_documento_cliente = $cliente->tipo_documento;
                $guia->documento_cliente = $cliente->documento;
                $guia->direccion_cliente = $cliente->direccion;
                $guia->cliente = $cliente->nombre;
                $guia->cliente_id = $cliente->id;
                $guia->user_id = auth()->user()->id;
                $guia->save();

                foreach($detalles as $detalle)
                {
                    $producto = Producto::find($detalle->producto_id);
                    DetalleGuia::create([
                        'guia_id' => $guia->id,
                        'producto_id' => $detalle->producto_id,
                        'lote_id' => $detalle->lote_id,
                        'codigo_producto' => $producto->codigo,
                        'unidad' => $producto->getMedida(),
                        'nombre_producto' => $producto->nombre,
                        'cantidad' => $detalle->cantidad,
                    ]);
                }

                $envio_prev = self::sunat_prev($guia->id);

                if(!$envio_prev['success'])
                {
                    DB::rollBack();
                    $productosJSON = $request->get('productos_tabla');
                    $detalles = json_decode($productosJSON[0]);
                    foreach ($detalles as $detalle) {
                        $lote = LoteProducto::find($detalle->lote_id);
                        $lote->cantidad_logica = $lote->cantidad_logica + $detalle->cantidad;
                        $lote->update();
                    }
                    Session::flash('error',$envio_prev['mensaje']);
                    return back()->with('sunat_error', 'error');
                }
                DB::commit();
                // $envio_post = self::sunat_post($guia->id);
                $guia_pdf = self::guia_pdf($guia->id);
                Session::flash('success','Guia de Remision creada.');
                return redirect()->route('ventas.guiasremision.index')->with('guardar', 'success');
            }
        }
        catch(Exception $e)
        {
            DB::rollBack();
            $productosJSON = $request->get('productos_tabla');
            $detalles = json_decode($productosJSON[0]);
            foreach ($detalles as $detalle) {
                $lote = LoteProducto::find($detalle->lote_id);
                $lote->cantidad_logica = $lote->cantidad_logica + $detalle->cantidad;
                $lote->update();                
            }
            return back()->with('error' , $e->getMessage());
        }
    }

    public function obtenerFecha($guia)
    {
        $date = strtotime($guia->fecha_emision);
        $fecha_emision = date('Y-m-d', $date);
        $hora_emision = date('H:i:s', $date);
        $fecha = $fecha_emision.'T'.$hora_emision.'-05:00';

        return $fecha;
    }

    public function obtenerProductos($guia)
    {
        //====== OBTENER ID GUÍA ====
        $guia_id    =   $guia->id;
        //===== OBTENER DETALLE DE LA GUÍA =====
        $guia_detalle   =   DB::select('select * from guia_detalles as gd   
                            where gd.guia_id = ?',[$guia_id]);
        
        // $detalles = DetalleGuia::where('guia_id',$guia->id)->get();

        // $arrayProductos = Array();
        // for($i = 0; $i < count($detalles); $i++){

        //     $arrayProductos[] = array(
        //         "codigo" => $detalles[$i]->codigo_producto,
        //         "unidad" => $detalles[$i]->unidad,
        //         "descripcion"=> $detalles[$i]->nombre_producto,
        //         "cantidad" => $detalles[$i]->cantidad,
        //         "codProdSunat" => '10',
        //     );
        // }

        return $guia_detalle;
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

    /*public function show($id)
    {
        $guia = Guia::with(['documento','detalles','detalles.lote','detalles.lote.producto'])->findOrFail($id);
        if ($guia->sunat == '0' || $guia->sunat == '2' ) {
            //ARREGLO GUIA
            $arreglo_guia = array(
                    "tipoDoc" => "09",
                    "serie" => "000",
                    "correlativo"=> "000",
                    "fechaEmision" => self::obtenerFecha($guia),

                    "company" => array(
                        "ruc" => $guia->ruc_empresa,
                        "razonSocial" => $guia->empresa,
                        "address" => array(
                            "direccion" => $guia->direccion_empresa,
                        )),


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
                            "direccion" => self::limitarDireccion($guia->direccion_empresa,50,"..."),
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


    }*/

/*    public function sunat($id)
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
                                "ruc" => $guia->ruc_empresa,
                                "razonSocial" => $guia->empresa,
                                "address" => array(
                                    "direccion" => $guia->direccion_empresa,
                                )),


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
                                "fecTraslado" =>  self::obtenerFecha($guia),//FECHA DEL TRANSLADO
                                "codPuerto" => "123",
                                "indTransbordo"=> false,
                                "pesoTotal" => $guia->peso_productos,
                                "undPesoTotal"=> "KGM",
                                "numContenedor" => "XD-2232",
                                "numBultos" => $guia->cantidad_productos,
                                "llegada" => array(
                                    "ubigueo" =>  $guia->ubigeo_llegada,
                                    "direccion" => self::limitarDireccion($guia->direccion_llegada,50,"..."),
                                ),
                                "partida" => array(
                                    "ubigueo" => $guia->ubigeo_partida,
                                    "direccion" => self::limitarDireccion($guia->direccion_empresa,50,"..."),
                                ),
                                "transportista"=> self::condicionReparto($guia)
                            ),

                            "details" =>  self::obtenerProductos($guia),
                    );

                    $data = enviarGuiaapi(json_encode($arreglo_guia));
                    //RESPUESTA DE LA SUNAT EN JSON
                    $json_sunat = json_decode($data);

                    if ($json_sunat->sunatResponse->success == true) {
                        if($json_sunat->sunatResponse->cdrResponse->code == "0") {
                            $guia->sunat = '1';
                            $respuesta_cdr = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                            $respuesta_cdr = json_decode($respuesta_cdr, true);
                            $guia->getCdrResponse = $respuesta_cdr;
                            $data = pdfGuiaapi(json_encode($arreglo_guia));
                            $name = $existe[0]->get('numeracion')->serie . "-" . $guia->correlativo . '.pdf';
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
                            $descripcion = "SE AGREGÓ LA GUIA DE REMISION ELECTRONICA: " . $existe[0]->get('numeracion')->serie . "-" . $guia->correlativo;
                            $gestion = "GUIA DE REMISION ELECTRONICA";
                            crearRegistro($guia, $descripcion, $gestion);

                            Session::flash('success', 'Guia de remision enviada a Sunat con exito.');
                            Session::flash('sunat_exito', '1');
                            Session::flash('id_sunat', $json_sunat->sunatResponse->cdrResponse->id);
                            Session::flash('descripcion_sunat', $json_sunat->sunatResponse->cdrResponse->description,);
                            return redirect()->route('ventas.guiasremision.index')->with('sunat_exito', 'success');
                        }
                        else {
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
                            return redirect()->route('ventas.guiasremision.index')->with('sunat_error', 'error');
                        }
                    } else{

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

                        }else {
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
                        return redirect()->route('ventas.guiasremision.index')->with('sunat_error', 'error');
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
*/

public function show($id)
{   
    $guia = Guia::with(['documento', 'detalles'])->findOrFail($id);
    $name = $guia->serie . "-" . $guia->correlativo . '.pdf';
    
    // Definir la ruta completa para el directorio PDF
    $rutaPdfGuia = storage_path('app/public/greenter/guías_remisión/pdf');

    // Crear directorio si no existe (usando File)
    if (!File::exists($rutaPdfGuia)) {
        File::makeDirectory($rutaPdfGuia, 0755, true, true);
    }

    // Generar el contenido del PDF
    $pdfContent = PDF::loadView('ventas.guias.reportes.guia', [
        'guia' => $guia,
        'empresa' => Empresa::first(),
    ])->setPaper('a4')->setWarnings(false)
      ->output();

    // Definir la ruta completa del archivo a guardar
    $filePath = $rutaPdfGuia . '/' . $name;

    // Guardar el PDF en la ubicación especificada
    File::put($filePath, $pdfContent);

    // Actualizar la guía con la ruta y nombre del archivo
    $guia->nombre_comprobante_archivo = $name;
    $guia->ruta_comprobante_archivo = 'greenter/guías_remisión/pdf/' . $name;
    $guia->update();

    // Retornar el archivo como respuesta
    return response()->file($filePath);
}

public function sunat($id){

    DB::beginTransaction();
    try {
        $guia = Guia::find($id);
        
        //OBTENER CORRELATIVO DE LA GUIA DE REMISION
        $existe = event(new NumeracionGuiaRemision($guia));
        if($existe[0]){
            if ($existe[0]->get('existe') == true) {
                if ($guia->sunat != '1') {
                  
                   
                        $data_transportista =   self::condicionReparto($guia);

                        $util = Util::getInstance();
                      
                        //========== Transportista ===========
                        $transp = new Transportist();
                        $transp->setTipoDoc($data_transportista['tipoDoc'])
                            ->setNumDoc($data_transportista['numDoc'])
                            ->setRznSocial($data_transportista['rznSocial'])
                            ->setNroMtc($data_transportista['placa'])
                            ->setplaca($data_transportista['placa'])
                            ->setchoferTipoDoc($data_transportista['choferTipoDoc'])
                            ->setchoferDoc($data_transportista['choferDoc']);

                        //=========== Envío ================
                        $envio = new Shipment();
                        $envio
                            ->setCodTraslado($guia->codTraslado()) // Cat.20 - Venta
                            ->setdesTraslado($guia->desTraslado())
                            ->setindTransbordo(false)
                            ->setnumContenedor('XD-2232')
                            ->setModTraslado('01') // Cat.18 - Transp. Publico
                            ->setFecTraslado(new \DateTime(self::obtenerFecha($guia)))
                            ->setPesoTotal(1)
                            ->setUndPesoTotal('KGM')
                            ->setNumBultos($guia->cantidad_productos) // Solo válido para importaciones
                            ->setLlegada(new Direction($guia->ubigeo_llegada, self::limitarDireccion($guia->direccion_llegada,50,"...")))
                            ->setPartida(new Direction($guia->ubigeo_partida, self::limitarDireccion($guia->direccion_empresa,50,"...")))
                            ->setTransportista($transp);


                        //===== despacho =======
                            $despatch = new Despatch();
                            $despatch->setVersion('2022')
                                ->setTipoDoc('09')
                                ->setSerie($existe[0]->get('numeracion')->serie)
                                ->setCorrelativo($guia->correlativo)
                                ->setFechaEmision(new \DateTime(self::obtenerFecha($guia)))
                                ->setCompany($util->getGRECompany())
                                ->setDestinatario((new Client())
                                    ->setTipoDoc('6')
                                    ->setNumDoc('20000000002')
                                    ->setRznSocial('EMPRESA DEST 1'))
                                ->setEnvio($envio);

                                
                            
                        //===== LLENANDO DETALLE =======
                        $productos= self::obtenerProductos($guia);
                        $detalles   =   [];
                        foreach ($productos as $producto) {
                            
                            $detail = new DespatchDetail();
                            $detail->setCantidad($producto->cantidad)
                                ->setUnidad($producto->unidad)
                                ->setDescripcion($producto->nombre_producto)
                                ->setCodigo($producto->codigo_producto);
                                
                            $detalles[] =   $detail;
                        }

                      
                        $despatch->setDetails($detalles);

                        //===== obteniendo configuración de envío ==========
                        $api = $this->controlConfiguracionGreenter($util); 

                        //======== CONSTRUYENDO XML Y ENVIANDO A SUNAT ==========
                        $res = $api->send($despatch);
                    
                        //======== RESPONSE ESTRUCTURA ========
                        // ticket(string) | success(boolean) | error
                        //==== GUARDANDO XML ====
                        $util->writeXml($despatch, $api->getLastXml(),"GUIA REMISION",null);
                        $guia->ruta_xml      =   'storage/greenter/guías_remisión/xml/'.$despatch->getName().'.xml';

                        dd($res);
                        //===== VERIFICANDO CONEXIÓN CON SUNAT =======
                        if($res->isSuccess()){
                            
                            //==== OBTENER Y GUARDAR TICKET ====
                            $ticket                 =   $res->getTicket();
                            $guia->ticket           =   $ticket;
                            $guia->sunat            =   '1';
                            $guia->regularize       =   '0';
                            $guia->despatch_name    =   $despatch->getName();
                            $guia->update();
                            
                            Session::flash('guia_exito', 'Guia de remisión enviada a Sunat.');            
                            DB::commit();                   
                            return redirect()->route('ventas.guiasremision.index');
                        } else{

                            //COMO SUNAT NO LO ADMITE VUELVE A SER 0
                            $guia->sunat        = '0';
                            $guia->regularize   = '1';
                            $guia->despatch_name    =   $despatch->getName();
                            $guia->update();

                            Session::flash('error_guia_remision', 'Guia de remision sin exito en el envio a sunat.');  
                            DB::commit();                   
                            return redirect()->route('ventas.guiasremision.index');
                        }
                   
                }else{
                    $guia->sunat = '1';
                    $guia->update();
                    Session::flash('error_guia_remision','Guia de remision ya fue enviado a Sunat.');
                    DB::commit();                   
                    return redirect()->route('ventas.guiasremision.index');
                }

            }else{
                Session::flash('error','Guia de remision no se encuentra registrado en la empresa.');
                DB::commit();                   
                return redirect()->route('ventas.guiasremision.index');
            }
        }else{
            Session::flash('error','Empresa sin parametros para emitir Guia de remisión remitente electrónica.');
            DB::commit();                   
            return redirect()->route('ventas.guiasremision.index');
        }


    } catch (\Throwable $th) {
        dd($th->getMessage());
        Session::flash('error_guia_remision', $th->getMessage());     
        DB::rollBack();                
        return redirect()->route('ventas.guiasremision.index');
    }
}

public function controlConfiguracionGreenter($util){
    //==== OBTENIENDO CONFIGURACIÓN DE GREENTER ======
    $greenter_config    =   DB::select('select gc.ruta_certificado,gc.id_api_guia_remision,gc.modo,
      gc.clave_api_guia_remision,e.ruc,e.razon_social,e.direccion_fiscal,e.ubigeo,
      e.direccion_llegada,gc.sol_user,gc.sol_pass
      from greenter_config as gc
      inner join empresas as e on e.id=gc.empresa_id
      inner join configuracion as c on c.propiedad = gc.modo
      where gc.empresa_id=1 and c.slug="AG"');



    if(count($greenter_config) === 0){
        throw new Exception('NO SE ENCONTRÓ NINGUNA CONFIGURACIÓN PARA GREENTER');
    }

    if(!$greenter_config[0]->sol_user){
        throw new Exception('DEBE ESTABLECER LA CREDENCIAL SOL_USER');
    }
    if(!$greenter_config[0]->id_api_guia_remision){
        throw new Exception('DEBE ESTABLECER EL ID API GUÍA DE REMISIÓN');
    }
    if(!$greenter_config[0]->clave_api_guia_remision){
        throw new Exception('DEBE ESTABLECER LA CLAVE API GUÍA DE REMISIÓN');
    }
    if(!$greenter_config[0]->sol_pass){
        throw new Exception('DEBE ESTABLECER LA CREDENCIAL SOL_PASS');
    }
    if ($greenter_config[0]->modo !== "BETA" && $greenter_config[0]->modo !== "PRODUCCION") {
        throw new Exception('NO SE HA CONFIGURADO EL AMBIENTE BETA O PRODUCCIÓN PARA GREENTER');
    }

    $see    =   null;
    $see    =   $util->getSeeApi($greenter_config[0]);

    //if($greenter_config[0]->modo === "BETA"){   
    //}

   
    if(!$see){
        throw new Exception('ERROR EN LA CONFIGURACIÓN DE GREENTER, SEE ES NULO');
    }

    return $see;
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
                            "ruc" => $guia->ruc_empresa,
                            "razonSocial" => $guia->empresa,
                            "address" => array(
                                "direccion" => $guia->direccion_empresa,
                            )),


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
                                "direccion" => self::limitarDireccion($guia->direccion_empresa,50,"..."),
                            ),
                            "transportista"=> self::condicionReparto($guia)
                        ),

                        "details" =>  self::obtenerProductos($guia),
                );

                $data = enviarGuiaapi(json_encode($arreglo_guia));
                //RESPUESTA DE LA SUNAT EN JSON
                $json_sunat = json_decode($data);

                if ($json_sunat->sunatResponse->success == true) {
                    if($json_sunat->sunatResponse->cdrResponse->code == "0") {
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


                        $guia->nombre_comprobante_archivo = $name;
                        $guia->ruta_comprobante_archivo = 'public/sunat/guia/' . $name;
                        $guia->update();

                        //Registro de actividad
                        $descripcion = "SE AGREGÓ LA GUIA DE REMISION ELECTRONICA: " . $guia->serie . "-" . $guia->correlativo;
                        $gestion = "GUIA DE REMISION ELECTRONICA";
                        crearRegistro($guia, $descripcion, $gestion);

                        return array('success' => true, 'mensaje' => 'Guia de remisión enviada a Sunat con exito.');
                    }
                    else {
                        $guia->sunat = '0';
                        $id_sunat = $json_sunat->sunatResponse->cdrResponse->code;
                        $descripcion_sunat = $json_sunat->sunatResponse->cdrResponse->description;

                        $respuesta_error = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                        $respuesta_error = json_decode($respuesta_error, true);
                        $guia->getCdrResponse = $respuesta_error;

                        $guia->update();
                        return array('success' => false, 'mensaje' => $descripcion_sunat);
                    }
                }else{

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
                    }else {
                        $id_sunat = $json_sunat->sunatResponse->cdrResponse->id;
                        $descripcion_sunat = $json_sunat->sunatResponse->cdrResponse->description;
                        $respuesta_error = json_encode($json_sunat->sunatResponse->cdrResponse, true);
                        $respuesta_error = json_decode($respuesta_error, true);
                        $guia->getCdrResponse = $respuesta_error;
                    };


                    $errorGuia = new ErrorGuia();
                    $errorGuia->guia_id = $guia->id;
                    $errorGuia->tipo = 'sunat-envio';
                    $errorGuia->descripcion = 'Error al enviar a sunat';
                    $errorGuia->ecxepcion = $descripcion_sunat;
                    $errorGuia->save();

                    $guia->update();
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

    public function destroy($id)
    {
        try {
            $guia = Guia::findOrFail($id);
            if ($guia->documento) {
                Session::flash('error', 'No puedes eliminar esta guia, ha sido creada a de un documento de venta.');
                return redirect()->route('ventas.guiasremision.index')->with('guardar', 'error');
            } else if ($guia->sunat == '1') {
                Session::flash('error', 'No puedes eliminar esta guia, ya ha sido enviada a sunat.');
                return redirect()->route('ventas.guiasremision.index')->with('guardar', 'error');
            } else {
                $nota = NotaSalidad::find($guia->nota_salida_id);
                foreach ($guia->detalles as $detalle) {
                    $nota_detalle = DetalleNotaSalidad::where('nota_salidad_id', $nota->id)->where('producto_id', $detalle->producto_id)->first();

                    if ($nota_detalle) {
                        $lote_producto = LoteProducto::findOrFail($nota_detalle->lote_id);

                        $lote_producto->cantidad_logica = $lote_producto->cantidad_logica + $nota_detalle->cantidad;
                        $lote_producto->cantidad = $lote_producto->cantidad + $nota_detalle->cantidad;
                        $lote_producto->update();
                    }
                }

                $guia->estado = "NULO";
                $guia->update();

                $nota->estado = "ANULADO";
                $nota->update();

                Session::flash('success', 'Guia eliminada correctamente.');
                return redirect()->route('ventas.guiasremision.index')->with('guardar', 'success');
            }
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
            return redirect()->route('ventas.guiasremision.index')->with('guardar', 'error');
        }
    }

    public function getXml($guia_id){
        $guia        =   Guia::find($guia_id);
        $nombreArchivo  =   basename($guia->ruta_xml);
        

        $headers = [
            'Content-Type' => 'text/xml',
        ];
    
        return Response::download($guia->ruta_xml, $nombreArchivo, $headers);
    }

    public function getCdr($guia_id){
        $guia           =   Guia::find($guia_id);
        $nombreArchivo  =   basename($guia->ruta_cdr);

        $headers = [
            'Content-Type' => 'text/xml',
        ];
    
        return Response::download($guia->ruta_cdr, $nombreArchivo, $headers);
    }

}
