<?php

namespace App\Almacenes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DetalleNotaSalidad extends Model
{
    protected $table = 'detalle_nota_salidad';
    protected $fillable = [
        'id',
        'nota_salidad_id',
        'lote_id',
        'cantidad',
        'producto_id'
    ];
    public $timestamps = true;

    public function nota_salidad(){
        return $this->belongsTo(NotaSalidad::class,'nota_salidad_id','id');
    }

    public function producto()
    {
        return $this->belongsTo('App\Almacenes\Producto');
    }

    public function lote()
    {
        return $this->belongsTo('App\Almacenes\LoteProducto','lote_id');
    }

    protected static function booted()
    {
        static::created(function(DetalleNotaSalidad $detalle){

            MovimientoNota::create([
                'cantidad'=> $detalle->cantidad,
                'observacion'=> $detalle->producto->nombre,
                'movimiento'=> "SALIDA",
                'lote_id'=> $detalle->lote_id,
                'usuario_id'=> Auth()->user()->id,
                'nota_id'=> $detalle->nota_salidad->id,
                'producto_id'=> $detalle->producto_id,
            ]);

            //KARDEX
            $kardex = new Kardex();
            $kardex->origen = 'SALIDA';
            $kardex->numero_doc = $detalle->nota_salidad->numero;
            $kardex->fecha = $detalle->nota_salidad->fecha;
            $kardex->cantidad = $detalle->cantidad;            
            $kardex->producto_id = $detalle->producto_id;
            $kardex->descripcion = $detalle->nota_salidad->destino;
            $kardex->precio = $detalle->producto->precio_venta_minimo;
            $kardex->importe = $detalle->producto->precio_venta_minimo * $detalle->cantidad;
            $kardex->stock = $detalle->producto->stock;
            $kardex->save();

            $lote_producto = LoteProducto::findOrFail($detalle->lote_id);
            $lote_productocantidad = $lote_producto->cantidad - $detalle->cantidad;
            $lote_productocantidad_logica = $lote_producto->cantidad - $detalle->cantidad;
            
            if($lote_producto->cantidad - $detalle->cantidad === 0)
            {
                $lote_producto->estado = '0';
                $lote_producto->upadate();
            }
            DB::update('update lote_productos set cantidad= ?,cantidad_logica = ? where id = ?', [$lote_productocantidad,$lote_productocantidad_logica,$detalle->lote_id]);

             //RECORRER DETALLE NOTAS
             $cantidadProductos = LoteProducto::where('producto_id',$detalle->producto_id)->where('estado','1')->sum('cantidad');
             //ACTUALIZAR EL STOCK DEL PRODUCTO
             $producto = Producto::findOrFail($detalle->producto_id);
             $producto->stock = $cantidadProductos;
             $producto->update();            
        });
    }
}
