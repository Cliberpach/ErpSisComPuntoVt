<?php

namespace App\Compras\Documento;

use Illuminate\Database\Eloquent\Model;
use App\Compras\CuentaProveedor;
use App\Mantenimiento\Tabla\Detalle as TablaDetalle;

class Documento extends Model
{
    protected $table = 'compra_documentos';
    public $timestamps = true;
    protected $fillable = [
            'id',
            'fecha_emision',
            'fecha_entrega',
            'empresa_id',
            'proveedor_id',
            'modo_compra',
            'numero_doc',
            'moneda',
            'observacion',
            'igv',
            'igv_check',
            'tipo_cambio',

            'tipo_compra',
            'orden_compra',
            'tipo_pago',

            'sub_total',
            'total_igv',
            'total',

            'serie_tipo',
            'numero_tipo',


            'estado',
            'enviado',
            'usuario_id',

        ];

    public function empresa()
    {
        return $this->belongsTo('App\Mantenimiento\Empresa\Empresa');
    }

    public function proveedor()
    {
        return $this->belongsTo('App\Compras\Proveedor');
    }

    public function usuario()
    {
        return $this->belongsTo('App\User','usuario_id');
    }

    public function cuenta()
    {
        return $this->hasOne('App\Compras\CuentaProveedor','compra_documento_id');
    }

    public function lotes()
    {
        return $this->hasMany('App\Almacenes\LoteProducto','compra_documento_id');
    }

    protected static function booted()
    {
        static::created(function(Documento $documento){
            $modo = TablaDetalle::where('descripcion',$documento->modo_compra)->first();
            if($modo->simbolo === 'CREDITO' || $modo->simbolo === 'credito' || $modo->simbolo === 'CRÉDITO' || $modo->simbolo === 'crédito')
            {
                $cuenta_proveedor = new CuentaProveedor();
                $cuenta_proveedor->compra_documento_id = $documento->id;
                $cuenta_proveedor->fecha_doc = $documento->fecha_emision;
                $cuenta_proveedor->saldo = $documento->total;
                $cuenta_proveedor->acta = 'DOCUMENTO COMPRA';
                $cuenta_proveedor->save();
            }
        });

        static::updated(function(Documento $documento){
            if($documento->cuenta)
            {
                $cuenta_proveedor = CuentaProveedor::find($documento->cuenta->id);
                $cuenta_proveedor->compra_documento_id = $documento->id;
                $cuenta_proveedor->fecha_doc = $documento->fecha_emision;
                $cuenta_proveedor->saldo = $documento->total;
                $cuenta_proveedor->acta = 'DOCUMENTO COMPRA';
                $cuenta_proveedor->update();
            }
            else
            {
                $modo = TablaDetalle::where('descripcion',$documento->modo_compra)->first();
                if($modo->simbolo === 'CREDITO' || $modo->simbolo === 'credito' || $modo->simbolo === 'CRÉDITO' || $modo->simbolo === 'crédito')
                {
                    $cuenta_proveedor = new CuentaProveedor();
                    $cuenta_proveedor->compra_documento_id = $documento->id;
                    $cuenta_proveedor->fecha_doc = $documento->fecha_emision;
                    $cuenta_proveedor->saldo = $documento->total;
                    $cuenta_proveedor->acta = 'DOCUMENTO COMPRA';
                    $cuenta_proveedor->save();
                }
            }
        });

        static::deleted(function(Documento $documento){
            //ANULAR LOTE producto
            if($documento->cuenta)
            {
                $cuenta_proveedor = CuentaProveedor::find($documento->cuenta->id);
                $cuenta_proveedor->delete();
            }

        });

    }

}
