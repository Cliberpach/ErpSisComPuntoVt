<?php

namespace App\Http\Controllers\Consultas;

use App\Almacenes\LoteProducto;
use App\Almacenes\Producto;
use App\Exports\DocumentosExport;
use App\Exports\GuiaExport;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Condicion;
use App\Mantenimiento\Empresa\Empresa;
use App\Ventas\Cliente;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\Guia;
use App\Ventas\Nota;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DocumentoController extends Controller
{
    public function index()
    {
        return view('consultas.documentos.index');
    }

    public function getTable(Request $request){

        try{
            $tipo = $request->tipo;
            $fecha_desde = $request->fecha_desde;
            $fecha_hasta = $request->fecha_hasta;


            if($tipo == 127 || $tipo == 128 || $tipo == 129)
            {

                $consulta = Documento::where('estado','!=','ANULADO')->where('tipo_venta', $tipo);
                if($fecha_desde && $fecha_hasta)
                {
                    $consulta = $consulta->whereBetween('fecha_documento', [$fecha_desde, $fecha_hasta]);
                }

                $consulta = $consulta->orderBy('id', 'desc')->get();

                $coleccion = collect();
                foreach($consulta as $doc){
                    $coleccion->push([
                        'id' => $doc->id,
                        'tipo_doc' => $doc->descripcionTipo(),
                        'numero' => $doc->serie . '-' . $doc->correlativo,
                        'total' => $doc->total,
                        'cliente' => $doc->cliente,
                        'fecha' => Carbon::parse($doc->fecha_documento)->format( 'd/m/Y'),
                        'estado' => $doc->estado_pago,
                        'convertir' => $doc->convertir,
                        'tipo' => $tipo
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'documentos' => $coleccion,
                ]);
            }
            else if($tipo == 125)
            {
                $consulta = Documento::where('estado','!=','ANULADO')->where('tipo_venta','!=',129);
                if($fecha_desde && $fecha_hasta)
                {
                    $consulta = $consulta->whereBetween('fecha_documento', [$fecha_desde, $fecha_hasta]);
                }

                $consulta = $consulta->orderBy('id', 'asc')->get();

                $coleccion = collect();

                foreach($consulta as $doc){
                    $coleccion->push([
                        'id' => $doc->id,
                        'tipo_doc' => $doc->descripcionTipo(),
                        'numero' => $doc->serie . '-' . $doc->correlativo,
                        'total' => $doc->total,
                        'cliente' => $doc->cliente,
                        'fecha' => Carbon::parse($doc->fecha_documento)->format( 'd/m/Y'),
                        'estado' => $doc->estado_pago,
                        'convertir' => $doc->convertir,
                        'tipo' => $doc->tipo_venta
                    ]);
                }

                $notas_electronicas = Nota::where('estado','!=','ANULADO')->where('tipo_nota',"0")->where('tipDocAfectado','!=','04');
                if($fecha_desde && $fecha_hasta)
                {
                    $notas_electronicas = $notas_electronicas->whereBetween('fechaEmision', [$fecha_desde, $fecha_hasta]);
                }

                $notas_electronicas = $notas_electronicas->orderBy('id', 'asc')->get();

                foreach($notas_electronicas as $nota){
                    $coleccion->push([
                        'id' => $nota->id,
                        'tipo_doc' => 'NOTA DE CRÉDITO',
                        'numero' => $nota->serie . '-' . $nota->correlativo,
                        'total' => $nota->mtoImpVenta,
                        'cliente' => $nota->cliente,
                        'fecha' => Carbon::parse($nota->fechaEmision)->format( 'd/m/Y'),
                        'estado' => $nota->estado,
                        'convertir' => '0',
                        'tipo' => $tipo
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'documentos' => $coleccion,
                ]);

            }
            else if($tipo == 126)
            {
                $ventas = Documento::where('estado','!=','ANULADO');
                if($fecha_desde && $fecha_hasta)
                {
                    $ventas = $ventas->whereBetween('fecha_documento', [$fecha_desde, $fecha_hasta]);
                }

                $ventas = $ventas->orderBy('id', 'asc')->get();

                $coleccion = collect();

                foreach($ventas as $doc){
                    $coleccion->push([
                        'id' => $doc->id,
                        'tipo_doc' => $doc->descripcionTipo(),
                        'numero' => $doc->serie . '-' . $doc->correlativo,
                        'total' => $doc->total,
                        'cliente' => $doc->cliente,
                        'fecha' => Carbon::parse($doc->fecha_documento)->format( 'd/m/Y'),
                        'estado' => $doc->estado_pago,
                        'convertir' => $doc->convertir,
                        'tipo' => $doc->tipo_venta
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'documentos' => $coleccion,
                ]);
            }
            else if($tipo == 130)
            {
                $consulta = Nota::where('estado','!=','ANULADO')->where('tipo_nota',"0")->where('tipDocAfectado','!=','04');
                if($fecha_desde && $fecha_hasta)
                {
                    $consulta = $consulta->whereBetween('fechaEmision', [$fecha_desde, $fecha_hasta]);
                }

                $consulta = $consulta->orderBy('id', 'desc')->get();

                $coleccion = collect();
                foreach($consulta as $doc){
                    $coleccion->push([
                        'id' => $doc->id,
                        'tipo_doc' => 'NOTA DE CRÉDITO',
                        'numero' => $doc->serie . '-' . $doc->correlativo,
                        'total' => $doc->mtoImpVenta,
                        'cliente' => $doc->cliente,
                        'fecha' => Carbon::parse($doc->fechaEmision)->format( 'd/m/Y'),
                        'estado' => $doc->estado,
                        'convertir' => '0',
                        'tipo' => $tipo
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'documentos' => $coleccion,
                ]);
            }
            else if($tipo == 132)
            {
                $consulta = Guia::where('estado','!=','NULO');
                if($fecha_desde && $fecha_hasta)
                {
                    $consulta = $consulta->whereBetween(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d")'), [$fecha_desde, $fecha_hasta]);
                }

                $consulta = $consulta->orderBy('id', 'desc')->get();

                $coleccion = collect();
                foreach($consulta as $doc){
                    $coleccion->push([
                        'id' => $doc->id,
                        'tipo_doc' => 'GUÍA DE REMISIÓN',
                        'numero' => $doc->serie . '-' . $doc->correlativo,
                        'total' => '-',
                        'cliente' => $doc->documento->cliente,
                        'fecha' => Carbon::parse($doc->created_at)->format( 'd/m/Y'),
                        'estado' => $doc->estado,
                        'convertir' => '0',
                        'tipo' => $tipo
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'documentos' => $coleccion,
                    'request' => $request->all()
                ]);
            }
            else{
                $coleccion = collect();
                return response()->json([
                    'success' => true,
                    'documentos' => $coleccion
                ]);
            }
        }
        catch(Exception $e)
        {
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage()
            ]);
        }
    }

    public function convertir($id)
    {
        $this->authorize('haveaccess','documento_venta.index');
        $empresas = Empresa::where('estado', 'ACTIVO')->get();
        $clientes = Cliente::where('estado', 'ACTIVO')->get();
        $productos = Producto::where('estado', 'ACTIVO')->get();
        $documento = Documento::findOrFail($id);
        $detalles = Detalle::where('documento_id',$id)->where('estado','ACTIVO')->with(['lote','lote.producto'])->get();
        $condiciones = Condicion::where('estado','ACTIVO')->get();
        $fecha_hoy = Carbon::now()->toDateString();
        $fullaccess = false;

        if(count(Auth::user()->roles)>0)
        {
            $cont = 0;
            while($cont < count(Auth::user()->roles))
            {
                if(Auth::user()->roles[$cont]['full-access'] == 'SI')
                {
                    $fullaccess = true;
                    $cont = count(Auth::user()->roles);
                }
                $cont = $cont + 1;
            }
        }
        return view('consultas.documentos.convertir',[
            'documento' => $documento,
            'detalles' => $detalles,
            'empresas' => $empresas,
            'clientes' => $clientes,
            'productos' => $productos,
            'fecha_hoy' => $fecha_hoy,
            'fullaccess' => $fullaccess,
            'condiciones' => $condiciones
        ]);
    }

    public function getDownload(Request $request)
    {
        ob_end_clean();
        ob_start();
        $tipo = $request->tipo;
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;
        if($tipo == 132)
        {
            return  Excel::download(new GuiaExport($fecha_desde,$fecha_hasta), 'GUIAS_'.$fecha_desde.'-'.$fecha_hasta.'.xlsx');
        }
        else {
            return  Excel::download(new DocumentosExport($tipo,$fecha_desde,$fecha_hasta), 'INFORME_'.$fecha_desde.'-'.$fecha_hasta.'.xlsx');
        }
    }
}
