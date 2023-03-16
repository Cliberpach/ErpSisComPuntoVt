<template>
     <div>
          <div class="row">
               <div class="col-lg-12">
                    <div class="panel panel-primary" id="panel_detalle">
                         <div class="panel-heading">
                              <h4 class=""><b>Detalle de Documento de Compra</b></h4>
                         </div>
                         <div class="panel-body ibox-content" :class="{ 'sk-loading': loading }">
                              <div class="sk-spinner sk-spinner-wave">
                                   <div class="sk-rect1"></div>
                                   <div class="sk-rect2"></div>
                                   <div class="sk-rect3"></div>
                                   <div class="sk-rect4"></div>
                                   <div class="sk-rect5"></div>
                              </div>
                              <div class="row">
                                   <div class="col-lg-6 col-xs-12 b-r">
                                        <div class="form-group row align-items-end">
                                             <div class="col-10 col-md-10">
                                                  <label class="required">Producto:</label>
                                                  <select class="form-control" ref="producto_id"
                                                       style="text-transform: uppercase; width:100%" name="producto_id"
                                                       id="producto_id">

                                                  </select>
                                             </div>
                                             <div class="col-2 col-md-2">
                                                  <button type="button" @click.prevent="reloadProductos"
                                                       class="btn btn-secondary">
                                                       <i class="fa fa-refresh"></i>
                                                  </button>
                                             </div>
                                        </div>

                                        <div class="form-group row">
                                             <div class="col-md-6">
                                                  <label class="required">Costo Flete:</label>
                                                  <input type="text" id="costo_flete" name="costo_flete"
                                                       class="form-control" v-model="form.costo_flete"
                                                       @keydown="validDecimal">
                                                  <div class="invalid-feedback"><b><span
                                                                 id="error-costo-flete"></span></b>
                                                  </div>
                                             </div>
                                        </div>

                                   </div>

                                   <div class="col-lg-6 col-xs-12">
                                        <div class="form-group row">
                                             <div class="col-md-6">
                                                  <div class="form-group">
                                                       <label class="col-form-label required"
                                                            for="amount">Importe:</label>
                                                       <input type="text" id="precio" name="precio" class="form-control"
                                                            v-model="form.precio" @keydown="validDecimal">
                                                       <div class="invalid-feedback"><b><span
                                                                      id="error-precio"></span></b>
                                                       </div>
                                                  </div>
                                             </div>
                                             <div class="col-md-6">

                                                  <label class="col-form-label required">Cantidad:</label>
                                                  <input type="text" id="cantidad" class="form-control"
                                                       v-model="form.cantidad" @keydown="validKeyup">
                                                  <div class="invalid-feedback"><b><span id="error-cantidad"></span></b>
                                                  </div>


                                             </div>
                                        </div>
                                        <div class="row d-none">
                                             <div class="col-md-6" id="fecha_vencimiento_campo">
                                                  <label class="required">Fecha de
                                                       vencimiento:</label>
                                                  <div class="input-group date">
                                                       <span class="input-group-addon">
                                                            <i class="fa fa-calendar"></i>
                                                       </span>
                                                       <input type="text" id="fecha_vencimiento"
                                                            name="fecha_vencimiento" class="form-control"
                                                            autocomplete="off" readonly>
                                                       <div class="invalid-feedback"><b><span
                                                                      id="error-fecha_vencimiento"></span></b>
                                                       </div>

                                                  </div>
                                             </div>
                                             <div class="col-md-6">
                                                  <label class="required">Lote:</label>
                                                  <input type="text" id="lote" name="lote" class="form-control"
                                                       onkeypress="return mayus(this);">
                                                  <div class="invalid-feedback"><b><span id="error-lote"></span></b>
                                                  </div>
                                             </div>
                                        </div>
                                        <div class="form-group row">
                                             <div class="col-lg-6 col-xs-12">
                                                  <label class="col-form-label" for="amount">&nbsp;</label>
                                                  <a class="btn btn-block btn-success " @click.prevent="Limpiar"
                                                       style='color:white;'> <i class="fa fa-paint-brush"></i>
                                                       LIMPIAR
                                                  </a>
                                             </div>

                                             <div class="col-lg-6 col-xs-12">
                                                  <label class="col-form-label" for="amount">&nbsp;</label>
                                                  <button class="btn btn-block btn-warning enviar_producto"
                                                       style='color:white;' id="btn_enviar_detalle" @click="Agregar"> <i
                                                            class="fa fa-plus"></i>
                                                       AGREGAR
                                                  </button>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                              <hr>
                              <div class="table-responsive">
                                   <table class="table table-bordered table-hover table-sm"
                                        style="text-transform:uppercase">
                                        <thead>
                                             <tr>
                                                  <th style="width: 2%;" class="bg-success"></th>
                                                  <th class="text-center bg-success" style="width: 5%;">ACCIONES</th>
                                                  <th class="text-center bg-success" style="width: 5%;">CANTIDAD</th>
                                                  <th class="text-center bg-success" style="width: 38%;">PRODUCTO</th>
                                                  <th class="text-center bg-success" style="width: 10%;">FECHA. VENC
                                                  </th>
                                                  <th class="text-center bg-success" style="width: 15%;">COSTO FLETE
                                                  </th>
                                                  <th class="text-center bg-success" style="width: 15%;">PRECIO</th>
                                                  <th class="text-center bg-success" style="width: 15%;">TOTAL</th>
                                             </tr>
                                        </thead>
                                        <tbody>
                                             <template v-if="form.detalles.length > 0">
                                                  <template v-for="(item, index) in form.detalles">
                                                       <tr :key="index" v-if="!item.eliminado">
                                                            <td class="text-center">{{ index + 1 }}</td>
                                                            <td class="text-center">
                                                                 <div class="btn-group btn-group-sm" role="group"
                                                                      aria-label="Basic example">
                                                                      <button type="button" class="btn btn-primary"
                                                                           @click.prevent="editItem(item)">
                                                                           <i class="fa fa-edit"></i>
                                                                      </button>
                                                                      <button type="button" class="btn btn-danger"
                                                                           @click.prevent="eliminar(item)">
                                                                           <i class="fa fa-trash"></i>

                                                                      </button>
                                                                 </div>
                                                            </td>
                                                            <td class="text-center">{{ item.cantidad }}</td>
                                                            <td class="text-left">{{ item.descripcion }}</td>
                                                            <td class="text-center">{{ item.fecha_vencimiento }}</td>
                                                            <td class="text-center"> {{ item.costo_flete }}</td>
                                                            <td class="text-center">{{ simboloMoneda(item.precio, 3) }}
                                                            </td>
                                                            <td class="text-center">{{ simboloMoneda(item.importe, 2) }}
                                                            </td>
                                                       </tr>
                                                  </template>
                                             </template>

                                             <template v-else>
                                                  <tr>
                                                       <td colspan="8" class="text-center">
                                                            <strong>No hay items</strong>
                                                       </td>
                                                  </tr>
                                             </template>
                                        </tbody>
                                        <tfoot style="text-transform:uppercase">
                                             <tr>
                                                  <th colspan="7" style="text-align:right">
                                                       Sub Total:</th>
                                                  <th class="text-center">
                                                       <span id="subtotal">{{
                                                            simboloMoneda(form.monto_sub_total, 2)
                                                       }}</span>
                                                  </th>
                                             </tr>
                                             <tr>
                                                  <th colspan="7" class="text-right">IGV <span id="igv_int">
                                                            {{ form.igv_text }}</span>:
                                                  </th>
                                                  <th class="text-center">
                                                       <span id="igv_monto">{{
                                                            simboloMoneda(form.monto_total_igv, 2)
                                                       }}</span>
                                                  </th>

                                             </tr>
                                             <tr>
                                                  <th colspan="7" class="text-right">
                                                       Percepcion:</th>
                                                  <th class="text-center">
                                                       <div class="form-group">
                                                            <input type="text" class="form-control" value=""
                                                                 id="percepcion" v-model="form.monto_percepcion"
                                                                 onkeypress="return filterFloat(event, this, false);">
                                                       </div>
                                                  </th>
                                             </tr>
                                             <tr>
                                                  <th colspan="7" class="text-right">TOTAL:
                                                  </th>
                                                  <th class="text-center">
                                                       <span id="total">{{ simboloMoneda(form.monto_total, 2) }}</span>
                                                  </th>
                                             </tr>
                                        </tfoot>
                                   </table>
                              </div>
                         </div>
                    </div>
               </div>
          </div>
          <ModalEditVue :show.sync="modalEdit" :item.sync="itemProducto" @editModal="editModal" />
     </div>
