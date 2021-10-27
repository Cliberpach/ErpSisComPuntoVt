<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;
use App\Mantenimiento\Tabla\Detalle as TablaDetalle;
use App\Ventas\DetalleCuentaCliente;
use App\Ventas\CuentaCliente;
class Nota extends Model
{
    protected $table = 'nota_electronica';
    protected $fillable = [
        'documento_id',
        'tipDocAfectado',
        'numDocfectado',
        'codMotivo',
        'desMotivo',

        'tipoDoc',
        'fechaEmision',
        'tipoMoneda',

        //DATOS DE LA EMPRESA
        'ruc_empresa',
        'empresa',
        'direccion_fiscal_empresa',
        'empresa_id',
        //CLIENTE
        'cod_tipo_documento_cliente',
        'tipo_documento_cliente',
        'documento_cliente',
        'direccion_cliente',
        'cliente',

        'sunat',
        'tipo_nota',

        'correlativo',
        'serie',

        'ruta_comprobante_archivo',
        'nombre_comprobante_archivo',

        'mtoOperGravadas',
        'mtoIGV',
        'totalImpuestos',
        'mtoImpVenta',
        'ublVersion',


        'guia_tipoDoc',
        'guia_nroDoc',
        'code',
        'value',
        'estado',
    ];

    public function documento()
    {
        return $this->belongsTo('App\Ventas\Documento\Documento','documento_id');
    }

    protected static function booted()
    {
        static::created(function(Nota $nota){
            //CREAR LOTE PRODUCTO
            $modo = TablaDetalle::find($nota->documento->forma_pago);
            if($modo->simbolo === 'CREDITO' || $modo->simbolo === 'credito' || $modo->simbolo === 'CRÉDITO' || $modo->simbolo === 'crédito')
            {
                if($nota->documento->cuenta)
                {
                    $monto_notas = Nota::where('documento_id',$nota->documento_id)->sum('mtoImpVenta');
                    $monto  = $nota->documento->total - $monto_notas;
                    $cuenta_cliente = CuentaCliente::find($nota->documento->cuenta->id);
                    if($monto > 0)
                    {
                        $cuenta_cliente->monto = $monto;
                    }
                    else
                    {
                        $cuenta_cliente->monto = 0.00;
                    }
                    
                    $monto_saldo = DetalleCuentaCliente::where('cuenta_cliente_id',$cuenta_cliente->id)->sum('monto');
                    if($monto - $monto_saldo > 0)
                    {
                        $cuenta_cliente->saldo = $monto - $monto_saldo;
                    }
                    else
                    {
                        $cuenta_cliente->saldo = 0.00;
                    }
                    
                    $cuenta_cliente->update();
                }
            }
        });

    }
}
