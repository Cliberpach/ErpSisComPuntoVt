<?php

namespace App\Http\Controllers\Reportes\Notas;

use App\Almacenes\Producto;
use App\Exports\Reportes\Notas\IngresoExport;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Tabla\General;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class IngresoController extends Controller
{
    public function index()
    {
        $origenes =  General::find(28)->detalles;
        $destinos =  General::find(29)->detalles;
        $productos = Producto::where('estado', 'ACTIVO')->get();
        return view('reportes.notas.ingreso',[
            "origenes" => $origenes, 'destinos' => $destinos,
            'productos' => $productos
        ]);
    }

    public function getTable(Request $request)
    {
        $producto = $request->producto_id;
        $origen = $request->origen;
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        return datatables()->query(
            DB::table('productos')
            ->join('detalle_nota_ingreso','productos.id','=','detalle_nota_ingreso.producto_id')
            ->join('nota_ingreso','nota_ingreso.id','=','detalle_nota_ingreso.nota_ingreso_id')
            ->select(
                'productos.id',
                'productos.nombre',
                'detalle_nota_ingreso.cantidad',
                'nota_ingreso.origen',
                DB::raw('DATE_FORMAT(nota_ingreso.created_at, "%Y-%m-%d") as fecha')
            )
            ->where('productos.id',$producto)
            ->where('nota_ingreso.origen',$origen)
            ->whereBetween(DB::raw('DATE_FORMAT(nota_ingreso.created_at, "%Y-%m-%d")'),[$fecha_ini,$fecha_fin])
        )->toJson();
    }

    public function getExcel(Request $request)
    {
        ob_end_clean();
        ob_start();
        $producto = $request->producto_id;
        $origen = $request->origen;
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        return  Excel::download(new IngresoExport($producto,$origen,$fecha_ini,$fecha_fin), 'INGRESOS '.$fecha_ini.'-'.$fecha_fin.'.xlsx');
    }
}
