<template>
     <div>
          <div class="wrapper wrapper-content animated fadeInRight">
               <div class="row">
                    <div class="col-lg-12">
                         <div class="ibox">
                              <div class="ibox-content">
                                   <div class="row">
                                        <div class="col-12">
                                             <form @submit.prevent="Guardar" id="enviar_orden">

                                                  <div class="row">
                                                       <div class="col-sm-6 b-r">
                                                            <h4 class=""><b>Documento de compra</b></h4>
                                                            <div class="row">
                                                                 <div class="col-md-12">
                                                                      <p>Modificar datos del documento de compra:</p>
                                                                 </div>
                                                            </div>

                                                            <div class="form-group row">

                                                                 <div class="col-lg-6 col-xs-12" id="fecha_documento">
                                                                      <label class="required">Fecha de Emisión</label>
                                                                      <div class="input-group date">
                                                                           <span class="input-group-addon">
                                                                                <i class="fa fa-calendar"></i>
                                                                           </span>

                                                                           <v-picker v-model="formulario.fecha_emision"
                                                                                :config="configDate"
                                                                                class="form-control" readonly />
                                                                      </div>
                                                                 </div>

                                                                 <div class="col-lg-6 col-xs-12" id="fecha_entrega">
                                                                      <label class="required">Fecha de Entrega</label>
                                                                      <div class="input-group date">
                                                                           <span class="input-group-addon">
                                                                                <i class="fa fa-calendar"></i>
                                                                           </span>
                                                                           <v-picker v-model="formulario.fecha_entrega"
                                                                                :config="configDate"
                                                                                class="form-control" readonly />
                                                                      </div>
                                                                 </div>
                                                            </div>

                                                            <div class="form-group">
                                                                 <label class="required">Empresa: </label>
                                                                 <select class="select2_form form-control"
                                                                      name="empresa_id" id="empresa_id" ref="empresa_id"
                                                                      disabled>
                                                                      <option></option>
                                                                 </select>
                                                            </div>

                                                            <hr>

                                                            <div class="row">
                                                                 <div class="col-md-12">
                                                                      <p>Modificar Proveedor:</p>
                                                                 </div>
                                                            </div>


                                                            <div class="form-group">
                                                                 <label class="required">Ruc / Dni: </label>

                                                                 <select class="select2_form form-control"
                                                                      style="text-transform: uppercase; width:100%"
                                                                      ref="proveedor_id" value="" name="proveedor_id"
                                                                      id="proveedor_id">
                                                                      <option></option>
                                                                 </select>

                                                            </div>

                                                            <div class="form-group">
                                                                 <label class="required">Razon Social: </label>
                                                                 <select class="select2_form form-control"
                                                                      style="text-transform: uppercase; width:100%"
                                                                      ref="proveedor_razon" value=""
                                                                      name="proveedor_razon" id="proveedor_razon">
                                                                      <option></option>

                                                                 </select>
                                                            </div>




                                                       </div>

                                                       <div class="col-sm-6">


                                                            <div class="form-group row">
                                                                 <div class="col-md-6">
                                                                      <label class="required">Condición: </label>
                                                                      <select id="condicion_id"
                                                                           v-model="formulario.condicion_id"
                                                                           name="condicion_id" class="form-control"
                                                                           ref="condicion_id">
                                                                           <option></option>

                                                                      </select>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="required">Moneda: </label>
                                                                      <select class="select2_form form-control"
                                                                           v-model="formulario.moneda"
                                                                           style="text-transform: uppercase; width:100%"
                                                                           value="" name="moneda" id="moneda"
                                                                           ref="moneda">
                                                                           <option></option>
                                                                      </select>


                                                                 </div>


                                                            </div>

                                                            <div class="form-group row">
                                                                 <div class="col-md-6">
                                                                      <label class="required">Tipo: </label>
                                                                      <select class="form-control"
                                                                           v-model="formulario.tipo_compra"
                                                                           name="tipo_compra" ref="tipo_compra"
                                                                           id="tipo_compra">
                                                                           <option></option>
                                                                      </select>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <div class="row">
                                                                           <div class="col-12 col-md-4">
                                                                                <label class="required"
                                                                                     id="serie_comprobante">Serie:
                                                                                </label>
                                                                                <input type="text" id="serie_tipo"
                                                                                     name="serie_tipo"
                                                                                     v-model="formulario.serie_tipo"
                                                                                     class="form-control"
                                                                                     :disabled="this.formulario.tipo_compra ? false : true">
                                                                           </div>
                                                                           <div class="col-12 col-md-8">
                                                                                <label class="required"
                                                                                     id="numero_comprobante">Nº:
                                                                                </label>
                                                                                <input type="text" id="numero_tipo"
                                                                                     name="numero_tipo"
                                                                                     v-model="formulario.numero_tipo"
                                                                                     class="form-control"
                                                                                     :disabled="this.formulario.tipo_compra ? false : true">
                                                                           </div>
                                                                      </div>
                                                                 </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                 <div class="col-md-6">
                                                                      <label class="" id="campo_tipo_cambio">Tipo de
                                                                           Cambio (S/.):</label>
                                                                      <input type="text" id="tipo_cambio"
                                                                           name="tipo_cambio"
                                                                           v-model="formulario.tipo_cambio"
                                                                           class="form-control">
                                                                 </div>

                                                                 <div class="col-md-6">
                                                                      <label id="igv_requerido">IGV (%):</label>
                                                                      <div class="input-group">
                                                                           <div class="input-group-prepend">
                                                                                <span class="input-group-addon">
                                                                                     <div
                                                                                          class="custom-control custom-checkbox">
                                                                                          <input type="checkbox"
                                                                                               class="custom-control-input"
                                                                                               id="igv_check"
                                                                                               v-model="formulario.igv_check"
                                                                                               name="igv_check">
                                                                                          <label
                                                                                               class="custom-control-label"
                                                                                               for="igv_check"></label>
                                                                                     </div>
                                                                                </span>

                                                                           </div>
                                                                           <input type="text" value="" maxlength="3"
                                                                                v-model="formulario.igv"
                                                                                class="form-control" name="igv" id="igv"
                                                                                :disabled="!formulario.igv_check">
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="form-group">
                                                                 <label>Observación:</label>
                                                                 <textarea type="text" v-model="formulario.observacion"
                                                                      placeholder="" class="form-control"
                                                                      name="observacion" id="observacion"
                                                                      value=""></textarea>
                                                            </div>
                                                       </div>
                                                  </div>
                                             </form>
                                        </div>
                                   </div>
                                   <hr>
                                   <!-- DETALLE -->
                                   <v-detalle :fecha_5="formulario.fecha_5" :documento="documento" :detalles="detalles"
                                        @reloadChange="updatedDetalle" :edit="formulario" />

                                   <div class="hr-line-dashed"></div>
                                   <div class="form-group row">

                                        <div class="col-md-6 text-left" style="color:#fcbc6c">
                                             <i class="fa fa-exclamation-circle"></i> <small>Los campos marcados con
                                                  asterisco
                                                  (<label class="required"></label>) son obligatorios.</small>
                                        </div>

                                        <div class="col-md-6 text-right">
                                             <a href="/compras/documentos/index" id="btn_cancelar" class="btn btn-w-m btn-default">
                                                  <i class="fa fa-arrow-left"></i> Regresar
                                             </a>
                                             <button type="submit" id="btn_grabar" form="enviar_orden"
                                                  class="btn btn-w-m btn-primary">
                                                  <i class="fa fa-save"></i> Grabar
                                             </button>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </div>
               </div>
          </div>
     </div>
