@extends('layout') @section('content')
<div class="wrapper wrapper-content">
    <div class="row">
            <div class="col-lg-3">
                <div class="ibox ">
                    <div class="ibox-title">
                        <span class="label label-success float-right">{{ mes() }}</span>
                        <h5>Ventas</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">{{ ventas_mensual() }}</h1>
                        <div class="stat-percent font-bold text-success d-none">98% <i class="fa fa-bolt"></i></div>
                        <small>Total de ventas:</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="ibox ">
                    <div class="ibox-title">
                        <span class="label label-info float-right">{{ mes() }}</span>
                        <h5>Compras</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">{{ compras_mensual() }}</h1>
                        <div class="stat-percent font-bold text-info d-none">20% <i class="fa fa-level-up"></i></div>
                        <small>Total de Compras:</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="ibox ">
                    <div class="ibox-title">
                        <span class="label label-primary float-right">{{ mes() }}</span>
                        <h5>Cuentas Cobrar</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">{{ cuentas_cobrar() }}</h1>
                        <div class="stat-percent font-bold text-navy d-none">44% <i class="fa fa-level-up"></i></div>
                        <small>Cuentas Cobrar total:</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="ibox ">
                    <div class="ibox-title">
                        <span class="label label-danger float-right">{{ mes() }}</span>
                        <h5>Cuentas Pagar</h5>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">{{ cuentas_pagar() }}</h1>
                        <div class="stat-percent font-bold text-danger d-none">38% <i class="fa fa-level-down"></i></div>
                        <small>Cuentas Pagar total:</small>
                    </div>
                </div>
    </div>
</div>

