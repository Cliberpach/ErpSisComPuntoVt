<li class="nav-header">
    <div class="dropdown profile-element">
        @if (auth()->user()->ruta_imagen)
            <img alt="image" alt="{{ auth()->user()->name }}" class="rounded-circle" height="48" width="48"
                src="{{ Storage::url(auth()->user()->ruta_imagen) }}" />
        @else
            <img alt="{{ auth()->user()->name }}" alt="{{ auth()->user()->name }}" class="rounded-circle" height="48"
                width="48" src="{{ asset('img/default.jpg') }}" />

        @endif
        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
            <span class="block m-t-xs font-bold">{{ auth()->user()->name }}</span>
            <span class="text-muted text-xs block">Administrador <b class="caret"></b></span>
        </a>
        <ul class="dropdown-menu animated fadeInRight m-t-xs">
            <li><a class="dropdown-item" href="login.html">Cerrar Sesión</a></li>
        </ul>
    </div>
    <div class="logo-element">
        <img src="{{ asset('img/default.png') }}" height="30" width="45">
    </div>
</li>

<li>
    <a href="{{ route('home') }}"><i class="fa fa-th-large"></i> <span class="nav-label">Panel de
            control</span></a>
</li>

@can('restore', [Auth::user(),['caja.index','movimiento_caja.index','egreso.index']])
<li class="@yield('caja-chica-active')">
    <a href="#"><i class="fa fa-archive"></i> <span class="nav-label">Caja Chica</span><span
            class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        @can('haveaccess', 'caja.index')
        <li class="@yield('caja-active')"><a href="{{ route('Caja.index') }}"><i class="fa fa-archive"></i>Cajas</a></li>
        @endcan
        @can('haveaccess', 'movimiento_caja.index')
        <li class="@yield('caja-movimiento-active')"><a href="{{ route('Caja.Movimiento.index') }}"><i class="fa fa-registered"></i> Apertura y Cierre Caja</a></li>
        @endcan
        @can('haveaccess', 'egreso.index')
        <li class="@yield('egreso-active')"> <a href="{{ route('Egreso.index') }}"><i class="fa fa-arrow-right"></i> Egreso</a></li>
        @endcan
    </ul>
</li>
@endcan

@can('restore', [Auth::user(),['proveedor.index','orden.index','documento_compra.index']])
<li class="@yield('compras-active')">
    <a href="#"><i class="fa fa-shopping-cart"></i> <span class="nav-label">Compras</span><span
            class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        @can('haveaccess', 'proveedor.index')
        <li class="@yield('proveedor-active')"><a href="{{ route('compras.proveedor.index') }}">Proveedores</a></li>
        @endcan
        @can('haveaccess', 'orden.index')
        <li class="@yield('orden-compra-active')"><a href="{{ route('compras.orden.index') }}">Orden Compra</a></li>
        @endcan
        @can('haveaccess', 'documento_compra.index')
        <li class="@yield('documento-active')"><a href="{{ route('compras.documento.index') }}">Doc. Compra</a></li>
        @endcan
    </ul>
</li>
@endcan

@can('restore', [Auth::user(),['cliente.index','cotizacion.index','documento_venta.index','guia.index']])
<li class="@yield('ventas-active')">
    <a href="#"><i class="fa fa-signal"></i> <span class="nav-label">Ventas</span><span
            class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        @can('haveaccess', 'cliente.index')
        <li class="@yield('clientes-active')"><a href="{{ route('ventas.cliente.index') }}">Clientes</a></li>
        @endcan
        @can('haveaccess', 'cotizacion.index')
        <li class="@yield('cotizaciones-active')"><a href="{{ route('ventas.cotizacion.index') }}">Cotizaciones</a></li>
        @endcan
        @can('haveaccess', 'documento_venta.index')
        <li class="@yield('documento-active')"><a href="{{ route('ventas.documento.index') }}">Doc. Venta</a></li>
        @endcan
        @can('haveaccess', 'guia.index')
        <li class="@yield('guias-remision-active')"><a href="{{ route('ventas.guiasremision.index') }}">Guias de Remision</a></li>
        @endcan
    </ul>
