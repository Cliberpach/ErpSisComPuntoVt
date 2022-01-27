<?php

namespace App\Http\Controllers\Consultas\Kardex;

use App\Almacenes\Kardex;
use App\Almacenes\Producto;
use App\Http\Controllers\Controller;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index()
    {
        return view('consultas.kardex.producto');
    }

    public function getTable(Request $request){

        $fecini = $request->fecha_desde;
        $fecfin = $request->fecha_hasta;
        $kardex = DB::select("call Sp_Rpte_Stock_fecha("."'".$fecini."'".","."'".$fecfin."'".")");


        return response()->json([
            'success' => true,
            'kardex' => $kardex,
        ]);
    }

    public function index_top()
    {
        return view('consultas.kardex.producto_top');
    }

    public function getTableTop(Request $request){
        $top = $request->top;

        $documentos = Documento::where('estado','!=','ANULADO');
        if($request->fecha_desde && $request->fecha_hasta)
        {
            $documentos = $documentos->whereBetween('fecha_documento', [$request->fecha_desde, $request->fecha_hasta]);
        }

        $documentos = $documentos->orderBy('id', 'desc')->get();



        $coleccion_aux = collect();
        $coleccion = collect();
        foreach($documentos as $documento){
            $detalles = Detalle::where('estado','ACTIVO')->where('documento_id',$documento->id)->get();
            foreach($detalles as $detalle)
            {
                $coleccion_aux->push([
                    'codigo' => $detalle->lote->producto->codigo,
                    'cantidad' => $detalle->cantidad,
                    'producto_id' => $detalle->lote->producto_id,
                    'producto' => $detalle->lote->producto->nombre,
                    'costo' => $detalle->lote->detalle_compra ? $detalle->lote->detalle_compra->precio : 0.00,
                    'precio' => $detalle->precio_nuevo,
                    'importe' => number_format($detalle->precio_nuevo * $detalle->cantidad, 2)
                ]);
            }
        }

        $productos = Producto::where('estado','ACTIVO')->get();

        foreach($productos as $producto)
        {
            $suma_vendidos = $coleccion_aux->where('producto_id', $producto->id)->sum('cantidad') ? $coleccion_aux->where('producto_id', $producto->id)->sum('cantidad') : 0;
            $suma_importe = $coleccion_aux->where('producto_id', $producto->id)->sum('importe') ? $coleccion_aux->where('producto_id', $producto->id)->sum('importe') : 0;
            $coleccion->push([
                'codigo' => $producto->codigo,
                'producto' => $producto->nombre,
                'cantidad' => $suma_vendidos,
                'importe' => $suma_importe,
            ]);
        }

        $coll = $coleccion->sortByDesc('cantidad')->take($top);

        $arr = array();
        foreach($coll as $coll_)
        {
            $arr_aux = array('codigo' => $coll_['codigo'],
            'producto' => $coll_['producto'],
            'cantidad' => $coll_['cantidad'],
            'importe' => $coll_['importe']);
            array_push($arr,$arr_aux);
        }

        return response()->json([
            'success' => true,
            'kardex' => $arr,
            'top' => count($coll->all())
        ]);
    }
}
