<?php

namespace App\Http\Controllers\Mantenimiento;

use App\Http\Controllers\Controller;
use App\Mantenimiento\Condicion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CondicionController extends Controller
{
    public function index()
    {
        $this->authorize('haveaccess','condicion.index');
        return view('mantenimiento.condiciones.index');
    }

    public function getRepository(){
        return datatables()->query(
            DB::table('condicions')
            ->select('condicions.*',
            DB::raw('DATE_FORMAT(created_at, "%d/%m/%Y") as creado'),
            DB::raw('DATE_FORMAT(updated_at, "%d/%m/%Y") as actualizado')
            )->where('condicions.estado','ACTIVO')->orderBy('id','DESC')
        )->toJson();
    }

    public function store(Request $request){
        $data = $request->all();

        $rules = [
            'descripcion_guardar' => 'required',
            'dias_guardar' => 'required',
        ];

        $message = [
            'descripcion_guardar.required' => 'El campo Descripción es obligatorio.',
            'dias_guardar.required' => 'El campo Ubicación es obligatorio.',
        ];

        Validator::make($data, $rules, $message)->validate();

        $condicion = new Condicion();
        $condicion->descripcion = $request->get('descripcion_guardar');
        $condicion->dias = $request->get('dias_guardar');
        $condicion->save();


        //Registro de actividad
        $descripcion = "SE AGREGÓ LA CONDICION CON EL NOMBRE: ". $condicion->descripcion;
        $gestion = "CONDICION";
        crearRegistro($condicion, $descripcion , $gestion);

        Session::flash('success','Condición creada.');
        return redirect()->route('mantenimiento.condiciones.index')->with('guardar', 'success');
    }

    public function update(Request $request){

        $data = $request->all();

        $rules = [
            'tabla_id' => 'required',
            'descripcion' => 'required',
            'dias' => 'required',
        ];

        $message = [
            'descripcion.required' => 'El campo Descripción es obligatorio.',
            'dias.required' => 'El campo dias es obligatorio.',
        ];

        Validator::make($data, $rules, $message)->validate();

        $condicion = Condicion::findOrFail($request->get('tabla_id'));
        $condicion->descripcion = $request->get('descripcion');
        $condicion->dias = $request->get('dias');
        $condicion->update();

        //Registro de actividad
        $descripcion = "SE MODIFICÓ LA CONDICION CON EL NOMBRE: ". $condicion->descripcion;
        $gestion = "CONDICIÓN";
        modificarRegistro($condicion, $descripcion , $gestion);

        Session::flash('success','Condición modificado.');
        return redirect()->route('mantenimiento.condiciones.index')->with('modificar', 'success');
    }

    public function destroy($id)
    {

        $condicion = Condicion::findOrFail($id);
        $condicion->estado = 'ANULADO';
        $condicion->update();

        //Registro de actividad
        $descripcion = "SE ELIMINÓ EL CONDICION CON EL NOMBRE: ". $condicion->descripcion;
        $gestion = "CONDICION";
        eliminarRegistro($condicion, $descripcion , $gestion);


        Session::flash('success','Condicion eliminada.');
        return redirect()->route('mantenimiento.condiciones.index')->with('eliminar', 'success');

    }

    public function exist(Request $request)
    {
        $condicion = null;

        if ($request->descripcion != null && $request->id != null && $request->dias != null) { // edit
            $condicion = Condicion::where([
                                    ['descripcion', $request->descripcion],
                                    ['dias', $request->dias],
                                    ['id', '<>', $request->id]
                                ])->where('estado','!=','ANULADO}')->first();
        } else { // create
            $condicion = Condicion::where('descripcion', $request->descripcion)->where('dias',(int)$request->dias)->where('estado','!=','ANULADO')->first();
        }

        $result = ['existe' => $condicion ? true : false, 'data' => $request->all()];

        return response()->json($result);

    }
}
