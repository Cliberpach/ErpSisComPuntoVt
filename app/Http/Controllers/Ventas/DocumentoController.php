<?php

namespace App\Http\Controllers\Ventas;

use App\Almacenes\LoteProducto;
use App\Almacenes\Producto;
use App\Events\ComprobanteRegistrado;
use App\Events\DocumentoNumeracion;
use App\Events\NotaRegistrada;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Empresa\Empresa;
use App\Mantenimiento\Empresa\Numeracion;
use App\Mantenimiento\Tabla\Detalle as TablaDetalle;
use App\Pos\DetalleMovimientoVentaCaja;
use App\Pos\MovimientoCaja;
use App\Ventas\Cliente;
use App\Ventas\Cotizacion;
use App\Ventas\CotizacionDetalle;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\Documento\Pago\Transferencia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade as PDF;
use App\Ventas\ErrorVenta;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Exception;
use Illuminate\Support\Facades\Mail;
//CONVERTIR DE NUMEROS A LETRAS
use Luecano\NumeroALetras\NumeroALetras;

class DocumentoController extends Controller
{
    public function index()
    {
        $this->authorize('haveaccess','documento_venta.index');
        return view('ventas.documentos.index');
    }

    public function getDocument(){

        $documentos = Documento::where('estado','!=','ANULADO')->orderBy('id', 'desc')->get();
        $coleccion = collect([]);

        $hoy = Carbon::now();
        foreach($documentos as $documento){

            $transferencia = 0.00;
            $otros = 0.00;
            $efectivo = 0.00;

            if($documento->tipo_pago_id)
            {
                if ($documento->tipo_pago_id == 1) {
                    $efectivo = $documento->importe;
                }
                else if ($documento->tipo_pago_id == 2){
                    $transferencia = $documento->importe ;
                    $efectivo = $documento->efectivo;
                }
                else {
                    $otros = $documento->importe;
                    $efectivo = $documento->efectivo;
                }
            }

            $fecha_v = $documento->created_at;
            $diff =  $fecha_v->diffInDays($hoy);

            $cantidad_notas = count($documento->notas);

            $coleccion->push([
                'id' => $documento->id,
                'tipo_venta' => $documento->nombreTipo(),
                'tipo_venta_id' => $documento->tipo_venta,
                'tipo_pago' => $documento->tipo_pago,
                'numero_doc' =>  $documento->serie.'-'.$documento->correlativo,
                'serie' => $documento->serie,
                'correlativo' => $documento->correlativo,
                'cliente' => $documento->tipo_documento_cliente.': '.$documento->documento_cliente.' - '.$documento->cliente,
                'empresa' => $documento->empresa,
                'cotizacion_venta' =>  $documento->cotizacion_venta,
                'fecha_documento' =>  Carbon::parse($documento->fecha_documento)->format( 'd/m/Y'),
                'estado' => $documento->estado,
                'sunat' => $documento->sunat,
                'otros' => 'S/. '.number_format($otros, 2, '.', ''),
                'efectivo' => 'S/. '.number_format($efectivo, 2, '.', ''),
                'transferencia' => 'S/. '.number_format($transferencia, 2, '.', ''),
                'total' => 'S/. '.number_format($documento->total, 2, '.', ''),
                'dias' => (int)(7 - $diff < 0 ? 0  : 7 - $diff),
                'notas' => $cantidad_notas
            ]);
        }

        return DataTables::of($coleccion)->toJson();
    }

    public function create(Request $request)
    {
        $this->authorize('haveaccess','documento_venta.index');
        $empresas = Empresa::where('estado', 'ACTIVO')->get();
        $clientes = Cliente::where('estado', 'ACTIVO')->get();
        $fecha_hoy = Carbon::now()->toDateString();
        $productos = Producto::where('estado', 'ACTIVO')->get();

        $cotizacion = '';
        $detalles = '';
        if($request->get('cotizacion')){
            //COLECCION DE ERRORES
            $errores = collect();
            $devolucion = false;
            $cotizacion =  Cotizacion::findOrFail( $request->get('cotizacion') );
            $detalles = CotizacionDetalle::where('cotizacion_id', $request->get('cotizacion'))->get();
            $lotes = self::cotizacionLote($detalles);

            $nuevoDetalle = collect();
            if(count($lotes) === 0)
            {
                $coll = new Collection();
                $coll->producto = '. No hay stock para ninguno de los productos';
                $coll->cantidad = '.';
                $errores->push($coll);

                return view('ventas.documentos.create',[
                    'cotizacion' => $cotizacion,
                    'empresas' => $empresas,
                    'clientes' => $clientes,
                    'productos' => $productos,
                    'lotes' => $nuevoDetalle,
                    'errores' => $errores,
                    'fecha_hoy' => $fecha_hoy,
                ]);
            }
            //COMPROBACION DE LOTES SI LAS CANTIDADES ENVIADAS SON IGUALES A LAS SOLICITADAS
            foreach ($detalles as $detalle) {
                $cantidadDetalle = $lotes->where('producto',$detalle->producto_id)->sum('cantidad');
                if($cantidadDetalle != $detalle->cantidad){
                    $devolucion = true;
                    $devolucionLotes = $lotes->where('producto',$detalle->producto_id)->first();
                    //LLENAR ERROR CANTIDAD SOLICITADA MAYOR AL STOCK
                    $coll = new Collection();
                    $coll->producto = $devolucionLotes->descripcion_producto;
                    $coll->cantidad = $detalle->cantidad;
                    $errores->push($coll);
                    self::devolverCantidad($lotes->where('producto',$detalle->producto_id));
                }else{
                    $nuevoSindevoluciones = $lotes->where('producto',$detalle->producto_id);
                    foreach ($nuevoSindevoluciones as $devolucion) {
                        $coll = new Collection();
                        $coll->producto_id = $devolucion->producto_id;
                        $coll->cantidad = $devolucion->cantidad;
                        $coll->precio_unitario = $devolucion->precio_unitario;
                        $coll->precio_inicial = $devolucion->precio_inicial;
                        $coll->precio_nuevo = $devolucion->precio_nuevo;
                        $coll->descuento = $devolucion->descuento;
                        $coll->dinero = $devolucion->dinero;
                        $coll->valor_unitario = $devolucion->valor_unitario;
                        $coll->valor_venta = $devolucion->valor_venta;
                        $coll->unidad = $devolucion->unidad;
                        $coll->descripcion_producto = $devolucion->descripcion_producto;
                        $coll->presentacion = $devolucion->presentacion;
                        $coll->producto = $devolucion->producto;
                        $nuevoDetalle->push( $coll);
                    }

                }
            }

            return view('ventas.documentos.create',[
                'cotizacion' => $cotizacion,
                'empresas' => $empresas,
                'clientes' => $clientes,
                'productos' => $productos,
                'lotes' => $nuevoDetalle,
                'errores' => $errores,
                'fecha_hoy' => $fecha_hoy,
            ]);

        }

        if (empty($cotizacion)) {
            return view('ventas.documentos.create',[
                'empresas' => $empresas,
                'clientes' => $clientes,
                'productos' => $productos,
                'fecha_hoy' => $fecha_hoy,
            ]);
        }
    }

