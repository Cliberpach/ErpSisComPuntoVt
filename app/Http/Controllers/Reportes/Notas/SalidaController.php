<?php

namespace App\Http\Controllers\Reportes\Notas;

use App\Almacenes\Producto;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Tabla\General;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalidaController extends Controller
{
    public function index()
    {
        $origenes =  General::find(28)->detalles;
        $destinos =  General::find(29)->detalles;
        $productos = Producto::where('estado', 'ACTIVO')->get();
        return view('reportes.notas.salida',[
            "origenes" => $origenes, 'destinos' => $destinos,
            'productos' => $productos
        ]);
    }

    public function getTable(Request $request)
    {
        $producto = $request->producto_id;
        $destino = $request->destino;
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        return datatables()->query(
            DB::table('productos')
            ->join('detalle_nota_salidad','productos.id','=','detalle_nota_salidad.producto_id')
            ->join('nota_salidad','nota_salidad.id','=','detalle_nota_salidad.nota_salidad_id')
            ->select(
                'productos.id',
                'productos.nombre',
                'detalle_nota_salidad.cantidad',
                'nota_salidad.destino',
                DB::raw('DATE_FORMAT(nota_salidad.created_at, "%Y-%m-%d") as fecha')
            )
            ->where('productos.id',$producto)
            ->where('nota_salidad.destino',$destino)
            ->whereBetween(DB::raw('DATE_FORMAT(nota_salidad.created_at, "%Y-%m-%d")'),[$fecha_ini,$fecha_fin])
        )->toJson();
    }
}
