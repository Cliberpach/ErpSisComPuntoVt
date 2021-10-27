<?php

namespace App\Http\Controllers\Consultas\Kardex;

use App\Almacenes\Kardex;
use App\Http\Controllers\Controller;
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
}
