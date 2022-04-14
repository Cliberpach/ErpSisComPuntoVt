<?php

namespace App\Http\Controllers\Consultas;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UtilidadController extends Controller
{
    public function index()
    {
        $this->authorize('haveaccess', 'utilidad_mensual.index');

        $lstAnios = DB::table('lote_productos')->select(DB::raw('year(created_at) as value'))->distinct()->orderBy('value', 'desc')->get();

        $fecha_hoy = Carbon::now();
        $mes = date_format($fecha_hoy, 'm');
        $anio = date_format($fecha_hoy, 'Y');

        return view('consultas.utilidad.index', [
            'lstAnios' => $lstAnios,
            'mes' => $mes,
            'anio_' => $anio,
        ]);
    }

    public function getDatos($mes, $anio)
    {
        $fecini = $anio . '-' . $mes . '-01';
        $fecini = date('Y-m-d', strtotime($fecini));
        $fecfin = date('Y-m-d', strtotime($fecini . "+ 1 month"));
        $utilidad_ventas =  DB::table('cotizacion_documento_detalles')
            ->join('cotizacion_documento', 'cotizacion_documento.id', '=', 'cotizacion_documento_detalles.documento_id')
            ->select(
                DB::raw('cast(cotizacion_documento_detalles.cantidad as decimal(15,4)) * (cast(cotizacion_documento_detalles.precio_nuevo as decimal(15,4)) - ifnull((select dni.costo_soles from lote_productos lp join detalle_nota_ingreso dni on lp.id = dni.lote_id where lp.id = cotizacion_documento_detalles.lote_id), (select (cdd.precio_soles + cdd.costo_flete_soles) from lote_productos lp_ join compra_documento_detalles cdd on lp_.id = cdd.lote_id where lp_.id = cotizacion_documento_detalles.lote_id))) as utilidad'),
            )
            ->where('cotizacion_documento.estado', '!=', 'ANULADO')
            ->where('cotizacion_documento_detalles.eliminado', '0')
            ->whereMonth('cotizacion_documento.fecha_documento', $mes)
            ->whereYear('cotizacion_documento.fecha_documento', $anio)
            ->get();

        $resta_utilidad_devoluciones = DB::table('nota_electronica_detalle')
            ->join('nota_electronica', 'nota_electronica.id', '=', 'nota_electronica_detalle.nota_id')
            ->join('cotizacion_documento_detalles', 'cotizacion_documento_detalles.id', '=', 'nota_electronica_detalle.detalle_id')
            ->select(
                DB::raw('cast(nota_electronica_detalle.cantidad as decimal(15,4)) * (cast(nota_electronica_detalle.mtoPrecioUnitario as decimal(15,4)) - ifnull((select dni.costo_soles from lote_productos lp join detalle_nota_ingreso dni on lp.id = dni.lote_id where lp.id = cotizacion_documento_detalles.lote_id), (select (cdd.precio_soles + cdd.costo_flete_soles) from lote_productos lp_ join compra_documento_detalles cdd on lp_.id = cdd.lote_id where lp_.id = cotizacion_documento_detalles.lote_id))) as utilidad')
            )
            ->where('nota_electronica.estado', '!=', 'ANULADO')
            ->whereMonth('nota_electronica.fechaEmision', $mes)
            ->whereYear('nota_electronica.fechaEmision', $anio)
            ->get();

        $resta_utilidad_ventas_convertidas =  DB::table('cotizacion_documento_detalles')
            ->join('cotizacion_documento', 'cotizacion_documento.id', '=', 'cotizacion_documento_detalles.documento_id')
            ->select(
                DB::raw('cast(cotizacion_documento_detalles.cantidad as decimal(15,4)) * (cast(cotizacion_documento_detalles.precio_nuevo as decimal(15,4)) - ifnull((select dni.costo_soles from lote_productos lp join detalle_nota_ingreso dni on lp.id = dni.lote_id where lp.id = cotizacion_documento_detalles.lote_id), (select (cdd.precio_soles + cdd.costo_flete_soles) from lote_productos lp_ join compra_documento_detalles cdd on lp_.id = cdd.lote_id where lp_.id = cotizacion_documento_detalles.lote_id))) as utilidad')
            )
            ->where('cotizacion_documento.estado', '!=', 'ANULADO')
            ->where('cotizacion_documento.tipo_venta', '129')
            ->where('cotizacion_documento.convertir', '!=', '')
            ->where('cotizacion_documento_detalles.eliminado', '0')
            ->whereMonth('cotizacion_documento.fecha_documento', $mes)
            ->whereYear('cotizacion_documento.fecha_documento', $anio)
            ->get();

        //----------------------------

        $inversion_compleja =  DB::table('productos')
            ->select(
                DB::raw("(
                    (
                        ifnull((SELECT sum(ddc.cantidad * ddc.precio_soles) from compra_documento_detalles ddc INNER JOIN compra_documentos dc ON ddc.documento_id = dc.id WHERE dc.fecha_emision < '{$fecini}' AND ddc.producto_id = productos.id AND dc.estado != 'ANULADO'),0) +
                        ifnull((SELECT sum(dni.cantidad * dni.costo_soles) from detalle_nota_ingreso dni INNER JOIN nota_ingreso ni ON dni.nota_ingreso_id = ni.id WHERE ni.fecha < '{$fecini}' AND dni.producto_id = productos.id AND ni.estado != 'ANULADO'),0) -
                        ifnull((SELECT SUM(ddv.cantidad * ddv.precio_nuevo) FROM cotizacion_documento_detalles ddv INNER JOIN cotizacion_documento dv ON ddv.documento_id = dv.id INNER JOIN lote_productos lp ON ddv.lote_id = lp.id WHERE dv.fecha_documento < '{$fecini}' AND dv.estado != 'ANULADO' AND lp.producto_id = productos.id AND ddv.eliminado = '0'),0) -
                        ifnull((SELECT SUM(ddv.cantidad * ddv.precio_nuevo) FROM cotizacion_documento_detalles ddv INNER JOIN cotizacion_documento dv ON ddv.documento_id = dv.id INNER JOIN lote_productos lp ON ddv.lote_id = lp.id WHERE dv.fecha_documento < '{$fecini}' AND dv.estado != 'ANULADO' AND lp.producto_id = productos.id AND dv.tipo_venta != '129' AND dv.convertir != '' AND ddv.eliminado = '0'),0) -
                        ifnull((SELECT SUM(dns.cantidad * productos.precio_compra) FROM detalle_nota_salidad dns INNER JOIN nota_salidad ns ON dns.nota_salidad_id = ns.id WHERE ns.fecha < '{$fecini}' AND ns.estado != 'ANULADO' AND dns.producto_id = productos.id),0) +
                        ifnull((SELECT SUM(ned.cantidad * productos.precio_compra) FROM nota_electronica_detalle ned INNER JOIN nota_electronica ne ON ned.nota_id = ne.id INNER JOIN cotizacion_documento_detalles cdd ON cdd.id = ned.detalle_id INNER JOIN lote_productos lpn ON lpn.id = cdd.lote_id WHERE ne.fechaEmision < '{$fecini}' AND ne.estado != 'ANULADO' AND lpn.producto_id = productos.id),0)
                    ) -
                    (
                        ifnull((SELECT SUM(vdd.cantidad * vdd.precio_nuevo) from cotizacion_documento_detalles vdd INNER JOIN cotizacion_documento vd ON vdd.documento_id = vd.id INNER JOIN lote_productos lp ON vdd.lote_id = lp.id WHERE vd.fecha_documento >= '{$fecini}' and vd.fecha_documento <= '{$fecfin}' AND vd.estado != 'ANULADO' AND lp.producto_id = productos.id AND vdd.eliminado = '0'),0) -
                        ifnull((SELECT SUM(vdd.cantidad * vdd.precio_nuevo) from cotizacion_documento_detalles vdd INNER JOIN cotizacion_documento vd ON vdd.documento_id = vd.id INNER JOIN lote_productos lp ON vdd.lote_id = lp.id WHERE vd.fecha_documento >= '{$fecini}' and vd.fecha_documento <= '{$fecfin}' AND vd.estado != 'ANULADO' AND lp.producto_id = productos.id AND vd.tipo_venta != '129' AND vd.convertir != '' AND vdd.eliminado = '0'),0) +
                        ifnull((SELECT SUM(dns.cantidad * productos.precio_compra) FROM detalle_nota_salidad dns INNER JOIN nota_salidad ns ON dns.nota_salidad_id = ns.id WHERE ns.fecha >= '{$fecini}' AND ns.fecha <= '{$fecfin}' AND ns.estado != 'ANULADO' AND dns.producto_id = productos.id),0)
                    ) +
                    ifnull((SELECT SUM(cdd.cantidad * cdd.precio_soles) from compra_documento_detalles cdd INNER JOIN compra_documentos cd ON cdd.documento_id = cd.id WHERE cd.fecha_emision >= '{$fecini}' AND cd.fecha_emision <= '{$fecfin}' AND cd.estado != 'ANULADO' AND cdd.producto_id = productos.id),0) +
                    ifnull((SELECT sum(dni.cantidad * dni.costo_soles) from detalle_nota_ingreso dni INNER JOIN nota_ingreso ni ON dni.nota_ingreso_id = ni.id WHERE ni.fecha >= '{$fecini}' AND ni.fecha <= '{$fecfin}' AND dni.producto_id = productos.id AND ni.estado != 'ANULADO'),0) +
                    ifnull((SELECT SUM(ned.cantidad * productos.precio_compra) FROM nota_electronica_detalle ned INNER JOIN nota_electronica ne ON ned.nota_id = ne.id INNER JOIN cotizacion_documento_detalles cdd ON cdd.id = ned.detalle_id INNER JOIN lote_productos lpn ON lpn.id = cdd.lote_id WHERE ne.fechaEmision >= '{$fecini}' AND ne.fechaEmision <= '{$fecfin}' AND  ne.estado != 'ANULADO' AND lpn.producto_id = productos.id),0)
                ) as inversion")
            )->get();

        $inversion_mensual = $inversion_compleja->where('inversion', '>', 0)->sum('inversion');
        $ventas_mensual = ventas_mensual_random($mes, $anio);
        $utilidad_mensual = $utilidad_ventas->sum('utilidad') - $resta_utilidad_devoluciones->sum('utilidad') - $resta_utilidad_ventas_convertidas->sum('utilidad');
        // return utilidad_mensual_random($mes, $anio);
        $porcentaje = 0;
        if ($ventas_mensual > 0) {
            $porcentaje = ($utilidad_mensual * 100) / $ventas_mensual;
        }

        $dolar_aux = json_encode(precio_dolar(), true);
        $dolar_aux = json_decode($dolar_aux, true);

        $dolar = (float)$dolar_aux['original']['venta'];

        $inversion_mensual_dolares = $inversion_mensual /  $dolar;
        $ventas_mensual_dolares = $ventas_mensual / $dolar;
        $utilidad_mensual_dolares = $utilidad_mensual / $dolar;

        return response()->json([
            'inversion_mensual' => $inversion_mensual,
            'ventas_mensual' => $ventas_mensual,
            'utilidad_mensual' => $utilidad_mensual,
            'inversion_mensual_dolares' => $inversion_mensual_dolares,
            'ventas_mensual_dolares' => $ventas_mensual_dolares,
            'utilidad_mensual_dolares' => $utilidad_mensual_dolares,
            'porcentaje' => $porcentaje,
        ]);
    }
}
