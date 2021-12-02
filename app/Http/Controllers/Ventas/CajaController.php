<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Ventas\Documento\Documento;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CajaController extends Controller
{
    public function index()
    {
        $this->authorize('haveaccess','ventascaja.index');
        return view('ventas.caja.index');
    }

    public function getDocument(){

        $fecha_hoy = Carbon::now()->toDateString();
        $documentos = Documento::where('estado','!=','ANULADO')->whereBetween('fecha_documento', [$fecha_hoy, $fecha_hoy])->orderBy('id', 'desc')->get();
        $coleccion = collect([]);

        $hoy = Carbon::now();
        foreach($documentos as $documento){

            $transferencia = 0.00;
            $otros = 0.00;
            $efectivo = 0.00;

            if($documento->tipo_pago_id)
            {
                if ($documento->tipo_pago_id == 1) {
                    $efectivo = $documento->importe;
                }
                else if ($documento->tipo_pago_id == 2){
                    $transferencia = $documento->importe ;
                    $efectivo = $documento->efectivo;
                }
                else {
                    $otros = $documento->importe;
                    $efectivo = $documento->efectivo;
                }
            }

            $fecha_v = $documento->created_at;
            $diff =  $fecha_v->diffInDays($hoy);

            $cantidad_notas = count($documento->notas);

            $code = '-';
            if(!empty($documento->getRegularizeResponse))
            {
                $json_data = json_decode($documento->getRegularizeResponse, false);
                $code = $json_data->code;
            }

            $coleccion->push([
                'id' => $documento->id,
                'tipo_venta' => $documento->nombreTipo(),
                'tipo_venta_id' => $documento->tipo_venta,
                'empresa' => $documento->empresaEntidad->razon_social,
                'tipo_pago' => $documento->tipo_pago_id,
                'numero_doc' =>  $documento->serie.'-'.$documento->correlativo,
                'serie' => $documento->serie,
                'correlativo' => $documento->correlativo,
                'cliente' => $documento->tipo_documento_cliente.': '.$documento->documento_cliente.' - '.$documento->cliente,
                'empresa' => $documento->empresa,
                'empresa_id' => $documento->empresa_id,
                'cliente_id' => $documento->cliente_id,
                'cotizacion_venta' =>  $documento->cotizacion_venta,
                'fecha_documento' =>  Carbon::parse($documento->fecha_documento)->format( 'd/m/Y'),
                'estado' => $documento->estado_pago,
                'condicion' => $documento->condicion->descripcion,
                'condicion_id' => $documento->condicion_id,
                'ruta_pago' => $documento->ruta_pago,
                'cuenta_id' => $documento->banco_empresa_id,
                'importe' => $documento->importe,
                'efectivo' => $documento->efectivo,
                'sunat' => $documento->sunat,
                'regularize' => $documento->regularize,
                'code' => $code,
                'otros' => 'S/. '.number_format($otros, 2, '.', ''),
                'efectivo' => 'S/. '.number_format($efectivo, 2, '.', ''),
                'transferencia' => 'S/. '.number_format($transferencia, 2, '.', ''),
                'total' => 'S/. '.number_format($documento->total, 2, '.', ''),
                'dias' => (int)(7 - $diff < 0 ? 0  : 7 - $diff),
                'notas' => $cantidad_notas
            ]);
        }

        return DataTables::of($coleccion)->toJson();
    }

    public function getDocumentClient(Request $request)
    {
        $fecha_hoy = Carbon::now()->toDateString();
        $documentos = Documento::where('estado','!=','ANULADO')->where('cliente_id', $request->cliente_id)->where('estado_pago', 'PENDIENTE')->whereBetween('fecha_documento', [$fecha_hoy, $fecha_hoy])->where('condicion_id', $request->condicion_id)->orderBy('id', 'desc')->get();
        $coleccion = collect([]);

        $hoy = Carbon::now();
        foreach($documentos as $documento){

            $transferencia = 0.00;
            $otros = 0.00;
            $efectivo = 0.00;

            if($documento->tipo_pago_id)
            {
                if ($documento->tipo_pago_id == 1) {
                    $efectivo = $documento->importe;
                }
                else if ($documento->tipo_pago_id == 2){
                    $transferencia = $documento->importe ;
                    $efectivo = $documento->efectivo;
                }
                else {
                    $otros = $documento->importe;
                    $efectivo = $documento->efectivo;
                }
            }

            $fecha_v = $documento->created_at;
            $diff =  $fecha_v->diffInDays($hoy);

            $cantidad_notas = count($documento->notas);

            $coleccion->push([
                'id' => $documento->id,
                'tipo_venta' => $documento->nombreTipo(),
                'tipo_venta_id' => $documento->tipo_venta,
                'empresa' => $documento->empresaEntidad->razon_social,
                'tipo_pago' => $documento->tipo_pago_id,
                'numero_doc' =>  $documento->serie.'-'.$documento->correlativo,
                'serie' => $documento->serie,
                'correlativo' => $documento->correlativo,
                'cliente' => $documento->tipo_documento_cliente.': '.$documento->documento_cliente.' - '.$documento->cliente,
                'empresa' => $documento->empresa,
                'empresa_id' => $documento->empresa_id,
                'cotizacion_venta' =>  $documento->cotizacion_venta,
                'fecha_documento' =>  Carbon::parse($documento->fecha_documento)->format( 'd/m/Y'),
                'estado' => $documento->estado_pago,
                'condicion' => $documento->condicion->descripcion,
                'sunat' => $documento->sunat,
                'otros' => 'S/. '.number_format($otros, 2, '.', ''),
                'efectivo' => 'S/. '.number_format($efectivo, 2, '.', ''),
                'transferencia' => 'S/. '.number_format($transferencia, 2, '.', ''),
                'total' => number_format($documento->total, 2, '.', ''),
                'dias' => (int)(7 - $diff < 0 ? 0  : 7 - $diff),
                'notas' => $cantidad_notas
            ]);
        }

        return response()->json([
            'success' => true,
            'ventas' => $coleccion
        ]);
    }

    public function storePago(Request $request)
    {
        try
        {
            DB::beginTransaction();
            $data = $request->all();

            $rules = [
                'tipo_pago_id'=> 'required',
                'efectivo'=> 'required',
                'importe'=> 'required',

            ];

            $message = [
                'tipo_pago_id.required' => 'El campo modo de pago es obligatorio.',
                'importe.required' => 'El campo importe es obligatorio.',
                'efectivo.required' => 'El campo efectivo es obligatorio.'
            ];

            $validator =  Validator::make($data, $rules, $message);

            if ($validator->fails()) {
                $clase = $validator->getMessageBag()->toArray();
                $cadena = "";
                foreach($clase as $clave => $valor) {
                    $cadena =  $cadena . "$valor[0] ";
                }

                Session::flash('error_store_pago',$cadena);
                DB::rollBack();
                return redirect()->route('ventas.caja.index');
            }

            $documento = Documento::find($request->venta_id);

            $documento->tipo_pago_id = $request->get('tipo_pago_id');
            $documento->importe = $request->get('importe');
            $documento->efectivo = $request->get('efectivo');
            $documento->estado_pago = 'PAGADA';
            $documento->banco_empresa_id = $request->get('cuenta_id');
            if($request->hasFile('imagen')){
                if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'pagos'))) {
                    mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'pagos'));
                }
                $documento->ruta_pago = $request->file('imagen')->store('public/pagos');
            }
            $documento->update();



            DB::commit();
            Session::flash('success','Documento pagado con exito.');
            return redirect()->route('ventas.caja.index');
        }
        catch(Exception $e)
        {
            DB::rollBack();
            Session::flash('error',$e->getMessage());
            return redirect()->route('ventas.caja.index');
        }
    }
}
