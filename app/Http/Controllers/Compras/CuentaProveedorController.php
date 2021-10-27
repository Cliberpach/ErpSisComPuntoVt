<?php

namespace App\Http\Controllers\Compras;

use App\Compras\CuentaProveedor;
use App\Compras\DetalleCuentaProveedor;
use App\Compras\Proveedor;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Empresa\Empresa;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class CuentaProveedorController extends Controller
{
    public function index() {
        $this->authorize('haveaccess','cuenta_proveedor.index');
        return view('compras.cuentaProveedor.index');
    }
    public function getTable() {
        $this->authorize('haveaccess','cuenta_proveedor.index');
        $datos=array();
        $cuentaProveedor=CuentaProveedor::get();
        foreach ($cuentaProveedor as $key => $value) {
            array_push($datos,array(
                "id"=>$value->id,
                "proveedor"=>$value->documento->proveedor->descripcion,
                "numero_doc"=>$value->documento->numero_doc,
                "fecha_doc"=>strval($value->documento->created_at) ,
                "monto"=>$value->documento->total,
                "acta"=>$value->acta,
                "saldo"=>$value->saldo,
                "estado"=>$value->estado
            ));
        }
        return DataTables::of($datos)->toJson();
    }
    public function getDatos(Request $request) 
    {
        $this->authorize('haveaccess','cuenta_proveedor.index');
        $cuenta=CuentaProveedor::findOrFail($request->id);
        return array(
            "id"=>$cuenta->id,
            "proveedor"=>$cuenta->documento->proveedor->descripcion,
            "numero"=>$cuenta->documento->numero_doc,
            "fecha"=>strval($cuenta->documento->created_at) ,
            "monto"=>$cuenta->documento->total,
            "acta"=>$cuenta->acta,
            "saldo"=>$cuenta->saldo,
            "estado"=>$cuenta->estado,
            "detalle"=>CuentaProveedor::findOrFail($request->id)->detallePago
        );
    }

    public function consulta(Request $request)
    {
        $this->authorize('haveaccess','cuenta_proveedor.index');
        $cuentas = DB::table('cuenta_proveedor')
        ->join('compra_documentos', 'compra_documentos.id', '=', 'cuenta_proveedor.compra_documento_id')
        ->join('proveedores', 'proveedores.id', '=', 'compra_documentos.proveedor_id')
        ->when($request->get('proveedor'), function ($query, $request) {
            return $query->where('proveedores.id', $request);
        })
        ->when($request->get('estado'), function ($query, $request) {
            return $query->where('cuenta_proveedor.estado',$request);
        })
        ->select(
            'cuenta_proveedor.*',
            'proveedores.descripcion as proveedor',
            'compra_documentos.numero_doc as numero_doc',
            'compra_documentos.created_at as fecha_doc',
            'compra_documentos.total as monto'
        )->get();
            return $cuentas;
    }
    public function detallePago(Request $request)
    {
        $this->authorize('haveaccess','cuenta_proveedor.index');
        $cuentaProveedor=CuentaProveedor::findOrFail($request->id);
        if($request->pago=="A CUENTA")
        {
            $detallepago=new DetalleCuentaProveedor();
            $detallepago->cuenta_proveedor_id=$cuentaProveedor->id;
            $detallepago->mcaja_id= movimientoUser()->id;
            $detallepago->observacion=$request->observacion;
            $detallepago->fecha=$request->fecha;
            $detallepago->importe=$request->importe_venta;
            $detallepago->efectivo=$request->efectivo_venta;
            $detallepago->tipo_pago_id=$request->modo_pago;
            $detallepago->save();
            $cant=$request->efectivo_venta+$request->importe_venta;
            $cuentaProveedor->saldo=$cuentaProveedor->saldo-$cant;
            $cuentaProveedor->save();
            $detallepago->saldo =$cuentaProveedor->saldo;
            $detallepago->update();

            if($request->hasFile('file')){
                $detallepago->ruta_imagen = $request->file('file')->store('public/cuenta/proveedor');
                $detallepago->update();
            }
            if($cuentaProveedor->saldo==0)
            {
                $cuentaProveedor->estado='PAGADO';
                $cuentaProveedor->save();
            }
        }
        else{
            $proveedor=$cuentaProveedor->documento->proveedor;
            $cuentasFaltantes=CuentaProveedor::where('estado','PENDIENTE')->get();
            $cantidadRecibidaEfectivo=$request->efectivo_venta;
            $cantidadRecibidaImporte=$request->importe_venta;
            foreach ($cuentasFaltantes as $key => $cuenta) {
                    if($cuenta->documento->proveedor->id==$proveedor->id && ($cantidadRecibidaEfectivo!=0 || $cantidadRecibidaImporte!=0))
                    {
                        $cantidadTotal=$cantidadRecibidaEfectivo+$cantidadRecibidaImporte;
                        $detallepago=new DetalleCuentaProveedor();
                        $detallepago->cuenta_proveedor_id=$cuenta->id;
                        $detallepago->mcaja_id= movimientoUser()->id;
                        $detallepago->observacion=$request->observacion;
                        $detallepago->fecha=$request->fecha;

                        $detallepago->tipo_pago_id=$request->modo_pago;
                        if($cuenta->saldo > $cantidadTotal)
                        {
                            if($cantidadRecibidaEfectivo==0)
                            {
                                $detallepago->efectivo=0;
                                $detallepago->importe=$cantidadRecibidaImporte;
                                $cuenta->saldo=$cuenta->saldo-$cantidadRecibidaImporte;
                                $cantidadRecibidaImporte=0;
                            }
                            else
                            {
                                $cuenta->saldo=$cuenta->saldo-$cantidadRecibidaEfectivo;
                                $detallepago->efectivo=$cantidadRecibidaEfectivo;
                                $cuenta->saldo=$cuenta->saldo-$cantidadRecibidaImporte;
                                $detallepago->importe=$cantidadRecibidaImporte;
                                $cantidadRecibidaEfectivo=0;
                                $cantidadRecibidaImporte=0;
                            }
                        }
                        else
                        {
                            if($cantidadRecibidaEfectivo==0)
                            {   $detallepago->efectivo=0;
                                $detallepago->importe=$cuenta->saldo;
                                $cantidadRecibidaImporte=$cantidadRecibidaImporte-$cuenta->saldo;

                            }
                            else
                            {
                                if($cuenta->saldo>$cantidadRecibidaEfectivo)
                                {

                                    $detallepago->efectivo=$cantidadRecibidaEfectivo;
                                    $detallepago->importe=$cuenta->saldo-$detallepago->efectivo;
                                    $cantidadRecibidaEfectivo=0;
                                    $cantidadRecibidaImporte=$cantidadRecibidaImporte- $detallepago->importe;
                                }
                                else{
                                    $detallepago->efectivo=$cantidadRecibidaEfectivo;
                                    $detallepago->importe=0;
                                    $cantidadRecibidaEfectivo=$cantidadRecibidaEfectivo-$cuenta->saldo;
                                }
                            }
                            $cuenta->saldo=0;
                        }
                        $detallepago->save();
                        $cuenta->save();
                        if($request->hasFile('file')){
                            $detallepago->ruta_imagen = $request->file('file')->store('public/cuenta/proveedor');
                            $detallepago->update();
                        }
                        if($cuenta->saldo==0)
                        {
                            $cuenta->estado='PAGADO';
                            $cuenta->save();
                        }
                    }
            }

        }
    }

    public function reporte($id)
    {
        $this->authorize('haveaccess','cuenta_proveedor.index');
        $cuenta = CuentaProveedor::findOrFail($id);
        $proveedor = $cuenta->documento->proveedor;
        $empresa = Empresa::first();
        $pdf = PDF::loadview('ventas.documentos.impresion.detalle_cuenta_proveedor',[
            'cuenta' => $cuenta,
            'detalles' => $cuenta->detallePago,
            'proveedor' => $proveedor,
            'empresa' => $empresa
            ])->setPaper('a4');
        return $pdf->stream('CUENTA-'.$cuenta->id.'.pdf');
    }
    public function imagen($id)
    {
        $this->authorize('haveaccess','cuenta_proveedor.index');
        $detalle = DetalleCuentaProveedor::find($id);
        $ruta = storage_path().'/app/'.$detalle->ruta_imagen;
        return response()->download($ruta);
    }
}
