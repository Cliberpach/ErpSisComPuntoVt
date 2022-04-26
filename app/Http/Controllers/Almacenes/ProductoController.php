<?php

namespace App\Http\Controllers\Almacenes;

use App\Almacenes\Almacen;
use App\Almacenes\Categoria;
use App\Almacenes\Marca;
use App\Almacenes\Producto;
use App\Almacenes\ProductoDetalle;
use App\Almacenes\TipoCliente;
use App\Exports\Producto\CodigoBarra;
use App\Exports\Producto\ProductosExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ProductoController extends Controller
{
    public function index()
    {
        $this->authorize('haveaccess','producto.index');
        return view('almacenes.productos.index');
    }

    public function getTable()
    {
        $this->authorize('haveaccess','producto.index');

        return datatables()->query(
            DB::table('productos')
            ->join('marcas','productos.marca_id','=','marcas.id')
            ->join('almacenes','almacenes.id','=','productos.almacen_id')
            ->join('categorias','categorias.id','=','productos.categoria_id')
            ->select('categorias.descripcion as categoria','almacenes.descripcion as almacen','marcas.marca','productos.*')
            ->orderBy('productos.id','DESC')
            ->where('productos.estado', 'ACTIVO')
        )->toJson();
    }

    public function create()
    {
        $this->authorize('haveaccess','producto.index');
        $marcas = Marca::where('estado', 'ACTIVO')->get();
        $almacenes = Almacen::where('estado', 'ACTIVO')->get();
        $categorias = Categoria::where('estado', 'ACTIVO')->get();
        return view('almacenes.productos.create', compact('marcas', 'categorias','almacenes'));
    }

    public function store(Request $request)
    {
        $this->authorize('haveaccess','producto.index');
        $data = $request->all();
        $rules = [
            // 'codigo' => ['string', 'max:50', Rule::unique('productos','codigo')->where(function ($query) {
            //     $query->whereIn('estado',["ACTIVO"]);
            // })],
            'codigo_barra' => ['nullable',Rule::unique('productos','codigo_barra')->where(function ($query) {
                $query->whereIn('estado',["ACTIVO"]);
            }),'min:4','max:20'],
            'nombre' => 'required',
            'marca' => 'required',
            'categoria' => 'required',
            'almacen' => 'required',
            'medida' => 'required',
            'stock_minimo' => 'required|numeric',
            'precio_venta_minimo' => 'numeric|nullable',
            'precio_venta_maximo' => 'numeric|nullable',
            'igv' => 'required|boolean',
        ];

        $message = [
            'codigo_barra.unique' => 'El campo Código de Barra debe de ser único.',
            'codigo_barra.min' => 'El campo Código de Barra debe de tener almenos 8 caracteres.',
            'codigo_barra.max' => 'El campo Código de Barra debe de tener solo 8 caracteres.',
            'linea_comercial.required' => 'El campo Linea Comercial es obligatorio',
            // 'codigo.unique' => 'El campo Código debe ser único',
            // 'codigo.max:50' => 'El campo Código debe tener como máximo 50 caracteres',
            'nombre.required' => 'El campo Descripción del Producto es obligatorio',
            'marca.required' => 'El campo Marca es obligatorio',
            'categoria.required' => 'El campo Categoria es obligatorio',
            'almacen.required' => 'El campo Almacen es obligatorio',
            'medida.required' => 'El campo Unidad de medida es obligatorio',
            'stock_minimo.required' => 'El campo Stock mínimo es obligatorio',
            'stock_minimo.numeric' => 'El campo Stock mínimo debe ser numérico',
            'igv.required' => 'El campo IGV es obligatorio',
            'igv.boolean' => 'El campo IGV debe ser SI o NO',
            'detalles.required' => 'Debe exitir al menos un detalle del producto',
            'detalles.string' => 'El formato de texto de los detalles es incorrecto',
        ];

        Validator::make($data, $rules, $message)->validate();

        DB::transaction(function () use ($request) {

            $producto = new Producto();
            $producto->codigo = $request->get('codigo');
            $producto->codigo_barra = $request->get('codigo_barra');
            $producto->nombre = $request->get('nombre');
            $producto->marca_id = $request->get('marca');
            $producto->almacen_id = $request->get('almacen');
            $producto->categoria_id = $request->get('categoria');
            $producto->medida = $request->get('medida');
            $producto->peso_producto = $request->get('peso_producto') ? $request->get('peso_producto') : 0;
            $producto->stock_minimo = $request->get('stock_minimo');
            $producto->precio_venta_minimo = $request->get('precio_venta_minimo');
            $producto->precio_venta_maximo = $request->get('precio_venta_maximo');
            $producto->peso_producto = $request->get('peso_producto');
            $producto->igv = $request->get('igv');
            $producto->save();

            $producto->codigo = 1000 + $producto->id;
            $producto->update();

            if($request->get('codigo_barra'))
            {
                $generatorPNG = new \Picqer\Barcode\BarcodeGeneratorPNG();
                $code = base64_encode($generatorPNG->getBarcode($request->get('codigo_barra'), $generatorPNG::TYPE_CODE_128));
                $data_code = base64_decode($code);
                $name =  $producto->codigo_barra.'.png';

                if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'productos'))) {
                    mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'productos'));
                }

                $pathToFile = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'productos'.DIRECTORY_SEPARATOR.$name);

                file_put_contents($pathToFile, $data_code);
            }

            //Llenado de los Clientes
            $clientesJSON = $request->get('clientes_tabla');
            $clientetabla = json_decode($clientesJSON[0]);

            foreach ($clientetabla as $cliente) {
                TipoCliente::create([
                    'producto_id' => $producto->id,
                    'cliente' => $cliente->cliente,
                    'porcentaje' => $cliente->monto_igv,
                    'monto' => $cliente->monto_igv,
                    'moneda' => $cliente->id_moneda,
                ]);
            }

            //Registro de actividad
            $descripcion = "SE AGREGÓ EL PRODUCTO CON LA DESCRIPCION: ". $producto->nombre;
            $gestion = "PRODUCTO";
            crearRegistro($producto, $descripcion , $gestion);


        });



        Session::flash('success','Producto creado.');
        return redirect()->route('almacenes.producto.index')->with('guardar', 'success');
    }

    public function edit($id)
    {
        $this->authorize('haveaccess','producto.index');
        $producto = Producto::findOrFail($id);
        $marcas = Marca::where('estado', 'ACTIVO')->get();
        $clientes = TipoCliente::where('estado','ACTIVO')->where('producto_id',$id)->get();
        $categorias = Categoria::where('estado', 'ACTIVO')->get();
        $almacenes = Almacen::where('estado', 'ACTIVO')->get();

        return view('almacenes.productos.edit', [
            'producto' => $producto,
            'marcas' => $marcas,
            'clientes' => $clientes,
            'categorias' => $categorias,
            'almacenes' => $almacenes,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('haveaccess','producto.index');
        $data = $request->all();
        $rules = [
            // 'codigo' => ['required','string', 'max:50', Rule::unique('productos','codigo')->where(function ($query) {
            //     $query->whereIn('estado',["ACTIVO"]);
            // })->ignore($id)],
            'codigo_barra' => ['nullable',Rule::unique('productos','codigo_barra')->where(function ($query) {
                $query->whereIn('estado',["ACTIVO"]);
            })->ignore($id),'min:4','max:20'],
            'nombre' => 'required',
            'almacen' => 'required',
            'marca' => 'required',
            'categoria' => 'required',
            'almacen' => 'required',
            'medida' => 'required',
            'igv' => 'required|boolean',
        ];

        $message = [
            'codigo_barra.unique' => 'El campo Código de barra debe ser único',
            'codigo_barra.min' => 'El campo Código de Barra debe de tener almenos 8 caracteres.',
            'codigo_barra.max' => 'El campo Código de Barra debe de tener solo 8 caracteres.',
            'nombre.required' => 'El campo Descripción del Producto es obligatorio',
            'almacen.required' => 'El campo Almacén es obligatorio',
            'marca.required' => 'El campo Marca es obligatorio',
            'categoria.required' => 'El campo Categoria es obligatorio',
            'medida.required' => 'El campo Unidad de Medida es obligatorio',
            'stock_minimo.required' => 'El campo Stock mínimo es obligatorio',
            'stock_minimo.numeric' => 'El campo Stock mínimo debe ser numérico',
            'igv.required' => 'El campo IGV es obligatorio',
            'igv.boolean' => 'El campo IGV debe ser SI o NO',
            'codigo_barra.unique' => 'El campo Código de Barra debe de ser único.',
        ];

        Validator::make($data, $rules, $message)->validate();

        $producto = Producto::findOrFail($id);
        $producto->codigo = $request->get('codigo');
        $producto->nombre = $request->get('nombre');
        $producto->marca_id = $request->get('marca');
        $producto->almacen_id = $request->get('almacen');
        $producto->categoria_id = $request->get('categoria');
        $producto->medida = $request->get('medida');
        $producto->peso_producto = $request->get('peso_producto') ? $request->get('peso_producto') : 0;
        $producto->stock_minimo = $request->get('stock_minimo');
        $producto->precio_venta_minimo = $request->get('precio_venta_minimo');
        $producto->precio_venta_maximo = $request->get('precio_venta_maximo');
        $producto->igv = $request->get('igv');
        $producto->peso_producto = $request->get('peso_producto');
        $producto->codigo_barra = $request->get('codigo_barra');
        $producto->update();

        if($request->get('codigo_barra'))
        {
            $generatorPNG = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $code = base64_encode($generatorPNG->getBarcode($request->get('codigo_barra'), $generatorPNG::TYPE_CODE_128));
            $data_code = base64_decode($code);
            $name =  $producto->codigo_barra.'.png';

            if(!file_exists(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'productos'))) {
                mkdir(storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'productos'));
            }

            $pathToFile = storage_path('app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'productos'.DIRECTORY_SEPARATOR.$name);

            file_put_contents($pathToFile, $data_code);
        }

        $clientesJSON = $request->get('clientes_tabla');
        $clientetabla = json_decode($clientesJSON[0]);


        if ($clientetabla) {
            $clientes = TipoCliente::where('producto_id', $id)->get();
            foreach ($clientes as $cliente) {
                $cliente->estado= "ANULADO";
                $cliente->update();
            }
            foreach ($clientetabla as $cliente) {
                foreach (tipo_clientes() as $tipo) {
                    if ($tipo->descripcion == $cliente->cliente) {
                        $clientetipo = $tipo->id;
                    }
                }

                TipoCliente::create([
                    'producto_id' => $producto->id,
                    'cliente' => $clientetipo,
                    'porcentaje' => $cliente->monto_igv,
                    'monto' => $cliente->monto_igv,
                    'moneda' => $cliente->id_moneda,
                ]);
            }
        }else{
            $clientes = TipoCliente::where('producto_id', $id)->get();
            foreach ($clientes as $cliente) {
                $cliente->estado= "ANULADO";
                $cliente->update();
            }
        }

        //Registro de actividad
        $descripcion = "SE MODIFICÓ EL PRODUCTO CON LA DESCRIPCION: ". $producto->nombre;
        $gestion = "PRODUCTO";
        modificarRegistro($producto, $descripcion , $gestion);

        Session::flash('success','Producto modificado.');
        return redirect()->route('almacenes.producto.index')->with('guardar', 'success');
    }

    public function show($id)
    {
        $this->authorize('haveaccess','producto.index');
        $producto = Producto::findOrFail($id);
        $clientes = TipoCliente::where('estado','ACTIVO')->where('producto_id',$id)->get();
        return view('almacenes.productos.show', [
            'producto' => $producto,
            'clientes' => $clientes,
        ]);
    }

    public function destroy($id)
    {
        $this->authorize('haveaccess','producto.index');
        $producto = Producto::findOrFail($id);
        $producto->estado = 'ANULADO';
        $producto->update();

       // $producto->detalles()->update(['estado'=> 'ANULADO']);

        //Registro de actividad
        $descripcion = "SE ELIMINÓ EL PRODUCTO CON LA DESCRIPCION: ". $producto->nombre;
        $gestion = "PRODUCTO";
        eliminarRegistro($producto, $descripcion , $gestion);

        Session::flash('success','Producto eliminado.');
        return redirect()->route('almacenes.producto.index')->with('eliminar', 'success');
    }

    public function destroyDetalle(Request $request)
    {
        $data = $request->all();

        $result = 1;
        // if ($data['id']) {
        //     ProductoDetalle::destroy($data['id']);
        //     $result = 1;
        // }

        $data = ['exito' => ($result === 1)];

        return response()->json($data);
    }

    public function getCodigo(Request $request)
    {
        $data = $request->all();
        $codigo = $data['codigo'];
        $id = $data['id'];
        $producto = null;

        if ($codigo && $id) { // edit
            $producto = Producto::where([
                                    ['codigo', $data['codigo']],
                                    ['id', '<>', $data['id']]
                                ])->first();
        } else if ($codigo && !$id) { // create
            $producto = Producto::where('codigo', $data['codigo'])->first();
        }

        $result = ['existe' => ($producto) ? true : false];

        return response()->json($result);
    }

    public function obtenerProducto($id)
    {
        $cliente_producto = DB::table('productos_clientes')
                    ->join('productos', 'productos_clientes.producto_id', '=', 'productos.id')
                    ->where('productos_clientes.estado','ACTIVO')
                    ->where('productos_clientes.producto_id',$id)
                    ->get();

        $producto = Producto::where('id',$id)->where('estado','ACTIVO')->first();

        $resultado = [
                'cliente_producto' => $cliente_producto,
                'producto' => $producto,
            ];
        return $resultado;
    }

    public function productoDescripcion($id)
    {
        $producto = Producto::findOrFail($id);
        return $producto;
    }

    public function getProducto($id)
    {
        $producto = Producto::findOrFail($id);
        return $producto;
    }


    public function getProductos(){
        return datatables()->query(
            DB::table('productos')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->select('productos.*','categorias.descripcion as categoria')
            ->where('productos.estado','ACTIVO')
        )->toJson();
    }

    public function generarCode()
    {
        return response()->json([
            'code' => generarCodigo(8)
        ]);
    }

    public function codigoBarras($id){
        ob_end_clean();
        ob_start();
        $producto = Producto::find($id);
        return  Excel::download(new CodigoBarra($producto), $producto->codigo_barra.'.xlsx');
    }

    public function getExcel()
    {
        ob_end_clean(); // this
        ob_start();
        return  Excel::download(new ProductosExport, 'productos.xlsx');
    }
}
