<?php

namespace App\Http\Controllers\Consultas\Caja;

use App\Http\Controllers\Controller;
use App\Ventas\Documento\Detalle;
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
                $ventas = Documento::where('estado','=','ACTIVO')->whereBetween('fecha_documento' , [$request->fecha_desde, $request->fecha_hasta])->orderBy('id', 'desc')->get();
            }
            else
            {
                $ventas = Documento::where('estado','=','ACTIVO')->orderBy('id', 'desc')->get();
            }

            $coleccion = collect();

            foreach ($ventas as $venta) {
                $detalles = Detalle::where('estado','ACTIVO')->where('documento_id',$venta->id)->get();
                foreach($detalles as $detalle)
                {
                    $precom = $detalle->lote->detalle_compra ? ($detalle->lote->detalle_compra->precio_soles + ($detalle->lote->detalle_compra->costo_flete_soles / $detalle->lote->detalle_compra->cantidad)) : $detalle->lote->detalle_nota->costo_soles;
                    $coleccion->push([
                        "fecha_doc" => $venta->fecha_documento,
                        "cantidad" => $detalle->cantidad,
                        "producto" => $detalle->lote->producto->nombre,
                        "precio_venta" => number_format($detalle->precio_nuevo,2),
                        "precio_compra" => number_format($precom,2),
                        "utilidad" => number_format(($detalle->precio_nuevo - $precom) * $detalle->cantidad,2),
                        "importe" => number_format(($detalle->precio_nuevo) * $detalle->cantidad,2)
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
