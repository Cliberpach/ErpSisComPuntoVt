<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Mantenimiento\Empresa\Empresa;
use App\Ventas\Cliente;
use App\Ventas\CuentaCliente;
use App\Ventas\DetalleCuentaCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Session;

class CuentaClienteController extends Controller
{
    public function index() {
        $fecha_hoy = Carbon::now()->toDateString();
        return view('ventas.cuentaCliente.index',compact('fecha_hoy'));
    }

    public function getTable() {
        $datos = array();
        $cuentaCliente = CuentaCliente::all();
        foreach ($cuentaCliente as $key => $value) {
            array_push($datos,array(
                "id"=>$value->id,
                "cliente"=>$value->documento->clienteEntidad->nombre,
                "numero_doc"=>$value->documento->numero_doc,
                "fecha_doc"=>$value->fecha_doc,
                "monto"=>$value->monto,
                "acta"=>$value->acta,
                "saldo"=>$value->saldo,
                "estado"=>$value->estado
            ));
        }
        return DataTables::of($datos)->toJson();
    }

    public function getDatos(Request $request) {

        $cuenta = CuentaCliente::findOrFail($request->id);
        return array(
            "id"=>$cuenta->id,
            "cliente"=>$cuenta->documento->clienteEntidad->nombre,
            "numero"=>$cuenta->documento->numero_doc,
            "fecha"=>$cuenta->fecha_doc,
            "monto"=>$cuenta->documento->total,
            "acta"=>$cuenta->acta,
            "saldo"=>$cuenta->saldo,
            "estado"=>$cuenta->estado,
            "detalle"=> $cuenta->detalles
        );
    }

    public function consulta(Request $request)
    {
        $cuentas = DB::table('cuenta_cliente')
        ->join('cotizacion_documento', 'cotizacion_documento.id', '=', 'cuenta_cliente.cotizacion_documento_id')
        ->join('clientes', 'clientes.id', '=', 'cotizacion_documento.cliente_id')
        ->when($request->get('cliente'), function ($query, $request) {
            return $query->where('clientes.id', $request);
        })
        ->when($request->get('estado'), function ($query, $request) {
            return $query->where('cuenta_cliente.estado',$request);
        })
        ->select(
            'cuenta_cliente.*',
            'clientes.nombre as cliente',
            'cotizacion_documento.numero_doc as numero_doc',
            'cotizacion_documento.created_at as fecha_doc',
            'cotizacion_documento.total as monto'
        )->get();
            return $cuentas;
    }

