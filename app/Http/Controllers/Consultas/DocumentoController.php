<?php

namespace App\Http\Controllers\Consultas;

use App\Almacenes\LoteProducto;
use App\Almacenes\Producto;
use App\Exports\DocumentosExport;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Condicion;
use App\Mantenimiento\Empresa\Empresa;
use App\Ventas\Cliente;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\Guia;
use App\Ventas\Nota;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

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
                'request' => $request->all()
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
                'request' => $request->all()
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
                    'cliente' => $doc->documento->cliente,
                    'fecha' => Carbon::parse($doc->created_at)->format( 'd/m/Y'),
                    'estado' => $doc->estado,
                    'convertir' => '0',
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
        return  Excel::download(new DocumentosExport($tipo,$fecha_desde,$fecha_hasta), 'INFORME_'.$fecha_desde.'-'.$fecha_hasta.'.xlsx');
    }
}