</li>
@endcan

@can('restore', [Auth::user(),['almacen.index','categoria.index','marca.index','producto.index','nota_ingreso.index','nota_salida.index']])
<li class="@yield('almacenes-active')">
    <a href="#"><i class="fa fa-bar-chart-o"></i> <span class="nav-label">Almacén</span><span
            class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        @can('haveaccess', 'almacen.index')
        <li class="@yield('almacen-active')"><a href="{{ route('almacenes.almacen.index') }}">Almacén</a></li>
        @endcan
        @can('haveaccess', 'categoria.index')
        <li class="@yield('categoria-active')"><a href="{{ route('almacenes.categorias.index') }}">Categoria</a></li>
        @endcan
        @can('haveaccess', 'marca.index')
        <li class="@yield('marca-active')"><a href="{{ route('almacenes.marcas.index') }}">Marca</a></li>
        @endcan
        @can('haveaccess', 'producto.index')
        <li class="@yield('producto-active')"><a href="{{ route('almacenes.producto.index') }}">Producto</a></li>
        @endcan
        @can('haveaccess', 'nota_ingreso.index')
        <li class="@yield('nota_ingreso-active')"><a href="{{ route('almacenes.nota_ingreso.index') }}">Nota Ingreso</a></li>
        @endcan
        @can('haveaccess', 'nota_salida.index')
        <li class="@yield('nota_salidad-active')"><a href="{{ route('almacenes.nota_salidad.index') }}">Nota Salida</a></li>
        @endcan
    </ul>
</li>
@endcan

@can('restore', [Auth::user(),['cuenta_proveedor.index','cuenta_cliente.index']])
<li class="@yield('cuentas-active')">
    <a href="#"><i class="fa fa-money"></i> <span class="nav-label">Cuentas </span><span
            class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        @can('haveaccess', 'cuenta_proveedor.index')
        <li class="@yield('cuenta-proveedor-active')"><a href="{{ route('cuentaProveedor.index') }}"><span class="nav-label">Proveedor</span></a></li>
        @endcan
        @can('haveaccess', 'cuenta_cliente.index')
        <li class="@yield('cuenta-cliente-active')"><a href="{{ route('cuentaCliente.index') }}"><span class="nav-label">Cliente</span></a></li>
        @endcan
    </ul>
</li>
@endcan

<li class="@yield('consulta-active')">
    <a href="#"><i class="fa fa-question-circle"></i> <span class="nav-label">Consulta </span><span
            class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        <li class="@yield('consulta-comprobantes-active')"><a href="{{ route('consultas.documento.index') }}">Documentos</a></li>
        <li class="@yield('consulta-ventas-active')">
            <a href="#">Ventas <span class="fa arrow"></span></a>
            <ul class="nav nav-third-level">
                <li class="@yield('consulta-ventas-cotizacion-active')"><a href="{{ route('consultas.ventas.cotizacion.index') }}">Cotización</a></li>
                <li class="@yield('consulta-ventas-documento-active')"><a href="{{ route('consultas.ventas.documento.index') }}">Doc. Venta</a></li>
                <li class="@yield('consulta-ventas-documento-no-active')"><a href="{{ route('consultas.ventas.documento.no.index') }}">No enviados</a></li>
            </ul>
        </li>
        <li class="@yield('consulta-compras-active')">
            <a href="#">Compras <span class="fa arrow"></span></a>
            <ul class="nav nav-third-level">
                <li class="@yield('consulta-compras-orden-active')"><a href="{{ route('consultas.compras.orden.index') }}">Orden de Compra</a></li>
                <li class="@yield('consulta-compras-documento-active')"><a href="{{ route('consultas.compras.documento.index') }}">Doc. Compras</a></li>
            </ul>
        </li>
        <li class="@yield('cuenta_proveedor-active')"><a href="{{ route('consultas.cuentas.proveedor.index') }}">Cuenta Proveedor</a></li>
        <li class="@yield('cuenta_cliente-active')"><a href="{{ route('consultas.cuentas.cliente.index') }}">Cuenta Cliente</a></li>
        <li class="@yield('nota_salida_consulta-active')"><a href="{{ route('consultas.notas.salidad.index') }}">Nota Salida</a></li>
        <li class="@yield('nota_ingreso_consulta-active')"><a href="{{ route('consultas.notas.ingreso.index') }}">Nota Ingreso</a></li>
        <li class="@yield('utilidad_bruta-active')"><a href="{{ route('consultas.caja.utilidad.index') }}">Utilidad Bruta</a></li>
    </ul>
