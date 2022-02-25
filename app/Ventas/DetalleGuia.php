<?php

namespace App\Ventas;

use App\Almacenes\Producto;
use Illuminate\Database\Eloquent\Model;

class DetalleGuia extends Model
{
    protected $table = 'guia_detalles';
    protected $fillable = [
        'guia_id',
        'producto_id',
        'codigo_producto',
        'cantidad',
        'nombre_producto',
        'unidad',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

}
