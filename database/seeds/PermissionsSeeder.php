<?php

use Illuminate\Database\Seeder;
use App\Permission\Model\Role;
use App\Permission\Model\Permission;
use App\User;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $useradmin = User::find(1);

        $roleadmin = Role::create([
            'name'=>'ADMIN',
            'slug'=>'ADMIN',
            'description'=>'Administrador',
            'full-access'=>'SI'
        ]);

        Role::create([
            'name'=>'USER',
            'slug'=>'USER',
            'description'=>'USER',
            'full-access'=>'NO'
        ]);

        $useradmin->roles()->sync([$roleadmin->id]);

        //User permission

        Permission::create([
            'name'  => 'Listar Usuarios',
            'slug'=>'user.index',
            'description'=>'El usuario puede listar usuarios'
        ]);


        Permission::create([
            'name'  => 'Crear Usuario',
            'slug'=>'user.create',
            'description'=>'El usuario puede crear usuarios'
        ]);


        Permission::create([
            'name'  => 'Editar Usuario',
            'slug'=>'user.edit',
            'description'=>'El usuario puede editar usuarios'
        ]);


        Permission::create([
            'name'  => 'Ver Usuario',
            'slug'=>'user.show',
            'description'=>'El usuario puede ver usuarios'
        ]);


        Permission::create([
            'name'  => 'Editar mi Usuario',
            'slug'=>'userown.edit',
            'description'=>'El usuario puede editar su propio usuario'
        ]);


        Permission::create([
            'name'  => 'Ver mi Usuario',
            'slug'=>'userown.show',
            'description'=>'El usuario puede ver su propio usuario'
        ]);


        Permission::create([
            'name'  => 'Eliminar Usuario',
            'slug'=>'user.delete',
            'description'=>'El usuario puede eliminar usuarios'
        ]);


        //Roles permission

        Permission::create([
            'name'  => 'Mantenedor Roles',
            'slug'=>'role.index',
            'description'=>'El usuario puede acceder al mantenedor de Roles'
        ]);

        //Almacenes permission
        
        Permission::create([
            'name'  => 'Mantenedor Almacén',
            'slug'=>'almacen.index',
            'description'=>'El usuario puede acceder al mantenedor de Almacenes'
        ]);

        //Categoria permission
        
        Permission::create([
            'name'  => 'Mantenedor Categoria',
            'slug'=>'categoria.index',
            'description'=>'El usuario puede acceder al mantenedor de Categorias'
        ]);

        //Kardex producto permission
        
        Permission::create([
            'name'  => 'Consulta kardex producto',
            'slug'=>'kardex_producto.index',
            'description'=>'El usuario puede acceder a la consulta de Kardex producto'
        ]);

        //Lote Produco permission
        
        Permission::create([
            'name'  => 'Consulta Lote Producto',
            'slug'=>'lote_producto.index',
            'description'=>'El usuario puede acceder a la consulta de Lote Productos'
        ]);

        //Marca permission
        
        Permission::create([
            'name'  => 'Mantenedor Marca',
            'slug'=>'marca.index',
            'description'=>'El usuario puede acceder al mantenedor de Marcas'
        ]);

        //Producto permission
        
        Permission::create([
            'name'  => 'Mantenedor Producto',
            'slug'=>'producto.index',
            'description'=>'El usuario puede acceder al mantenedor de Productos'
        ]);

        //Nota Ingreso permission
        
        Permission::create([
            'name'  => 'Mantenedor Notas de Ingreso',
            'slug'=>'nota_ingreso.index',
            'description'=>'El usuario puede acceder al mantenedor de Notas de Ingreso'
        ]);

        //Nota Salida permission
        
        Permission::create([
            'name'  => 'Mantenedor Notas de Salida',
            'slug'=>'nota_salida.index',
            'description'=>'El usuario puede acceder al mantenedor de Notas de Salida'
        ]);

        //Tipo Cliente permission
        
        Permission::create([
            'name'  => 'Mantenedor Tipos de Cliente',
            'slug'=>'tipo_cliente.index',
            'description'=>'El usuario puede acceder al mantenedor de Tipos de Cliente'
        ]);

        //Orden compra permission
        
        Permission::create([
            'name'  => 'Mantenedor Ordenes de Compra',
            'slug'=>'orden_compra.index',
            'description'=>'El usuario puede acceder al mantenedor de Ordenes de Compra'
        ]);

        //Documento de compra permission
        
        Permission::create([
            'name'  => 'Mantenedor Documentos de compra',
            'slug'=>'documento_compra.index',
            'description'=>'El usuario puede acceder al mantenedor de Documentos de Compra'
        ]);

        //Proveedor permission
        
        Permission::create([
            'name'  => 'Mantenedor Proveedores',
            'slug'=>'proveedor.index',
            'description'=>'El usuario puede acceder al mantenedor de Proveedores'
        ]);

        //Cuenta proveedor permission
        
        Permission::create([
            'name'  => 'Mantenedor Cuentas de proveedor',
            'slug'=>'cuenta_proveedor.index',
            'description'=>'El usuario puede acceder al mantenedor de Cuentas de Proveedor'
        ]);

        //Colaborador permission
        
        Permission::create([
            'name'  => 'Mantenedor Colaboradores',
            'slug'=>'colaborador.index',
            'description'=>'El usuario puede acceder al mantenedor de Colaboradores'
        ]);

        //Empresa permission
        
        Permission::create([
            'name'  => 'Mantenedor Empresas',
            'slug'=>'empresa.index',
            'description'=>'El usuario puede acceder al mantenedor de Empresas'
        ]);

        //Personas permission
        
        Permission::create([
            'name'  => 'Mantenedor de Personas',
            'slug'=>'persona.index',
            'description'=>'El usuario puede acceder al mantenedor de Personas'
        ]);

        //Tabla permission
        
        Permission::create([
            'name'  => 'Mantenedor Tablas General',
            'slug'=>'tabla.index',
            'description'=>'El usuario puede acceder al mantenedor de Tablas Generales'
        ]);

        //Vendedor permission
        
        Permission::create([
            'name'  => 'Mantenedor Vendedores',
            'slug'=>'vendedor.index',
            'description'=>'El usuario puede acceder al mantenedor de Vendedores'
        ]);

        //Caja permission
        
        Permission::create([
            'name'  => 'Mantenedor Cajas',
            'slug'=>'caja.index',
            'description'=>'El usuario puede acceder al mantenedor de Cajas'
        ]);

        //Movimiento Caja permission
        
        Permission::create([
            'name'  => 'Listar Movimientos Caja',
            'slug'=>'movimiento_caja.index',
            'description'=>'El usuario puede acceder al mantenedor de Movimientos Caja'
        ]);

        Permission::create([
            'name'  => 'Aperturar Caja',
            'slug'=>'movimiento_caja.create',
            'description'=>'El usuario puede aperturar Caja'
        ]);

        //Cliente permission
        
        Permission::create([
            'name'  => 'Mantenedor Clientes',
            'slug'=>'cliente.index',
            'description'=>'El usuario puede acceder al mantenedor de Clientes'
        ]);

        //Cotizacion permission
        
        Permission::create([
            'name'  => 'Mantenedor Cotizaciones',
            'slug'=>'cotizacion.index',
            'description'=>'El usuario puede acceder al mantenedor de Notas de Salida'
        ]);

        //Egreso permission
        
        Permission::create([
            'name'  => 'Mantenedor Egresos',
            'slug'=>'egreso.index',
            'description'=>'El usuario puede acceder al mantenedor de Egresos'
        ]);

        //Documento Venta permission
        
        Permission::create([
            'name'  => 'Mantenedor Documentos Venta',
            'slug'=>'documento_venta.index',
            'description'=>'El usuario puede acceder al mantenedor de Dpcumentos de Venta'
        ]);

        //Cuenta Cliente permission
        
        Permission::create([
            'name'  => 'Mantenedor Cuentas Cliente',
            'slug'=>'cuenta_cliente.index',
            'description'=>'El usuario puede acceder al mantenedor de Cuentas Cliente'
        ]);

        //Guia permission
        
        Permission::create([
            'name'  => 'Mantenedor Guias de Remision',
            'slug'=>'guia.index',
            'description'=>'El usuario puede acceder al mantenedor de Guias de Remision'
        ]);

        //Nota Electronica permission
        
        Permission::create([
            'name'  => 'Mantenedor Notas Electronicas',
            'slug'=>'nota_electronica.index',
            'description'=>'El usuario puede acceder al mantenedor de Notas Electronicas'
        ]);
    }
}