    public function devolverCantidad($devoluciones)
    {
        foreach ($devoluciones as $devolucion) {
            $lote = LoteProducto::findOrFail($devolucion->producto_id);
            $lote->cantidad_logica = $lote->cantidad_logica + $devolucion->cantidad;
            $lote->cantidad =  $lote->cantidad_logica;
            $lote->estado = '1';
            $lote->update();
        }
    }

    public function cotizacionLote($detalles)
    {
        $nuevoDetalle = collect();
        foreach ($detalles as $detalle) {
            $lotes = LoteProducto::where('producto_id',$detalle->producto_id)
                    ->where('estado','1')
                    ->where('cantidad_logica','>',0)
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->get();
            //INICIO CON LA CANTIDAD DEL DETALLE
            $cantidadSolicitada = $detalle->cantidad;

            foreach ($lotes as $lote) {
                //SE OBTUVO LA CANTIDAD SOLICITADA DEL LOTE
                if ($cantidadSolicitada > 0) {
                    //CANTIDAD LOGICA DEL LOTE ES IGUAL A LA CANTIDAD SOLICITADA
                    $cantidadLogica = $lote->cantidad_logica;
                    if ($cantidadLogica == $cantidadSolicitada) {
                        //CREAMOS EL NUEVO DETALLE
                        $coll = new Collection();
                        $coll->producto_id = $lote->id;
                        $coll->cantidad = $lote->cantidad_logica;
                        $coll->precio_unitario = $detalle->precio_unitario;
                        $coll->precio_inicial = $detalle->precio_inicial;
                        $coll->precio_nuevo = $detalle->precio_nuevo;
                        $coll->descuento = $detalle->descuento;
                        $coll->dinero = $detalle->dinero;
                        $coll->valor_unitario = $detalle->valor_unitario;
                        $coll->valor_venta = $detalle->valor_venta;
                        $coll->unidad = $lote->producto->medidaCompleta();
                        $coll->descripcion_producto= $lote->producto->nombre.' - '.$lote->codigo_lote;
                        $coll->presentacion = $lote->producto->medida;
                        $coll->producto = $lote->producto->id;
                        $nuevoDetalle->push( $coll);
                        //ACTUALIZAMOS EL LOTE
                        $lote->cantidad_logica = $lote->cantidad_logica - $cantidadSolicitada;
                        //REDUCIMOS LA CANTIDAD SOLICITADA
                        $cantidadSolicitada = 0;
                        $lote->update();
                    }else{
                        if ($lote->cantidad_logica < $cantidadSolicitada) {
                            //CREAMOS EL NUEVO DETALLE
                            $coll = new Collection();
                            $coll->producto_id = $lote->id;
                            $coll->cantidad = $lote->cantidad_logica;
                            $coll->precio_unitario = $detalle->precio_unitario;
                            $coll->precio_inicial = $detalle->precio_inicial;
                            $coll->precio_nuevo = $detalle->precio_nuevo;
                            $coll->descuento = $detalle->descuento;
                            $coll->dinero = $detalle->dinero;
                            $coll->valor_unitario = $detalle->valor_unitario;
                            $coll->valor_venta = $detalle->valor_venta;
                            $coll->unidad = $lote->producto->medidaCompleta();
                            $coll->descripcion_producto= $lote->producto->nombre.' - '.$lote->codigo_lote;
                            $coll->presentacion = $lote->producto->medida;
                            $coll->producto = $lote->producto->id;
                            $nuevoDetalle->push($coll);
                            //REDUCIMOS LA CANTIDAD SOLICITADA
                            $cantidadSolicitada = $cantidadSolicitada - $lote->cantidad_logica;
                            //ACTUALIZAMOS EL LOTE
                            $lote->cantidad_logica = 0;
                            $lote->update();
                        }else{
                            if ($lote->cantidad_logica > $cantidadSolicitada) {
                                 //CREAMOS EL NUEVO DETALLE
                                $coll = new Collection();
                                $coll->producto_id = $lote->id;
                                $coll->cantidad = $cantidadSolicitada ;
                                $coll->precio_unitario = $detalle->precio_unitario;
                                $coll->precio_inicial = $detalle->precio_inicial;
                                $coll->precio_nuevo = $detalle->precio_nuevo;
                                $coll->descuento = $detalle->descuento;
                                $coll->dinero = $detalle->dinero;
                                $coll->valor_unitario = $detalle->valor_unitario;
                                $coll->valor_venta = $detalle->valor_venta;
                                $coll->unidad = $lote->producto->medidaCompleta();
                                $coll->descripcion_producto = $lote->producto->nombre.' - '.$lote->codigo_lote;
                                $coll->presentacion = $lote->producto->medida;
                                $coll->producto = $lote->producto->id;
                                $nuevoDetalle->push( $coll);
                                //ACTUALIZAMOS EL LOTE
                                $lote->cantidad_logica = $lote->cantidad_logica - $cantidadSolicitada;
                                //REDUCIMOS LA CANTIDAD SOLICITADA
                                $cantidadSolicitada = 0;
                                $lote->update();
                            }

                        }

                    }

                }

            }
        }


        return $nuevoDetalle;

    }

