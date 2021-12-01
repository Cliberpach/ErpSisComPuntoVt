<?php

namespace App\Ventas\Documento;

use App\Mantenimiento\Condicion;
use Illuminate\Database\Eloquent\Model;

use App\Mantenimiento\Tabla\Detalle as TablaDetalle;
use App\Ventas\CuentaCliente;

class Documento extends Model
{
    protected $table = 'cotizacion_documento';
    protected $fillable = [
        //DATOS DE LA EMPRESA
        'ruc_empresa',
        'empresa',
        'direccion_fiscal_empresa',
        'empresa_id',
        //CLIENTE
        'tipo_documento_cliente',
        'documento_cliente',
        'direccion_cliente',
        'cliente',
        'cliente_id',
        'condicion_id',
        'moneda',
        'numero_doc',
        'fecha_documento',
        'fecha_atencion',
        'fecha_vencimiento',
        'sub_total',
        'total_igv',
        'total',
        'user_id',
        'estado',
        'igv',
        'igv_check',
        'tipo_venta',
        'cotizacion_venta',
        'sunat',
        'envio_sunat',
        'getCdrResponse',
        'correlativo',
        'serie',
        'ruta_comprobante_archivo',
        'nombre_comprobante_archivo',

        'convertir'
    ];



    public function detalles()
    {
        return $this->hasMany('App\Ventas\Documento\Detalle','documento_id');
    }

    public function notas()
    {
        return $this->hasMany('App\Ventas\Nota','documento_id');
    }

    public function condicion()
    {
        return $this->belongsTo('App\Mantenimiento\Condicion', 'condicion_id');
    }

    public function empresaEntidad()
    {
        return $this->belongsTo('App\Mantenimiento\Empresa\Empresa', 'empresa_id');
    }

    public function clienteEntidad()
    {
        return $this->belongsTo('App\Ventas\Cliente', 'cliente_id');
    }


    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function tipo_pago()
    {
        return $this->belongsTo('App\Ventas\TipoPago','tipo_pago_id');
    }

    public function nombreTipo(): string
    {
        $venta = tipos_venta()->where('id', $this->tipo_venta)->first();
        if (is_null($venta))
            return "-";
        else
            return strval($venta->nombre);
    }

    public function descripcionTipo(): string
    {
        $venta = tipos_venta()->where('id', $this->tipo_venta)->first();
        if (is_null($venta))
            return "-";
        else
            return strval($venta->descripcion);
    }

    public function tipoOperacion(): string
    {
        $venta = tipos_venta()->where('id', $this->tipo_venta)->first();
        if (is_null($venta))
            return "-";
        else
            return strval($venta->operacion);
    }

    public function tipoDocumento(): string
    {
        $venta = tipos_venta()->where('id', $this->tipo_venta)->first();
        if (is_null($venta))
            return "-";
        else
            return strval($venta->simbolo);
    }

    public function nombreDocumento(): string
    {
        $venta = tipos_venta()->where('id', $this->tipo_venta)->first();
        if (is_null($venta))
            return "-";
        else
            return strval($venta->nombre);
    }

    public function formaPago(): string
    {
        $condicion = Condicion::where('id', $this->condicion_id)->first();
        if (is_null($condicion))
            return "-";
        else
            return strval($condicion->descripcion.' '.($condicion->dias > 0 ? $condicion->dias.' dias' : ''));
    }

    public function simboloMoneda(): string
    {
        $moneda = tipos_moneda()->where('id', $this->moneda)->first();
        if (is_null($moneda))
            return "-";
        else
            return $moneda->parametro;
    }


    public function tipoDocumentoCliente(): string
    {
        $documento = tipos_documento()->where('simbolo', $this->tipo_documento_cliente)->first();
        if (is_null($documento))
            return "-";
        else
            return $documento->parametro;
    }

    public function cuenta()
    {
        return $this->hasOne('App\Ventas\CuentaCliente','cotizacion_documento_id');
    }

    protected static function booted()
    {
        static::created(function(Documento $documento){
            //CREAR CUENTA CLIENTE
            $condicion = Condicion::find($documento->condicion_id);
            if($condicion->descripcion === 'CREDITO' || $condicion->descripcion === 'credito' || $condicion->descripcion === 'CRÉDITO' || $condicion->descripcion === 'crédito')
            {
                $cuenta_cliente = new CuentaCliente();
                $cuenta_cliente->cotizacion_documento_id = $documento->id;
                $cuenta_cliente->numero_doc = $documento->numero_doc;
                $cuenta_cliente->fecha_doc = $documento->fecha_documento;
                $cuenta_cliente->monto = $documento->total;
                $cuenta_cliente->acta = 'DOCUMENTO VENTA';
                $cuenta_cliente->saldo = $documento->total;
                $cuenta_cliente->save();
            }
        });

        static::updated(function(Documento $documento){
            if($documento->cuenta)
           {
               $cuenta_cliente = CuentaCliente::find($documento->cuenta->id);
               $cuenta_cliente->cotizacion_documento_id = $documento->id;
               $cuenta_cliente->numero_doc = $documento->numero_doc;
               $cuenta_cliente->fecha_doc = $documento->fecha_documento;
               $cuenta_cliente->monto = $documento->total;
               $cuenta_cliente->acta = 'DOCUMENTO VENTA';
               $cuenta_cliente->saldo = $documento->total;
               $cuenta_cliente->update();

               if($cuenta_cliente->saldo - $cuenta_cliente->detalles->sum('monto') > 0)
               {
                   $cuenta_cliente->saldo =  $cuenta_cliente->saldo - $cuenta_cliente->detalles->sum('monto');
               }
               else
               {
                   $cuenta_cliente->saldo = 0;
                   $cuenta_cliente->estado = 'PAGADO';
               }

               $cuenta_cliente->update();
           }
           else
           {
               $condicion = Condicion::find($documento->condicion_id);
               if($condicion->descripcion === 'CREDITO' || $condicion->descripcion === 'credito' || $condicion->descripcion === 'CRÉDITO' || $condicion->descripcion === 'crédito')
               {
                   $cuenta_cliente = new CuentaCliente();
                   $cuenta_cliente->cotizacion_documento_id = $documento->id;
                   $cuenta_cliente->numero_doc = $documento->numero_doc;
                   $cuenta_cliente->fecha_doc = $documento->fecha_documento;
                   $cuenta_cliente->monto = $documento->total;
                   $cuenta_cliente->acta = 'DOCUMENTO VENTA';
                   $cuenta_cliente->saldo = $documento->total;
                   $cuenta_cliente->save();
               }
           }

        });

        static::deleted(function(Documento $documento){
           //ANULAR CUENTA
           if($documento->cuenta)
           {
               $cuenta_cliente = CuentaCliente::find($documento->cuenta->id);
               $cuenta_cliente->estado = 'ANULADO';
               $cuenta_cliente->update();
           }

        });

    }
}
