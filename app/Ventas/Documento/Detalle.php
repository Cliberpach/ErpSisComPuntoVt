<?php

namespace App\Ventas\Documento;

use App\Almacenes\Kardex;
use App\Almacenes\LoteProducto;
use App\Almacenes\Producto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Detalle extends Model
{
    protected $table = 'cotizacion_documento_detalles';
    protected $fillable = [
        'documento_id',
        'lote_id',
        'codigo_producto',
        'unidad',
        'nombre_producto',
        'codigo_lote',
        'cantidad',
        'precio_inicial',
        'precio_unitario',
        'precio_nuevo',
        'precio_minimo',
        'descuento',
        'dinero',
        'valor_unitario',
        'valor_venta',
        'estado',
        'eliminado',
    ];

    public function detalles()
    {
        return $this->hasMany('App\Ventas\NotaDetalle', 'detalle_id', 'id');
    }

    public function documento()
    {
        return $this->belongsTo('App\Ventas\Documento\Documento');
    }

    public function lote()
    {
        return $this->belongsTo('App\Almacenes\LoteProducto', 'lote_id');
    }

    protected static function booted()
    {
        static::created(function (Detalle $detalle) {

            //KARDEX
            $kardex = new Kardex();
            $kardex->origen = 'VENTA';
            $kardex->numero_doc = $detalle->documento->numero_doc;
            $kardex->fecha = $detalle->documento->fecha_documento;
            $kardex->cantidad = $detalle->cantidad;
            $kardex->producto_id = $detalle->lote->producto_id;
            $kardex->descripcion = 'CLIENTES VARIOS';
            $kardex->precio = $detalle->precio_nuevo;
            $kardex->importe = $detalle->precio_nuevo * $detalle->cantidad;
            $kardex->stock = $detalle->lote->producto->stock;
            $kardex->save();

            $producto = Producto::find($detalle->lote->producto_id);
            $producto->precio_venta_minimo = $detalle->precio_unitario;
            $producto->update();
        });

        static::updated(function (Detalle $detalle) {

            // if($detalle->eliminado == '1')
            // {
            //     $lote = LoteProducto::find($detalle->lote_id);
            //     $lote->cantidad = $lote->cantidad + $detalle->cantidad;
            //     $lote->cantidad_logica = $lote->cantidad_logica + $detalle->cantidad;
            //     $lote->update();
            // }
        });
    }
}
