<?php

namespace App\Ventas;

use App\Mantenimiento\Condicion;
use Illuminate\Database\Eloquent\Model;
use App\Mantenimiento\Tabla\Detalle as TablaDetalle;
use App\Pos\DetalleMovimientoEgresosCaja;
use App\Pos\Egreso;
use App\Ventas\DetalleCuentaCliente;
use App\Ventas\CuentaCliente;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;

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
            $condicion = Condicion::find($nota->documento->condicion_id);
            if($condicion->descripcion == 'CREDITO' || $condicion->descripcion == 'credito' || $condicion->descripcion == 'CRÉDITO' || $condicion->descripcion == 'crédito' || $condicion->descripcion == 'Crédito' || $condicion->descripcion == 'Credito')
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
                        $cuenta_cliente->estado='PAGADO';
                    }

                    $monto_saldo = DetalleCuentaCliente::where('cuenta_cliente_id',$cuenta_cliente->id)->sum('monto');
                    if($monto - $monto_saldo > 0)
                    {
                        $cuenta_cliente->saldo = $monto - $monto_saldo;
                    }
                    else
                    {
                        $cuenta_cliente->saldo = 0.00;
                        $cuenta_cliente->estado='PAGADO';
                    }

                    $cuenta_cliente->update();

                    // $documento = Documento::find($nota->documento->id);
                    // $documento->total = $documento->total;
                    // $documento->update();
                }
            }

            $documento = Documento::find($nota->documento->id);
            $detalles = Detalle::where('documento_id', $nota->documento->id)->get();
            $cont = 0;

            foreach($detalles as $detalle)
            {
                if($detalle->cantidad == $detalle->detalles->sum('cantidad'))
                {
                    $cont = $cont + 1;
                }
            }

            if(count($detalles) == $cont)
            {
                $documento->sunat = '2';
                $documento->update();
            }

            $cuenta = TablaDetalle::find(165);
            $cuenta_id = $cuenta->descripcion == 'DEVOLUCION' ? $cuenta->id : (TablaDetalle::where('descripcion','DEVOLUCION')->first() ? TablaDetalle::where('descripcion','DEVOLUCION')->first()->id : null);
            $egreso = new Egreso();
            $egreso->tipodocumento_id = 120;
            $egreso->cuenta_id = $cuenta_id;
            $egreso->documento = $nota->documento->serie.' - '.$nota->documento->correlativo;
            $egreso->descripcion = 'EGRESO POR DEVOLUCION';
            $egreso->importe = $nota->mtoImpVenta;
            $egreso->save();
            $detalleMovimientoEgreso = new DetalleMovimientoEgresosCaja();
            $detalleMovimientoEgreso->mcaja_id = movimientoUser()->id;
            $detalleMovimientoEgreso->egreso_id = $egreso->id;
            $detalleMovimientoEgreso->save();
        });

    }
}