</template>
<script>
import { Spanish } from "flatpickr/dist/l10n/es"
import detalleCompraVue from './detalleCompra.vue';

export default {
     props: ['id'],
     components: {
          'v-detalle': detalleCompraVue
     },
     data() {
          return {
               documento: null,
               detalles: null,
               formulario: {
                    fecha_emision: $fechaActual,
                    fecha_entrega: $fechaActual,
                    condicion_id: null,
                    moneda: null,
                    tipo_compra: null,
                    serie_tipo: null,
                    numero_tipo: null,
                    tipo_cambio: null,
                    igv_check: false,
                    igv: null,
                    empresa_id: null,
                    proveedor_id: null,
                    proveedor_razon: null,
                    observacion: null,
                    fecha_5: null
               },
               dataCondicion: [],
               dataTipoMoneda: [],
               dataTipoCompra: [],
               dataEmpresas: [],
               dataProveedores: [],
               select2: {
                    condicion: null,
                    monedas: null,
                    tipo_compra: null,
                    empresa: null,
                    proveedor: null,
                    razon_social: null
               },
               itemDetalle: null,
               configDate: {
                    dateFormat: 'd/m/Y',
                    locale: Spanish
               },
          }
     },
     watch: {
          dataCondicion() {
               this.dataCondicion.forEach(item => {
                    let option = new Option(`${item.descripcion} ${item.dias > 0 ? item.dias + ' dias' : ''}`, item.id, false, false);
                    this.select2.condicion.append(option).trigger("change");
               });
               this.select2.condicion.on("select2:select", (e) => {
                    const { id } = e.params.data;
                    this.formulario.condicion_id = id;
                    alert("sds");
               });

               this.select2.condicion.on("select2:unselect", (e) => {
                    this.formulario.condicion_id = null;
               });
               this.select2.condicion.val(this.formulario.condicion_id).trigger("change.select2");
          },
          dataTipoMoneda() {
               this.dataTipoMoneda.forEach(item => {
                    let opt = new Option(`${item.simbolo}-${item.descripcion}`, item.descripcion, false, false);
                    this.select2.monedas.append(opt);
               });
               this.select2.monedas.on("select2:select", (e) => {
                    const { id } = e.params.data;
                    this.formulario.moneda = id;
                    this.$nextTick(this.cambioMoneda);
                    // await ;
               });

               this.select2.monedas.on("select2:unselect", (e) => {
                    this.formulario.moneda = null;
               });
               this.select2.monedas.val(this.formulario.moneda).trigger("change.select2");
          },
          dataTipoCompra() {
               this.dataTipoCompra.forEach(item => {
                    let opt = new Option(item.descripcion, item.descripcion, false, false);
                    this.select2.tipo_compra.append(opt).trigger("change");
               });

               this.select2.tipo_compra.on("select2:select", (e) => {
                    const { id } = e.params.data;
                    this.formulario.tipo_compra = id;
               });

               this.select2.tipo_compra.on("select2:unselect", (e) => {
                    this.formulario.tipo_compra = null;
               });
               this.select2.tipo_compra.val(this.formulario.tipo_compra).trigger("change.select2");
          },
          dataEmpresas() {
               this.dataEmpresas.forEach(item => {
                    let opt = new Option(item.razon_social, item.id);
                    this.select2.empresa.append(opt).trigger("change");
               });
               this.select2.empresa.val(this.formulario.empresa_id).trigger("change.select2");
          },
          dataProveedores() {
               this.dataProveedores.forEach(item => {
                    let opt = new Option(item.ruc, item.id, false, false);
                    this.select2.proveedor.append(opt).trigger("change");
                    let opt1 = new Option(item.descripcion, item.id, false, false);
                    this.select2.razon_social.append(opt1).trigger("change");
               });

               this.select2.proveedor.on("select2:select", (e) => {
                    const { id } = e.params.data;
                    this.formulario.proveedor_id = Number(id);
                    this.select2.razon_social.val(id).trigger("change.select2");
               });

               this.select2.proveedor.on("select2:unselect", (e) => {
                    this.formulario.proveedor_id = null;
                    this.select2.razon_social.val(null).trigger("change.select2");
               });

               this.select2.proveedor.val(this.formulario.proveedor_id).trigger("change.select2");

               //Razon Social
               this.select2.razon_social.on("select2:select", (e) => {
                    const { id } = e.params.data;
                    this.formulario.proveedor_id = Number(id);
                    this.select2.proveedor.val(id).trigger("change.select2");
               });

               this.select2.razon_social.on("select2:unselect", (e) => {
                    this.formulario.proveedor_id = null;
                    this.select2.proveedor.val(null).trigger("change.select2");
               });

               this.select2.razon_social.val(this.formulario.proveedor_id).trigger("change.select2");
          },
          formulario: {
               handler(value) {
                    if (value && this.documento) {
                         this.documento.igv_check = value.igv_check;
                         this.documento.igv = value.igv;
                         this.documento.moneda = value.moneda;
                         if (!value.igv_check) {
                              value.igv = null;
                              this.documento.igv = null;
                         } else {
                              if (value.igv_check && !value.igv) {
                                   this.documento.igv = 18;
                                   value.igv = 18;
                              } else {
                                   this.documento.igv = Number(value.igv);
                              }
                         }

                    }
               },
               deep: true
          },
     },
     methods: {
          async getTables() {
               try {
                    const { data } = await axios.get(route("compras.documento.getTables", { id: this.id }));
                    const { condiciones, tipos_moneda, tipo_compra, documento, empresas, proveedores, fecha_5, detalles } = data;
                    let fecha_emision = documento.fecha_emision.replaceAll("-", "/").split("/");
                    let fecha_entrega = documento.fecha_entrega.replaceAll("-", "/").split("/");
                    this.formulario.fecha_emision = `${fecha_emision[2]}/${fecha_emision[1]}/${fecha_emision[0]}`;
                    this.formulario.fecha_entrega = `${fecha_entrega[2]}/${fecha_entrega[1]}/${fecha_entrega[0]}`;
                    this.formulario.condicion_id = documento.condicion_id;
                    this.formulario.moneda = documento.moneda;
                    this.formulario.tipo_compra = documento.tipo_compra;
                    this.formulario.serie_tipo = documento.serie_tipo;
                    this.formulario.numero_tipo = documento.numero_tipo;
                    this.formulario.igv_check = !!Number(documento.igv_check);
                    this.formulario.igv = documento.igv;
                    this.formulario.tipo_cambio = documento.tipo_cambio;
                    this.formulario.empresa_id = documento.empresa_id;
                    this.formulario.proveedor_id = documento.proveedor_id;
                    this.formulario.fecha_5 = fecha_5;
                    this.documento = documento;
                    this.detalles = detalles;
                    this.dataEmpresas = empresas;
                    this.dataCondicion = condiciones;
                    this.dataTipoMoneda = tipos_moneda;
                    this.dataTipoCompra = tipo_compra;
                    this.dataProveedores = proveedores;
                    this.documento.igv_check = !!Number(documento.igv_check);
               } catch (ex) {

               }
          },
          comboSelected() {
               let options = {
                    placeholder: "SELECCIONAR",
                    allowClear: true,
                    height: '200px',
                    width: '100%',
                    minimumResultsForSearch: -1
               };

               let opt = {
                    placeholder: "SELECCIONAR",
                    allowClear: true,
                    height: '200px',
                    width: '100%',
               };

               let condicion_id = $(this.$refs.condicion_id).select2(options);
               let moneda_id = $(this.$refs.moneda).select2(options);
               let tipo_compra = $(this.$refs.tipo_compra).select2(options);
               let empresa_id = $(this.$refs.empresa_id).select2(options);
               let proveedor_id = $(this.$refs.proveedor_id).select2(opt);
               let proveedor_razon = $(this.$refs.proveedor_razon).select2(opt);

               this.select2.razon_social = proveedor_razon;
               this.select2.proveedor = proveedor_id;
               this.select2.condicion = condicion_id;
               this.select2.monedas = moneda_id;
               this.select2.tipo_compra = tipo_compra;
               this.select2.empresa = empresa_id;
          },
          async cambioMoneda() {
               if (this.formulario.moneda == 'DOLARES') {
                    const { data } = await axios.get(route("compras.orden.dolar"));
                    this.formulario.tipo_cambio = data.venta;
               }
               else {
                    this.formulario.tipo_cambio = null;
               }
          },
          updatedDetalle(detalle) {
               this.itemDetalle = detalle;
          },
          async Guardar() {

               let timerInterval;
               Swal.fire({
                    title: 'Actualizando...',
                    icon: 'info',
                    customClass: {
                         container: 'my-swal'
                    },
                    timer: 10,
                    allowOutsideClick: false,
                    didOpen: async () => {
                         Swal.showLoading();
                         Swal.stopTimer();
                         try {
                              let formulario = Object.assign(this.formulario, this.itemDetalle);
                              const { data } = await axios.put(route('compras.documento.update', { id: this.documento.id }), formulario);
                              const { success, message } = data;
                              if (!success)
                                   throw message;
                              timerInterval = 0;
                              Swal.resumeTimer();
                              Swal.fire({
                                   title: '¡Actualizado!',
                                   text: message,
                                   icon: 'success',
                                   showConfirmButton: true,
                                   allowOutsideClick: false,
                              }).then(({ isConfirmed }) => {
                                   if (isConfirmed) {
                                        window.location = route('compras.documento.index');
                                   }
                              });

                         } catch (ex) {
                              Swal.resumeTimer();
                              toastr.error(ex, 'Actualizacion');
                         }

                    }
               });

          },
          pickers() {
               window.addEventListener("load", () => {

               });
          }

     },
     mounted() {
          this.comboSelected();
          this.getTables();
          this.pickers();
     }
}
</script>