<div class="row">
            <div class="col-lg-12">

                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>Ventas</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>

                        </div>
                    </div>

                    <div class="ibox-content">
                        <div class="row">
                        <div class="col-lg-9 text-center">

                            <h2><b>Ventas por departamentos</b></h2>

                            <div class="flot-chart">
                                <div class="flot-chart-content" id="ventas_mensuales"></div>
                            </div>


                        </div>
                        <div class="col-lg-3">
                            <ul class="stat-list">
                                <li>
                                    <h2 class="no-margins">2,346</h2>
                                    <small>Total orders in period</small>
                                    <div class="stat-percent">48% <i class="fa fa-level-up text-navy"></i></div>
                                    <div class="progress progress-mini">
                                        <div style="width: 48%;" class="progress-bar"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="no-margins ">4,422</h2>
                                    <small>Orders in last month</small>
                                    <div class="stat-percent">60% <i class="fa fa-level-down text-navy"></i></div>
                                    <div class="progress progress-mini">
                                        <div style="width: 60%;" class="progress-bar"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="no-margins ">9,180</h2>
                                    <small>Monthly income from orders</small>
                                    <div class="stat-percent">22% <i class="fa fa-bolt text-navy"></i></div>
                                    <div class="progress progress-mini">
                                        <div style="width: 22%;" class="progress-bar"></div>
                                    </div>
                                </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>


            </div>

            <div class="col-lg-12">
                <div class="ibox ">

                    <div class="ibox-title">
                        <h5>Compras</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>

                        </div>
                    </div>

                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-9 text-center">
                                <h2><b>Compras por Categoria</b></h2>
                                <div class="flot-chart">
                                    <div class="flot-chart-content" id="compras_mensuales"></div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <ul class="stat-list">
                                    <li>
                                        <h2 class="no-margins">2,346</h2>
                                        <small>Total orders in period</small>
                                        <div class="stat-percent">48% <i class="fa fa-level-up text-navy"></i></div>
                                        <div class="progress progress-mini">
                                            <div style="width: 48%;" class="progress-bar"></div>
                                        </div>
                                    </li>
                                    <li>
                                        <h2 class="no-margins ">4,422</h2>
                                        <small>Orders in last month</small>
                                        <div class="stat-percent">60% <i class="fa fa-level-down text-navy"></i></div>
                                        <div class="progress progress-mini">
                                            <div style="width: 60%;" class="progress-bar"></div>
                                        </div>
                                    </li>
                                    <li>
                                        <h2 class="no-margins ">9,180</h2>
                                        <small>Monthly income from orders</small>
                                        <div class="stat-percent">22% <i class="fa fa-bolt text-navy"></i></div>
                                        <div class="progress progress-mini">
                                            <div style="width: 22%;" class="progress-bar"></div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>



            </div>

            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>Producción</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>

                        </div>
                    </div>

                    <div class="ibox-content">
                        <div class="row">

                            <div class="col-lg-9">
                                <div class="flot-chart">
                                    <div class="flot-chart-content" id="flot-dashboard-chart"></div>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <ul class="stat-list">
                                    <li>
                                        <h2 class="no-margins">2,346</h2>
                                        <small>Total orders in period</small>
                                        <div class="stat-percent">48% <i class="fa fa-level-up text-navy"></i></div>
                                        <div class="progress progress-mini">
                                            <div style="width: 48%;" class="progress-bar"></div>
                                        </div>
                                    </li>
                                    <li>
                                        <h2 class="no-margins ">4,422</h2>
                                        <small>Orders in last month</small>
                                        <div class="stat-percent">60% <i class="fa fa-level-down text-navy"></i></div>
                                        <div class="progress progress-mini">
                                            <div style="width: 60%;" class="progress-bar"></div>
                                        </div>
                                    </li>
                                    <li>
                                        <h2 class="no-margins ">9,180</h2>
                                        <small>Monthly income from orders</small>
                                        <div class="stat-percent">22% <i class="fa fa-bolt text-navy"></i></div>
                                        <div class="progress progress-mini">
                                            <div style="width: 22%;" class="progress-bar"></div>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>Cuentas por Cobrar</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>

                        </div>

                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-9 text-center">
                                <h2><b>Cuentas por cobrar en departamentos</b></h2>
                                <div class="flot-chart">
                                    <div class="flot-chart-content" id="cuentas_mensuales"></div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <ul class="stat-list">
                                    <li>
                                        <h2 class="no-margins">2,346</h2>
                                        <small>Total orders in period</small>
                                        <div class="stat-percent">48% <i class="fa fa-level-up text-navy"></i></div>
                                        <div class="progress progress-mini">
                                            <div style="width: 48%;" class="progress-bar"></div>
                                        </div>
                                    </li>
                                    <li>
                                        <h2 class="no-margins ">4,422</h2>
                                        <small>Orders in last month</small>
                                        <div class="stat-percent">60% <i class="fa fa-level-down text-navy"></i></div>
                                        <div class="progress progress-mini">
                                            <div style="width: 60%;" class="progress-bar"></div>
                                        </div>
                                    </li>
                                    <li>
                                        <h2 class="no-margins ">9,180</h2>
                                        <small>Monthly income from orders</small>
                                        <div class="stat-percent">22% <i class="fa fa-bolt text-navy"></i></div>
                                        <div class="progress progress-mini">
                                            <div style="width: 22%;" class="progress-bar"></div>
                                        </div>
                                    </li>
                                </ul>

                            </div>

                        </div>
                    </div>

                </div>
            </div>



</div>


@stop
@push('styles')
<link href="{{asset('Inspinia/css/plugins/morris/morris-0.4.3.min.css')}}" rel="stylesheet">
@endpush
@push('scripts')

<script src="{{asset('Inspinia/js/plugins/morris/raphael-2.1.0.min.js')}}"></script>
<script src="{{asset('Inspinia/js/plugins/morris/morris.js')}}"></script>

