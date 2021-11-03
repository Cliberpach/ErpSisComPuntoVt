<?php

namespace App\Ventas\Documento;

use App\Almacenes\Kardex;
use App\Almacenes\LoteProducto;
use Illuminate\Database\Eloquent\Model;

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
        'descuento',
        'dinero',
        'valor_unitario',
        'valor_venta',
        'estado'
    ];

    public function detalles()
    {
        return $this->hasMany('App\Ventas\NotaDetalle','detalle_id','id');
    }

    public function documento()
    {
        return $this->belongsTo('App\Ventas\Documento\Documento');
    }

    public function lote()
    {
        return $this->belongsTo('App\Almacenes\LoteProducto','lote_id');
    }

    protected static function booted()
    {
        static::created(function(Detalle $detalle){

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
            $kardex->stock = $detalle->lote->producto->stock - $detalle->cantidad;
            $kardex->save();

        });

        static::updated(function(Detalle $detalle){

            if($detalle->estado == 'ANULADO')
            {
                $lote = LoteProducto::find($detalle->lote_id);
                $lote->cantidad = $lote->cantidad + $detalle->cantidad;
                $lote->cantidad_logica = $lote->cantidad_logica + $detalle->cantidad;
                $lote->update();
            }
        });
    }
}
