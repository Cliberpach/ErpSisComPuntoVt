<?php

namespace App\Http\Controllers;

use App\Compras\CuentaProveedor;
use App\Compras\Documento\Documento as DocumentoDocumento;
use App\Ventas\CuentaCliente;
use App\Ventas\Documento\Documento;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use stdClass;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        return view('home');
    }

    public function dashboard()
    {
        try
        {
            $fecha_actual = Carbon::now();

            $meses_aux = array();
            for ($i = 0; $i < 6; $i++) {
                $f_old = (string)date("d-m-Y", strtotime($fecha_actual . "- " . $i . " month"));
                $m = array("ENE", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DIC");
                $f_old = Carbon::parse($f_old);
                $mes = $m[($f_old->format('n')) - 1];
                $nombre = $mes . ' ' . $f_old->format('Y');
                $ob = new stdClass();
                $ob->fecha = date("d-m-Y", strtotime($fecha_actual . "- " . $i . " month"));
                $ob->nombre = $nombre;
                $ob->anio = date("Y", strtotime($fecha_actual . "- " . $i . " month"));
                $ob->mes = date("m", strtotime($fecha_actual . "- " . $i . " month"));
                array_push($meses_aux, $ob);
            }

            $ventas = array();
            for ($j = 0; $j < count($meses_aux); $j++) {
                $total = Documento::where('estado','!=','ANULADO')->whereMonth('fecha_documento',$meses_aux[$j]->mes)->whereYear('fecha_documento',$meses_aux[$j]->anio)->sum('total');
                array_push($ventas, array("nombre" => $meses_aux[$j]->nombre, "total" => number_format($total,3)));
            }

            $compras = array();
            for ($j = 0; $j < count($meses_aux); $j++) {
                $total = DocumentoDocumento::where('estado','!=','ANULADO')->whereMonth('fecha_emision',$meses_aux[$j]->mes)->whereYear('fecha_emision',$meses_aux[$j]->anio)->sum('total_soles');
                array_push($compras, array("nombre" => $meses_aux[$j]->nombre, "total" => number_format($total,3)));
            }

            $cobrar = array();
            for ($j = 0; $j < count($meses_aux); $j++) {
                $total = CuentaCliente::where('estado','!=','ANULADO')->whereMonth('created_at',$meses_aux[$j]->mes)->whereYear('created_at',$meses_aux[$j]->anio)->sum('saldo');
                array_push($cobrar, array("nombre" => $meses_aux[$j]->nombre, "total" => number_format($total,3)));
            }

            $pagar = array();
            for ($j = 0; $j < count($meses_aux); $j++) {
                $total = CuentaProveedor::where('estado','!=','ANULADO')->whereMonth('created_at',$meses_aux[$j]->mes)->whereYear('created_at',$meses_aux[$j]->anio)->sum('saldo');
                array_push($pagar, array("nombre" => $meses_aux[$j]->nombre, "total" => number_format($total,3)));
            }

            return response()->json([
                'success' => true,
                'ventas' => $ventas,
                'compras' => $compras,
                'cobrar' => $cobrar,
                'pagar' => $pagar,
            ]);
        }
        catch(Exception $e)
        {
            return response()->json([
                'success' => false
            ]);
        }
    }
}
