<?php

namespace App\Http\Controllers\Consultas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UtilidadController extends Controller
{
    public function index()
    {
        $this->authorize('haveaccess','utilidad_mensual.index');
        $inversion_mensual = compras_mensual();
        $ventas_mensual = ventas_mensual();
        $utilidad_mensual = utilidad_mensual();
        $porcentaje = 0;
        if($ventas_mensual > 0)
        {
            $porcentaje = ($utilidad_mensual * 100) / $ventas_mensual;
        }

        $dolar_aux = json_encode(precio_dolar(), true);
        $dolar_aux = json_decode($dolar_aux, true);

        $dolar = (float)$dolar_aux['original']['venta'];

        $inversion_mensual_dolares = $inversion_mensual /  $dolar;
        $ventas_mensual_dolares = $ventas_mensual / $dolar;
        $utilidad_mensual_dolares = $utilidad_mensual / $dolar;

        return view('consultas.utilidad.index',[
            'inversion_mensual' => number_format($inversion_mensual, 2),
            'inversion_mensual_dolares' => number_format($inversion_mensual_dolares, 2),
            'ventas_mensual' => number_format($ventas_mensual, 2),
            'ventas_mensual_dolares' => number_format($ventas_mensual_dolares, 2),
            'utilidad_mensual' => number_format($utilidad_mensual, 2),
            'utilidad_mensual_dolares' => number_format($utilidad_mensual_dolares, 2),
            'porcentaje' => number_format($porcentaje, 2),
        ]);
    }
}
