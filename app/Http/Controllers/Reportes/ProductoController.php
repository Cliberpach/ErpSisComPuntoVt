<?php

namespace App\Http\Controllers\Reportes;

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
        $compras = Detalle::where('producto_id', $id)->orderBy('id', 'desc')->take(5)->get();
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
        $ventas = DocumentoDetalle::orderBy('id', 'desc')->take(5)->get();
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
}