<script>
    new Morris.Bar({

    element: 'ventas_mensuales',

    data: [
        { departamento: 'Amazonas', clientes: 20 },
        { departamento: 'Ancash', clientes: 10 },
        { departamento: 'Apurimac', clientes: 5 },
        { departamento: 'Arequipa', clientes: 5 },
        { departamento: 'Ayacucho', clientes: 20 },
        { departamento: 'Cajamarca', clientes: 20 },
        { departamento: 'Callao', clientes: 20 },
        { departamento: 'Huancavelica', clientes: 10 },
        { departamento: 'Huanuco', clientes: 5 },
        { departamento: 'Ica', clientes: 5 },
        { departamento: 'La Libertad', clientes: 20 },
        { departamento: 'Lambayaque', clientes: 20 },

        { departamento: 'Lima', clientes: 20 },
        { departamento: 'Loreto', clientes: 20 },
        { departamento: 'Madre de Dios', clientes: 10 },
        { departamento: 'Moquegua', clientes: 5 },
        { departamento: 'Pasco', clientes: 5 },
        { departamento: 'Piura', clientes: 20 },
        { departamento: 'Puno', clientes: 20 },

        { departamento: 'San Martin', clientes: 5 },
        { departamento: 'Tacna', clientes: 20 },
        { departamento: 'Tumbes', clientes: 20 },
        { departamento: 'Ucayali', clientes: 20 },
    ],
    xkey: 'departamento',

    ykeys: ['clientes'],

    labels: ["Ventas"],
    hideHover: 'auto',
    resize: true,
    barColors: ['#1ab394'],
    });
</script>

<script>
    new Morris.Bar({

    element: 'cuentas_mensuales',

    data: [
        { departamento: 'Amazonas', deuda: 50000 },
        { departamento: 'Ancash', deuda: 10000 },
        { departamento: 'Apurimac', deuda: 50000 },
        { departamento: 'Arequipa', deuda: 50000 },
        { departamento: 'Ayacucho', deuda: 20000 },
        { departamento: 'Cajamarca', deuda: 20000 },
        { departamento: 'Callao', deuda: 20000 },
        { departamento: 'Huancavelica', deuda: 10000 },
        { departamento: 'Huanuco', deuda: 50000 },
        { departamento: 'Ica', deuda: 50000 },
        { departamento: 'La Libertad', deuda: 20000 },
        { departamento: 'Lambayaque', deuda: 20000 },

        { departamento: 'Lima', deuda: 20000 },
        { departamento: 'Loreto', deuda: 20000 },
        { departamento: 'Madre de Dios', deuda: 10000 },
        { departamento: 'Moquegua', deuda: 50000 },
        { departamento: 'Pasco', deuda: 50000 },
        { departamento: 'Piura', deuda: 30000 },
        { departamento: 'Puno', deuda: 20000 },

        { departamento: 'San Martin', deuda: 50000 },
        { departamento: 'Tacna', deuda: 20000 },
        { departamento: 'Tumbes', deuda: 20000 },
        { departamento: 'Ucayali', deuda: 20000 },
    ],
    xkey: 'departamento',

    ykeys: ['deuda'],

    labels: ["deuda"],
    hideHover: 'auto',
    resize: true,
    barColors: ['#1ab394'],
    });
</script>

<script>
    new Morris.Bar({

    element: 'compras_mensuales',

    data: [
        { categoria: 'Sist. Digestivo', productos: 50000 },
        { categoria: 'Sist. Inmunológico', productos: 10000 },
        { categoria: 'Sist. Urinario', productos: 50000 },
        { categoria: 'Sist. Circulatorio', productos: 50000 },
        { categoria: 'Sist. Glandular', productos: 20000 },
        { categoria: 'Sist. Estructural', productos: 20000 },
        { categoria: 'Sist. Nervioso', productos: 20000 },
        { categoria: 'Sist. Respiratorio', productos: 10000 },
        { categoria: 'Nutrición', productos: 50000 },
        { categoria: 'Energizantes', productos: 50000 },
        { categoria: 'Control de Peso', productos: 20000 },
        { categoria: 'Belleza', productos: 20000 },
    ],
    xkey: 'categoria',

    ykeys: ['productos'],

    labels: ["productos"],
    hideHover: 'auto',
    resize: true,
    barColors: ['#1ab394'],
    });
</script>

@endpush
