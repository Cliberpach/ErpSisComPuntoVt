<?php

namespace App\Http\Controllers\Reportes;

use App\Almacenes\DetalleNotaIngreso;
use App\Almacenes\DetalleNotaSalidad;
use App\Almacenes\Producto;
use App\Compras\Documento\Detalle;
use App\Http\Controllers\Controller;
use App\Ventas\Documento\Detalle as DocumentoDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductoController extends Controller
{
    public function informe()
    {
        return view('reportes.almacenes.producto.informe');
    }

    public function getTable()
    {
        return datatables()->query(
            DB::table('productos')
            ->join('marcas','productos.marca_id','=','marcas.id')
            ->join('almacenes','almacenes.id','=','productos.almacen_id')
            ->join('categorias','categorias.id','=','productos.categoria_id')
            ->select('categorias.descripcion as categoria','almacenes.descripcion as almacen','marcas.marca','productos.*')
            ->orderBy('productos.id','ASC')
            ->where('productos.estado', 'ACTIVO')
        )->toJson();
    }

    public function llenarCompras($id)
    {
        $compras = Detalle::where('producto_id', $id)->where('estado','ACTIVO')->orderBy('id', 'desc')->get();
        $coleccion = collect([]);
        foreach($compras as $producto) {
            $coleccion->push([
                'proveedor' => $producto->documento->proveedor->descripcion,
                'documento' => $producto->documento->tipo_compra,
                'numero' => $producto->documento->serie_tipo.'-'.$producto->documento->numero_tipo,
                'fecha_emision' => $producto->documento->fecha_emision,
                'cantidad' => $producto->cantidad,
                'precio' => $producto->precio_soles,
                'lote' => $producto->lote,
                'fecha_vencimiento' => $producto->fecha_vencimiento,
            ]);
        }
        return DataTables::of($coleccion)->make(true);
    }

    public function llenarVentas($id)
    {
        $ventas = DocumentoDetalle::orderBy('id', 'desc')->where('estado','ACTIVO')->get();
        $coleccion = collect([]);
        foreach($ventas as $producto) {
            if($producto->lote->producto_id == $id)
            {
                $coleccion->push([
                    'cliente' => $producto->documento->clienteEntidad->nombre,
                    'documento' => $producto->documento->nombreTipo(),
                    'numero' => $producto->documento->serie.'-'.$producto->documento->correlativo,
                    'fecha_emision' => $producto->documento->fecha_atencion,
                    'cantidad' => $producto->cantidad,
                    'precio' => $producto->precio_nuevo,
                    'lote' => $producto->lote->codigo_lote,
                    'fecha_vencimiento' => $producto->documento->fecha_vencimiento,
                ]);
            }
        }
        return DataTables::of($coleccion)->make(true);
    }

    public function llenarSalidas($id)
    {
        $salidas = DetalleNotaSalidad::orderBy('id', 'desc')->where('producto_id', $id)->get();
        $coleccion = collect([]);
        foreach($salidas as $salida) {
            $coleccion->push([
                'origen' => $salida->nota_salidad->origen,
                'destino' => $salida->nota_salidad->destino,
                'cantidad' => $salida->cantidad,
                'lote' => $salida->lote->codigo_lote,
            ]);
        }
        return DataTables::of($coleccion)->make(true);
    }

    public function llenarIngresos($id)
    {
        $ingresos = DetalleNotaIngreso::orderBy('id', 'desc')->where('producto_id', $id)->get();
        $coleccion = collect([]);
        foreach($ingresos as $ingreso) {
            $coleccion->push([
                'origen' => $ingreso->nota_ingreso->origen,
                'destino' => $ingreso->nota_ingreso->destino,
                'cantidad' => $ingreso->cantidad,
                'costo' => $ingreso->costo_soles,
                'total' => $ingreso->valor_ingreso,
            ]);
        }
        return DataTables::of($coleccion)->make(true);
    }
}
