<?php

namespace App\Http\Controllers\Compras;

use App\Almacenes\LoteProducto;
use App\Almacenes\Producto;
use App\Compras\CuentaProveedor;
use App\Compras\Detalle as ComprasDetalle;
use App\Compras\Documento\Detalle as DocumentoDetalle;
use App\Compras\Documento\Documento;
use App\Compras\Documento\Pago\Transferencia;
use App\Compras\Orden;
use App\Compras\Pago as ComprasPago;
use App\Compras\Proveedor;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Empresa\Empresa;
use App\Movimientos\MovimientoAlmacen;
use App\Ventas\Documento\Pago\Pago;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade as PDF;
use App\Mantenimiento\Tabla\Detalle as TablaDetalle;
use Exception;

class DocumentoController extends Controller
{
    public function index()
    {
        $this->authorize('haveaccess','documento_compra.index');
        return view('compras.documentos.index');
    }

    public function getDocument(){
        $this->authorize('haveaccess','documento_compra.index');
        $documentos = Documento::where('estado','!=','ANULADO')->get();
        $coleccion = collect([]);
        foreach($documentos as $documento){
            $detalles = DocumentoDetalle::where('documento_id',$documento->id)->get();
            $documento = Documento::findOrFail($documento->id);
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
                $decimal_total = number_format($total, 2, '.', '');
            }else{
                $calcularIgv = $documento->igv/100;
                $base = $subtotal / (1 + $calcularIgv);
                $nuevo_igv = $subtotal - $base;
                $decimal_total = number_format($subtotal, 2, '.', '');
            }
            //TIPO DE PAGO (OTROS)

            // CALCULAR ACUENTA EN MONEDA
            $acuenta = 0;
            $saldo = 0;

            if($documento->cuenta)
            {

                $efectivo = $documento->cuenta->detallePago->sum('efectivo');
                $importe = $documento->cuenta->detallePago->sum('importe');
                $acuenta = $efectivo + $importe;
            }
            else
            {
                $acuenta = $decimal_total;
            }


            $saldo = $decimal_total - $acuenta;
            //CALCULAR SALDO
            if ($saldo == 0.0) {
                $documento->estado = "PAGADA";
                $documento->update();
            }else{
                $documento->estado = "PENDIENTE";
                $documento->update();
            }
            $modo = TablaDetalle::where('descripcion',$documento->modo_compra)->first();

            $coleccion->push([
                'id' => $documento->id,
                'tipo' => $documento->tipo_compra,
                'tipo_pago' => $documento->tipo_pago,
                'proveedor' => $documento->proveedor->descripcion,
                'empresa' => $documento->empresa->razon_social,
                'fecha_emision' =>  Carbon::parse($documento->fecha_emision)->format( 'd/m/Y'),
                'igv' =>  $documento->igv,
                'moneda' =>  $documento->moneda,
                'tipo_cambio' =>  $documento->tipo_cambio,
                'orden_compra' =>  $documento->orden_compra,
                'subtotal' => $tipo_moneda.' '.number_format($subtotal, 2, '.', ''),
                'estado' => $documento->estado,
                'saldo' => $tipo_moneda.' '.number_format($saldo, 2, '.', ''),
                'acuenta' => $tipo_moneda.' '.number_format($acuenta, 2, '.', ''),
                'total' => $tipo_moneda.' '.number_format($decimal_total, 2, '.', ''),
                'modo' => $modo->simbolo,
            ]);
        }
        return DataTables::of($coleccion)->toJson();
    }

    public function create(Request $request)
    {
        $this->authorize('haveaccess','documento_compra.index');
        $orden = '';
        $detalles = '';
        if($request->get('orden')){
            $orden = Orden::findOrFail( $request->get('orden') );
            $detalles = ComprasDetalle::where('orden_id', $request->get('orden'))->get();
        }
        $empresas = Empresa::where('estado','ACTIVO')->get();
        $proveedores = Proveedor::where('estado','ACTIVO')->get();
        $productos = Producto::where('estado','ACTIVO')->get();
        $modos =  modo_compra();
        $fecha_hoy = Carbon::now()->toDateString();
        $fecha_actual = Carbon::now();
        $fecha_actual = date("d/m/Y",strtotime($fecha_actual));
        $fecha_5 = date("d/m/Y",strtotime($fecha_hoy."+ 5 years"));
        $monedas =  tipos_moneda();
        if (empty($orden)) {
            return view('compras.documentos.create',[
                'empresas' => $empresas,
                'proveedores' => $proveedores,
                'productos' => $productos,
                'modos' => $modos,
                'monedas' => $monedas,
                'fecha_hoy' => $fecha_hoy,
                'fecha_actual' => $fecha_actual,
                'fecha_5' => $fecha_5,
            ]);
        }else{
            return view('compras.documentos.create',[
                'orden' => $orden,
                'empresas' => $empresas,
                'proveedores' => $proveedores,
                'productos' => $productos,
                'modos' => $modos,
                'monedas' => $monedas,
                'fecha_hoy' => $fecha_hoy,
                'fecha_actual' => $fecha_actual,
                'detalles' => $detalles,
                'fecha_5' => $fecha_5,
            ]);
        }
    }

    public function getProduct()
    {
        $productos = Producto::where('estado', 'ACTIVO')->get();
        return response()->json([
            'productos' => $productos
        ]);
    }

    public function store(Request $request){
        $this->authorize('haveaccess','documento_compra.index');
        try
        {
            DB::beginTransaction();
            $productosJSON = $request->get('productos_tabla');
            $productotabla = json_decode($productosJSON[0]);

            $data = $request->all();
            $rules = [
                'fecha_emision'=> 'required',
                'fecha_entrega'=> 'required',
                'tipo_compra'=> 'required',
                'numero_tipo'=> 'required',
                'serie_tipo'=> 'required',
                'proveedor_id'=> 'required',
                'modo_compra'=> 'required',
                'observacion' => 'nullable',
                'moneda' => 'nullable',
                'tipo_cambio' => 'nullable|numeric',
                'igv' => 'required_if:igv_check,==,on|numeric|digits_between:1,3',
            ];

            $message = [
                'fecha_emision.required' => 'El campo Fecha de Emisión es obligatorio.',
                'tipo_compra.required' => 'El campo Tipo es obligatorio.',
                'fecha_entrega.required' => 'El campo Fecha de Entrega es obligatorio.',
                'numero_tipo.required' => 'El campo Número es obligatorio.',
                'serie_tipo.required' => 'El campo Serie es obligatorio.',
                'proveedor_id.required' => 'El campo Proveedor es obligatorio.',
                'modo_compra.required' => 'El campo Modo de Compra es obligatorio.',
                'moneda.required' => 'El campo Moneda es obligatorio.',
                'igv.required_if' => 'El campo Igv es obligatorio.',
                'igv.digits' => 'El campo Igv puede contener hasta 3 dígitos.',
                'igv.numeric' => 'El campo Igv debe se numérico.',
                'tipo_cambio.numeric' => 'El campo Tipo de Cambio debe se numérico.',
            ];

            Validator::make($data, $rules, $message)->validate();
            $documento = new Documento();
            $documento->fecha_emision = Carbon::createFromFormat('d/m/Y', $request->get('fecha_emision'))->format('Y-m-d');
            $documento->fecha_entrega = Carbon::createFromFormat('d/m/Y', $request->get('fecha_entrega'))->format('Y-m-d');
            $documento->sub_total = (float) $request->get('monto_sub_total');
            $documento->total_igv = (float) $request->get('monto_total_igv');
            $documento->total = (float) $request->get('monto_total');
            //-------------------------------
            if($request->get('moneda') === 'DOLARES')
            {
                $documento->sub_total_soles = (float) $request->get('monto_sub_total') * (float) $request->get('tipo_cambio');
                $documento->total_igv_soles = (float) $request->get('monto_total_igv') * (float) $request->get('tipo_cambio');
                $documento->total_soles = (float) $request->get('monto_total') * (float) $request->get('tipo_cambio');
            }
            else
            {
                $documento->sub_total_soles = (float) $request->get('monto_sub_total');
                $documento->total_igv_soles = (float) $request->get('monto_total_igv');
                $documento->total_soles = (float) $request->get('monto_total');
            }
            //-------------------------------
            $documento->empresa_id = '1';
            $documento->numero_tipo = $request->get('numero_tipo');
            $documento->serie_tipo = $request->get('serie_tipo');
            $documento->proveedor_id = $request->get('proveedor_id');
            $documento->modo_compra = $request->get('modo_compra');
            $documento->observacion = $request->get('observacion');
            $documento->moneda = $request->get('moneda');
            $documento->tipo_cambio = $request->get('tipo_cambio');
            $documento->usuario_id = auth()->user()->id;
            $documento->igv = $request->get('igv');
            if ($request->get('igv_check') == "on") {
                $documento->igv_check = "1";
            };
            $documento->tipo_compra = $request->get('tipo_compra');
            $documento->orden_compra = $request->get('orden_id');
            $documento->save();

            $numero_doc = $documento->id;
            $documento->numero_doc = 'COMPRA-'.$numero_doc;
            $documento->update();
            //Llenado de los productos
            $productosJSON = $request->get('productos_tabla');
            $productotabla = json_decode($productosJSON[0]);
            foreach ($productotabla as $detalle) {
                $producto = Producto::findOrFail($detalle->producto_id);
                $precio_soles = $detalle->precio;
                $costo_flete_soles = $detalle->costo_flete;
                //-------------------------------
                if($request->get('moneda') === 'DOLARES')
                {
                    $precio_soles = (float) $detalle->precio * (float) $request->get('tipo_cambio');
                    $costo_flete_soles = (float) $detalle->costo_flete * (float) $request->get('tipo_cambio');
                }
                else
                {
                    $precio_soles = (float) $detalle->precio;
                    $costo_flete_soles = (float) $detalle->costo_flete;
                }
                //-------------------------------
                DocumentoDetalle::create([
                    'documento_id' => $documento->id,
                    'producto_id' => $detalle->producto_id,
                    'descripcion_producto' => $producto->nombre,
                    'presentacion_producto' => '-',
                    'codigo_producto' => $producto->codigo,
                    'medida_producto' => $producto->medida,
                    'cantidad' => $detalle->cantidad,
                    'precio' => $detalle->precio,
                    'precio_soles' => $precio_soles,
                    'costo_flete' => $detalle->costo_flete,
                    'costo_flete_soles' => $costo_flete_soles,
                    'fecha_vencimiento' =>  Carbon::createFromFormat('d/m/Y', $detalle->fecha_vencimiento)->format('Y-m-d'),
                    'lote' => $detalle->lote,
                ]);
            }
            // TRANSFERRIR PAGOS DE LA ORDEN SI EXISTEN
            if($request->get('orden_id')){

                $documento = Documento::findOrFail($documento->id);
                $documento->tipo_pago =  "1";
                $documento->update();
            }
            //Registro de actividad
            $descripcion = "SE AGREGÓ EL DOCUMENTO DE COMPRA CON LA FECHA DE EMISION: ". Carbon::parse($documento->fecha_emision)->format('d/m/y');
            $gestion = "DOCUMENTO DE COMPRA";

            crearRegistro($documento, $descripcion , $gestion);
            DB::commit();
            Session::flash('success','Documento de Compra creada.');
            return redirect()->route('compras.documento.index')->with('guardar', 'success');

        }
        catch(Exception $e)
        {
            DB::rollBack();
            Session::flash('error',$e->getMessage());
            return redirect()->route('compras.documento.index')->with('guardar', 'error');
        }
    }

    public function edit($id)
    {
        $this->authorize('haveaccess','documento_compra.index');
        $empresas = Empresa::where('estado','ACTIVO')->get();
        $detalles = DocumentoDetalle::where('documento_id',$id)->get();
        $proveedores = Proveedor::where('estado','ACTIVO')->get();
        $documento = Documento::findOrFail($id);
        $productos = producto::where('estado','ACTIVO')->get();
        $presentaciones =  presentaciones();
        $fecha_hoy = Carbon::now()->toDateString();
        $fecha_actual = Carbon::now();
        $fecha_actual = date("d/m/Y",strtotime($fecha_actual));
        $fecha_5 = date("d/m/Y",strtotime($fecha_hoy."+ 5 years"));
        return view('compras.documentos.edit',[
            'empresas' => $empresas,
            'proveedores' => $proveedores,
            'documento' => $documento,
            'productos' => $productos,
            'presentaciones' => $presentaciones,
            'fecha_hoy' => $fecha_hoy,
            'fecha_actual' => $fecha_actual,
            'fecha_5' => $fecha_5,
            'detalles' => $detalles,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('haveaccess','documento_compra.index');
        $data = $request->all();
        $rules = [
            'fecha_emision'=> 'required',
            'fecha_entrega'=> 'required',
            'tipo_compra'=> 'required',
            'numero_tipo'=> 'required',
            'serie_tipo'=> 'required',
            'proveedor_id'=> 'required',
            'modo_compra'=> 'required',
            'observacion' => 'nullable',
            'moneda' => 'nullable',
            'tipo_cambio' => 'nullable|numeric',
            'igv' => 'required_if:igv_check,==,on|numeric|digits_between:1,3',
        ];
        $message = [
            'fecha_emision.required' => 'El campo Fecha de Emisión es obligatorio.',
            'tipo_compra.required' => 'El campo Tipo es obligatorio.',
            'fecha_entrega.required' => 'El campo Fecha de Entrega es obligatorio.',
            'numero_tipo.required' => 'El campo Número es obligatorio.',
            'serie_tipo.required' => 'El campo Serie es obligatorio.',
            'proveedor_id.required' => 'El campo Proveedor es obligatorio.',
            'modo_compra.required' => 'El campo Modo de Compra es obligatorio.',
            'moneda.required' => 'El campo Moneda es obligatorio.',
            'igv.required_if' => 'El campo Igv es obligatorio.',
            'igv.digits' => 'El campo Igv puede contener hasta 3 dígitos.',
            'igv.numeric' => 'El campo Igv debe se numérico.',
            'tipo_cambio.numeric' => 'El campo Tipo de Cambio debe se numérico.',
        ];
        Validator::make($data, $rules, $message)->validate();
        $documento = Documento::findOrFail($id);
        $documento->fecha_emision = Carbon::createFromFormat('d/m/Y', $request->get('fecha_emision'))->format('Y-m-d');
        $documento->fecha_entrega = Carbon::createFromFormat('d/m/Y', $request->get('fecha_entrega'))->format('Y-m-d');
        $documento->sub_total = (float) $request->get('monto_sub_total');
            $documento->total_igv = (float) $request->get('monto_total_igv');
            $documento->total = (float) $request->get('monto_total');
            //-------------------------------
            if($request->get('moneda') === 'DOLARES')
            {
                $documento->sub_total_soles = (float) $request->get('monto_sub_total') * (float) $request->get('tipo_cambio');
                $documento->total_igv_soles = (float) $request->get('monto_total_igv') * (float) $request->get('tipo_cambio');
                $documento->total_soles = (float) $request->get('monto_total') * (float) $request->get('tipo_cambio');
            }
            else
            {
                $documento->sub_total_soles = (float) $request->get('monto_sub_total');
                $documento->total_igv_soles = (float) $request->get('monto_total_igv');
                $documento->total_soles = (float) $request->get('monto_total');
            }
            //-------------------------------
        $documento->empresa_id = '1';
        $documento->proveedor_id = $request->get('proveedor_id');
        $documento->modo_compra = $request->get('modo_compra');
        $documento->observacion = $request->get('observacion');
        $documento->moneda = $request->get('moneda');
        $documento->tipo_cambio = $request->get('tipo_cambio');
        $documento->numero_tipo = $request->get('numero_tipo');
        $documento->serie_tipo = $request->get('serie_tipo');
        $documento->usuario_id = auth()->user()->id;
        if ($request->get('igv_check') == "on") {
            $documento->igv_check = "1";
            $documento->igv = $request->get('igv');
        }else{
            $documento->igv_check = '';
            $documento->igv = '';
        }
        $documento->tipo_compra = $request->get('tipo_compra');
        $documento->update();
        $productosJSON = $request->get('productos_tabla');
        $productotabla = json_decode($productosJSON[0]);
        if ($productotabla) {
            DocumentoDetalle::where('documento_id', $documento->id)->delete();

            foreach($documento->lotes as $lot)
            {
                MovimientoAlmacen::where('lote_id', $lot->id)->where('producto_id', $lot->producto_id)->where('compra_documento_id', $lot->compra_documento_id)->where('nota', 'COMPRA')->where('movimiento', 'INGRESO')->delete();
                $lote = LoteProducto::find($lot->id);
                $producto_id = $lote->producto_id;
                $lote->estado = '0';
                $lote->update();

                //RECORRER DETALLE NOTAS
                $cantidadProductos = LoteProducto::where('producto_id',$producto_id)->where('estado','1')->sum('cantidad');
                //ACTUALIZAR EL STOCK DEL PRODUCTO
                $producto = Producto::findOrFail($lote->producto_id);
                $producto->stock = $cantidadProductos ? $cantidadProductos : 0.00;
                $producto->update();
            }

            foreach ($productotabla as $detalle) {
                $producto = Producto::findOrFail($detalle->producto_id);
                $precio_soles = $detalle->precio;
                $costo_flete_soles = $detalle->costo_flete;
                //-------------------------------
                if($request->get('moneda') === 'DOLARES')
                {
                    $precio_soles = (float) $detalle->precio * (float) $request->get('tipo_cambio');
                    $costo_flete_soles = (float) $detalle->costo_flete * (float) $request->get('tipo_cambio');
                }
                else
                {
                    $precio_soles = (float) $detalle->precio;
                    $costo_flete_soles = (float) $detalle->costo_flete;
                }
                DocumentoDetalle::create([
                    'documento_id' => $documento->id,
                    'producto_id' => $detalle->producto_id,
                    'descripcion_producto' => $producto->nombre,
                    'presentacion_producto' => '-',
                    'codigo_producto' => $producto->codigo,
                    'medida_producto' => $producto->medida,
                    'cantidad' => $detalle->cantidad,
                    'precio' => $detalle->precio,
                    'precio_soles' => $precio_soles,
                    'costo_flete' => $detalle->costo_flete,
                    'costo_flete_soles' => $costo_flete_soles,
                    'fecha_vencimiento' =>  Carbon::createFromFormat('d/m/Y', $detalle->fecha_vencimiento)->format('Y-m-d'),
                    'lote' => $detalle->lote,
                ]);
            }
        }
        //Registro de actividad
        $descripcion = "SE MODIFICÓ EL DOCUMENTO DE COMPRA CON LA FECHA DE EMISION: ". Carbon::parse($documento->fecha_emision)->format('d/m/y');
        $gestion = "DOCUMENTO DE COMPRA";
        modificarRegistro($documento, $descripcion , $gestion);
        Session::flash('success','Documento de Compra modificada.');
        return redirect()->route('compras.documento.index')->with('modificar', 'success');
    }

    public function destroy($id)
    {
        $this->authorize('haveaccess','documento_compra.index');
        $documento = Documento::findOrFail($id);
        $documento->estado = 'ANULADO';
        $documento->update();
        //Registro de actividad
        $descripcion = "SE ELIMINÓ EL DOCUMENTO DE COMPRA CON LA FECHA DE EMISION: ". Carbon::parse($documento->fecha_emision)->format('d/m/y');
        $gestion = "DOCUMENTO DE COMPRA";
        eliminarRegistro($documento, $descripcion , $gestion);

        DB::table('kardex')
            ->where('numero_doc', $documento->numero_doc)
            ->update(['estado' => 'ANULADO']);

        if($documento->cuenta)
        {
            $cuenta_proveedor = CuentaProveedor::find($documento->cuenta->id);
            $cuenta_proveedor->delete();

            //Registro de actividad
            $descripcion = "SE ELIMINÓ EL DOCUMENTO DE COMPRA DEL DOCUMENTO (".$documento->id.") CON LA FECHA DE EMISION: ". Carbon::parse($documento->fecha_emision)->format('d/m/y');
            $gestion = "DOCUMENTO DE COMPRA";
            eliminarRegistro($documento, $descripcion , $gestion);
        }

        Session::flash('success','Documento de Compra eliminada.');
        return redirect()->route('compras.documento.index')->with('eliminar', 'success');
    }

    public function show($id)
    {
        $this->authorize('haveaccess','documento_compra.index');
        $documento = Documento::findOrFail($id);
        $nombre_completo = $documento->usuario->user->persona->apellido_paterno.' '.$documento->usuario->user->persona->apellido_materno.' '.$documento->usuario->user->persona->nombres;
        $detalles = DocumentoDetalle::where('documento_id',$id)->get();
        $presentaciones = presentaciones();
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
        return view('compras.documentos.show', [
            'documento' => $documento,
            'detalles' => $detalles,
            'presentaciones' => $presentaciones,
            'subtotal' => $decimal_subtotal,
            'moneda' => $tipo_moneda,
            'igv' => $decimal_igv,
            'total' => $decimal_total,
            'nombre_completo' => $nombre_completo
        ]);
    }

    public function report($id)
    {
        $this->authorize('haveaccess','documento_compra.index');
        ini_set("max_execution_time", 60000);
        $documento = Documento::findOrFail($id);
        $nombre_completo = $documento->usuario->user->persona->apellido_paterno.' '.$documento->usuario->user->persona->apellido_materno.' '.$documento->usuario->user->persona->nombres;
        $detalles = DocumentoDetalle::where('documento_id',$id)->get();
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

        // $data = [
        //     'documento' => $documento,
        //     'nombre_completo' => $nombre_completo,
        //     'detalles' => $detalles,
        //     'presentaciones' => $presentaciones,
        //     'subtotal' => $decimal_subtotal,
        //     'moneda' => $tipo_moneda,
        //     'igv' => $decimal_igv,
        //     'total' => $decimal_total,
        // ];

        // return $data;


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

    }

    public function comprobante_store(Request $request)
    {
        $compras = Documento::where('moneda', $request->moneda)->where('serie_tipo', $request->serie_tipo)->where('numero_tipo', $request->numero_tipo)->where('proveedor_id',$request->proveedor_id)->where('tipo_compra',$request->tipo_compra)->get();
        $success = true;
        if(count($compras) > 0){
            $success = false;
        }

        return response()->json([
            'success' => $success
        ]);
    }

    public function comprobante_update(Request $request)
    {
        $compras = Documento::where('moneda', $request->moneda)->where('serie_tipo', $request->serie_tipo)->where('numero_tipo', $request->numero_tipo)->where('proveedor_id',$request->proveedor_id)->where('tipo_compra',$request->tipo_compra)->where('id', '!=',$request->id)->get();
        $success = true;
        if(count($compras) > 0){
            $success = false;
        }

        return response()->json([
            'success' => $success
        ]);
    }
}
