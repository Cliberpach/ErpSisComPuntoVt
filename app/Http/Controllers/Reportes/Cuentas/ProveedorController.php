<?php

namespace App\Http\Controllers\Reportes\Cuentas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    public function index()
    {
        return view('reportes.cuentas.proveedor');
    }

    public function getTable(Request $request)
    {
        $proveedor = $request->proveedor_id;
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        return datatables()->query(
            DB::table('cuenta_proveedor')
            ->join('compra_documentos','compra_documentos.id','=','cuenta_proveedor.compra_documento_id')
            ->join('proveedores','proveedores.id','=','compra_documentos.proveedor_id')
            ->select(
                'cuenta_proveedor.id',
                'cuenta_proveedor.saldo',
                'compra_documentos.total as monto',
                'compra_documentos.fecha_emision as fecha',
                'proveedores.descripcion as proveedor',
                'compra_documentos.modo_compra as modo_pago',
                'compra_documentos.numero_doc as documento',
                'compra_documentos.moneda',
                DB::raw('(compra_documentos.total - cuenta_proveedor.saldo) as acta'),
                DB::raw('ifnull((select fecha
                from detalle_cuenta_proveedor dcp
                where dcp.cuenta_proveedor_id = cuenta_proveedor.id
               order by id desc
               limit 1),"-") as fecha_ultima'),
            )
            ->where('compra_documentos.proveedor_id',$proveedor)
            ->whereBetween('cuenta_proveedor.fecha_doc',[$fecha_ini,$fecha_fin])
        )->toJson();
    }
}
