<?php

namespace App\Http\Controllers\Almacenes;

use App\Almacenes\DetalleNotaSalidad;
use App\Almacenes\LoteProducto;
use App\Almacenes\MovimientoNota;
use App\Almacenes\NotaSalidad;
use App\Almacenes\Producto;
use App\Compras\Documento\Pago\Detalle;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Tabla\General;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class NotaSalidadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('haveaccess','nota_salida.index');
        return view('almacenes.nota_salidad.index');
    }
    public function gettable()
    {
        $this->authorize('haveaccess','nota_salida.index');
        $data=DB::table("nota_salidad as n")
        ->select('n.*',)->where('n.estado','ACTIVO')->get();
        return DataTables::of($data)->make(true);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('haveaccess','nota_salida.index');
        $fecha_hoy = Carbon::now()->toDateString();
        $fecha=Carbon::createFromFormat('Y-m-d', $fecha_hoy);
        $fecha=str_replace("-", "", $fecha);
        $fecha=str_replace(" ", "", $fecha);
        $fecha=str_replace(":", "", $fecha);
        $origenes=  General::find(28)->detalles;
        $destinos=  General::find(29)->detalles;
        $lotes=DB::table('lote_productos')->get();
        $ngenerado=$fecha.(DB::table('nota_salidad')->count()+1);
        $usuarios=User::get();
        $productos=Producto::where('estado','ACTIVO')->get();
        return view('almacenes.nota_salidad.create',["fecha_hoy"=>$fecha_hoy,
        "origenes"=>$origenes,'destinos'=>$destinos,
        'ngenerado'=>$ngenerado,'usuarios'=>$usuarios,
        'productos'=>$productos,'lotes'=>$lotes]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('haveaccess','nota_salida.index');
        $data = $request->all();

        $rules = [

            'fecha' => 'required',
            'destino' => 'required',
            'origen' => 'nullable',
            'notadetalle_tabla'=>'required',


        ];
        $message = [
            'fecha.required' => 'El campo fecha  es Obligatorio',
            'destino.required' => 'El campo destino  es Obligatorio',
            'notadetalle_tabla.required'=>'No hay dispositivos',
        ];

        Validator::make($data, $rules, $message)->validate();

        $notasalidad=new NotaSalidad();
        $notasalidad->numero=$request->get('numero');
        $notasalidad->fecha=$request->get('fecha');
        $destino=DB::table('tabladetalles')->where('id',$request->destino)->first();
        $notasalidad->destino=$destino->descripcion;
        $notasalidad->origen=$request->origen;
        $notasalidad->usuario=Auth()->user()->usuario;
        $notasalidad->save();

        $articulosJSON = $request->get('notadetalle_tabla');
        $notatabla = json_decode($articulosJSON[0]);

        foreach ($notatabla as $fila) {
           DetalleNotaSalidad::create([
                'nota_salidad_id' => $notasalidad->id,
                'lote_id' => $fila->lote_id,
                'cantidad' => $fila->cantidad,
                'producto_id'=> $fila->producto_id,
            ]);
        }
        $descripcion = "SE AGREGÓ LA NOTA DE SALIDAD";
        $gestion = "ALMACEN / NOTA SALIDAD";
        crearRegistro($notasalidad, $descripcion , $gestion);


        Session::flash('success','NOTA DE SALIDAD');
        return redirect()->route('almacenes.nota_salidad.index')->with('guardar', 'success');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('haveaccess','nota_salida.index');
        $notasalidad=NotaSalidad::findOrFail($id);
        $data=array();
        $detallenotasalidad=DB::table('detalle_nota_salidad')->where('nota_salidad_id',$notasalidad->id)->get();
        foreach($detallenotasalidad as $fila)
        {
            $lote = LoteProducto::find($fila->lote_id);
            $producto =Producto::find($fila->producto_id);
            array_push($data,array(
                    'producto_id'=>$fila->producto_id,
                    'codigo'=>$producto->codigo,
                    'cantidad'=>$fila->cantidad,
                    'lote'=>$lote->codigo_lote,
                    'producto'=>$producto->nombre,
                    'costo' => $lote->detalle_compra ? $lote->detalle_compra->precio : 0.00,
                    'precio'=> $producto->precio_venta_minimo,
                    'lote_id' => $fila->lote_id
            ));
        }
        $origenes=  General::find(28)->detalles;
        $destinos=  General::find(29)->detalles;
        $lotes=DB::table('lote_productos')->get();
        $usuarios=User::get();
        $productos=Producto::where('estado','ACTIVO')->get();
        return view('almacenes.nota_salidad.show',[
        "origenes"=>$origenes,'destinos'=>$destinos,
       'usuarios'=>$usuarios,
        'productos'=>$productos,'lotes'=>$lotes,'notasalidad'=>$notasalidad,'detalle'=>json_encode($data)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->authorize('haveaccess','nota_salida.index');
        $notasalidad=NotaSalidad::findOrFail($id);
        $data=array();
        $detallenotasalidad=DB::table('detalle_nota_salidad')->where('nota_salidad_id',$notasalidad->id)->get();
        foreach($detallenotasalidad as $fila)
        {
            $lote=DB::table('lote_productos')->where('id',$fila->lote_id)->first();
            $producto=DB::table('productos')->where('id',$fila->producto_id)->first();
            array_push($data,array(
                    'producto_id'=>$fila->producto_id,
                    'cantidad'=>$fila->cantidad,
                    'lote'=>$lote->codigo_lote,
                    'producto'=>$producto->nombre,
                    'lote_id'=>$fila->lote_id
            ));
        }
        $origenes=  General::find(28)->detalles;
        $destinos=  General::find(29)->detalles;
        $lotes=DB::table('lote_productos')->get();
        $usuarios=User::get();
        $productos=Producto::where('estado','ACTIVO')->get();
        return view('almacenes.nota_salidad.edit',[
        "origenes"=>$origenes,'destinos'=>$destinos,
       'usuarios'=>$usuarios,
        'productos'=>$productos,'lotes'=>$lotes,'notasalidad'=>$notasalidad,'detalle'=>json_encode($data)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->authorize('haveaccess','nota_salida.index');
         $data = $request->all();

         $rules = [

            'fecha' => 'required',
            'destino' => 'required',
            'origen' => 'nullable',
            'notadetalle_tabla'=>'required',


        ];

        $message = [
            'fecha.required' => 'El campo fecha  es Obligatorio',
            'destino.required' => 'El campo destino  es Obligatorio',
            'notadetalle_tabla.required'=>'No hay dispositivos',
        ];

         Validator::make($data, $rules, $message)->validate();


         //$registro_sanitario = new RegistroSanitario();
         $notasalidad=NotaSalidad::findOrFail($id);
         $notasalidad->fecha=$request->get('fecha');
         $destino=DB::table('tabladetalles')->where('id',$request->destino)->first();
         $notasalidad->destino=$destino->descripcion;
         $notasalidad->usuario=Auth()->user()->usuario;
         $notasalidad->update();

         $productosJSON = $request->get('notadetalle_tabla');
         $notatabla = json_decode($productosJSON[0]);
         if($notatabla != "")
         {
             DetalleNotaSalidad::where('nota_salidad_id',$notasalidad->id)->delete();
             foreach ($notatabla as $fila) {

                $lote_producto = LoteProducto::findOrFail($fila->lote_id);
                $cantidadmovimiento = DB::table("movimiento_nota")->where('lote_id',$fila->lote_id)->where('producto_id',$fila->producto_id)->where('nota_id',$id)->where('movimiento','SALIDA')->first()->cantidad;
                $cantidadmovimiento = $cantidadmovimiento ? $cantidadmovimiento : 0;
                $lote_producto->cantidad = $lote_producto->cantidad + $cantidadmovimiento;
                $lote_producto->cantidad_logica = $lote_producto->cantidad + $cantidadmovimiento;
                $lote_producto->update();

                MovimientoNota::where('lote_id',$fila->lote_id)->where('producto_id',$fila->producto_id)->where('nota_id',$id)->where('movimiento','SALIDA')->delete();

                DetalleNotaSalidad::create([
                    'nota_salidad_id' => $id,
                    'lote_id' => $fila->lote_id,
                    'cantidad' => $fila->cantidad,
                    'producto_id'=> $fila->producto_id,
                ]);

              }
         }
         //Registro de actividad
         $descripcion = "SE AGREGÓ LA NOTA DE SALIDAD ";
         $gestion = "ALMACEN / NOTA SALIDAD";
         crearRegistro($notasalidad, $descripcion , $gestion);


         Session::flash('success','NOTA DE SALIDAD');
         return redirect()->route('almacenes.nota_salidad.index')->with('guardar', 'success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('haveaccess','nota_salida.index');
        $notasalidad = NotaSalidad::findOrFail($id);
        $notasalidad->estado="ANULADO";
        $notasalidad->save();
        foreach($notasalidad->detalles as $detalle)
        {
            $lote = LoteProducto::find($detalle->lote_id);
            $lote->cantidad = $lote->cantidad + $detalle->cantidad;
            $lote->cantidad_logica = $lote->cantidad + $detalle->cantidad;
            $lote->update();
        }
        Session::flash('success','NOTA DE SALIDAD');
        return redirect()->route('almacenes.nota_salidad.index')->with('guardar', 'success');
    }

    public function getLot()
    {
        $this->authorize('haveaccess','nota_salida.index');
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
            ->where('productos_clientes.cliente','121')
            ->where('productos_clientes.moneda','1')
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
            $lote = LoteProducto::findOrFail($detalle->lote_id);
            $lote->cantidad_logica = $lote->cantidad_logica + $detalle->cantidad;
            //$lote->cantidad =  $lote->cantidad_logica;
            $lote->estado = '1';
            $lote->update();
            $mensaje = 'Cantidad devuelta';
        };

        return $mensaje;
    }

    //DEVOLVER CANTIDAD LOGICA AL CERRAR VENTANA EDIT
    public function returnQuantityEdit(Request $request)
    {
        $data = $request->all();
        $cantidades = $data['cantidades'];
        $productosJSON = $cantidades;
        $productotabla = json_decode($productosJSON);
        $id = $data['nota_id'];
        $mensaje = '';
        foreach ($productotabla as $detalle) {
            //DEVOLVEMOS CANTIDAD AL LOTE Y AL LOTE LOGICO
            $lote = LoteProducto::findOrFail($detalle->lote_id);
            //$cantidadmovimiento = DB::table("movimiento_nota")->where('lote_id',$lote->id)->where('producto_id',$lote->producto_id)->where('nota_id',$id)->where('movimiento','SALIDA')->first()->cantidad;
            $movimiento = DB::table("movimiento_nota")->where('lote_id',$lote->id)->where('producto_id',$lote->producto_id)->where('nota_id',$id)->where('movimiento','SALIDA')->first();
            if($movimiento)
            {
                $cantidadmovimiento = $movimiento->cantidad;

                if($cantidadmovimiento > $detalle->cantidad)
                {
                    $mover = $cantidadmovimiento - $detalle->cantidad;
                    $lote->cantidad_logica = $lote->cantidad_logica - $mover;
                }
                else
                {
                    $mover = $detalle->cantidad - $cantidadmovimiento;
                    $lote->cantidad_logica = $lote->cantidad_logica + $mover;
                }



                //$lote->cantidad =  $lote->cantidad_logica;
                $lote->estado = '1';
                $lote->update();
            }
            else{
                $lote = LoteProducto::findOrFail($detalle->lote_id);
                $lote->cantidad_logica = $lote->cantidad_logica + $detalle->cantidad;
                //$lote->cantidad =  $lote->cantidad_logica;
                $lote->estado = '1';
                $lote->update();
                $mensaje = 'Cantidad devuelta';
            }
            $mensaje = 'Cantidad devuelta';
        };

        return $mensaje;
    }

    //DEVOLVER CANTIDAD LOGICA DEL LOTE ELIMINADO
    public function returnQuantityLoteInicio(Request $request)
    {
        $data = $request->all();
        $cantidades = $data['cantidades'];
        $productosJSON = $cantidades;
        $productotabla = json_decode($productosJSON);
        $mensaje = '';
        foreach ($productotabla as $detalle) {
            //DEVOLVEMOS CANTIDAD AL LOTE Y AL LOTE LOGICO
            $lote = LoteProducto::findOrFail($detalle->lote_id);
            $lote->cantidad_logica = $lote->cantidad_logica - $detalle->cantidad;
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

    //ACTUALIZAR LOTE E EDICION DE CANTIDAD
    public function updateLoteEdit(Request $request)
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