    public function store(Request $request)
    {
        $this->authorize('haveaccess','documento_venta.index');
        ini_set("max_execution_time", 60000);
        try{

            DB::beginTransaction();
            $data = $request->all();

            $rules = [
                'fecha_documento_campo'=> 'required',
                'fecha_atencion_campo'=> 'required',
                'tipo_venta'=> 'required',
                'forma_pago'=> 'required',
                'tipo_pago_id'=> 'nullable',
                'efectivo'=> 'required',
                'importe'=> 'required',
                'empresa_id'=> 'required',
                'cliente_id'=> 'required',
                'observacion' => 'nullable',
                'igv' => 'required_if:igv_check,==,on|numeric|digits_between:1,3',

            ];

            $message = [
                'fecha_documento_campo.required' => 'El campo Fecha de Emisión es obligatorio.',
                'tipo_venta.required' => 'El campo tipo de venta es obligatorio.',
                'forma_pago.required' => 'El campo forma de pago es obligatorio.',
                //'tipo_pago_id.required' => 'El campo modo de pago es obligatorio.',
                'importe.required' => 'El campo importe es obligatorio.',
                'efectivo.required' => 'El campo efectivo es obligatorio.',
                'fecha_atencion_campo.required' => 'El campo Fecha de Entrega es obligatorio.',
                'empresa_id.required' => 'El campo Empresa es obligatorio.',
                'cliente_id.required' => 'El campo Cliente es obligatorio.',
                'igv.required_if' => 'El campo Igv es obligatorio.',
                'igv.digits' => 'El campo Igv puede contener hasta 3 dígitos.',
                'igv.numeric' => 'El campo Igv debe se numérico.',


            ];


            $validator =  Validator::make($data, $rules, $message);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => true,
                    'data' => array('mensajes' => $validator->getMessageBag()->toArray())
                ]);

            }

            $documento = new Documento();
            $documento->fecha_documento = $request->get('fecha_documento_campo');
            $documento->fecha_atencion =  $request->get('fecha_atencion_campo');
            $documento->fecha_vencimiento =  $request->get('fecha_vencimiento_campo');
            //EMPRESA
            $empresa = Empresa::findOrFail($request->get('empresa_id'));
            $documento->ruc_empresa =  $empresa->ruc;
            $documento->empresa =  $empresa->razon_social;
            $documento->direccion_fiscal_empresa =  $empresa->direccion_fiscal;
            $documento->empresa_id = $request->get('empresa_id'); //OBTENER NUMERACION DE LA EMPRESA
            //CLIENTE
            $cliente = Cliente::findOrFail($request->get('cliente_id'));

            $documento->tipo_documento_cliente =  $cliente->tipo_documento;
            $documento->documento_cliente =  $cliente->documento;
            $documento->direccion_cliente =  $cliente->direccion;
            $documento->cliente =  $cliente->nombre;
            $documento->cliente_id = $request->get('cliente_id'); //OBTENER TIENDA DEL CLIENTE

            $documento->tipo_venta = $request->get('tipo_venta');
            $documento->forma_pago = $request->get('forma_pago');
            $documento->observacion = $request->get('observacion');
            $documento->user_id = auth()->user()->id;
            $documento->sub_total = $request->get('monto_sub_total');
            $documento->total_igv = $request->get('monto_total_igv');
            $documento->total = $request->get('monto_total');
            $documento->igv = $request->get('igv') ? $request->get('igv') : 18;
            $documento->moneda = 1;

            $documento->tipo_pago_id = $request->get('tipo_pago_id');
            $documento->importe = $request->get('importe');
            $documento->efectivo = $request->get('efectivo');

            if ($request->get('igv_check') == "on") {
                $documento->igv_check = "1";
            };

            $documento->cotizacion_venta = $request->get('cotizacion_id');
            $documento->save();

            $numero_doc = $documento->id;
            $documento->numero_doc = 'VENTA-'.$numero_doc;
            $documento->update();
            //Llenado de los articulos
            $productosJSON = $request->get('productos_tabla[]');
            $productotabla = json_decode($productosJSON);
            foreach ($productotabla as $producto) {
                $lote = LoteProducto::findOrFail($producto->producto_id);
                Detalle::create([
                    'documento_id' => $documento->id,
                    'lote_id' => $producto->producto_id, //LOTE
                    'codigo_producto' => $lote->producto->codigo,
                    'unidad' => $lote->producto->getMedida(),
                    'nombre_producto' => $lote->producto->nombre,
                    'codigo_lote' => $lote->codigo_lote,
                    'cantidad' => $producto->cantidad,
                    'precio_unitario' => $producto->precio_unitario,
                    'precio_inicial' => $producto->precio_inicial,
                    'precio_nuevo' => $producto->precio_nuevo,
                    'dinero' => $producto->dinero,
                    'descuento' => $producto->descuento,
                    'valor_unitario' => $producto->valor_unitario,
                    'valor_venta' => $producto->valor_venta,
                ]);

                $lote->cantidad =  $lote->cantidad - $producto->cantidad;
                if($lote->cantidad - $producto->cantidad == 0)
                {
                    $lote->cantidad_logica =  0;
                }
                $lote->update();
            }

            $detalle = new DetalleMovimientoVentaCaja();
            $detalle->cdocumento_id = $documento->id;
            $detalle->mcaja_id = movimientoUser()->id;
            $detalle->save();

            $envio_prev = self::sunat($documento->id);
            if(!$envio_prev['success'])
            {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje'=> $envio_prev['mensaje']
                ]);
            }


            $documento = Documento::find($documento->id);
            $documento->nombre_comprobante_archivo = $documento->serie.'-'.$documento->correlativo.'.pdf';
            $documento->update();

            //Registro de actividad
            $descripcion = "SE AGREGÓ EL DOCUMENTO DE VENTA CON LA FECHA: ". Carbon::parse($documento->fecha_documento)->format('d/m/y');
            $gestion = "DOCUMENTO DE VENTA";
            crearRegistro($documento , $descripcion , $gestion);

            if((int)$documento->tipo_venta === 127 || (int)$documento->tipo_venta === 128)
            {
                DB::commit();
                if($request->envio_sunat)
                {
                    $envio_ = self::sunat_valida($documento->id);
                    $documento->envio_sunat = '1';
                }
                $vp = self::venta_comprobante($documento->id);
                $ve = self::venta_email($documento->id);
                Session::flash('success','Documento de venta creado.');

                return response()->json([
                    'success' => true,
                    'documento_id'=> $documento->id
                ]);
            }
            else{
                DB::commit();
                $vp = self::venta_comprobante($documento->id);
                $ve = self::venta_email($documento->id);
                Session::flash('success','Documento de venta creado.');
                return response()->json([
                    'success' => true,
                    'documento_id'=> $documento->id
                ]);
                //return redirect()->route('ventas.documento.index')->with('documento_id', $documento->id);
            }
        }
        catch(Exception $e)
        {
            $productosJSON = $request->get('productos_tabla[]');
            $productotabla = json_decode($productosJSON);
            DB::rollBack();
            /*foreach ($productotabla as $producto) {
                $lote = LoteProducto::findOrFail($producto->producto_id);
                $lote->cantidad_logica =  $lote->cantidad_logica + $producto->cantidad;
                $lote->update();
            }*/
            //Session::flash('error', (string) $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje'=> $e->getMessage(), //'Ocurrio un error porfavor volver a intentar, si el error persiste comunicarse con el administrador del sistema.'
                'excepcion' => $e->getMessage()
            ]);
            //return redirect()->route('ventas.documento.index');
        }
    }

    public function venta_comprobante($id)
    {
        try
        {
            $documento = Documento::find($id);
            $detalles = Detalle::where('estado','ACTIVO')->where('documento_id',$id)->get();
            $empresa = Empresa::findOrFail($documento->empresa_id);

            $legends = self::obtenerLeyenda($documento);
            $legends = json_encode($legends,true);
            $legends = json_decode($legends,true);

            if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'comprobantessiscom'))) {
                mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'comprobantessiscom'));
            }
            $pdf_condicion = $empresa->condicion === '1' ? 'comprobante_normal_nuevo' : 'comprobante_normal';

            PDF::loadview('ventas.documentos.impresion.'.$pdf_condicion,[
                'documento' => $documento,
                'detalles' => $detalles,
                'moneda' => $documento->simboloMoneda(),
                'empresa' => $empresa,
                "legends" =>  $legends,
                ])->setPaper('a4')->setWarnings(false)
                ->save(public_path().'/storage/comprobantessiscom/'.$documento->nombre_comprobante_archivo);

            return array('success' => true,'mensaje' => 'Documento validado.');
        }
        catch(Exception $e)
        {
            $documento = Documento::find($id);

            $errorVenta = new ErrorVenta();
            $errorVenta->documento_id = $documento->id;
            $errorVenta->tipo = 'pdf';
            $errorVenta->descripcion = 'Error al generar pdf';
            $errorVenta->ecxepcion = $e->getMessage();
            $errorVenta->save();
            return array('success' => false,'mensaje' => 'Documento no validado.');
        }
    }

    public function venta_email($id)
    {
        try
        {
            $documento = Documento::find($id);

            if((int)$documento->tipo_venta === 127 || (int)$documento->tipo_venta === 128)
            {
                if($documento->clienteEntidad->correo_electronico)
                {
                    Mail::send('ventas.documentos.mail.cliente_mail',compact("documento"), function ($mail) use ($documento) {
                        $mail->to($documento->clienteEntidad->correo_electronico);
                        $mail->subject('SISCOM '. $documento->nombreDocumento());
                        $mail->attach(storage_path('app/public/comprobantessiscom/' . $documento->nombre_comprobante_archivo), [
                            'foto' => ''.$documento->nombre_comprobante_archivo,
                        ]);
                        $mail->attach(storage_path('app/public/xml/' . $documento->xml), [
                            'foto' => ''.$documento->xml,
                        ]);
                        $mail->from('developer.limpiecito@gmail.com','SISCOM');
                    });
                }
            }
            else{
                if($documento->clienteEntidad->correo_electronico)
                {
                    Mail::send('ventas.documentos.mail.cliente_mail',compact("documento"), function ($mail) use ($documento) {
                        $mail->to($documento->clienteEntidad->correo_electronico);
                        $mail->subject('SISCOM '. $documento->nombreDocumento());
                        $mail->attach(storage_path('app/public/comprobantessiscom/' . $documento->nombre_comprobante_archivo), [
                            'foto' => ''.$documento->nombre_comprobante_archivo,
                        ]);
                        $mail->from('developer.limpiecito@gmail.com','SISCOM');
                    });
                }
            }

            return array('success' => true,'mensaje' => 'Documento validado.');
        }
        catch(Exception $e)
        {
            $documento = Documento::find($id);

            $errorVenta = new ErrorVenta();
            $errorVenta->documento_id = $documento->id;
            $errorVenta->tipo = 'email';
            $errorVenta->descripcion = 'Error al enviar email';
            $errorVenta->ecxepcion = $e->getMessage();
            $errorVenta->save();
            return array('success' => false,'mensaje' => 'Documento no validado.');
        }
    }

    public function destroy($id)
    {
        $this->authorize('haveaccess','documento_venta.index');
        $documento = Documento::findOrFail($id);
        $documento->estado = 'ANULADO';
        $documento->update();

        $detalles = Detalle::where('documento_id',$id)->where('estado', 'ACTIVO')->get();
        foreach ($detalles as $detalle) {
            //ANULAMOS EL DETALLE
            $detalle->estado = "ANULADO";
            $detalle->update();
        }

        //Registro de actividad
        $descripcion = "SE ELIMINÓ EL DOCUMENTO DE VENTA CON LA FECHA: ". Carbon::parse($documento->fecha_documento)->format('d/m/y');
        $gestion = "DOCUMENTO DE VENTA";
        eliminarRegistro($documento, $descripcion , $gestion);

        Session::flash('success','Documento de Venta eliminada.');
        return redirect()->route('ventas.documento.index')->with('eliminar', 'success');

    }

    public function show($id)
    {
        $this->authorize('haveaccess','documento_venta.index');
        $documento = Documento::findOrFail($id);
        $nombre_completo = $documento->user->persona->apellido_paterno.' '.$documento->user->persona->apellido_materno.' '.$documento->user->persona->nombres;
        $detalles = Detalle::where('documento_id',$id)->where('estado', 'ACTIVO')->get();
        //TOTAL EN LETRAS
        $formatter = new NumeroALetras();
        $convertir = $formatter->toInvoice($documento->total, 2, 'SOLES');


        return view('ventas.documentos.show', [
            'documento' => $documento,
            'detalles' => $detalles,
            'nombre_completo' => $nombre_completo,
            'cadena_valor' => $convertir
        ]);

    }

    public function report($id)
    {
        $documento = Documento::findOrFail($id);
        $nombre_completo = $documento->user->persona->apellido_paterno.' '.$documento->user->persona->apellido_materno.' '.$documento->user->persona->nombres;
        $detalles = Detalle::where('documento_id',$id)->where('estado', 'ACTIVO')->get();
        $subtotal = 0;
        $igv = '';
        $tipo_moneda = '';
        foreach($detalles as $detalle){
            $subtotal = ($detalle->cantidad * $detalle->precio) + $subtotal;
        }
        foreach(tipos_moneda() as $moneda){
            if ($moneda->descripcion == $documento->moneda) {
                $tipo_moneda= $moneda->simbolo;
            }
        }


        if (!$documento->igv) {
            $igv = $subtotal * 0.18;
            $total = $subtotal + $igv;
            $decimal_subtotal = number_format($subtotal, 2, '.', '');
            $decimal_total = number_format($total, 2, '.', '');
            $decimal_igv = number_format($igv, 2, '.', '');
        }else{
            $calcularIgv = $documento->igv/100;
            $base = $subtotal / (1 + $calcularIgv);
            $nuevo_igv = $subtotal - $base;
            $decimal_subtotal = number_format($base, 2, '.', '');
            $decimal_total = number_format($subtotal, 2, '.', '');
            $decimal_igv = number_format($nuevo_igv, 2, '.', '');
        }



        $presentaciones = presentaciones();
        $paper_size = array(0,0,360,360);
        $pdf = PDF::loadview('compras.documentos.reportes.detalle',[
            'documento' => $documento,
            'nombre_completo' => $nombre_completo,
            'detalles' => $detalles,
            'presentaciones' => $presentaciones,
            'subtotal' => $decimal_subtotal,
            'moneda' => $tipo_moneda,
            'igv' => $decimal_igv,
            'total' => $decimal_total,
            ])->setPaper('a4')->setWarnings(false);
        return $pdf->stream();
    }

    public function TypePay($id)
    {
        DB::table('cotizacion_documento_pago_detalle_cajas')
            ->join('cotizacion_documento_pagos','cotizacion_documento_pagos.id','=','cotizacion_documento_pago_detalle_cajas.pago_id')
            ->join('cotizacion_documento_pago_cajas','cotizacion_documento_pago_cajas.id','=','cotizacion_documento_pago_detalle_cajas.caja_id')
            ->select('cotizacion_documento_pago_cajas.*','cotizacion_documento_pagos.*')
            ->where('cotizacion_documento_pagos.documento_id','=',$id)
            // //ANULAR
            ->where('cotizacion_documento_pagos.estado','!=','ANULADO')
            ->update(['cotizacion_documento_pago_cajas.estado' => 'ANULADO']);



        //TIPO DE DOCUMENTO
        $documento = Documento::findOrFail($id);
        $documento->tipo_pago = null;
        $documento->estado = 'PENDIENTE';
        $documento->update();

        Session::flash('success','Tipo de pagos anulados, puede crear nuevo pago.');
        return redirect()->route('ventas.documento.index')->with('exitosa', 'success');
    }

    public function obtenerFecha($documento)
    {
        $date = strtotime($documento->fecha_documento);
        $fecha_emision = date('Y-m-d', $date);
        $hora_emision = date('H:i:s', $date);
        $fecha = $fecha_emision.'T'.$hora_emision.'-05:00';

        return $fecha;
    }

    public function voucher($value)
    {
        try
        {
            $cadena = explode('-',$value);
            $id = $cadena[0];
            $size = (int) $cadena[1];
            $qr = self::qr_code($id);
            $documento = Documento::findOrFail($id);
            $detalles = Detalle::where('documento_id',$id)->where('estado','ACTIVO')->get();
            if((int)$documento->tipo_venta === 127 || (int)$documento->tipo_venta === 128)
            {
                if ($documento->sunat == '0' || $documento->sunat == '2' ) {
                    //ARREGLO COMPROBANTE
                    $arreglo_comprobante = array(
                        "tipoOperacion" => $documento->tipoOperacion(),
                        "tipoDoc"=> $documento->tipoDocumento(),
                        "serie" => '000',
                        "correlativo" => '000',
                        "fechaEmision" => self::obtenerFecha($documento),
                        "observacion" => $documento->observacion,
                        "tipoMoneda" => $documento->simboloMoneda(),
                        "client" => array(
                            "tipoDoc" => $documento->tipoDocumentoCliente(),
                            "numDoc" => $documento->documento_cliente,
                            "rznSocial" => $documento->cliente,
                            "address" => array(
                                "direccion" => $documento->direccion_cliente,
                            )),
                        "company" => array(
                            "ruc" =>  $documento->ruc_empresa,
                            "razonSocial" => $documento->empresa,
                            "address" => array(
                                "direccion" => $documento->direccion_fiscal_empresa,
                            )),
                        "mtoOperGravadas" => $documento->sub_total,
                        "mtoOperExoneradas" => 0,
                        "mtoIGV" => $documento->total_igv,

                        "valorVenta" => $documento->sub_total,
                        "totalImpuestos" => $documento->total_igv,
                        "mtoImpVenta" => $documento->total,
                        "ublVersion" => "2.1",
                        "details" => self::obtenerProductos($documento->id),
                        "legends" =>  self::obtenerLeyenda($documento),
                    );

                    $comprobante= json_encode($arreglo_comprobante);
                    $data = generarComprobanteapi($comprobante, $documento->empresa_id);
                    $name = $documento->id.'.pdf';
                    $pathToFile = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'comprobantes'.DIRECTORY_SEPARATOR.$name);
                    if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'comprobantes'))) {
                        mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'comprobantes'));
                    }
                    file_put_contents($pathToFile, $data);
                    //return response()->file($pathToFile);
                    $empresa = Empresa::first();

                    $legends = self::obtenerLeyenda($documento);
                    $legends = json_encode($legends,true);
                    $legends = json_decode($legends,true);
                    $detalles = Detalle::where('estado','ACTIVO')->where('documento_id', $documento->id)->get();
                    if($size === 80)
                    {
                        $pdf = PDF::loadview('ventas.documentos.impresion.comprobante_ticket',[
                            'documento' => $documento,
                            'detalles' => $detalles,
                            'moneda' => $documento->simboloMoneda(),
                            'empresa' => $empresa,
                            "legends" =>  $legends,
                            ])->setPaper([0, 0, 226.772, 651.95]);
                        return $pdf->stream($documento->serie.'-'.$documento->correlativo.'.pdf');
                    }
                    else
                    {
                        $pdf_condicion = $empresa->condicion === '1' ? 'comprobante_normal_nuevo' : 'comprobante_normal';
                        $pdf = PDF::loadview('ventas.documentos.impresion.'.$pdf_condicion,[
                            'documento' => $documento,
                            'detalles' => $detalles,
                            'moneda' => $documento->simboloMoneda(),
                            'empresa' => $empresa,
                            "legends" =>  $legends,
                            ])->setPaper('a4')->setWarnings(false);

                        return $pdf->stream($documento->serie.'-'.$documento->correlativo.'.pdf');
                    }
                }else{

                    //OBTENER CORRELATIVO DEL COMPROBANTE ELECTRONICO
                    $comprobante = event(new ComprobanteRegistrado($documento,$documento->serie));
                    //ENVIAR COMPROBANTE PARA LUEGO GENERAR PDF
                    $data = generarComprobanteapi($comprobante[0],$documento->empresa_id);
                    $name = $documento->id.'.pdf';
                    $pathToFile = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'comprobantes'.DIRECTORY_SEPARATOR.$name);
                    if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'comprobantes'))) {
                        mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'comprobantes'));
                    }
                    file_put_contents($pathToFile, $data);

                    $empresa = Empresa::first();

                    $legends = self::obtenerLeyenda($documento);
                    $legends = json_encode($legends,true);
                    $legends = json_decode($legends,true);

                    $detalles = Detalle::where('estado','ACTIVO')->where('documento_id', $documento->id)->get();

                    if($size === 80)
                    {
                        $pdf = PDF::loadview('ventas.documentos.impresion.comprobante_ticket',[
                            'documento' => $documento,
                            'detalles' => $detalles,
                            'moneda' => $documento->simboloMoneda(),
                            'empresa' => $empresa,
                            "legends" =>  $legends,
                            ])->setPaper([0, 0, 226.772, 651.95]);
                        return $pdf->stream($documento->serie.'-'.$documento->correlativo.'.pdf');
                    }
                    else
                    {
                        $pdf_condicion = $empresa->condicion === '1' ? 'comprobante_normal_nuevo' : 'comprobante_normal';
                        $pdf = PDF::loadview('ventas.documentos.impresion.'.$pdf_condicion,[
                            'documento' => $documento,
                            'detalles' => $detalles,
                            'moneda' => $documento->simboloMoneda(),
                            'empresa' => $empresa,
                            "legends" =>  $legends,
                            ])->setPaper('a4')->setWarnings(false);

                        return $pdf->stream($documento->serie.'-'.$documento->correlativo.'.pdf');
                    }
                }
            }
            else
            {

                if(empty($documento->correlativo))
                {
                    event(new DocumentoNumeracion($documento));
                }
                $empresa = Empresa::first();

                $legends = self::obtenerLeyenda($documento);
                $legends = json_encode($legends,true);
                $legends = json_decode($legends,true);

                if($size === 80)
                {
                    $pdf = PDF::loadview('ventas.documentos.impresion.comprobante_ticket',[
                        'documento' => $documento,
                        'detalles' => $detalles,
                        'moneda' => $documento->simboloMoneda(),
                        'empresa' => $empresa,
                        "legends" =>  $legends,
                        ])->setPaper([0, 0, 226.772, 651.95]);
                    return $pdf->stream($documento->serie.'-'.$documento->correlativo.'.pdf');
                }
                else
                {
                    $pdf_condicion = $empresa->condicion === '1' ? 'comprobante_normal_nuevo' : 'comprobante_normal';
                    $pdf = PDF::loadview('ventas.documentos.impresion.'.$pdf_condicion,[
                        'documento' => $documento,
                        'detalles' => $detalles,
                        'moneda' => $documento->simboloMoneda(),
                        'empresa' => $empresa,
                        "legends" =>  $legends,
                        ])->setPaper('a4')->setWarnings(false);

                    return $pdf->stream($documento->serie.'-'.$documento->correlativo.'.pdf');
                }
            }
        }
        catch(Exception $e)
        {
            $cadena = explode('-',$value);
            $id = $cadena[0];
            $size = (int) $cadena[1];
            $documento = Documento::findOrFail($id);
            $detalles = Detalle::where('documento_id',$id)->where('estado','ACTIVO')->get();
            $empresa = Empresa::first();

            $legends = self::obtenerLeyenda($documento);
            $legends = json_encode($legends,true);
            $legends = json_decode($legends,true);

            if($size === 80)
            {
                $pdf = PDF::loadview('ventas.documentos.impresion.comprobante_ticket',[
                    'documento' => $documento,
                    'detalles' => $detalles,
                    'moneda' => $documento->simboloMoneda(),
                    'empresa' => $empresa,
                    "legends" =>  $legends,
                    ])->setPaper([0, 0, 226.772, 651.95]);
                return $pdf->stream($documento->serie.'-'.$documento->correlativo.'.pdf');
            }
            else
            {
                $pdf_condicion = $empresa->condicion === '1' ? 'comprobante_normal_nuevo' : 'comprobante_normal';
                $pdf = PDF::loadview('ventas.documentos.impresion.'.$pdf_condicion,[
                    'documento' => $documento,
                    'detalles' => $detalles,
                    'moneda' => $documento->simboloMoneda(),
                    'empresa' => $empresa,
                    "legends" =>  $legends,
                    ])->setPaper('a4')->setWarnings(false);

                return $pdf->stream($documento->serie.'-'.$documento->correlativo.'.pdf');
            }
        }
    }

    public function xml($id)
    {

        $documento = Documento::findOrFail($id);
        if((int)$documento->tipo_venta === 127 || (int)$documento->tipo_venta === 128)
        {
            if ($documento->sunat == '0' || $documento->sunat == '2' ) {
                //ARREGLO COMPROBANTE
                $arreglo_comprobante = array(
                    "tipoOperacion" => $documento->tipoOperacion(),
                    "tipoDoc"=> $documento->tipoDocumento(),
                    "serie" => '000',
                    "correlativo" => '000',
                    "fechaEmision" => self::obtenerFecha($documento),
                    "observacion" => $documento->observacion,
                    "tipoMoneda" => $documento->simboloMoneda(),
                    "client" => array(
                        "tipoDoc" => $documento->tipoDocumentoCliente(),
                        "numDoc" => $documento->documento_cliente,
                        "rznSocial" => $documento->cliente,
                        "address" => array(
                            "direccion" => $documento->direccion_cliente,
                        )),
                    "company" => array(
                        "ruc" =>  $documento->ruc_empresa,
                        "razonSocial" => $documento->empresa,
                        "address" => array(
                            "direccion" => $documento->direccion_fiscal_empresa,
                        )),
                    "mtoOperGravadas" => $documento->sub_total,
                    "mtoOperExoneradas" => 0,
                    "mtoIGV" => $documento->total_igv,

                    "valorVenta" => $documento->sub_total,
                    "totalImpuestos" => $documento->total_igv,
                    "mtoImpVenta" => $documento->total ,
                    "ublVersion" => "2.1",
                    "details" => self::obtenerProductos($documento->id),
                    "legends" =>  self::obtenerLeyenda($documento),
                );

                $comprobante= json_encode($arreglo_comprobante);
                $data = generarXmlapi($comprobante, $documento->empresa_id);
                $name = $documento->serie.'-'.$documento->correlativo.'.xml';
                $pathToFile = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.$name);
                if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'xml'))) {
                    mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'xml'));
                }
                file_put_contents($pathToFile, $data);

                $ruta = public_path().'/storage/xml/'.$name;

                return response()->download($ruta);
                // return response()->file($pathToFile);

            }else{

                //OBTENER CORRELATIVO DEL COMPROBANTE ELECTRONICO
                $comprobante = event(new ComprobanteRegistrado($documento,$documento->serie));
                //ENVIAR COMPROBANTE PARA LUEGO GENERAR XML
                $data = generarXmlapi($comprobante[0],$documento->empresa_id);
                $name = $documento->serie.'-'.$documento->correlativo.'.xml';
                $pathToFile = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.$name);
                if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'xml'))) {
                    mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'xml'));
                }
                file_put_contents($pathToFile, $data);
                $ruta = public_path().'/storage/xml/'.$name;

                return response()->download($ruta);
                //return response()->file($pathToFile);
            }
        }
        else
        {
            Session::flash('error', 'Este documento no retorna este formato.');
            return back();
        }

    }

    public function qr_code($id)
    {
        try{
            $documento = Documento::findOrFail($id);

            if($documento->sunat == '1')
            {
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

                $pathToFile_qr = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'qrs'.DIRECTORY_SEPARATOR.$name_qr);

                if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'qrs'))) {
                    mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'qrs'));
                }

                file_put_contents($pathToFile_qr, $data_qr);

                $documento->ruta_qr = 'public/qrs/'.$name_qr;
                $documento->update();

                return array('success' => true,'mensaje' => 'QR creado exitosamente');
            }

            if($documento->sunat == '0'){
                $miQr = QrCode::
                        format('svg')
                        ->size(130)  //defino el tamaño
                        ->backgroundColor(0, 0, 0) //defino el fondo
                        ->color(255, 255, 255)
                        ->margin(1)  //defino el margen
                        ->generate($documento->ruc_empresa.'|'.$documento->tipoDocumento().'|'.$documento->serie.'|'.$documento->correlativo.'|'.$documento->total_igv.'|'.$documento->total.'|'.getFechaFormato( $documento->fecha_emision ,'d/m/Y'));

                $name_qr = $documento->serie."-".$documento->correlativo.'.svg';

                $pathToFile_qr = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'qrs'.DIRECTORY_SEPARATOR.$name_qr);

                if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'qrs'))) {
                    mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'qrs'));
                }

                file_put_contents($pathToFile_qr, $miQr);

                $documento->ruta_qr = 'public/qrs/'.$name_qr;
                $documento->update();
                return array('success' => false,'mensaje' => 'Ya tiene QR');
            }
        }
        catch(Exception $e)
        {
            return array('success' => false,'mensaje' => $e->getMessage());
        }
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
        $detalles = Detalle::where('documento_id',$id)->where('estado','ACTIVO')->get();
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
        try{
            $documento = Documento::findOrFail($id);
            //OBTENER CORRELATIVO DEL COMPROBANTE ELECTRONICO
            $existe = event(new DocumentoNumeracion($documento));
            if($existe[0]){
                if ($existe[0]->get('existe') == true) {
                    return array('success' => true,'mensaje' => 'Documento validado.');
                }else{
                    return array('success' => false, 'mensaje' => 'Tipo de Comprobante no registrado en la empresa.');
                }
            }else{
                return array('success' => false, 'mensaje' => 'Empresa sin parametros para emitir comprobantes electronicos.');
            }
        }
        catch(Exception $e)
        {
            return array('success' => false,'mensaje' => $e->getMessage());
        }

    }

    public function sunat_valida($id)
    {
        try
        {
            $documento = Documento::find($id);
            if ($documento->sunat != '1') {
                //ARREGLO COMPROBANTE
                $arreglo_comprobante = array(
                    "tipoOperacion" => $documento->tipoOperacion(),
                    "tipoDoc"=> $documento->tipoDocumento(),
                    "serie" => $documento->serie,
                    "correlativo" => $documento->correlativo,
                    "fechaEmision" => self::obtenerFechaEmision($documento),
                    "fecVencimiento" => self::obtenerFechaVencimiento($documento),
                    "observacion" => $documento->observacion,
                    "formaPago" => array(
                        "moneda" =>  $documento->simboloMoneda(),
                        "tipo" =>  $documento->formaPago(),
                    ),
                    "tipoMoneda" => $documento->simboloMoneda(),
                    "client" => array(
                        "tipoDoc" => $documento->tipoDocumentoCliente(),
                        "numDoc" => $documento->documento_cliente,
                        "rznSocial" => $documento->cliente,
                        "address" => array(
                            "direccion" => $documento->direccion_cliente,
                        )),
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

                //OBTENER JSON DEL COMPROBANTE EL CUAL SE ENVIARA A SUNAT
                $data = enviarComprobanteapi(json_encode($arreglo_comprobante), $documento->empresa_id);

                //RESPUESTA DE LA SUNAT EN JSON
                $json_sunat = json_decode($data);
                if ($json_sunat->sunatResponse->success == true) {

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

                    $pathToFile_qr = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'qrs'.DIRECTORY_SEPARATOR.$name_qr);

                    if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'qrs'))) {
                        mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'qrs'));
                    }

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

                    // Session::flash('success','Documento de Venta enviada a Sunat con exito.');
                    // return view('ventas.documentos.index',[

                    //     'id_sunat' => $json_sunat->sunatResponse->cdrResponse->id,
                    //     'descripcion_sunat' => $json_sunat->sunatResponse->cdrResponse->description,
                    //     'notas_sunat' => $json_sunat->sunatResponse->cdrResponse->notes,
                    //     'sunat_exito' => true

                    // ])->with('sunat_exito', 'success');
                    return array('success' => true,'mensaje' => 'Documento de Venta enviada a Sunat con exito.');

                }else{

                    //COMO SUNAT NO LO ADMITE VUELVE A SER 0
                    // $documento->correlativo = null;
                    // $documento->serie = null;
                    $documento->sunat = '0';
                    $documento->update();

                    if ($json_sunat->sunatResponse->error) {
                        $id_sunat = $json_sunat->sunatResponse->error->code;
                        $descripcion_sunat = $json_sunat->sunatResponse->error->message;


                    }else {
                        $id_sunat = $json_sunat->sunatResponse->cdrResponse->id;
                        $descripcion_sunat = $json_sunat->sunatResponse->cdrResponse->description;

                    };

                    $errorVenta = new ErrorVenta();
                    $errorVenta->documento_id = $documento->id;
                    $errorVenta->tipo = 'sunat-envio';
                    $errorVenta->descripcion = 'Error al enviar a sunat';
                    $errorVenta->ecxepcion = $descripcion_sunat;
                    $errorVenta->save();

                    return array('success' => false, 'mensaje' => $descripcion_sunat);
                }
            }else{
                $documento->sunat = '1';
                $documento->update();
                // Session::flash('error','Documento de venta fue enviado a Sunat.');
                // return redirect()->route('ventas.documento.index')->with('sunat_existe', 'error');

                return array('success' => false, 'mensaje' => 'Documento de venta fue enviado a Sunat.');
            }
        }
        catch(Exception $e)
        {
            $documento = Documento::find($id);

            $errorVenta = new ErrorVenta();
            $errorVenta->documento_id = $documento->id;
            $errorVenta->tipo = 'sunat-envio';
            $errorVenta->descripcion = 'Error al enviar a sunat';
            $errorVenta->ecxepcion = $e->getMessage();
            $errorVenta->save();
            return array('success' => false, 'mensaje' => $e->getMessage());
        }
    }

    public function vouchersAvaible(Request $request)
    {
        $data = $request->all();
        $empresa_id = $data['empresa_id'];
        $tipo = $data['tipo_id'];
        $detalle = TablaDetalle::findOrFail($tipo);
        $empresa = Empresa::findOrFail($empresa_id);
        $resultado = (Numeracion::where('empresa_id',$empresa_id)->where('estado','ACTIVO')->where('tipo_comprobante',$tipo))->exists();

        $enviar = [
                    'existe' => ($resultado == true) ? true : false,
                    'comprobante' => $detalle->descripcion,
                    'empresa' => $empresa->razon_social,
                ];

        return  response()->json($enviar);


    }

    public function customers_all(Request $request)
    {
        $clientes = Cliente::where('estado','!=','ANULADO')->get();

        $enviar = [
                    'clientes' => $clientes
                ];

        return  response()->json($enviar);


    }

    public function customers(Request $request)
    {
        $data = $request->all();
        $tipo = $data['tipo_id'];
        $pun_tipo = '';

        if ($tipo == '127') {
            $clientes = Cliente::where('estado','!=','ANULADO')
            ->where('tipo_documento','RUC')
            ->get();
            $pun_tipo = '1';
        }else{
            $clientes = Cliente::where('estado','!=','ANULADO')
            ->where('tipo_documento','!=','RUC')
            ->get();
            $pun_tipo = '0';
        }

        $enviar = [
                    'clientes' => $clientes,
                    'tipo' => $pun_tipo,
                ];

        return  response()->json($enviar);


    }

    //LOTES PARA BUSQUEDA
    public function getLot($tipo_cliente)
    {
        return datatables()->query(
            DB::table('lote_productos')
            ->join('productos_clientes','productos_clientes.producto_id','=','lote_productos.producto_id')
            ->join('productos','productos.id','=','lote_productos.producto_id')
            ->join('categorias','categorias.id','=','productos.categoria_id')
            ->join('tabladetalles','tabladetalles.id','=','productos.medida')
            ->leftJoin('compra_documento_detalles','compra_documento_detalles.lote_id','=','lote_productos.id')
            ->select('compra_documento_detalles.precio_soles','lote_productos.*','productos.nombre','productos.codigo_barra','productos_clientes.cliente','productos_clientes.moneda','tabladetalles.simbolo as unidad_producto',
                    'productos_clientes.monto','categorias.descripcion as categoria', DB::raw('DATE_FORMAT(lote_productos.fecha_vencimiento, "%d/%m/%Y") as fecha_venci')) //DB::raw('DATE_FORMAT(lote_productos.fecha_vencimiento, "%d/%m/%Y") as fecha_venci')
            ->where('lote_productos.cantidad_logica','>',0)
            ->where('lote_productos.estado','1')
            ->where('productos_clientes.cliente','121') //TIPO DE CLIENTE CONSUMIDOR TABLA DETALLE (121)
            ->where('productos_clientes.moneda','1') // TABLA DETALLE SOLES(1)
            ->orderBy('lote_productos.id','ASC')
            ->where('productos_clientes.estado','ACTIVO')
        )->toJson();
    }

    //CAMBIAR CANTIDAD LOGICA DEL LOTE
    public function quantity(Request $request)
    {
        $data = $request->all();
        $producto_id = $data['producto_id'];
        $cantidad = $data['cantidad'];
        $condicion = $data['condicion'];
        $mensaje = '';
        $lote = LoteProducto::findOrFail($producto_id);
        //DISMINUIR
        if ($lote->cantidad_logica >= $cantidad && $condicion == '1' ) {
            $nuevaCantidad = $lote->cantidad_logica - $cantidad;
            $lote->cantidad_logica = $nuevaCantidad;
            $lote->update();
            $mensaje = 'Cantidad aceptada';
        }
        //AUMENTAR
        if ($condicion == '0' ) {
            $nuevaCantidad = $lote->cantidad_logica + $cantidad;
            $lote->cantidad_logica = $nuevaCantidad;
            $lote->update();
            $mensaje = 'Cantidad regresada';
        }

        return $mensaje;
    }

    //DEVOLVER CANTIDAD LOGICA AL CERRAR VENTANA
    public function returnQuantity(Request $request)
    {
        $data = $request->all();
        $cantidades = $data['cantidades'];
        $productosJSON = $cantidades;
        $productotabla = json_decode($productosJSON);
        $mensaje = '';
        foreach ($productotabla as $detalle) {
            //DEVOLVEMOS CANTIDAD AL LOTE Y AL LOTE LOGICO
            $lote = LoteProducto::findOrFail($detalle->producto_id);
            $lote->cantidad_logica = $lote->cantidad_logica + $detalle->cantidad;
            //$lote->cantidad =  $lote->cantidad_logica;
            $lote->estado = '1';
            $lote->update();
            $mensaje = 'Cantidad devuelta';
        };

        return $mensaje;

    }

    //DEVOLVER LOTE
    public function returnLote(Request $request)
    {
        $data = $request->all();
        $lote_id = $data['lote_id'];
        $lote = LoteProducto::find($lote_id);

        if($lote)
        {
            return response()->json([
                'success' => true,
                'lote' => $lote,
            ]);
        }
        else{
            return response()->json([
                'success' => false,
            ]);
        }
    }

    //ACTUALIZAR LOTE E EDICION DE CANTIDAD
    public function updateLote(Request $request)
    {
        try{
            DB::beginTransaction();
            $data = $request->all();
            $lote_id = $data['lote_id'];
            $cantidad_sum = $data['cantidad_sum'];
            $cantidad_res = $data['cantidad_res'];
            $lote = LoteProducto::find($lote_id);

            if($lote)
            {
                $lote->cantidad_logica = $lote->cantidad_logica + ($cantidad_sum - $cantidad_res);
                $lote->update();
                DB::commit();
                return response()->json([
                    'success' => true,
                    'lote' => $lote,
                ]);
            }
            else{
                DB::rollBack();
                return response()->json([
                    'success' => false,
                ]);
            }
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'success' => false,
            ]);
        }
    }
}
