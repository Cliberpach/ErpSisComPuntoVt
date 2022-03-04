<?php

namespace App\Http\Controllers\Configuracion;

use App\Configuracion\Configuracion;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Empresa\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $config = Configuracion::all();
        $empresa = Empresa::first();
        return view('configuracion.index', compact('config','empresa'));
    }

    public function update(Request $request, $id)
    {
        $config = Configuracion::find($id);
        $config->propiedad = $request->propiedad;
        $config->update();

        Session::flash('success',$config->descripcion.' modificada.');
        return redirect()->route('configuracion.index');

    }

    public function codigo(Request $request)
    {
        $data = $request->all();

        $rules = [
            'codigo_precio_menor' => 'required_if:estado_precio_menor,1'
        ];
        $message = [
            'codigo_precio_menor.required_if' => 'Si el estado es igual a activo el codigo debe ser diferente de nulo.',
        ];

        $validator =  Validator::make($data, $rules, $message);

        if ($validator->fails()) {
            $clase = $validator->getMessageBag()->toArray();
            $cadena = "";
            foreach($clase as $clave => $valor) {
                $cadena =  $cadena . "$valor[0] ";
            }
            Session::flash('error', $cadena);
            return redirect()->route('configuracion.index');
        }

        $empresa = Empresa::find(1);
        $empresa->codigo_precio_menor = $request->codigo_precio_menor;
        if($request->estado_precio_menor)
        {
            $empresa->estado_precio_menor = $request->estado_precio_menor;
        }
        else
        {

            $empresa->estado_precio_menor = '0';
        }
        $empresa->update();

        Session::flash('success', 'Se cambio el codigo de precio menor.');
        return redirect()->route('configuracion.index');
    }
}
