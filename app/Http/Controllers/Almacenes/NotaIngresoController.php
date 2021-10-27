<?php

namespace App\Http\Controllers\Almacenes;

use App\Almacenes\DetalleNotaIngreso;
use App\Almacenes\LoteProducto;
use App\Almacenes\MovimientoNota;
use App\Almacenes\NotaIngreso;
use App\Almacenes\Producto;
use App\Exports\ErrorExcel;
use App\Exports\ModeloExport;
use App\Exports\ProductosExport;
use App\Http\Controllers\Controller;
use App\Imports\DataExcel;
use App\Mantenimiento\Tabla\General;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;
use App\Imports\NotaIngreso as ImportsNotaIngreso;
use Illuminate\Support\Facades\Log;

class NotaIngresoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('haveaccess','nota_ingreso.index');
        return view('almacenes.nota_ingresos.index');
    }
    public function gettable()
    {
        $data = DB::table("nota_ingreso as n")->select('n.*',)->where('n.estado', 'ACTIVO')->get();
        return DataTables::of($data)->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('haveaccess','nota_ingreso.index');
        $fecha_hoy = Carbon::now()->toDateString();
        $fecha = Carbon::createFromFormat('Y-m-d', $fecha_hoy);
        $fecha = str_replace("-", "", $fecha);
        $fecha = str_replace(" ", "", $fecha);
        $fecha = str_replace(":", "", $fecha);
        $fecha_actual = Carbon::now();
        $fecha_actual = date("d/m/Y",strtotime($fecha_actual));
        $fecha_5 = date("Y-m-d",strtotime($fecha_hoy."+ 5 years"));
        $origenes =  General::find(28)->detalles;
        $destinos =  General::find(29)->detalles;
        $lotes = DB::table('lote_productos')->get();
        $ngenerado = $fecha . (DB::table('nota_ingreso')->count() + 1);
        $usuarios = User::get();
        $productos = Producto::where('estado', 'ACTIVO')->get();
        return view('almacenes.nota_ingresos.create', [
            "fecha_hoy" => $fecha_hoy,
            "fecha_actual" => $fecha_actual,
            "fecha_5" => $fecha_5,
            "origenes" => $origenes, 'destinos' => $destinos,
            'ngenerado' => $ngenerado, 'usuarios' => $usuarios,
            'productos' => $productos, 'lotes' => $lotes
        ]);
    }
    public function getProductos(Request $request)
    {
        $data = DB::table('lote_productos')->where('id', $request->lote_id)->get();
        return json_encode($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('haveaccess','nota_ingreso.index');
        $fecha_hoy = Carbon::now()->toDateString();
        $fecha = Carbon::createFromFormat('Y-m-d', $fecha_hoy);
        $fecha = str_replace("-", "", $fecha);
        $fecha = str_replace(" ", "", $fecha);
        $fecha = str_replace(":", "", $fecha);

        $data = $request->all();

        $rules = [
            'fecha' => 'required',
            'destino' => 'nullable',
            'origen' => 'required',
            'notadetalle_tabla' => 'required',


        ];
        $message = [

            'fecha.required' => 'El campo fecha  es Obligatorio',
            'origen.required' => 'El campo origen  es Obligatorio',
            'notadetalle_tabla.required' => 'No hay dispositivos',
        ];

        Validator::make($data, $rules, $message)->validate();


        //$registro_sanitario = new RegistroSanitario();
        $notaingreso = new NotaIngreso();
        $notaingreso->numero = $fecha . (DB::table('nota_ingreso')->count() + 1);
        $notaingreso->fecha = $request->get('fecha');
       if($request->destino)
       {
            $destino = DB::table('tabladetalles')->where('id', $request->destino)->first();
            $notaingreso->destino = $destino->descripcion;
       }
        $origen = DB::table('tabladetalles')->where('id', $request->origen)->first();
        $notaingreso->origen = $origen->descripcion;
        $notaingreso->usuario = Auth()->user()->usuario;
        $notaingreso->save();

        $articulosJSON = $request->get('notadetalle_tabla');
        $notatabla = json_decode($articulosJSON[0]);

        foreach ($notatabla as $fila) {
            DetalleNotaIngreso::create([
                'nota_ingreso_id' => $notaingreso->id,
                'lote' => $fila->lote,
                'cantidad' => $fila->cantidad,
                'producto_id' => $fila->producto_id,
                'fecha_vencimiento' => $fila->fechavencimiento
            ]);
        }

        //Registro de actividad
        $descripcion = "SE AGREGÓ LA NOTA DE INGRESO ";
        $gestion = "ALMACEN / NOTA INGRESO";
        crearRegistro($notaingreso, $descripcion, $gestion);


        Session::flash('success', 'NOTA DE INGRESO');
        return redirect()->route('almacenes.nota_ingreso.index')->with('guardar', 'success');
    }

    public function storeFast(Request $request)
    {
        $fecha_hoy = Carbon::now()->toDateString();
        $fecha_ = Carbon::createFromFormat('Y-m-d', $fecha_hoy);
        $fecha = Carbon::createFromFormat('Y-m-d', $fecha_hoy);
        $fecha = str_replace("-", "", $fecha);
        $fecha = str_replace(" ", "", $fecha);
        $fecha = str_replace(":", "", $fecha);

        $fecha_actual = Carbon::now();
        $fecha_actual = date("d/m/Y",strtotime($fecha_actual));
        $fecha_5 = date("Y-m-d",strtotime($fecha_hoy."+ 5 years"));

        $data = $request->all();

        $rules = [
            'producto_id' => 'required',
            'cantidad' => 'nullable',
        ];

        $message = [

            'producto_id.required' => 'El campo producto  es Obligatorio',
            'cantidad.required' => 'El campo cantidad  es Obligatorio',
        ];

        $validator =  Validator::make($data, $rules, $message);

        if ($validator->fails()) {
            Session::flash('error','Ingreso no creado porfavor llenar todos los datos.');
            return redirect()->route('almacenes.producto.index')->with('guardar', 'error');
        }

        $notaingreso = new NotaIngreso();
        $notaingreso->numero = $fecha . (DB::table('nota_ingreso')->count() + 1);
        $notaingreso->fecha = $fecha_;
        $notaingreso->origen = 'INGRESO RAPIDO';
        $notaingreso->usuario = Auth()->user()->usuario;
        $notaingreso->save();

        DetalleNotaIngreso::create([
            'nota_ingreso_id' => $notaingreso->id,
            'lote' => 'LT-'.$fecha_actual,
            'cantidad' => $request->cantidad,
            'producto_id' => $request->producto_id,
            'fecha_vencimiento' => $fecha_5
        ]);

        //Registro de actividad
        $descripcion = "SE AGREGÓ LA NOTA DE INGRESO ";
        $gestion = "ALMACEN / NOTA INGRESO";
        crearRegistro($notaingreso, $descripcion, $gestion);


        Session::flash('success','Ingreso creado correctamente.');
        return redirect()->route('almacenes.producto.index')->with('guardar', 'success');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->authorize('haveaccess','nota_ingreso.index');

        $fecha_hoy = Carbon::now()->toDateString();
        $fecha = Carbon::createFromFormat('Y-m-d', $fecha_hoy);
        $fecha = str_replace("-", "", $fecha);
        $fecha = str_replace(" ", "", $fecha);
        $fecha = str_replace(":", "", $fecha);
        $fecha_actual = Carbon::now();
        $fecha_actual = date("d/m/Y",strtotime($fecha_actual));
        $fecha_5 = date("Y-m-d",strtotime($fecha_hoy."+ 5 years"));
        $notaingreso = NotaIngreso::findOrFail($id);
        $data = array();
        $detallenotaingreso = DB::table('detalle_nota_ingreso')->where('nota_ingreso_id', $notaingreso->id)->get();
        foreach ($detallenotaingreso as $fila) {
            $lote = LoteProducto::where('codigo_lote', $fila->lote)->first();
            $producto = DB::table('productos')->where('id', $fila->producto_id)->first();
            array_push($data, array(
                'producto_id' => $fila->producto_id,
                'cantidad' => $fila->cantidad,
                'lote' => $lote->codigo_lote,
                'producto' => $producto->nombre,
                'fechavencimiento' => $fila->fecha_vencimiento,
            ));
        }
        $origenes =  General::find(28)->detalles;
        $destinos =  General::find(29)->detalles;
        $lotes = DB::table('lote_productos')->get();
        $usuarios = User::get();
        $productos = Producto::where('estado', 'ACTIVO')->get();
        return view('almacenes.nota_ingresos.edit', [
            "fecha_hoy" => $fecha_hoy,
            "fecha_actual" => $fecha_actual,
            "fecha_5" => $fecha_5,
            "origenes" => $origenes,
            'destinos' => $destinos,
            'usuarios' => $usuarios,
            'productos' => $productos,
            'lotes' => $lotes,
            'notaingreso' => $notaingreso,
            'detalle' => json_encode($data)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->authorize('haveaccess','nota_ingreso.index');
        $data = $request->all();

        $rules = [
            'fecha' => 'required',
            'destino' => 'nullable',
            'origen' => 'required',
            'notadetalle_tabla' => 'required',


        ];
        $message = [

            'fecha.required' => 'El campo fecha  es Obligatorio',
            'origen.required' => 'El campo origen  es Obligatorio',
            'notadetalle_tabla.required' => 'No hay dispositivos',
        ];

        Validator::make($data, $rules, $message)->validate();


        //$registro_sanitario = new RegistroSanitario();
        $notaingreso = NotaIngreso::findOrFail($id);
        $notaingreso->fecha = $request->get('fecha');
        if($request->destino)
        {
             $destino = DB::table('tabladetalles')->where('id', $request->destino)->first();
             $notaingreso->destino = $destino->descripcion;
        }
        $origen = DB::table('tabladetalles')->where('id', $request->origen)->first();
        $notaingreso->origen = $origen->descripcion;
        $notaingreso->usuario = Auth()->user()->usuario;
        $notaingreso->update();

        $articulosJSON = $request->get('notadetalle_tabla');
        $notatabla = json_decode($articulosJSON[0]);
        if ($notatabla != "") {
            foreach($notaingreso->lotes as $lot)
            {
                MovimientoNota::where('lote_id', $lot->id)->where('producto_id', $lot->producto_id)->where('nota_id', $lot->nota_ingreso_id)->where('movimiento', 'INGRESO')->delete();
                $lot->estado = '0';
                $lot->update();
            }
            DetalleNotaIngreso::where('nota_ingreso_id', $notaingreso->id)->delete();
            //LoteProducto::where('nota_ingreso_id', $notaingreso->id)->delete();
            foreach ($notatabla as $fila) {
                DetalleNotaIngreso::create([
                    'nota_ingreso_id' => $id,
                    'lote' => $fila->lote,
                    'cantidad' => $fila->cantidad,
                    'producto_id' => $fila->producto_id,
                    'fecha_vencimiento' => $fila->fechavencimiento
                ]);
            }
        }

        //Registro de actividad
        $descripcion = "SE ACTUALIZO NOTA DE INGRESO ";
        $gestion = "ALMACEN / NOTA INGRESO";
        crearRegistro($notaingreso, $descripcion, $gestion);


        Session::flash('success', 'NOTA DE INGRESO');
        return redirect()->route('almacenes.nota_ingreso.index')->with('guardar', 'success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('haveaccess','nota_ingreso.index');
        $notaingreso = NotaIngreso::findOrFail($id);
        $notaingreso->estado = "ANULADO";
        $notaingreso->save();
        // foreach($notaingreso->detalles as $detalle)
        // {

        // }
        Session::flash('success', 'NOTA DE INGRESO');
        return redirect()->route('almacenes.nota_ingreso.index')->with('guardar', 'success');
    }

    public function uploadnotaingreso(Request $request)
    {
        $data = array();
        $file = $request->file();
        $archivo = $file['files'][0];
        $objeto = new DataExcel();
        Excel::import($objeto, $archivo);

        $datos = $objeto->data;

        try {
            Excel::import(new ImportsNotaIngreso, $archivo);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            $failures = $e->failures();

            foreach ($failures as $failure) {
                array_push($data, array(
                    "fila" => $failure->row(),
                    "atributo" => $failure->attribute(),
                    "error" => $failure->errors()
                ));
            }
            array_push($data, array("excel" => $datos));
        } catch (Exception $er) {
            Log::info($er);
            array_push($data, array(
                "fila" => 0,
                "atributo" => 'none',
                "error" => $er->getMessage()
            ));
        }

        return json_encode($data);
    }

    public function getDownload()
    {
        ob_end_clean(); // this
        ob_start();
        return  Excel::download(new ModeloExport, 'modelo_nota_ingreso.xlsx');
    }

    public function getProductosExcel()
    {
        ob_end_clean(); // this
        ob_start();
        return  Excel::download(new ProductosExport, 'productos.xlsx');
    }
    public function getErrorExcel(Request $request)
    {
        ob_end_clean(); // this
        ob_start();
        $errores = array();
        $datos = json_decode(($request->arregloerrores));
        for ($i = 0; $i < count($datos) - 1; $i++) {
            array_push($errores, array(
                "fila" => $datos[$i]->fila,
                "atributo" => $datos[$i]->atributo,
                "error" => $datos[$i]->error
            ));
        }
        $data = $datos[count($datos) - 1]->excel;

        return  Excel::download(new ErrorExcel($data, $errores), 'excel_error.xlsx');
    }
}
