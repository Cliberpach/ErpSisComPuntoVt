<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class CuentaCliente extends Model
{
    protected $table = 'cuenta_cliente';
    public $timestamps = true;
    protected $fillable = [
            'cotizacion_documento_id',
            'numero_doc',
            'fecha_doc',
            'monto',
            'acta',
            'saldo',
            'estado',
        ];

    public function documento()
    {
        return $this->belongsTo('App\Ventas\Documento\Documento','cotizacion_documento_id','id');
    }

    public function detalles()
    {
        return $this->hasMany('App\Ventas\DetalleCuentaCliente');
    }
}