</template>
<script>
import { FormatNumber } from "../../../helpers.js";
import ModalEditVue from "./ModalEdit.vue";
export default {
     components: {
          ModalEditVue
     },
     props: ['fecha_5', 'documento', 'detalles', 'edit'],
     data() {
          return {
               loading: false,
               dataProductos: [],
               form: {
                    producto_id: null,
                    costo_flete: null,
                    precio: null,
                    cantidad: null,
                    fecha_vencimiento: null,
                    lote: null,
                    descripcion: null,
                    detalles: [],
                    monto_sub_total: 0,
                    monto_total_igv: 0,
                    monto_total: 0,
                    monto_percepcion: '',
                    productos_tabla: null,
                    igv_text: ''
               },
               select2: {
                    producto_id: null
               },
               modalEdit: false,
               itemProducto: null
          }
     },
     watch: {
          dataProductos() {
               let opt = new Option('', '', false, false);
               this.select2.producto_id.append(opt).trigger("change");

               this.dataProductos.forEach(item => {
                    let descripcion = item.nombre.replace('&quot;', ' ');
                    let opt = new Option(`${descripcion} ${item.codigo}`, item.id, false, false);
                    this.select2.producto_id.append(opt).trigger("change");
               });
               this.select2.producto_id.on("select2:select", (e) => {
                    const { id } = e.params.data;
                    this.form.producto_id = Number(id);
                    this.getProducto(id);
               });
               this.select2.producto_id.on("select2:unselect", (e) => {
                    this.form.producto_id = null;
               });
          },
          fecha_5(value) {
               this.form.fecha_vencimiento = `${value}`
          },
          detalles(value) {
               value.forEach(item => {
                    let precio = isNaN(Number(item.precio)) ? 0 : Number(item.precio);
                    let cantidad = isNaN(Number(item.cantidad)) ? 0 : Number(item.cantidad);
                    this.form.detalles.push({
                         producto_id: item.producto_id,
                         descripcion: item.producto_nombre,
                         costo_flete: Number(item.costo_flete),
                         precio,
                         cantidad,
                         lote: item.lote,
                         fecha_vencimiento: item.fechaFormateada,
                         detalle_id: item.id,
                         importe: FormatNumber((precio * cantidad), 2),
                         totalVenta: item.totalVenta,
                         lote_id: item.lote_id,
                         eliminado: false
                    });
               });

          },
          form: {
               handler(value) {
                    if (value && this.documento) {
                         this.reloadDetalles();
                         this.sumarTotales();
                    }
                    if (value && !value.producto_id) {
                         this.select2.producto_id.val(null).trigger("change.select2");
                    }

               },
               deep: true
          },
          documento: {
               handler(value) {

                    if (value && this.documento) {
                         this.reloadDetalles();
                         this.sumarTotales();
                    }
               },
               deep: true
          }
     },
     methods: {
          async obtnerProductos() {
               try {
                    this.loading = true;
                    const { data } = await axios.get(route('compras.documento.getProduct'));
                    this.loading = false;
                    this.dataProductos = data.data;
               } catch (ex) {
                    this.loading = false;
               }
          },
          select2ComboBox() {
               let opt = {
                    placeholder: "SELECCIONAR",
                    allowClear: true,
                    height: '200px',
                    width: '100%',
               };

               let producto_id = $(this.$refs.producto_id).select2(opt);
               this.select2.producto_id = producto_id;
          },
          reloadProductos() {
               this.select2.producto_id.empty().trigger("change");
               this.obtnerProductos();
          },
          async getProducto(id) {
               try {
                    let producto = this.dataProductos.find(item => item.id === Number(id));
                    if (!producto)
                         throw "";
                    this.form.descripcion = `${producto.nombre} - ${this.form.lote}`;
               } catch (ex) {

               }
          },
          Agregar() {
               try {
                    if (!this.edit.moneda)
                         throw "Selecciona una moneda.";
                    if (!this.edit.condicion_id)
                         throw "Selecciona una condicion.";
                    if (!this.edit.tipo_compra)
                         throw "Selecciona tipo de compra.";
                    if (!this.edit.proveedor_id)
                         throw "Selecciona un proveedor.";

                    let obj = this.form.detalles.find(item => item.producto_id == this.form.producto_id);

                    if (obj)
                         throw "Producto ya está agregado.";
                    this.form.costo_flete = this.form.costo_flete != "" ? this.form.costo_flete : 0;
                    let precio = isNaN(Number(this.form.precio)) ? 0 : Number(this.form.precio);
                    let cantidad = isNaN(Number(this.form.cantidad)) ? 0 : Number(this.form.cantidad);
                    let idproducto = isNaN(Number(this.form.producto_id)) ? 0 : Number(this.form.producto_id);
                    let valid = true;

                    if (precio == 0) {
                         valid = false;
                    }
                    if (cantidad === 0) {
                         valid = false;
                    }
                    if (idproducto == 0) {
                         valid = false;
                    }

                    if (!valid)
                         throw "Campos faltantes.";
                    this.productoAgregar();
               } catch (ex) {
                    toastr.error(ex);
               }
          },
          productoAgregar() {
               let precio = isNaN(Number(this.form.precio)) ? 0 : Number(this.form.precio);
               let cantidad = isNaN(Number(this.form.cantidad)) ? 0 : Number(this.form.cantidad);
               this.form.detalles.push({
                    producto_id: this.form.producto_id,
                    descripcion: this.form.descripcion.replace('&quot;', ''),
                    costo_flete: Number(this.form.costo_flete),
                    precio: FormatNumber(precio / cantidad, 3),
                    cantidad: cantidad,
                    lote: this.form.lote,
                    fecha_vencimiento: this.form.fecha_vencimiento,
                    detalle_id: 0,
                    importe: precio,
                    totalVenta: 0,
                    lote_id: 0,
                    eliminado: false
               });
               this.Limpiar();
          },
          validKeyup(e) {
               let key = isNaN(Number(e.key));
               if (key) {
                    if (e.keyCode != 8) {
                         e.preventDefault();
                    }
               }
          },
          validDecimal(e) {
               let match = /^\d*\.?\d*$/;
               let valor = e.key;
               if (!match.test(valor)) {
                    if (e.keyCode != 8) {
                         e.preventDefault();
                    }
               }
          },
          simboloMoneda(valor, digit) {
               if (this.documento) {
                    if (this.documento.moneda == "SOLES") {
                         return FormatNumber(Number(valor), digit, true, 'PE');
                    } else {
                         return FormatNumber(Number(valor), digit, true, 'US');
                    }
               } else {
                    return valor;
               }

          },
          eliminar(item) {
               try {
                    if (item.totalVenta > 0)
                         throw "Este producto ya fue vendido";
                    const swalWithBootstrapButtons = Swal.mixin({
                         customClass: {
                              confirmButton: 'btn btn-success ml-1',
                              cancelButton: 'btn btn-danger'
                         },
                         buttonsStyling: false
                    })

                    swalWithBootstrapButtons.fire({
                         title: '¿Estas seguro(a)?',
                         text: "¡No podrás revertir esto!",
                         icon: 'warning',
                         showCancelButton: true,
                         confirmButtonText: '¡Sí, bórralo!',
                         cancelButtonText: '¡No, cancela!',
                         reverseButtons: true
                    }).then((result) => {
                         if (result.isConfirmed) {
                              item.eliminado = true;
                              swalWithBootstrapButtons.fire(
                                   '¡Eliminado!',
                                   'El item ha sido eliminado.',
                                   'success'
                              )
                         } else if (
                              /* Read more about handling dismissals below */
                              result.dismiss === Swal.DismissReason.cancel
                         ) {
                              swalWithBootstrapButtons.fire(
                                   'Cancelado',
                                   'Tu item está a salvo :)',
                                   'error'
                              )
                         }
                    })
               } catch (ex) {
                    Swal.fire({
                         position: 'top-center',
                         icon: 'error',
                         title: ex,
                         html: `<p class="mb-1">${item.descripcion}</p> <p> <strong>Total Vendidos: </strong> ${item.totalVenta}</p>`,
                         showConfirmButton: false,
                         allowOutsideClick: false,
                         showCloseButton: true,
                         focusDeny: true
                    });
               }
          },
          reloadDetalles() {
               this.form.productos_tabla = JSON.stringify(this.form.detalles);
               this.$emit("reloadChange", this.form);
          },
          editItem(item) {
               this.modalEdit = true;
               this.itemProducto = item;
          },
          sumarTotales() {
               if (this.documento) {
                    let total = this.form.detalles.filter(({ eliminado }) => !eliminado).reduce((sum, { importe }) => Number(sum) + Number(importe), 0);

                    if (this.documento.igv_check) {

                         this.conIgv(FormatNumber(total, 2));
                    } else {

                         this.sinIgv(FormatNumber(total, 2));
                    }
               }
          },
          sinIgv(subtotal) {

               let percepcion = Number(this.form.monto_percepcion);
               let igv = Number(subtotal) * 0.18;
               let total = Number(subtotal) + igv + percepcion;

               this.form.monto_sub_total = Number(FormatNumber(subtotal, 2));
               this.form.monto_total_igv = Number(FormatNumber(igv, 2));
               this.form.monto_total = Number(FormatNumber(total, 2));
               this.form.igv_text = "18%";
          },
          conIgv(subtotal) {
               let percepcion = Number(this.form.monto_percepcion);
               var calcularIgv = this.documento.igv / 100;
               var base = subtotal / (1 + calcularIgv)
               var nuevo_igv = subtotal - base;
               let total = Number(subtotal) + percepcion;
               this.form.monto_sub_total = Number(FormatNumber(base, 2));
               this.form.monto_total_igv = Number(FormatNumber(nuevo_igv, 2));
               this.form.monto_total = Number(FormatNumber(total, 2));
               this.form.igv_text = `${this.documento.igv}%`;
          },
          editModal(data) {
               this.form.detalles.forEach(item => {
                    if (item.producto_id == data.producto_id) {
                         item.cantidad = Number(data.cantidad);
                         item.precio = Number(data.precio);
                         item.importe = Number(data.precio) * Number(data.cantidad);
                         item.costo_flete = Number(data.costo_flete);
                    }
               });
          },
          Limpiar() {
               this.form.producto_id = null;
               this.form.cantidad = null;
               this.form.precio = null;
               this.form.costo_flete = null;
          }
     },
     mounted() {
          this.form.lote = `LT-${$fechaActual}`;
          this.select2ComboBox();
          this.obtnerProductos();
     }
}

</script>
