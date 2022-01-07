<?php

namespace App\Http\Controllers\Configuracion;

use App\Configuracion\Configuracion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $config = Configuracion::all();
        return view('configuracion.index', compact('config'));
    }

    public function update(Request $request, $id)
    {
        $config = Configuracion::find($id);
        $config->propiedad = $request->propiedad;
        $config->update();

        Session::flash('success',$config->descripcion.' modificada.');
        return redirect()->route('configuracion.index');

    }
}
