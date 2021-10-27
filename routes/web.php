<?php

use App\Almacenes\Kardex;
use App\Almacenes\LoteProducto;
use App\Http\Controllers\Almacenes\NotaSalidadController;
use App\Mantenimiento\Empresa\Empresa;
use App\Pos\MovimientoCaja;
use App\User;
use App\Ventas\CuentaCliente;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\NotaDetalle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    if(Auth()->user())
    {
        return view('home');
    }
    else
    {   return view('auth.login');

    }

});

Auth::routes();

Route::group([
    'middleware' => 'auth',
    // 'middleware' => 'Cors'
],
function(){
    Route::get('/home', 'HomeController@index')->name('home');

    Route::get('logout', 'Auth\LoginController@logout')->name('logout');

    //Parametro
    Route::get('parametro/getApiruc/{ruc}', 'ParametroController@apiRuc')->name('getApiruc');
    Route::get('parametro/getApidni/{dni}', 'ParametroController@apiDni')->name('getApidni');

    // Mantenimiento
    //Tabla General - Protegido con crud_general
    Route::prefix('mantenimiento/tablas/generales')->group(function() {
        Route::get('index', 'Mantenimiento\Tabla\GeneralController@index')->name('mantenimiento.tabla.general.index');
        Route::get('getTable','Mantenimiento\Tabla\GeneralController@getTable')->name('getTable');
        Route::put('update', 'Mantenimiento\Tabla\GeneralController@update')->name('mantenimiento.tabla.general.update');
    });
    //Users
    Route::prefix('users')->group(function() {
        Route::get('/', 'Seguridad\UserController@index')->name('user.index');
        Route::get('destroy/{id}', 'Seguridad\UserController@destroy')->name('user.destroy');
        Route::get('create', 'Seguridad\UserController@create')->name('user.create');
        Route::post('store', 'Seguridad\UserController@store')->name('user.store');
        Route::get('edit/{id}','Seguridad\UserController@edit')->name('user.edit');
        Route::put('update/{id}', 'Seguridad\UserController@update')->name('user.update');
        Route::get('show/{id}','Seguridad\UserController@show')->name('user.show');
    });

    //Roles
    Route::prefix('roles')->group(function() {
        Route::get('/', 'Seguridad\RoleController@index')->name('role.index');
        Route::get('destroy/{id}', 'Seguridad\RoleController@destroy')->name('role.destroy');
        Route::get('create', 'Seguridad\RoleController@create')->name('role.create');
        Route::post('store', 'Seguridad\RoleController@store')->name('role.store');
        Route::get('edit/{id}','Seguridad\RoleController@edit')->name('role.edit');
        Route::put('update/{id}', 'Seguridad\RoleController@update')->name('role.update');
        Route::get('show/{id}','Seguridad\RoleController@show')->name('role.show');
        Route::get('getTable','Seguridad\RoleController@getTable')->name('role.getTable');

    });

    //Tabla Detalle
    Route::prefix('mantenimiento/tablas/detalles')->group(function() {
        Route::get('index/{id}', 'Mantenimiento\Tabla\DetalleController@index')->name('mantenimiento.tabla.detalle.index');
        Route::get('getTable/{id}','Mantenimiento\Tabla\DetalleController@getTable')->name('getTableDetalle');
        Route::get('destroy/{id}', 'Mantenimiento\Tabla\DetalleController@destroy')->name('mantenimiento.tabla.detalle.destroy');
        Route::post('store', 'Mantenimiento\Tabla\DetalleController@store')->name('mantenimiento.tabla.detalle.store');
        Route::put('update', 'Mantenimiento\Tabla\DetalleController@update')->name('mantenimiento.tabla.detalle.update');
        Route::get('getDetail/{id}','Mantenimiento\Tabla\DetalleController@getDetail')->name('mantenimiento.tabla.detalle.getDetail');
        Route::post('/exist', 'Mantenimiento\Tabla\DetalleController@exist')->name('mantenimiento.tabla.detalle.exist');

    });
    //Empresas
    Route::prefix('mantenimiento/empresas')->group(function() {
        Route::get('index', 'Mantenimiento\Empresa\EmpresaController@index')->name('mantenimiento.empresas.index');
        Route::get('getBusiness','Mantenimiento\Empresa\EmpresaController@getBusiness')->name('getBusiness');
        Route::get('create','Mantenimiento\Empresa\EmpresaController@create')->name('mantenimiento.empresas.create');
        Route::post('store', 'Mantenimiento\Empresa\EmpresaController@store')->name('mantenimiento.empresas.store');
        Route::get('destroy/{id}', 'Mantenimiento\Empresa\EmpresaController@destroy')->name('mantenimiento.empresas.destroy');
        Route::get('show/{id}', 'Mantenimiento\Empresa\EmpresaController@show')->name('mantenimiento.empresas.show');
        Route::get('edit/{id}', 'Mantenimiento\Empresa\EmpresaController@edit')->name('mantenimiento.empresas.edit');
        Route::put('update/{id}', 'Mantenimiento\Empresa\EmpresaController@update')->name('mantenimiento.empresas.update');
        Route::get('serie/{id}', 'Mantenimiento\Empresa\EmpresaController@serie')->name('serie.empresa.facturacion');
        Route::post('certificate', 'Mantenimiento\Empresa\EmpresaController@certificate')->name('mantenimiento.empresas.certificado');
        Route::get('obtenerNumeracion/{id}','Mantenimiento\Empresa\EmpresaController@obtenerNumeracion')->name('mantenimiento.empresas.obtenerNumeracion');

    });
    // Ubigeo
    Route::prefix('mantenimiento/ubigeo')->group(function() {
        Route::post('/provincias', 'Mantenimiento\Ubigeo\UbigeoController@provincias')->name('mantenimiento.ubigeo.provincias');
        Route::post('/distritos', 'Mantenimiento\Ubigeo\UbigeoController@distritos')->name('mantenimiento.ubigeo.distritos');
        Route::post('/api_ruc', 'Mantenimiento\Ubigeo\UbigeoController@api_ruc')->name('mantenimiento.ubigeo.api_ruc');
    });
     // Colaboradores
     Route::prefix('mantenimiento/colaboradores')->group(function() {
        Route::get('/', 'Mantenimiento\Colaborador\ColaboradorController@index')->name('mantenimiento.colaborador.index');
        Route::get('/getTable', 'Mantenimiento\Colaborador\ColaboradorController@getTable')->name('mantenimiento.colaborador.getTable');
        Route::get('/registrar', 'Mantenimiento\Colaborador\ColaboradorController@create')->name('mantenimiento.colaborador.create');
        Route::post('/registrar', 'Mantenimiento\Colaborador\ColaboradorController@store')->name('mantenimiento.colaborador.store');
        Route::get('/actualizar/{id}', 'Mantenimiento\Colaborador\ColaboradorController@edit')->name('mantenimiento.colaborador.edit');
        Route::put('/actualizar/{id}', 'Mantenimiento\Colaborador\ColaboradorController@update')->name('mantenimiento.colaborador.update');
        Route::get('/datos/{id}', 'Mantenimiento\Colaborador\ColaboradorController@show')->name('mantenimiento.colaborador.show');
        Route::get('/destroy/{id}', 'Mantenimiento\Colaborador\ColaboradorController@destroy')->name('mantenimiento.colaborador.destroy');
        Route::post('/getDNI', 'Mantenimiento\Colaborador\ColaboradorController@getDNI')->name('mantenimiento.colaborador.getDni');
    });
    // Vendedores
    Route::prefix('mantenimiento/vendedores')->group(function() {
        Route::get('/', 'Mantenimiento\Vendedor\VendedorController@index')->name('mantenimiento.vendedor.index');
        Route::get('/getTable', 'Mantenimiento\Vendedor\VendedorController@getTable')->name('mantenimiento.vendedor.getTable');
        Route::get('/registrar', 'Mantenimiento\Vendedor\VendedorController@create')->name('mantenimiento.vendedor.create');
        Route::post('/registrar', 'Mantenimiento\Vendedor\VendedorController@store')->name('mantenimiento.vendedor.store');
        Route::get('/actualizar/{id}', 'Mantenimiento\Vendedor\VendedorController@edit')->name('mantenimiento.vendedor.edit');
        Route::put('/actualizar/{id}', 'Mantenimiento\Vendedor\VendedorController@update')->name('mantenimiento.vendedor.update');
        Route::get('/datos/{id}', 'Mantenimiento\Vendedor\VendedorController@show')->name('mantenimiento.vendedor.show');
        Route::get('/destroy/{id}', 'Mantenimiento\Vendedor\VendedorController@destroy')->name('mantenimiento.vendedor.destroy');
        Route::post('/getDNI', 'Mantenimiento\Vendedor\VendedorController@getDNI')->name('mantenimiento.vendedor.getDni');
    });

    //Almacenes
    //Almacen
    Route::prefix('almacenes/almacen')->group(function() {
        Route::get('index', 'Almacenes\AlmacenController@index')->name('almacenes.almacen.index');
        Route::get('getRepository','Almacenes\AlmacenController@getRepository')->name('getRepository');
        Route::get('destroy/{id}', 'Almacenes\AlmacenController@destroy')->name('almacenes.almacen.destroy');
        Route::post('store', 'Almacenes\AlmacenController@store')->name('almacenes.almacen.store');
        Route::put('update', 'Almacenes\AlmacenController@update')->name('almacenes.almacen.update');
        Route::post('almacen/exist', 'Almacenes\AlmacenController@exist')->name('almacenes.almacen.exist');
    });
    //Categoria
    Route::prefix('almacenes/categorias')->group(function() {
        Route::get('index', 'Almacenes\CategoriaController@index')->name('almacenes.categorias.index');
        Route::get('getCategory','Almacenes\CategoriaController@getCategory')->name('getCategory');
        Route::get('destroy/{id}', 'Almacenes\CategoriaController@destroy')->name('almacenes.categorias.destroy');
        Route::post('store', 'Almacenes\CategoriaController@store')->name('almacenes.categorias.store');
        Route::put('update', 'Almacenes\CategoriaController@update')->name('almacenes.categorias.update');
    });
    //Marcas
    Route::prefix('almacenes/marcas')->group(function() {
        Route::get('index', 'Almacenes\MarcaController@index')->name('almacenes.marcas.index');
        Route::get('getmarca','Almacenes\MarcaController@getmarca')->name('getmarca');
        Route::get('destroy/{id}', 'Almacenes\MarcaController@destroy')->name('almacenes.marcas.destroy');
        Route::post('store', 'Almacenes\MarcaController@store')->name('almacenes.marcas.store');
        Route::put('update', 'Almacenes\MarcaController@update')->name('almacenes.marcas.update');
        Route::post('/exist', 'Almacenes\MarcaController@exist')->name('almacenes.marcas.exist');
    });
    //Productos
    Route::prefix('almacenes/productos')->group(function() {
        Route::get('/', 'Almacenes\ProductoController@index')->name('almacenes.producto.index');
        Route::get('/getTable', 'Almacenes\ProductoController@getTable')->name('almacenes.producto.getTable');
        Route::get('/registrar', 'Almacenes\ProductoController@create')->name('almacenes.producto.create');
        Route::post('/registrar', 'Almacenes\ProductoController@store')->name('almacenes.producto.store');
        Route::get('/actualizar/{id}', 'Almacenes\ProductoController@edit')->name('almacenes.producto.edit');
        Route::put('/actualizar/{id}', 'Almacenes\ProductoController@update')->name('almacenes.producto.update');
        Route::get('/datos/{id}', 'Almacenes\ProductoController@show')->name('almacenes.producto.show');
        Route::get('/destroy/{id}', 'Almacenes\ProductoController@destroy')->name('almacenes.producto.destroy');
        Route::post('/destroyDetalle', 'Almacenes\ProductoController@destroyDetalle')->name('almacenes.producto.destroyDetalle');
        Route::post('/getCodigo', 'Almacenes\ProductoController@getCodigo')->name('almacenes.producto.getCodigo');

        Route::get('getProductos','Almacenes\ProductoController@getProductos')->name('getProductos');
        Route::get('getProducto/{id}','Almacenes\ProductoController@getProducto')->name('getProducto');

        Route::get('/obtenerProducto/{id}', 'Almacenes\ProductoController@obtenerProducto')->name('almacenes.producto.obtenerProducto');

        Route::get('/productoDescripcion/{id}', 'Almacenes\ProductoController@productoDescripcion')->name('almacenes.producto.productoDescripcion');

    });
    //NotaIngreso
    Route::prefix('almacenes/nota_ingreso')->group(function() {
        Route::get('index', 'Almacenes\NotaIngresoController@index')->name('almacenes.nota_ingreso.index');
        Route::get('getdata','Almacenes\NotaIngresoController@gettable')->name('almacenes.nota_ingreso.data');
        Route::get('create','Almacenes\NotaIngresoController@create')->name('almacenes.nota_ingreso.create');
        Route::post('store', 'Almacenes\NotaIngresoController@store')->name('almacenes.nota_ingreso.store');
        Route::post('/storeFast', 'Almacenes\NotaIngresoController@storeFast')->name('almacenes.nota_ingreso.storeFast');
        Route::get('edit/{id}','Almacenes\NotaIngresoController@edit')->name('almacenes.nota_ingreso.edit');
        Route::get('show/{id}','Almacenes\NotaIngresoController@show')->name('almacenes.nota_ingreso.show');
        Route::put('update/{id}', 'Almacenes\NotaIngresoController@update')->name('almacenes.nota_ingreso.update');
        Route::get('destroy/{id}', 'Almacenes\NotaIngresoController@destroy')->name('almacenes.nota_ingreso.destroy');
        Route::post('productos', 'Almacenes\NotaIngresoController@getProductos')->name('almacenes.nota_ingreso.productos');
        Route::post('uploadnotaingreso', 'Almacenes\NotaIngresoController@uploadnotaingreso')->name('almacenes.nota_ingreso.uploadnotaingreso');
        Route::get('downloadexcel', 'Almacenes\NotaIngresoController@getDownload')->name('almacenes.nota_ingreso.downloadexcel');
        Route::get('downloadproductosexcel', 'Almacenes\NotaIngresoController@getProductosExcel')->name('almacenes.nota_ingreso.downloadproductosexcel');
        Route::get('downloaderrorexcel', 'Almacenes\NotaIngresoController@getErrorExcel')->name('almacenes.nota_ingreso.error_excel');
    });
    //NotaSalida
    Route::prefix('almacenes/nota_salidad')->group(function() {
        Route::get('index', 'Almacenes\NotaSalidadController@index')->name('almacenes.nota_salidad.index');
        Route::get('getdata','Almacenes\NotaSalidadController@gettable')->name('almacenes.nota_salidad.data');
        Route::get('create','Almacenes\NotaSalidadController@create')->name('almacenes.nota_salidad.create');
        Route::post('store', 'Almacenes\NotaSalidadController@store')->name('almacenes.nota_salidad.store');
        Route::get('edit/{id}','Almacenes\NotaSalidadController@edit')->name('almacenes.nota_salidad.edit');
        Route::get('show/{id}','Almacenes\NotaSalidadController@show')->name('almacenes.nota_salidad.show');
        Route::put('update/{id}', 'Almacenes\NotaSalidadController@update')->name('almacenes.nota_salidad.update');
        Route::get('destroy/{id}', 'Almacenes\NotaSalidadController@destroy')->name('almacenes.nota_salidad.destroy');
        Route::post('productos', 'Almacenes\NotaSalidadController@getProductos')->name('almacenes.nota_salidad.productos');
        Route::get('getLot','Almacenes\NotaSalidadController@getLot')->name('almacenes.nota_salidad.getLot');

        Route::post('cantidad/', 'Almacenes\NotaSalidadController@quantity')->name('almacenes.nota_salidad.cantidad');
        Route::post('devolver/cantidad', 'Almacenes\NotaSalidadController@returnQuantity')->name('almacenes.nota_salidad.devolver.cantidades');
        Route::post('devolver/cantidadedit', 'Almacenes\NotaSalidadController@returnQuantityEdit')->name('almacenes.nota_salidad.devolver.cantidadesedit');
        Route::post('devolver/lotesinicio', 'Almacenes\NotaSalidadController@returnQuantityLoteInicio')->name('almacenes.nota_salidad.devolver.lotesinicio');
        Route::post('obtener/lote', 'Almacenes\NotaSalidadController@returnLote')->name('almacenes.nota_salidad.obtener.lote');
        Route::post('update/lote', 'Almacenes\NotaSalidadController@updateLote')->name('almacenes.nota_salidad.update.lote');
    });

    //Compras
    //Proveedores
    Route::prefix('compras/proveedores')->group(function() {
        Route::get('index', 'Compras\ProveedorController@index')->name('compras.proveedor.index');
        Route::get('getProvider','Compras\ProveedorController@getProvider')->name('getProvider');
        Route::get('create','Compras\ProveedorController@create')->name('compras.proveedor.create');
        Route::post('store', 'Compras\ProveedorController@store')->name('compras.proveedor.store');
        Route::get('edit/{id}','Compras\ProveedorController@edit')->name('compras.proveedor.edit');
        Route::get('show/{id}','Compras\ProveedorController@show')->name('compras.proveedor.show');
        Route::put('update/{id}', 'Compras\ProveedorController@update')->name('compras.proveedor.update');
        Route::get('destroy/{id}', 'Compras\ProveedorController@destroy')->name('compras.proveedor.destroy');
    });
    //Ordenes de Compra
    Route::prefix('compras/ordenes')->group(function() {
        Route::get('index', 'Compras\OrdenController@index')->name('compras.orden.index');
        Route::get('getOrder','Compras\OrdenController@getOrder')->name('getOrder');
        Route::get('create','Compras\OrdenController@create')->name('compras.orden.create');
        Route::post('store', 'Compras\OrdenController@store')->name('compras.orden.store');
        Route::get('edit/{id}','Compras\OrdenController@edit')->name('compras.orden.edit');
        Route::get('show/{id}','Compras\OrdenController@show')->name('compras.orden.show');
        Route::put('update/{id}', 'Compras\OrdenController@update')->name('compras.orden.update');
        Route::get('destroy/{id}', 'Compras\OrdenController@destroy')->name('compras.orden.destroy');
        Route::get('reporte/{id}','Compras\OrdenController@report')->name('compras.orden.reporte');
        Route::get('email/{id}','Compras\OrdenController@email')->name('compras.orden.email');
        Route::get('concretada/{id}','Compras\OrdenController@concretized')->name('compras.orden.concretada');
        Route::get('consultaEnvios/{id}','Compras\OrdenController@send')->name('compras.orden.envios');
        Route::get('documento/{id}','Compras\OrdenController@document')->name('compras.orden.documento');
        Route::get('nuevodocumento/{id}','Compras\OrdenController@newdocument')->name('compras.orden.nuevodocumento');
        Route::get('confirmarEliminar/{id}','Compras\OrdenController@confirmDestroy')->name('compras.orden.confirmDestroy');
        Route::get('dolar','Compras\OrdenController@dolar')->name('compras.orden.dolar');

        //Pagos
        Route::get('pagos/index/{id}', 'Compras\PagoController@index')->name('compras.pago.index');
        Route::get('getPay/{id}','Compras\PagoController@getPay')->name('getPay');
        Route::get('pagos/create/{id}', 'Compras\PagoController@create')->name('compras.pago.create');
        Route::post('pagos/store/', 'Compras\PagoController@store')->name('compras.pago.store');
        Route::get('pagos/destroy/', 'Compras\PagoController@destroy')->name('compras.pago.destroy');
        Route::get('pagos/show/', 'Compras\PagoController@show')->name('compras.pago.show');
    });
    //Documentos
    Route::prefix('compras/documentos')->group(function(){
        Route::get('/index', 'Compras\DocumentoController@index')->name('compras.documento.index');
        Route::get('/getDocument','Compras\DocumentoController@getDocument')->name('getDocument');
        Route::get('/create', 'Compras\DocumentoController@create')->name('compras.documento.create');
        Route::post('/store', 'Compras\DocumentoController@store')->name('compras.documento.store');
        Route::post('/consulta-store', 'Compras\DocumentoController@comprobante_store')->name('compras.documento.consulta_store');
        Route::post('/consulta-update', 'Compras\DocumentoController@comprobante_update')->name('compras.documento.consulta_update');
        Route::get('/edit/{id}','Compras\DocumentoController@edit')->name('compras.documento.edit');
        Route::put('/update/{id}', 'Compras\DocumentoController@update')->name('compras.documento.update');
        Route::get('/destroy/{id}', 'Compras\DocumentoController@destroy')->name('compras.documento.destroy');
        Route::get('/show/{id}','Compras\DocumentoController@show')->name('compras.documento.show');
        Route::get('/reporte/{id}','Compras\DocumentoController@report')->name('compras.documento.reporte');

        Route::get('/tipoPago/{id}','Compras\DocumentoController@TypePay')->name('compras.documento.tipo_pago.existente');

        //Pagos
        Route::get('pagos/index/{id}', 'Compras\Documentos\PagoController@index')->name('compras.documentos.pago.index');
        Route::get('getPay/{id}','Compras\Documentos\PagoController@getPayDocument')->name('getPay.documentos');
        Route::get('pagos/create/{id}', 'Compras\Documentos\PagoController@create')->name('compras.documentos.pago.create');
        Route::post('pagos/store/', 'Compras\Documentos\PagoController@store')->name('compras.documentos.pago.store');
        Route::get('pagos/destroy/{id}', 'Compras\Documentos\PagoController@destroy')->name('compras.documentos.pago.destroy');
        Route::get('pagos/show/{id}', 'Compras\Documentos\PagoController@show')->name('compras.documentos.pago.show');
        Route::get('getBox/document/{id}', 'Compras\Documentos\PagoController@getBox')->name('compras.documentos.pago.getBox');

        //Pago Transferencia
        Route::get('transferencia/pagos/index/{id}', 'Compras\Documentos\TransferenciaController@index')->name('compras.documentos.transferencia.pago.index');
        Route::get('transferencia/getPay/{id}','Compras\Documentos\TransferenciaController@getPay')->name('compras.documentos.transferencia.getPay');
        Route::get('transferencia/pagos/create/{id}', 'Compras\Documentos\TransferenciaController@create')->name('compras.documentos.transferencia.pago.create');
        Route::post('transferencia/pagos/store/', 'Compras\Documentos\TransferenciaController@store')->name('compras.documentos.transferencia.pago.store');
        Route::get('transferencia/pagos/destroy/', 'Compras\Documentos\TransferenciaController@destroy')->name('compras.documentos.transferencia.pago.destroy');
        Route::get('transferencia/pagos/show/', 'Compras\Documentos\TransferenciaController@show')->name('compras.documentos.transferencia.pago.show');
    });

    // Clientes
    Route::prefix('ventas/clientes')->group(function() {

        Route::get('/', 'Ventas\ClienteController@index')->name('ventas.cliente.index');
        Route::get('/getTable', 'Ventas\ClienteController@getTable')->name('ventas.cliente.getTable');
        Route::get('/registrar', 'Ventas\ClienteController@create')->name('ventas.cliente.create');
        Route::post('/registrar', 'Ventas\ClienteController@store')->name('ventas.cliente.store');
        Route::post('/registrarFast', 'Ventas\ClienteController@storeFast')->name('ventas.cliente.storeFast');
        Route::get('/actualizar/{id}', 'Ventas\ClienteController@edit')->name('ventas.cliente.edit');
        Route::put('/actualizar/{id}', 'Ventas\ClienteController@update')->name('ventas.cliente.update');
        Route::get('/datos/{id}', 'Ventas\ClienteController@show')->name('ventas.cliente.show');
        Route::get('/destroy/{id}', 'Ventas\ClienteController@destroy')->name('ventas.cliente.destroy');
        Route::post('/getDocumento', 'Ventas\ClienteController@getDocumento')->name('ventas.cliente.getDocumento');
        Route::post('/getCustomer', 'Ventas\ClienteController@getCustomer')->name('ventas.cliente.getcustomer');
        //Tiendas
        Route::get('tiendas/index/{id}', 'Ventas\TiendaController@index')->name('clientes.tienda.index');
        Route::get('tiendas/getShop/{id}','Ventas\TiendaController@getShop')->name('clientes.tienda.shop');
        Route::get('tiendas/create/{id}', 'Ventas\TiendaController@create')->name('clientes.tienda.create');
        Route::post('tiendas/store/', 'Ventas\TiendaController@store')->name('clientes.tienda.store');
        Route::put('tiendas/update/{id}', 'Ventas\TiendaController@update')->name('clientes.tienda.update');
        Route::get('tiendas/destroy/{id}', 'Ventas\TiendaController@destroy')->name('clientes.tienda.destroy');
        Route::get('tiendas/show/{id}', 'Ventas\TiendaController@show')->name('clientes.tienda.show');
        Route::get('tiendas/actualizar/{id}', 'Ventas\TiendaController@edit')->name('clientes.tienda.edit');
    });
    // Cotizaciones
    Route::prefix('ventas/cotizaciones')->group(function() {
        Route::get('/', 'Ventas\CotizacionController@index')->name('ventas.cotizacion.index');
        Route::get('/getTable', 'Ventas\CotizacionController@getTable')->name('ventas.cotizacion.getTable');
        Route::get('/registrar', 'Ventas\CotizacionController@create')->name('ventas.cotizacion.create');
        Route::post('/registrar', 'Ventas\CotizacionController@store')->name('ventas.cotizacion.store');
        Route::get('/actualizar/{id}', 'Ventas\CotizacionController@edit')->name('ventas.cotizacion.edit');
        Route::put('/actualizar/{id}', 'Ventas\CotizacionController@update')->name('ventas.cotizacion.update');
        Route::get('/datos/{id}', 'Ventas\CotizacionController@show')->name('ventas.cotizacion.show');
        Route::get('/destroy/{id}', 'Ventas\CotizacionController@destroy')->name('ventas.cotizacion.destroy');
        Route::get('reporte/{id}','Ventas\CotizacionController@report')->name('ventas.cotizacion.reporte');
        Route::get('email/{id}','Ventas\CotizacionController@email')->name('ventas.cotizacion.email');
        Route::get('documento/{id}','Ventas\CotizacionController@document')->name('ventas.cotizacion.documento');
        Route::get('nuevodocumento/{id}','Ventas\CotizacionController@newdocument')->name('ventas.cotizacion.nuevodocumento');
    });

    // Documentos - cotizaciones
    Route::prefix('ventas/documentos')->group(function(){

        Route::get('index', 'Ventas\DocumentoController@index')->name('ventas.documento.index');
        Route::get('getDocument','Ventas\DocumentoController@getDocument')->name('ventas.getDocument');
        Route::get('create', 'Ventas\DocumentoController@create')->name('ventas.documento.create');
        Route::post('store', 'Ventas\DocumentoController@store')->name('ventas.documento.store');
        Route::get('edit/{id}','Ventas\DocumentoController@edit')->name('ventas.documento.edit');
        Route::put('update/{id}', 'Ventas\DocumentoController@update')->name('ventas.documento.update');
        Route::get('destroy/{id}', 'Ventas\DocumentoController@destroy')->name('ventas.documento.destroy');
        Route::get('show/{id}','Ventas\DocumentoController@show')->name('ventas.documento.show');
        Route::get('reporte/{id}','Ventas\DocumentoController@report')->name('ventas.documento.reporte');
        Route::get('tipoPago/{id}','Ventas\DocumentoController@TypePay')->name('ventas.documento.tipo_pago.existente');
        //Route::get('comprobante/{id}','Ventas\DocumentoController@voucher')->name('ventas.documento.comprobante');
        Route::get('xml/{id}','Ventas\DocumentoController@xml')->name('ventas.documento.xml');

        Route::post('cantidad', 'Ventas\DocumentoController@quantity')->name('ventas.documento.cantidad');
        Route::post('devolver/cantidad', 'Ventas\DocumentoController@returnQuantity')->name('ventas.documento.devolver.cantidades');
        Route::post('obtener/lote', 'Ventas\DocumentoController@returnLote')->name('ventas.documento.obtener.lote');
        Route::post('update/lote', 'Ventas\DocumentoController@updateLote')->name('ventas.documento.update.lote');

        Route::post('customers','Ventas\DocumentoController@customers')->name('ventas.customers');
        Route::post('customers-all','Ventas\DocumentoController@customers_all')->name('ventas.customers_all');
        Route::get('getLot/{id}','Ventas\DocumentoController@getLot')->name('ventas.getLot');
        Route::post('vouchersAvaible','Ventas\DocumentoController@vouchersAvaible')->name('ventas.vouchersAvaible');
    });
    //COMPROBANTES ELECTRONICOS
    Route::prefix('comprobantes/electronicos')->group(function(){
        Route::get('/', 'Ventas\Electronico\ComprobanteController@index')->name('ventas.comprobantes');
        Route::get('getVouchers','Ventas\Electronico\ComprobanteController@getVouchers')->name('ventas.getVouchers');
        Route::get('sunat/{id}','Ventas\Electronico\ComprobanteController@sunat')->name('ventas.documento.sunat');
    });

    //GUIAS DE REMISION
    Route::prefix('guiasremision/')->group(function(){

        Route::get('index', 'Ventas\GuiaController@index')->name('ventas.guiasremision.index');
        Route::get('getGuia','Ventas\GuiaController@getGuias')->name('ventas.getGuia');
        Route::get('create/{id}', 'Ventas\GuiaController@create')->name('ventas.guiasremision.create');
        Route::post('store', 'Ventas\GuiaController@store')->name('ventas.guiasremision.store');
        Route::put('update/{id}', 'Ventas\GuiaController@update')->name('ventas.guiasremision.update');
        Route::get('destroy/{id}', 'Ventas\GuiaController@destroy')->name('ventas.guiasremision.destroy');
        Route::get('show/{id}','Ventas\GuiaController@show')->name('ventas.guiasremision.show');
        Route::get('reporte/{id}','Ventas\GuiaController@report')->name('ventas.guiasremision.reporte');
        Route::get('tiendaDireccion/{id}', 'Ventas\GuiaController@tiendaDireccion')->name('ventas.guiasremision.tienda_direccion');
        Route::get('sunat/guia/{id}','Ventas\GuiaController@sunat')->name('ventas.guiasremision.sunat');
        // Route::get('tipoPago/{id}','Ventas\GuiaController@TypePay')->name('ventas.documento.tipo_pago.existente');
        // Route::get('comprobante/{id}','Ventas\GuiaController@voucher')->name('ventas.documento.comprobante');
        // Route::get('sunat/comprobante/{id}','Ventas\GuiaController@sunat')->name('ventas.documento.sunat');

    });

    //NOTAS DE CREDITO / DEBITO
    Route::prefix('notas/electronicos')->group(function(){
        Route::get('index/{id}', 'Ventas\Electronico\NotaController@index')->name('ventas.notas');
        Route::get('index_dev/{id}', 'Ventas\Electronico\NotaController@index_dev')->name('ventas.notas_dev');
        Route::get('create', 'Ventas\Electronico\NotaController@create')->name('ventas.notas.create');
        Route::post('store', 'Ventas\Electronico\NotaController@store')->name('ventas.notas.store');
        Route::get('getNotes/{id}','Ventas\Electronico\NotaController@getNotes')->name('ventas.getNotes');
        Route::get('getDetalles/{id}','Ventas\Electronico\NotaController@getDetalles')->name('ventas.getDetalles');
        Route::get('show/{id}','Ventas\Electronico\NotaController@show')->name('ventas.notas.show');
        Route::get('show_dev/{id}','Ventas\Electronico\NotaController@show_dev')->name('ventas.notas_dev.show');
        Route::get('sunat/{id}','Ventas\Electronico\NotaController@sunat')->name('ventas.notas.sunat');
    });

    //Caja
    Route::prefix('caja')->group(function () {
        Route::get('/index','Pos\CajaController@index')->name('Caja.index');
        Route::get('/getCajas','Pos\CajaController@getCajas')->name('Caja.getCajas');
        Route::post('/store','Pos\CajaController@store')->name('Caja.store');
        Route::post('/update/{id}','Pos\CajaController@update')->name('Caja.update');
        Route::get('/destroy/{id}','Pos\CajaController@destroy')->name('Caja.destroy');
        //-------------------------Movimientos de Caja -------------------------
        Route::get('index/movimiento','Pos\CajaController@indexMovimiento')->name('Caja.Movimiento.index');
        Route::get('getMovimientosCajas','Pos\CajaController@getMovimientosCajas')->name('Caja.get_movimientos_cajas');
        Route::post('aperturaCaja','Pos\CajaController@aperturaCaja')->name('Caja.apertura');
        Route::post('cerrarCaja','Pos\CajaController@cerrarCaja')->name('Caja.cerrar');
        Route::get('estadoCaja','Pos\CajaController@estadoCaja')->name('Caja.estado');
        Route::get('cajaDatosCierre','Pos\CajaController@cajaDatosCierre')->name('Caja.datos.cierre');
        Route::get('verificarEstadoUser','Pos\CajaController@verificarEstadoUser')->name('Caja.movimiento.verificarestado');
        Route::get('repoteMovimiento/{id}','Pos\CajaController@reporteMovimiento')->name('Caja.reporte.movimiento');
    });
    Route::prefix('egreso')->group(function () {
        Route::get('index','EgresoController@index')->name('Egreso.index');
        Route::get('getEgresos','EgresoController@getEgresos')->name('Egreso.getEgresos');
        Route::get('getEgreso','EgresoController@getEgreso')->name('Egreso.getEgreso');
        Route::post('store','Egresocontroller@store')->name('Egreso.store');
        Route::post('update/{id}','Egresocontroller@update')->name('Egreso.update');
        Route::get('destroy/{id}','Egresocontroller@destroy')->name('Egreso.destroy');
        Route::get('recibo/{size}','Egresocontroller@recibo')->name('Egreso.recibo');
    });

    Route::prefix('cuentaProveedor')->group(function () {
        Route::get('index','Compras\CuentaProveedorController@index')->name('cuentaProveedor.index');
        Route::get('getTable','Compras\CuentaProveedorController@getTable')->name('cuentaProveedor.getTable');
        Route::get('getDatos','Compras\CuentaProveedorController@getDatos')->name('cuentaProveedor.getDatos');
        Route::post('detallePago','Compras\CuentaProveedorController@detallePago')->name('cuentaProveedor.detallePago');
        Route::get('consulta','Compras\CuentaProveedorController@consulta')->name('cuentaProveedor.consulta');
        Route::get('reporte/{id}','Compras\CuentaProveedorController@reporte')->name('cuentaProveedor.reporte');
        Route::get('imagen/{id}','Compras\CuentaProveedorController@imagen')->name('cuentaProveedor.imagen');
    });

    Route::prefix('cuentaCliente')->group(function () {
        Route::get('index','Ventas\CuentaClienteController@index')->name('cuentaCliente.index');
        Route::get('getTable','Ventas\CuentaClienteController@getTable')->name('cuentaCliente.getTable');
        Route::get('getDatos','Ventas\CuentaClienteController@getDatos')->name('cuentaCliente.getDatos');
        Route::post('detallePago/{id}','Ventas\CuentaClienteController@detallePago')->name('cuentaCliente.detallePago');
        Route::get('consulta','Ventas\CuentaClienteController@consulta')->name('cuentaCliente.consulta');
        Route::get('reporte/{id}','Ventas\CuentaClienteController@reporte')->name('cuentaCliente.reporte');
        Route::get('imagen/{id}','Ventas\CuentaClienteController@imagen')->name('cuentaCliente.imagen');
    });

    Route::prefix('modeloExcel')->group(function(){
        Route::get('cliente','ModeloExcelController@cliente')->name('ModeloExcel.cliente');
        Route::get('categoria','ModeloExcelController@categoria')->name('ModeloExcel.categoria');
        Route::get('marca','ModeloExcelController@marca')->name('ModeloExcel.marca');
        Route::get('producto','ModeloExcelController@producto')->name('ModeloExcel.producto');
        Route::get('proveedor','ModeloExcelController@proveedor')->name('ModeloExcel.proveedor');
    });

    Route::prefix('importExcel')->group(function(){
        Route::post('cliente','ImportExcelController@uploadcliente')->name('ImportExcel.uploadcliente');
        Route::post('categoria','ImportExcelController@uploadcategoria')->name('ImportExcel.uploadcategoria');
        Route::post('marca','ImportExcelController@uploadmarca')->name('ImportExcel.uploadmarca');
        Route::post('producto','ImportExcelController@uploadproducto')->name('ImportExcel.uploadproducto');
        Route::post('proveedor','ImportExcelController@uploadproveedor')->name('ImportExcel.uploadproveedor');
    });

    // Cosultas - Ventas - Documentos
    Route::prefix('consultas/ventas/documentos')->group(function(){

        Route::get('index', 'Consultas\Ventas\DocumentoController@index')->name('consultas.ventas.documento.index');
        Route::post('getTable','Consultas\Ventas\DocumentoController@getTable')->name('consultas.ventas.documento.getTable');

    });

    // Cosultas - Ventas - Documentos - NO
    Route::prefix('consultas/ventas/documentos-no')->group(function(){

        Route::get('index', 'Consultas\Ventas\NoEnviadosController@index')->name('consultas.ventas.documento.no.index');
        Route::post('getTable','Consultas\Ventas\NoEnviadosController@getTable')->name('consultas.ventas.documento.no.getTable');
        Route::get('edit/{id}','Consultas\Ventas\NoEnviadosController@edit')->name('consultas.ventas.documento.no.edit');
        Route::post('update/{id}','Consultas\Ventas\NoEnviadosController@update')->name('consultas.ventas.documento.no.update');

        Route::get('getLot','Consultas\Ventas\NoEnviadosController@getLot')->name('consultas.ventas.documento.no.getLot');
        Route::post('cantidad/', 'Consultas\Ventas\NoEnviadosController@quantity')->name('consultas.ventas.documento.no.cantidad');
        Route::post('devolver/cantidad', 'Consultas\Ventas\NoEnviadosController@returnQuantity')->name('consultas.ventas.documento.no.devolver.cantidades');
        Route::post('devolver/cantidadedit', 'Consultas\Ventas\NoEnviadosController@returnQuantityEdit')->name('consultas.ventas.documento.no.devolver.cantidadesedit');
        Route::post('devolver/lotesinicio', 'Consultas\Ventas\NoEnviadosController@returnQuantityLoteInicio')->name('consultas.ventas.documento.no.devolver.lotesinicio');
        Route::post('obtener/lote', 'Consultas\Ventas\NoEnviadosController@returnLote')->name('consultas.ventas.documento.no.obtener.lote');
        Route::post('update/lote/edit', 'Consultas\Ventas\NoEnviadosController@updateLote')->name('consultas.ventas.documento.no.update.lote');

    });

    // Cosultas - Ventas - Cotizaciones
    Route::prefix('consultas/ventas/cotizaciones')->group(function(){

        Route::get('index', 'Consultas\Ventas\CotizacionController@index')->name('consultas.ventas.cotizacion.index');
        Route::post('getTable','Consultas\Ventas\CotizacionController@getTable')->name('consultas.ventas.cotizacion.getTable');

    });

     // Cosultas - Compras - Ordenes
     Route::prefix('consultas/compras/cotizaciones')->group(function(){

        Route::get('index', 'Consultas\Compras\OrdenController@index')->name('consultas.compras.orden.index');
        Route::post('getTable','Consultas\Compras\OrdenController@getTable')->name('consultas.compras.orden.getTable');

    });

    // Cosultas - Compras - Documentos
    Route::prefix('consultas/compras/documentos')->group(function(){

        Route::get('index', 'Consultas\Compras\DocumentoController@index')->name('consultas.compras.documento.index');
        Route::post('getTable','Consultas\Compras\DocumentoController@getTable')->name('consultas.compras.documento.getTable');

    });

     // Cosultas - Cuentas - Proveedores
     Route::prefix('consultas/cuentas/proveedores')->group(function(){

        Route::get('index', 'Consultas\Cuentas\ProveedorController@index')->name('consultas.cuentas.proveedor.index');
        Route::post('getTable','Consultas\Cuentas\ProveedorController@getTable')->name('consultas.cuentas.proveedor.getTable');

    });

     // Cosultas - Cuentas - Clientes
     Route::prefix('consultas/cuentas/clientes')->group(function(){

        Route::get('index', 'Consultas\Cuentas\ClienteController@index')->name('consultas.cuentas.cliente.index');
        Route::post('getTable','Consultas\Cuentas\ClienteController@getTable')->name('consultas.cuentas.cliente.getTable');

    });

     // Cosultas - Notas - Salida
     Route::prefix('consultas/notas/salidad')->group(function(){

        Route::get('index', 'Consultas\Notas\SalidadController@index')->name('consultas.notas.salidad.index');
        Route::post('getTable','Consultas\Notas\SalidadController@getTable')->name('consultas.notas.salidad.getTable');

    });

     // Cosultas - Notas - Ingreso
     Route::prefix('consultas/notas/ingreso')->group(function(){

        Route::get('index', 'Consultas\Notas\IngresoController@index')->name('consultas.notas.ingreso.index');
        Route::post('getTable','Consultas\Notas\IngresoController@getTable')->name('consultas.notas.ingreso.getTable');

    });

    // Cosultas - Kardex - Producto
    Route::prefix('consultas/kardex/producto')->group(function(){

        Route::get('index', 'Consultas\Kardex\ProductoController@index')->name('consultas.kardex.producto.index');
        Route::post('getTable','Consultas\Kardex\ProductoController@getTable')->name('consultas.kardex.producto.getTable');

    });


    // Cosultas - Kardex - Salida -Ventas
    Route::prefix('consultas/kardex/salidas')->group(function(){

        Route::get('index-V', 'Consultas\Kardex\SalidaController@ventas')->name('consultas.kardex.ventas.index');
        Route::post('getTableVentas','Consultas\Kardex\SalidaController@getTableVentas')->name('consultas.kardex.ventas.getTable');

        Route::get('index-N', 'Consultas\Kardex\SalidaController@notas')->name('consultas.kardex.notas.index');
        Route::post('getTableNotas','Consultas\Kardex\SalidaController@getTableNotas')->name('consultas.kardex.notas.getTable');

    });

    // Cosultas - Caja - Utilidad
    Route::prefix('consultas/caja/utilidad')->group(function(){

        Route::get('index', 'Consultas\Caja\UtilidadController@index')->name('consultas.caja.utilidad.index');
        Route::post('getTable','Consultas\Caja\UtilidadController@getTable')->name('consultas.caja.utilidad.getTable');

    });
});

Route::get('ventas/documentos/comprobante/{id}','Ventas\DocumentoController@voucher')->name('ventas.documento.comprobante');

Route::get('ruta', function () {
    //https://www.oratlas.com/lector-online-de-texto

    return ventas_x_mes();
    return '<h1>SISCOM</h1>';
    $dif = (int)(8-9);

    $doc = Documento::find(3);
    //$json_data = json_encode($doc->getCdrResponse,false);
    //$json_data = json_decode($json_data, false);
    $json_data = json_decode($doc->getCdrResponse, false);
    return $json_data->code;
});
