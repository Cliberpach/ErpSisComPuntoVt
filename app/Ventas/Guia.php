<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class Guia extends Model
{
    protected $table = 'guias_remision';
    protected $fillable = [
        'documento_id',
        'cantidad_productos',
        'peso_productos',

        'tienda_id',
        
        'direccion_llegada',
        // 'direccion_partida', //EMPRESA DOCUMENTO DE VENTA PLANO

        //OFICINA
        'ruc_transporte_oficina',
        'nombre_transporte_oficina',
        //DOMICILIO
        'ruc_transporte_domicilio',
        'nombre_transporte_domicilio',

        'observacion',
        'ubigeo_llegada',
        'ubigeo_partida',
        'estado',
        'sunat',
        'correlativo',
        'serie',
        'ruta_comprobante_archivo',
        'nombre_comprobante_archivo',
        'dni_conductor',
        'placa_vehiculo'
    ];

    public function documento()
    {
        return $this->belongsTo('App\Ventas\Documento\Documento','documento_id');
    }

    public function tienda()
    {
        return $this->belongsTo('App\Ventas\Tienda','tienda_id');
    }
}