</li>

<li class="@yield('reporte-active')">
    <a href="#"><i class="fa fa-money"></i> <span class="nav-label">Reporte </span><span
            class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        <li class="@yield('caja_diaria-active')"><a href="#">Caja Diaria</a></li>
        <li class="@yield('ventas_reporte-active')"><a href="#">Ventas</a></li>
        <li class="@yield('compras_reporte-active')"><a href="#">Compras</a></li>
        <li class="@yield('nota_salida_reporte-active')"><a href="#">Nota Salida</a></li>
        <li class="@yield('nota_ingreso_reporte-active')"><a href="#">Nota Ingreso</a></li>
        <li class="@yield('cuentas_x_cobrar_reporte-active')"><a href="#">Cuentas por Cobrar</a></li>
        <li class="@yield('cuentas_x_pagar_reporte-active')"><a href="#">Cuentas por Pagar</a></li>
        <li class="@yield('stock_valorizado_reporte-active')"><a href="#">Stock Valorizado</a></li>
    </ul>
</li>

<li class="@yield('kardex-active')">
    <a href="#"><i class="fa fa-exclamation"></i> <span class="nav-label">Kardex</span><span
            class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        <li class="@yield('proveedor_kardex-active')"><a href="#">Proveedor</a></li>
        <li class="@yield('cliente_kardex-active')"><a href="#">Cliente</a></li>
        <li class="@yield('producto_kardex-active')"><a href="{{ route('consultas.kardex.producto.index') }}">Producto</a></li>
        <li class="@yield('salida-kardex-active')">
            <a href="#">Salidas <span class="fa arrow"></span></a>
            <ul class="nav nav-third-level">
                <li class="@yield('salida-ventas-active')"><a href="{{ route('consultas.kardex.ventas.index') }}">Ventas</a></li>
                <li class="@yield('salida-notas-active')"><a href="{{ route('consultas.kardex.notas.index') }}">Salidas</a></li>
            </ul>
        </li>
    </ul>
</li>


@can('restore', [Auth::user(),['colaborador.index','vendedor.index','empresa.index','tabla.index']])
<li class="@yield('mantenimiento-active')">
    <a href="#"><i class="fa fa-cogs"></i> <span class="nav-label">Mantenimento</span><span
            class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        @can('haveaccess', 'colaborador.index')
        <li class="@yield('colaboradores-active')"><a href="{{ route('mantenimiento.colaborador.index') }}">Colaboradores</a></li>
        @endcan
        @can('haveaccess', 'vendedor.index')
        <li class="@yield('vendedores-active')"><a href="{{ route('mantenimiento.vendedor.index') }}">Vendedores</a></li>
        @endcan
        @can('haveaccess', 'empresa.index')
        <li class="@yield('empresas-active')"><a href="{{ route('mantenimiento.empresas.index') }}">Empresas</a></li>
        @endcan
        @can('haveaccess', 'tabla.index')
        <li class="@yield('tablas-active')"><a href="{{ route('mantenimiento.tabla.general.index') }}">Tablas Generales</a></li>
        @endcan
    </ul>
</li>
@endcan

@can('restore', [Auth::user(),['user.index','role.index']])
<li class="@yield('seguridad-active')">
    <a href="#"><i class="fa fa-key"></i> <span class="nav-label">Seguridad</span><span
        class="fa arrow"></span></a>
    <ul class="nav nav-second-level collapse">
        @can('haveaccess', 'user.index')
        <li class="@yield('users-active')"><a href="{{ route('user.index') }}">Usuarios</a></li>
        @endcan
        @can('haveaccess', 'role.index')
        <li class="@yield('roles-active')"><a href="{{ route('role.index') }}">Roles</a></li>
        @endcan
    </ul>
</li>

@endcan
