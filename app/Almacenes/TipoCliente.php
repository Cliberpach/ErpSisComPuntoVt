<?php

namespace App\Almacenes;

use Illuminate\Database\Eloquent\Model;

class TipoCliente extends Model
{
    protected $table = 'productos_clientes';
    protected $fillable = [
        'cliente',
        'monto',
        'producto_id',
        'moneda',
        'estado'
    ];

    public function producto()
    {
        return $this->belongsTo('App\Almacenes\Producto');
    }

    public function tipocliente(): string
    {
        $cliente = tipo_clientes()->where('id', $this->cliente)->first();
        if (is_null($cliente))
            return "-";
        else
            return $cliente->descripcion;
    }

}
