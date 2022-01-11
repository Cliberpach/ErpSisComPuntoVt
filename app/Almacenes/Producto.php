<?php

namespace App\Almacenes;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'almacen_id',
        'marca_id',
        'categoria_id',
        'medida',
        'stock',
        'stock_minimo',
        'precio_venta_minimo',
        'precio_venta_maximo',
        'igv',
        'estado',
        'codigo_barra',
        'peso_producto',
        'porcentaje_normal',
        'porcentaje_distribuidor',
    ];
    protected $casts = [
        'igv' => 'boolean'
    ];
    public function almacen()
    {
        return $this->belongsTo('App\Almacenes\Almacen');
    }
    public function marca()
    {
        return $this->belongsTo('App\Almacenes\Marca');
    }
    public function categoria()
    {
        return $this->belongsTo('App\Almacenes\Categoria');
    }
    public function detalles()
    {
        return $this->hasMany('App\Almacenes\ProductoDetalle');
    }
    public function tipoCliente()
    {
        return $this->hasMany('App\Almacenes\TipoCliente','producto_id', 'id');
    }
    public function getDescripcionCompleta()
    {
        return $this->codigo.' - '.$this->nombre;
    }
    public function tabladetalle()
    {
        return $this->belongsTo('App\Mantenimiento\Tabla\Detalle','medida');
    }
    public function getMedida(): string
    {
        $medida = unidad_medida()->where('id', $this->medida)->first();
        if (is_null($medida))
            return "-";
        else
            return $medida->simbolo;
    }
    public function medidaCompleta(): string
    {
        $medida = unidad_medida()->where('id', $this->medida)->first();
        if (is_null($medida))
            return "-";
        else
            return $medida->simbolo.' - '.$medida->descripcion;
    }
}
