<?php

namespace App\Listeners;

use App\Notifications\FacturacionNotification;
use App\Notifications\RegularizeNotification;
use App\Ventas\Documento\Documento;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BroadcastUserLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $delete = DB::table('notifications');

        if(!PuntoVenta() && !FullAccess())
        {
            $delete = $delete->where('notifiable_id',Auth::user()->id);
        }

        $delete = $delete->delete();

        $documentos =  DB::table('cotizacion_documento')
        ->select(
            'cotizacion_documento.*',
            DB::raw('json_unquote(json_extract(cotizacion_documento.getRegularizeResponse, "$.code")) as code')
        )
        ->whereIn('cotizacion_documento.tipo_venta',['127','128'])
        ->where('cotizacion_documento.estado', '!=','ANULADO')
        ->where('cotizacion_documento.sunat', '=','0');

        if(!PuntoVenta() && !FullAccess())
        {
            $documentos = $documentos->where('user_id',Auth::user()->id);
        }

        $documentos = $documentos->orderBy('cotizacion_documento.id','desc')->get();
        foreach($documentos as $documento)
        {
            if($documento->code != '1033')
            {
                Auth::user()->notify(new FacturacionNotification($documento));
            }
        }

        $regularizaciones =  DB::table('cotizacion_documento')
        ->select(
            'cotizacion_documento.*',
        )
        ->orderBy('cotizacion_documento.id','DESC')
        ->whereIn('cotizacion_documento.tipo_venta',['127','128'])
        ->where('cotizacion_documento.estado', '!=','ANULADO')
        ->where('cotizacion_documento.sunat', '!=','2')
        ->where(DB::raw('JSON_EXTRACT(cotizacion_documento.getRegularizeResponse, "$.code")'),'1033')
        ->where('cotizacion_documento.regularize','1');

        if(!PuntoVenta() && !FullAccess())
        {
            $regularizaciones = $regularizaciones->where('user_id',Auth::user()->id);
        }

        $regularizaciones = $regularizaciones->get();


        foreach($regularizaciones as $doc)
        {
            Auth::user()->notify(new RegularizeNotification($doc));
        }

    }
}
