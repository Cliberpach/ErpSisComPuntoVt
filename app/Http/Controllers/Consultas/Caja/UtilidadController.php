<?php

namespace App\Http\Controllers\Consultas\Caja;

use App\Http\Controllers\Controller;
use App\Ventas\Documento\Documento;
use Exception;
use Illuminate\Http\Request;

class UtilidadController extends Controller
{
    public function index()
    {
        return view('consultas.caja.utilidad');
    }

    public function getTable(Request $request){

        try
        {
            if($request->fecha_desde && $request->fecha_hasta)
            {
                $ventas = Documento::where('estado','!=','ANULADO')->whereBetween('fecha_documento' , [$request->fecha_desde, $request->fecha_hasta])->orderBy('id', 'desc')->get();
            }
            else
            {
                $ventas = Documento::where('estado','!=','ANULADO')->orderBy('id', 'desc')->get();
            }

            $coleccion = collect();

            foreach ($ventas as $key => $value) {
                foreach($value->detalles as $detalle)
                {
                    $precom = $detalle->lote->detalle_compra ? ($detalle->lote->detalle_compra->precio + ($detalle->lote->detalle_compra->costo_flete / $detalle->lote->detalle_compra->cantidad)) : 0.00;
                    $coleccion->push([
                        "fecha_doc" => $value->fecha_documento,
                        "cantidad" => $detalle->cantidad,
                        "producto" => $detalle->lote->producto->nombre,
                        "precio_venta" => $detalle->precio_nuevo,
                        "precio_compra" => $precom,
                        "utilidad" => $detalle->precio_nuevo - $precom,
                        "importe" => ($detalle->precio_nuevo - $precom) * $detalle->cantidad
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'ventas' => $coleccion
            ]);
        }
        catch(Exception $e)
        {
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage()
            ]);
        }
    }
}