    public function detallePago(Request $request, $id)
    {
        DB::beginTransaction();
            $CuentaCliente = CuentaCliente::findOrFail($id);
            if($request->pago == "A CUENTA")
            {
                $detallepago = new DetalleCuentaCliente();
                $detallepago->cuenta_cliente_id = $CuentaCliente->id;
                $detallepago->mcaja_id = movimientoUser()->id;
                $detallepago->monto = $request->cantidad;
                $detallepago->importe=$request->importe_venta;
                $detallepago->efectivo=$request->efectivo_venta;
                $detallepago->tipo_pago_id=$request->modo_pago;
                $detallepago->observacion = $request->observacion;
                $detallepago->fecha = $request->fecha;
                $detallepago->save();

                $CuentaCliente->saldo = $CuentaCliente->saldo - $request->cantidad;
                $CuentaCliente->update();

                $detallepago->saldo = $CuentaCliente->saldo;
                $detallepago->update();

                if($request->hasFile('imagen')){
                    $detallepago->ruta_imagen = $request->file('imagen')->store('public/cuenta/cobrar');
                    $detallepago->update();
                }

                if($CuentaCliente->saldo == 0)
                {
                    $CuentaCliente->estado='PAGADO';
                    $CuentaCliente->save();
                }
            }
            else{
                $cliente = $CuentaCliente->documento->clienteEntidad;
                $cuentasFaltantes = CuentaCliente::where('estado','PENDIENTE')->get();
                $cantidadRecibida = $request->cantidad;
                $cantidadRecibidaEfectivo=$request->efectivo_venta;
                $cantidadRecibidaImporte=$request->importe_venta;
                foreach ($cuentasFaltantes as $key => $cuenta) {
                    if($cliente->id == $cliente->id && $cantidadRecibida != 0)
                    {
                        $detallepago = new DetalleCuentaCliente();
                        $detallepago->mcaja_id = movimientoUser()->id;
                        $detallepago->cuenta_cliente_id = $cuenta->id;
                        $detallepago->monto = 0;
                        $detallepago->observacion=$request->observacion;
                        $detallepago->fecha = $request->fecha;
                        $detallepago->tipo_pago_id=$request->modo_pago;
                        $detallepago->save();
                        if($cuenta->saldo > $cantidadRecibida)
                        {
                            // $detallepago->monto = $cantidadRecibida;
                            // $cuenta->saldo = $cuenta->saldo - $cantidadRecibida;
                            // $cantidadRecibida = 0;
                            if($cantidadRecibidaEfectivo == 0)
                            {
                                $detallepago->efectivo = 0;
                                $detallepago->importe = $cantidadRecibidaImporte;
                                $detallepago->monto = $cantidadRecibida;
                                $cuenta->saldo = $cuenta->saldo - $cantidadRecibida;
                                $cantidadRecibidaImporte = 0;
                                
                                $cantidadRecibida = $cantidadRecibidaEfectivo + $cantidadRecibidaImporte;
                            }
                            else
                            {
                                $detallepago->efectivo = $cantidadRecibidaEfectivo;
                                $detallepago->importe = $cantidadRecibidaImporte;
                                $detallepago->monto = $cantidadRecibida;
                                $cuenta->saldo = $cuenta->saldo - $cantidadRecibida;
                                $cantidadRecibidaEfectivo = 0;
                                $cantidadRecibidaImporte = 0;
                                
                                $cantidadRecibida = $cantidadRecibidaEfectivo + $cantidadRecibidaImporte;
                            }
                        }
                        else{
                            if($cantidadRecibidaEfectivo == 0)
                            {   
                                $importe = 0;
                                if($cantidadRecibidaImporte > $cuenta->saldo)
                                {
                                    $importe = $cantidadRecibidaImporte - $cuenta->saldo;
                                }else
                                {
                                    $importe = $cantidadRecibidaImporte;
                                }

                                $detallepago->efectivo  = $cantidadRecibidaEfectivo;
                                $detallepago->importe = $cuenta->saldo;
                                $detallepago->monto = $cuenta->saldo;
                                $cantidadRecibidaImporte = $importe;
                                $cantidadRecibida = $cantidadRecibidaEfectivo + $cantidadRecibidaImporte;
                                $cuenta->saldo = 0;
                            }
                            else
                            {
                                if($cantidadRecibidaImporte == 0)
                                {
                                    $efectivo = 0;
                                    if($cantidadRecibidaEfectivo > $cuenta->saldo)
                                    {
                                        $efectivo = $cantidadRecibidaEfectivo - $cuenta->saldo;
                                    }else
                                    {
                                        $efectivo = $cantidadRecibidaEfectivo;
                                    }
                                    $detallepago->efectivo  = $efectivo;
                                    $detallepago->importe = $cantidadRecibidaImporte;
                                    $detallepago->monto = $cuenta->saldo;
                                    $cantidadRecibidaEfectivo = $cantidadRecibidaEfectivo - $efectivo;
                                    
                                    $cantidadRecibida = $cantidadRecibidaEfectivo + $cantidadRecibidaImporte;
                                }
                                else
                                {
                                    $detallepago->efectivo = $cantidadRecibidaEfectivo;
                                    $detallepago->importe = $cuenta->saldo - $cantidadRecibidaEfectivo;
                                    $detallepago->monto = $detallepago->efectivo + $detallepago->importe;
                                    $cantidadRecibidaImporte =  $cantidadRecibidaImporte - ($cuenta->saldo - $cantidadRecibidaEfectivo);                                    
                                    $cantidadRecibidaEfectivo = 0;
                                    $cantidadRecibida = $cantidadRecibidaEfectivo + $cantidadRecibidaImporte;
                                }
                            }
                            $cuenta->saldo = 0;
                        }

                        $detallepago->update();
                        if($request->hasFile('imagen')){
                            $detallepago->ruta_imagen = $request->file('imagen')->store('public/cuenta/cobrar');
                            $detallepago->update();
                        }

                        $cuenta->update();

                        if($cuenta->saldo == 0)
                        {
                            $cuenta->estado='PAGADO';
                            $cuenta->update();
                        }
                    }
                }

            }

            DB::commit();
            Session::flash('success', 'Pago agregado correctamene');
            return redirect()->route('cuentaCliente.index');
    }

    public function reporte($id)
    {
        $cuenta = CuentaCliente::findOrFail($id);
        $cliente = Cliente::find($cuenta->documento->cliente_id);
        $empresa = Empresa::first();
        $pdf = PDF::loadview('ventas.documentos.impresion.detalle_cuenta',[
            'cuenta' => $cuenta,
            'detalles' => $cuenta->detalles,
            'cliente' => $cliente,
            'empresa' => $empresa
            ])->setPaper('a4');
        return $pdf->stream('CUENTA-'.$cuenta->id.'.pdf');
    }

    public function imagen($id)
    {
        $detalle = DetalleCuentaCliente::find($id);
        $ruta = storage_path().'/app/'.$detalle->ruta_imagen;
        return response()->download($ruta);
    }
}
