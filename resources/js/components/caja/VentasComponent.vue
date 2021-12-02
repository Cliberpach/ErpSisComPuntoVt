<template>
    <div>
        <div class="row wrapper border-bottom white-bg page-heading">
            <div class="col-12">
                <h2 style="text-transform:uppercase">
                    <b>Listado de Documentos de Venta de Hoy</b>
                </h2>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a @click="irHome()">Panel de Control</a>
                    </li>
                    <li class="breadcrumb-item active">
                        <strong>Documentos de Ventas</strong>
                    </li>
                </ol>
            </div>
        </div>

        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox ">
                        <div class="ibox-content">
                            <div class="table-responsive">
                                <table
                                    class="table dataTables-documento table-striped table-bordered table-hover"
                                    style="text-transform:uppercase"
                                >
                                    <thead>
                                        <tr>
                                            <th
                                                colspan="2"
                                                class="text-center"
                                            ></th>
                                            <th colspan="5" class="text-center">
                                                DOCUMENTO DE VENTA
                                            </th>
                                            <th colspan="4" class="text-center">
                                                FORMAS DE PAGO
                                            </th>
                                            <th
                                                colspan="4"
                                                class="text-center"
                                            ></th>
                                        </tr>
                                        <tr>
                                            <th style="display:none;"></th>
                                            <th class="text-center">C.O</th>
                                            <th class="text-center"># DOC</th>
                                            <th class="text-center">
                                                FECHA DOC.
                                            </th>
                                            <th class="text-center">TIPO</th>
                                            <th class="text-center">CLIENTE</th>
                                            <th class="text-center">MONTO</th>
                                            <th class="text-center">TRANSF.</th>
                                            <th class="text-center">OTROS</th>
                                            <th class="text-center">EFECT.</th>
                                            <th class="text-center">TIEMPO</th>
                                            <th class="text-center">ESTADO</th>
                                            <th class="text-center">SUNAT</th>
                                            <th class="text-center">
                                                DESCARGAS
                                            </th>
                                            <th class="text-center">
                                                ACCIONES
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="modal inmodal"
            id="modal_descargas_pdf"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content animated bounceInRight">
                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                        >
                            <span aria-hidden="true">&times;</span>
                            <span class="sr-only">Close</span>
                        </button>
                        <h4 class="modal-title descarga-title"></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row justify-content-center">
                            <div class="col-12 col-md-6 text-center">
                                <div class="form-group">
                                    <button class="btn btn-info file-pdf">
                                        <i class="fa fa-file-pdf-o"></i></button
                                    ><br />
                                    <b>Descargar A4</b>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 text-center">
                                <div class="form-group">
                                    <button class="btn btn-info file-ticket">
                                        <i class="fa fa-file-o"></i></button
                                    ><br />
                                    <b>Descargar Ticket</b>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm"
                                    data-dismiss="modal"
                                >
                                    <i class="fa fa-times"></i> Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal inmodal" id="modal_ventas" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content animated bounceInRight">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                            <span class="sr-only">Close</span>
                        </button>
                        <h4 class="modal-title">VENTAS PENDIENTES DE PAGO</h4>
                        <small class="font-bold ventas-title"></small>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table dataTables-ventas-pendientes table-striped table-bordered table-hover">
                                        <thead>
                                            <th>TIPO DOC</th>
                                            <th># DOC</th>
                                            <th>FECHA DOC</th>
                                            <th>MONTO</th>
                                            <th><i class="fa fa-dashboard"></i></th>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-6 text-left">
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="modal inmodal" id="modal_pago" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content animated bounceInRight">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                            <span class="sr-only">Close</span>
                        </button>
                        <h4 class="modal-title pago-title"></h4>
                        <small class="font-bold pago-subtitle"></small>
                    </div>
                    <div class="modal-body">
                        <form action="" id="pago_venta" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-12 col-md-6 br">
                                    <div class="form-group d-none">
                                        <label class="col-form-label required">Venta</label>
                                        <input type="text" class="form-control" id="venta_id" name="venta_id" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label required">Tipo Pago</label>
                                        <input type="text" class="form-control" id="tipo_pago_id" name="tipo_pago_id" v-model="form.tipo_pago_id" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label required">Monto</label>
                                        <input type="text" class="form-control" id="monto_venta" name="monto_venta" onkeypress="return filterFloat(event, this);" readonly>
                                    </div>
                                    <div class="form-group|">
                                        <label class="col-form-label required">Efectivo</label>
                                        <input type="text" value="0.00" class="form-control" id="efectivo" name="efectivo" onkeypress="return filterFloat(event, this);" onkeyup="changeEfectivo(this)">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label required">Modo de pago</label>
                                        <v-select
                                            :options="desc_pagos"
                                            placeholder="SELECCIONAR"
                                            v-model="tipo_pago"
                                            @input="setSelectedPago"
                                        ></v-select>
                                    </div>
                                    <div class="form-group">
                                        <label  class="col-form-label required">Importe</label>
                                        <input type="text" class="form-control" id="importe" name="importe" onkeypress="return filterFloat(event, this);" onkeyup="changeImporte(this)">
                                    </div>
                                    <div class="form-group d-none" id="div_cuentas">
                                        <label class="col-form-label">Cuentas</label>
                                        <v-select
                                            :options="cuentas"
                                            placeholder="SELECCIONAR"
                                            v-model="cuenta"
                                            @input="setSelectedCuenta"
                                        ></v-select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label id="imagen_label">Imagen:</label>

                                        <div class="custom-file">
                                            <input id="imagen" type="file" name="imagen" class="custom-file-input"   accept="image/*">

                                            <label for="imagen" id="imagen_txt"
                                                class="custom-file-label selected">Seleccionar</label>

                                            <div class="invalid-feedback"><b><span id="error-imagen"></span></b></div>

                                        </div>
                                    </div>
                                    <div class="form-group row justify-content-center">
                                        <div class="col-6 align-content-center">
                                            <div class="row justify-content-end">
                                                <a href="javascript:void(0);" id="limpiar_imagen">
                                                    <span class="badge badge-danger">x</span>
                                                </a>
                                            </div>
                                            <div class="row justify-content-center">
                                                <p>
                                                    <img class="imagen" src="/img/default.png"
                                                        alt="">
                                                    <input id="url_imagen" name="url_imagen" type="hidden" value="">
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-6 text-left">
                            <i class="fa fa-exclamation-circle leyenda-required"></i> <small class="leyenda-required">Los campos marcados con asterisco (<label class="required"></label>) son obligatorios.</small>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="submit" class="btn btn-primary btn-sm" form="pago_venta"><i class="fa fa-save"></i> Guardar</button>
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import "datatables.net-bs4";
import "datatables.net-buttons-bs4";
export default {
    props: ["modospago"],
    data() {
        return {
            table: null,
            tableVentas: null,
            ventas: [],
            desc_pagos: [],
            cuentas: [],
            value_pagos: [],
            options: [],
            tipo_pago: null,
            cuenta: null,
            form: {
                venta_id: null,
                tipo_pago_id: null,
                importe: null,
                efectivo: null,
                monto_venta: null,
                cuenta_id: null,
                image: null,

            }
        };
    },
    mounted() {
        let $this = this;
        for(let i = 0; i < $this.modospago.length; i++)
        {
            let pago = {'code': $this.modospago[i].id, 'label': $this.modospago[i].descripcion}
            $this.desc_pagos.push(pago);
        }

        $this.loadTable();
        Echo.channel("ventasCaja").listen("VentasCajaEvent", e => {
            toastr.success("Nueva venta registrada");
            $this.actualizarTable();
        });
        $this.table.on('click','.pagar',function(){
            let data = $this.table.row($(this).closest("tr")).data();
            $('.ventas-title').html(data.cliente);
            $('#modal_ventas').modal('show');
            $this.initTableVentas(data.cliente_id, data.condicion_id);
        });

        $this.tableVentas.on('click','.pagar',function(){
            let data =$this.tableVentas.row($(this).closest("tr")).data();
            $('.pago-title').html(data.numero_doc);
            $this.form.monto_venta = data.total;
            $this.form.venta_id = data.id;
            $this.form.importe = data.total;
            $this.form.efectivo = '0.00';
            $('.pago-subtitle').html(data.cliente);

            $('#modal_pago').modal('show');
            $this.tipo_pago = 'EFECTIVO';
            $this.form.tipo_pago_id = '1';
            $this.initCuentas(data.empresa_id);
        });
    },
    methods: {
        irHome: function() {
            window.location = route("home");
        },
        loadTable: function() {
            this.table = $(".dataTables-documento").DataTable({
                dom: '<"html5buttons"B>lTfgitp',
                bPaginate: true,
                bLengthChange: true,
                bFilter: true,
                bInfo: true,
                bAutoWidth: false,
                processing: true,
                ajax: route("ventas.caja.getDocument"),
                columns: [
                    //DOCUMENTO DE VENTA
                    {
                        data: "cotizacion_venta",
                        className: "text-center letrapequeña",
                        visible: false
                    },

                    {
                        data: null,
                        className: "text-center letrapequeña",
                        render: function(data) {
                            if (data.cotizacion_venta) {
                                return "<input type='checkbox' disabled checked>";
                            } else {
                                return "<input type='checkbox' disabled>";
                            }
                        }
                    },

                    {
                        data: "numero_doc",
                        className: "text-center letrapequeña"
                    },

                    {
                        data: "fecha_documento",
                        className: "text-center letrapequeña"
                    },

                    {
                        data: "tipo_venta",
                        className: "text-center letrapequeña",
                        visible: false
                    },

                    {
                        data: "cliente",
                        className: "text-left letrapequeña"
                    },

                    {
                        data: "total",
                        className: "text-center letrapequeña"
                    },
                    {
                        data: "transferencia",
                        className: "text-center letrapequeña"
                    },

                    {
                        data: "otros",
                        className: "text-center letrapequeña"
                    },
                    {
                        data: "efectivo",
                        className: "text-center letrapequeña"
                    },
                    {
                        data: "dias",
                        className: "text-center letrapequeña"
                    },

                    {
                        data: null,
                        className: "text-center letrapequeña",
                        render: function(data) {
                            switch (data.estado) {
                                case "PENDIENTE":
                                    return (
                                        "<span class='badge badge-danger' d-block>" +
                                        data.estado +
                                        "</span>"
                                    );
                                    break;
                                case "PAGADA":
                                    return (
                                        "<span class='badge badge-primary verPago' style='cursor: pointer;' d-block>" +
                                        data.estado +
                                        "</span>"
                                    );
                                    break;
                                case "ADELANTO":
                                    return (
                                        "<span class='badge badge-success' d-block>" +
                                        data.estado +
                                        "</span>"
                                    );
                                    break;
                                case "DEVUELTO":
                                    return (
                                        "<span class='badge badge-warning' d-block>" +
                                        data.estado +
                                        "</span>"
                                    );
                                    break;
                                default:
                                    return (
                                        "<span class='badge badge-success' d-block>" +
                                        data.estado +
                                        "</span>"
                                    );
                            }
                        }
                    },

                    {
                        data: null,
                        className: "text-center letrapequeña",
                        render: function(data) {
                            switch (data.sunat) {
                                case "1":
                                    return "<span class='badge badge-primary' d-block>ACEPTADO</span>";
                                    break;
                                case "2":
                                    return "<span class='badge badge-danger' d-block>NULA</span>";
                                    break;
                                default:
                                    return "<span class='badge badge-success' d-block>REGISTRADO</span>";
                            }
                        }
                    },
                    {
                        data: null,
                        className: "text-center letrapequeña",
                        render: function(data) {
                            return (
                                "<button class='btn btn-info btn-pdf mb-1' title='PDF'>PDF</button>" +
                                "<button class='btn btn-info' onclick='xmlElectronico(" +
                                data.id +
                                ")' title='XML'>XML</button>"
                            );
                        }
                    },
                    {
                        data: null,
                        className: "text-center letrapequeña",
                        render: function(data) {
                            let cadena = "";
                            if (
                                data.condicion == "CONTADO" &&
                                data.estado == "PENDIENTE"
                            ) {
                                cadena =
                                    cadena +
                                    "<button type='button' class='btn btn-sm btn-primary m-1 pagar' @click='fnPagar("+data+")' title='Pagar'><i class='fa fa-money'></i> Pagar</button>";
                            } else {
                                cadena =
                                    cadena +
                                    "<button type='button' class='btn btn-sm btn-success m-1 verPago' title='Ver'><i class='fa fa-eye'></i> Ver Pago</button>";
                            }

                            return cadena;
                        }
                    }
                ],
                fnRowCallback: function(
                    nRow,
                    aData,
                    iDisplayIndex,
                    iDisplayIndexFull
                ) {
                    /*if (aData.sunat == 0 && aData.tipo_venta_id != 129) {
                        $('td', nRow).css('background-color', '#D6EAF8');
                    }

                    if (aData.sunat == 1 && aData.tipo_venta_id != 129) {
                        $('td', nRow).css('background-color', '#D1F2EB');
                    }*/

                    if (aData.notas > 0) {
                        $("td", nRow).css("background-color", "#FDEBD0");
                    }
                },
                language: {
                    url: window.location.origin + "/Spanish.json"
                },
                order: []
            });
            this.tableVentas = $('.dataTables-ventas-pendientes').DataTable({
                "bPaginate": true,
                "bLengthChange": true,
                "bFilter": true,
                "bInfo": true,
                "bAutoWidth": false,
                "data": this.ventas,
                "columns": [
                    //DOCUMENTO DE VENTA
                    {
                        data: 'tipo_venta',
                        className: "text-center letrapequeña",
                    },
                    {
                        data: 'numero_doc',
                        className: "text-center letrapequeña",
                    },
                    {
                        data: 'fecha_documento',
                        className: "text-center letrapequeña"
                    },
                    {
                        data: 'total',
                        className: "text-center letrapequeña",
                    },
                    {
                        data: null,
                        className: "text-center letrapequeña",
                        render: function(data) {

                            let cadena = '';

                            if(data.condicion == 'CONTADO' && data.estado == 'PENDIENTE')
                            {
                                cadena = cadena +
                                "<button type='button' class='btn btn-sm btn-primary m-1 pagar' title='Pagar'><i class='fa fa-money'></i> Pagar</button>";
                            }

                            return cadena;
                        }
                    }

                ],
                "language": {
                    "url": window.location.origin + "/Spanish.json"
                },
                "order": [],
            });
        },
        actualizarTable: function() {
            this.table.ajax.reload();
        },
        initTableVentas: function(cliente_id, condicion_id)
        {
            let $this = this;
            let timerInterval;
            Swal.fire({
                title: 'Cargando...',
                icon: 'info',
                customClass: {
                    container: 'my-swal'
                },
                timer: 10,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    Swal.stopTimer();
                    $.ajax({
                        dataType : 'json',
                        type : 'post',
                        url : route('ventas.caja.getDocumentClient'),
                        data : {'_token': $('input[name=_token]').val(), 'cliente_id': cliente_id, 'condicion_id': condicion_id},
                        success: function(response) {
                            if (response.success) {
                                $this.ventas = [];
                                $this.ventas = response.ventas;
                                $this.loadTableVentas();
                                timerInterval = 0;
                                Swal.resumeTimer();
                                //console.log(colaboradores);
                            } else {
                                Swal.resumeTimer();
                                $this.ventas = [];
                                $this.loadTableVentas();
                            }
                        }
                    });
                },
                willClose: () => {
                    clearInterval(timerInterval)
                }
            });
        },
        loadTableVentas: function()
        {
            this.tableVentas.clear();
            this.tableVentas.rows.add(this.ventas);
            this.tableVentas.draw();
        },
        initCuentas:function(empresa_id)
        {
            let $this = this;
            $this.cuentas = [];
            let timerInterval;
            Swal.fire({
                title: 'Cargando...',
                icon: 'info',
                customClass: {
                    container: 'my-swal'
                },
                timer: 10,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    Swal.stopTimer();
                    $.ajax({
                        dataType : 'json',
                        type : 'post',
                        url : route('ventas.documento.getCuentas'),
                        data : {'_token': $('input[name=_token]').val(), 'empresa_id': empresa_id},
                        success: function(response) {
                            if (response.success) {
                                if (response.cuentas.length > 0) {
                                    for(var i = 0;i < response.cuentas.length; i++)
                                    {
                                        let newOption = {'code': response.cuentas[i].id, 'label': response.cuentas[i].descripcion + ': ' + response.cuentas[i].num_cuenta};
                                        $this.cuentas.push(newOption);
                                    }

                                } else {
                                }
                                timerInterval = 0;
                                Swal.resumeTimer();
                            } else {
                                timerInterval = 0;
                                Swal.resumeTimer();
                            }
                        }
                    });
                },
                willClose: () => {
                    clearInterval(timerInterval)
                }
            });
        },
        setSelectedPago: function(value)
        {
            let $this = this;
            if(value != null)
            {
                $this.form.tipo_pago_id = value.code;
            }
            else
            {
                $this.form.tipo_pago_id = null;
            }
            // let monto = $('#monto_venta').val();
            // let importe = $('#importe').val();
            // let efectivo = $('#efectivo').val();
            // let suma = convertFloat(importe) + convertFloat(efectivo);
            // $('#cuenta_id').val('').trigger('change.select2');
            // $('#efectivo').attr('readonly', false);
            // $('#importe').attr('readonly', false);
            // if(b.value != '')
            // {
            //     let cadena = b.value.split('-');
            //     if(cadena[1] == 'EFECTIVO')
            //     {
            //         $('#efectivo').attr('readonly', true);
            //         $('#importe').attr('readonly', true);
            //         $('#efectivo').val('0.00');
            //         $('#importe').val(monto);
            //     }
            //     $('#tipo_pago_id').val(cadena[0]);

            //     if(cadena[1] == 'TRANSFERENCIA')
            //     {
            //         $('#div_cuentas').removeClass('d-none');
            //     }else{
            //         $('#div_cuentas').addClass('d-none');
            //     }
            // }else{
            //     $('#tipo_pago_id').val('');
            // }

        },
        setSelectedCuenta: function(value)
        {

        }
    },
    updated() {
        this.$nextTick(function () {

        });
    }
};
</script>
