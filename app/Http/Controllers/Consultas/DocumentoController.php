<?php

namespace App\Http\Controllers\Consultas;

use App\Http\Controllers\Controller;
use App\Ventas\Documento\Documento;
use App\Ventas\Guia;
use App\Ventas\Nota;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DocumentoController extends Controller
{
    public function index()
    {
        return view('consultas.documentos.index');
    }

    public function getTable(Request $request){

        $tipo = $request->tipo;
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;


        if((int)$request->tipo < 130)
        {

            $consulta = Documento::where('estado','!=','ANULADO')->where('tipo_venta', $request->tipo);
            if($request->fecha_desde && $request->fecha_hasta)
            {
                $consulta = $consulta->whereBetween('fecha_documento', [$request->fecha_desde, $request->fecha_hasta]);
            }

            $consulta = $consulta->orderBy('id', 'desc')->get();

            $coleccion = collect();
            foreach($consulta as $doc){
                $coleccion->push([
                    'id' => $doc->id,
                    'tipo_doc' => $doc->descripcionTipo(),
                    'numero' => $doc->serie . '-' . $doc->correlativo,
                    'total' => $doc->total,
                    'fecha' => Carbon::parse($doc->fecha_documento)->format( 'd/m/Y'),
                    'estado' => $doc->estado,
                    'tipo' => $tipo
                ]);
            }

            return response()->json([
                'success' => true,
                'documentos' => $coleccion
            ]);
        }
        else if($request->tipo == 130)
        {
            $consulta = Nota::where('estado','!=','ANULADO')->where('tipo_nota',"0");
            if($request->fecha_desde && $request->fecha_hasta)
            {
                $consulta = $consulta->whereBetween('fechaEmision', [$request->fecha_desde, $request->fecha_hasta]);
            }

            $consulta = $consulta->orderBy('id', 'desc')->get();

            $coleccion = collect();
            foreach($consulta as $doc){
                $coleccion->push([
                    'id' => $doc->id,
                    'tipo_doc' => 'NOTA DE CRÉDITO',
                    'numero' => $doc->serie . '-' . $doc->correlativo,
                    'total' => $doc->mtoImpVenta,
                    'fecha' => Carbon::parse($doc->fechaEmision)->format( 'd/m/Y'),
                    'estado' => $doc->estado,
                    'tipo' => $tipo
                ]);
            }

            return response()->json([
                'success' => true,
                'documentos' => $coleccion
            ]);
        }
        else if($request->tipo == 132)
        {
            $consulta = Guia::where('estado','!=','NULO');
            if($request->fecha_desde && $request->fecha_hasta)
            {
                $consulta = $consulta->whereBetween('created_at', [$request->fecha_desde, $request->fecha_hasta]);
            }

            $consulta = $consulta->orderBy('id', 'desc')->get();

            $coleccion = collect();
            foreach($consulta as $doc){
                $coleccion->push([
                    'id' => $doc->id,
                    'tipo_doc' => 'GUÍA DE REMISIÓN',
                    'numero' => $doc->serie . '-' . $doc->correlativo,
                    'total' => '-',
                    'fecha' => Carbon::parse($doc->created_at)->format( 'd/m/Y'),
                    'estado' => $doc->estado,
                    'tipo' => $tipo
                ]);
            }

            $coleccion = collect();
            return response()->json([
                'success' => true,
                'documentos' => $coleccion
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
}
