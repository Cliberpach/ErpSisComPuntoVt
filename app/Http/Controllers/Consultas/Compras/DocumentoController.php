<?php

namespace App\Http\Controllers\Consultas\Compras;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Compras\Documento\Detalle;
use App\Compras\Documento\Documento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpParser\Comment\Doc;

class DocumentoController extends Controller
{
    public function index()
    {
        return view('consultas.compras.documentos.index');
    }

    public function getTable(Request $request){

        if($request->fecha_desde && $request->fecha_hasta)
        {
            $documentos = Documento::where('estado','!=','ANULADO')->whereBetween('fecha_emision', [$request->fecha_desde, $request->fecha_hasta])->orderBy('id', 'desc')->get();
        }
        else
        {
            $documentos = Documento::where('estado','!=','ANULADO')->orderBy('id', 'desc')->get();
        }
        

        
        $coleccion = collect();
        foreach($documentos as $documento){
            $detalles = Detalle::where('documento_id',$documento->id)->get();
            $documento = Documento::findOrFail($documento->id);
            $subtotal = 0;
            $igv = '';
            $tipo_moneda = '';
            foreach($detalles as $detalle){
                $subtotal = ($detalle->cantidad * $detalle->precio) + $subtotal;
            }
            foreach(tipos_moneda() as $moneda){
                if ($moneda->descripcion == $documento->moneda) {
                    $tipo_moneda= $moneda->simbolo;
                }
            }
            if (!$documento->igv) {
                $igv = $subtotal * 0.18;
                $total = $subtotal + $igv;
                $decimal_total = number_format($total, 2, '.', '');
            }else{
                $calcularIgv = $documento->igv/100;
                $base = $subtotal / (1 + $calcularIgv);
                $nuevo_igv = $subtotal - $base;
                $decimal_total = number_format($subtotal, 2, '.', '');
            }
            //TIPO DE PAGO (OTROS)
            //$otros = calcularMontosAcuentaDocumentos($documento->id);
            //Pagos a cuenta
            $pagos = DB::table('compra_documento_transferencia')
            ->join('compra_documentos','compra_documento_transferencia.documento_id','=','compra_documentos.id')
            ->select('compra_documento_transferencia.*','compra_documentos.moneda as moneda_orden')
            ->where('compra_documento_transferencia.documento_id','=',$documento->id)
            ->where('compra_documento_transferencia.estado','!=','ANULADO')
            ->get();
            // CALCULAR ACUENTA EN MONEDA
            $acuenta = 0;
            $soles = 0;
            foreach($pagos as $pago){
                $acuenta = $acuenta + $pago->monto;
                if ($pago->moneda_orden == "SOLES") {
                    $soles = $soles + $pago->monto;
                }else{
                    $soles = $soles + $pago->cambio;
                }
            }
            $saldo = 0;
            if ($documento->tipo_pago == '1') {
                $saldo = $decimal_total - $acuenta;
            }else{
                 $saldo = $decimal_total;
            }
            //CALCULAR SALDO
            // $saldo = $decimal_total - $acuenta;
            //CAMBIAR ESTADO DE LA ORDEN A PAGADA
            if ($saldo == 0.0) {
                $documento->estado = "PAGADA";
                $documento->update();
            }else{
                $documento->estado = "PENDIENTE";
                $documento->update();
            }
            $coleccion->push([
                'id' => $documento->id,
                'tipo' => $documento->tipo_compra,
                'tipo_pago' => $documento->tipo_pago,
                'proveedor' => $documento->proveedor->descripcion,
                'empresa' => $documento->empresa->razon_social,
                'fecha_emision' =>  Carbon::parse($documento->fecha_emision)->format( 'd/m/Y'),
                'igv' =>  $documento->igv,
                'orden_compra' =>  $documento->orden_compra,
                'subtotal' => $tipo_moneda.' '.number_format($subtotal, 2, '.', ''),
                // 'fecha_entrega' =>  Carbon::parse($documento->fecha_entrega)->format( 'd/m/Y'),
                'estado' => $documento->estado,
               // 'otros' => $tipo_moneda.' '.number_format($otros, 2, '.', ''),
                'otros' => $tipo_moneda,
                'saldo' => $tipo_moneda.' '.number_format($saldo, 2, '.', ''),
                'transferencia' => $tipo_moneda.' '.number_format($acuenta, 2, '.', ''),
                'total' => $tipo_moneda.' '.number_format($decimal_total, 2, '.', ''),
            ]);
        }

        return response()->json([
            'success' => true,
            'documentos' => $coleccion
        ]);
    }
}
