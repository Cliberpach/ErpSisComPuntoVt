<?php

namespace App\Http\Controllers\Consultas\Kardex;

use App\Almacenes\Kardex;
use App\Almacenes\Producto;
use App\Http\Controllers\Controller;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        return view('consultas.kardex.producto');
    }

    public function getTable(Request $request){

        if($request->fecha_desde && $request->fecha_hasta)
        {
            $kardex = Kardex::whereBetween('fecha', [$request->fecha_desde, $request->fecha_hasta])->orderBy('id', 'desc')->get();
        }
        else
        {
            $kardex = Kardex::orderBy('id', 'desc')->get();
        }



        $coleccion = collect();
        foreach($kardex as $item){

            $coleccion->push([
                'producto' => $item->producto->nombre,
                'origen' => $item->origen,
                'numero_doc' => $item->numero_doc,
                'fecha' => $item->fecha,
                'cantidad' => $item->cantidad,
                'descripcion' => $item->descripcion,
                'precio' => $item->precio,
                'importe' =>  $item->importe,
                'stock' =>  $item->stock
            ]);
        }

        return response()->json([
            'success' => true,
            'kardex' => $coleccion,
        ]);
    }

    public function getTableTop(Request $request){

        $top = 4;
        //$top = $request->top;

        $documentos = Documento::where('estado','!=','ANULADO');
        // if($request->fecha_desde && $request->fecha_hasta)
        // {
        //     $documentos = $documentos->whereBetween('fecha_documento', [$request->fecha_desde, $request->fecha_hasta]);
        // }

        $documentos = $documentos->orderBy('id', 'desc')->get();

        // if($request->fecha_desde && $request->fecha_hasta)
        // {
        //     $documentos = Documento::where('estado','!=','ANULADO')->whereBetween('fecha_documento', [$request->fecha_desde, $request->fecha_hasta])->orderBy('id', 'desc')->get();
        // }
        // else
        // {
        //     $documentos = Documento::where('estado','!=','ANULADO')->orderBy('id', 'desc')->get();
        // }



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
            $suma_vendidos = $coleccion_aux->where('producto_id', $producto->id)->sum('cantidad');
            $suma_importe = $coleccion_aux->where('producto_id', $producto->id)->sum('importe');
            $coleccion->push([
                'codigo' => $producto->codigo,
                'producto' => $producto->nombre,
                'cantidad' => $suma_vendidos,
                'importe' => $suma_importe,
            ]);
        }

        return response()->json([
            'success' => true,
            'ventas' => $coleccion->orderBy('cantidad')->take($top),
        ]);
    }
}
