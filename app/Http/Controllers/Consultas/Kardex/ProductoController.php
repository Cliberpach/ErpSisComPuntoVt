<?php

namespace App\Http\Controllers\Consultas\Kardex;

use App\Almacenes\Kardex;
use App\Almacenes\Producto;
use App\Http\Controllers\Controller;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index()
    {
        return view('consultas.kardex.producto');
    }

    public function getTable(Request $request)
    {

        try {
            $fecini = $request->fecha_desde;
            $fecfin = $request->fecha_hasta;
            $kardex = DB::select("call Sp_Rpte_Stock_fecha(" . "'" . $fecini . "'" . "," . "'" . $fecfin . "'" . ")");

            /*$kardex = DB::table('productos')
            ->join('categorias','categorias.id','=','productos.categoria_id')
            ->select(
                'productos.id',
                'productos.nombre',
                'categorias.descripcion',

                DB::raw("(ifnull((SELECT sum(ddc.cantidad) from compra_documento_detalles ddc INNER JOIN compra_documentos dc ON ddc.documento_id = dc.id WHERE dc.fecha_emision < $fecini AND ddc.producto_id = productos.id AND dc.estado != 'ANULADO'),0) + ifnull((SELECT sum(dni.cantidad) from detalle_nota_ingreso dni INNER JOIN nota_ingreso ni ON dni.nota_ingreso_id = ni.id WHERE ni.fecha < $fecini AND dni.producto_id = productos.id AND ni.estado != 'ANULADO'),0) - ifnull((SELECT SUM(ddv.cantidad) FROM cotizacion_documento_detalles ddv INNER JOIN cotizacion_documento dv ON ddv.documento_id = dv.id INNER JOIN lote_productos lp ON ddv.lote_id = lp.id WHERE dv.fecha_documento < $fecini AND dv.estado != 'ANULADO' AND lp.producto_id = productos.id),0) - ifnull((SELECT SUM(dns.cantidad) FROM detalle_nota_salidad dns INNER JOIN nota_salidad ns ON dns.nota_salidad_id = ns.id WHERE ns.fecha < $fecini AND ns.estado != 'ANULADO' AND dns.producto_id = productos.id),0) + ifnull((SELECT SUM(ned.cantidad) FROM nota_electronica_detalle ned INNER JOIN nota_electronica ne ON ned.nota_id = ne.id INNER JOIN cotizacion_documento_detalles cdd ON cdd.id = ned.detalle_id INNER JOIN lote_productos lpn ON lpn.id = cdd.lote_id WHERE ne.fechaEmision < $fecini AND ne.estado != 'ANULADO' AND lpn.producto_id = productos.id),0)) as STOCKINI"),
                DB::raw("ifnull((SELECT SUM(cdd.cantidad) from compra_documento_detalles cdd INNER JOIN compra_documentos cd ON cdd.documento_id = cd.id WHERE cd.fecha_emision >= $fecini AND cd.fecha_emision <= $fecfin AND cd.estado != 'ANULADO' AND cdd.producto_id = productos.id),0) AS COMPRAS"),
                DB::raw("ifnull((SELECT sum(dni.cantidad) from detalle_nota_ingreso dni INNER JOIN nota_ingreso ni ON dni.nota_ingreso_id = ni.id WHERE ni.fecha >= $fecini AND ni.fecha <= $fecfin AND dni.producto_id = productos.id AND ni.estado != 'ANULADO'),0) AS INGRESOS"),
                DB::raw("ifnull((SELECT SUM(ned.cantidad) FROM nota_electronica_detalle ned INNER JOIN nota_electronica ne ON ned.nota_id = ne.id INNER JOIN cotizacion_documento_detalles cdd ON cdd.id = ned.detalle_id INNER JOIN lote_productos lpn ON lpn.id = cdd.lote_id WHERE ne.fechaEmision >= $fecini AND ne.fechaEmision <= $fecfin AND  ne.estado != 'ANULADO' AND lpn.producto_id = productos.id),0) as DEVOLUCIONES"),
                DB::raw("ifnull((SELECT SUM(vdd.cantidad) from cotizacion_documento_detalles vdd INNER JOIN cotizacion_documento vd ON vdd.documento_id = vd.id INNER JOIN lote_productos lp ON vdd.lote_id = lp.id WHERE vd.fecha_documento >= $fecini and vd.fecha_documento <= $fecfin AND vd.estado != 'ANULADO' AND lp.producto_id = productos.id),0) AS VENTAS"),
                DB::raw("ifnull((SELECT SUM(dns.cantidad) FROM detalle_nota_salidad dns INNER JOIN nota_salidad ns ON dns.nota_salidad_id = ns.id WHERE ns.fecha >= $fecini AND ns.fecha <= $fecfin AND ns.estado != 'ANULADO' AND dns.producto_id = productos.id),0) AS SALIDAS"),
                DB::raw("((ifnull((SELECT sum(ddc.cantidad) from compra_documento_detalles ddc INNER JOIN compra_documentos dc ON ddc.documento_id = dc.id WHERE dc.fecha_emision < $fecini AND ddc.producto_id = productos.id AND dc.estado != 'ANULADO'),0) + ifnull((SELECT sum(dni.cantidad) from detalle_nota_ingreso dni INNER JOIN nota_ingreso ni ON dni.nota_ingreso_id = ni.id WHERE ni.fecha < $fecini AND dni.producto_id = productos.id AND ni.estado != 'ANULADO'),0) - ifnull((SELECT SUM(ddv.cantidad) FROM cotizacion_documento_detalles ddv INNER JOIN cotizacion_documento dv ON ddv.documento_id = dv.id INNER JOIN lote_productos lp ON ddv.lote_id = lp.id WHERE dv.fecha_documento < $fecini AND dv.estado != 'ANULADO' AND lp.producto_id = productos.id),0) - ifnull((SELECT SUM(dns.cantidad) FROM detalle_nota_salidad dns INNER JOIN nota_salidad ns ON dns.nota_salidad_id = ns.id WHERE ns.fecha < $fecini AND ns.estado != 'ANULADO' AND dns.producto_id = productos.id),0) + ifnull((SELECT SUM(ned.cantidad) FROM nota_electronica_detalle ned INNER JOIN nota_electronica ne ON ned.nota_id = ne.id INNER JOIN cotizacion_documento_detalles cdd ON cdd.id = ned.detalle_id INNER JOIN lote_productos lpn ON lpn.id = cdd.lote_id WHERE ne.fechaEmision < $fecini AND ne.estado != 'ANULADO' AND lpn.producto_id = productos.id),0)) - (ifnull((SELECT SUM(vdd.cantidad) from cotizacion_documento_detalles vdd INNER JOIN cotizacion_documento vd ON vdd.documento_id = vd.id INNER JOIN lote_productos lp ON vdd.lote_id = lp.id WHERE vd.fecha_documento >= $fecini and vd.fecha_documento <= $fecfin AND vd.estado != 'ANULADO' AND lp.producto_id = productos.id),0) - ifnull((SELECT SUM(dns.cantidad) FROM detalle_nota_salidad dns INNER JOIN nota_salidad ns ON dns.nota_salidad_id = ns.id WHERE ns.fecha >= $fecini AND ns.fecha <= $fecfin AND ns.estado != 'ANULADO' AND dns.producto_id = productos.id),0)) + ifnull((SELECT SUM(cdd.cantidad) from compra_documento_detalles cdd INNER JOIN compra_documentos cd ON cdd.documento_id = cd.id WHERE cd.fecha_emision >= $fecini AND cd.fecha_emision <= $fecfin AND cd.estado != 'ANULADO' AND cdd.producto_id = productos.id),0) + ifnull((SELECT sum(dni.cantidad) from detalle_nota_ingreso dni INNER JOIN nota_ingreso ni ON dni.nota_ingreso_id = ni.id WHERE ni.fecha >= $fecini AND ni.fecha <= $fecfin AND dni.producto_id = productos.id AND ni.estado != 'ANULADO'),0) + ifnull((SELECT SUM(ned.cantidad) FROM nota_electronica_detalle ned INNER JOIN nota_electronica ne ON ned.nota_id = ne.id INNER JOIN cotizacion_documento_detalles cdd ON cdd.id = ned.detalle_id INNER JOIN lote_productos lpn ON lpn.id = cdd.lote_id WHERE ne.fechaEmision >= $fecini AND ne.fechaEmision <= $fecfin AND  ne.estado != 'ANULADO' AND lpn.producto_id = productos.id),0)) as STOCK"),
                DB::raw($fecini.' as fecini'),
                DB::raw("$fecfin as fecfin"),
            )->get();*/
            return response()->json([
                'success' => true,
                'kardex' => $kardex,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function index_top()
    {
        return view('consultas.kardex.producto_top');
    }

    public function getTableTop(Request $request)
    {
        $top = $request->top;

        $documentos = Documento::where('estado', '!=', 'ANULADO');
        if ($request->fecha_desde && $request->fecha_hasta) {
            $documentos = $documentos->whereBetween('fecha_documento', [$request->fecha_desde, $request->fecha_hasta]);
        }

        $documentos = $documentos->orderBy('id', 'desc')->get();



        $coleccion_aux = collect();
        $coleccion = collect();
        foreach ($documentos as $documento) {
            $detalles = Detalle::where('estado', 'ACTIVO')->where('documento_id', $documento->id)->get();
            foreach ($detalles as $detalle) {
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

        $productos = Producto::where('estado', 'ACTIVO')->get();

        foreach ($productos as $producto) {
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
        foreach ($coll as $coll_) {
            $arr_aux = array(
                'codigo' => $coll_['codigo'],
                'producto' => $coll_['producto'],
                'cantidad' => $coll_['cantidad'],
                'importe' => $coll_['importe']
            );
            array_push($arr, $arr_aux);
        }

        return response()->json([
            'success' => true,
            'kardex' => $arr,
            'top' => count($coll->all())
        ]);
    }
}
