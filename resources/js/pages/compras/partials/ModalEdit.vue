<template>
     <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel"
          aria-hidden="true">
          <div class="modal-dialog" role="document">
               <div class="modal-content">
                    <div class="modal-header">
                         <h4 class="modal-title" id="modalEditLabel">Editar Producto</h4>
                         <button type="button" class="close" @click.prevent="cerrarModal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <div class="modal-body">
                         <form>
                              <div class="row form-group">
                                   <div class="col-md-12">
                                        <label for="">Producto</label>
                                        <input type="text" v-model="form.descripcion" class="form-control" disabled>
                                   </div>

                              </div>
                              <div class="row form-group">
                                   <div class="col-md-4">
                                        <label for="">Cantidad</label>
                                        <input type="text" v-model="form.cantidad" class="form-control">
                                   </div>
                                   <div class="col-md-4">
                                        <label for="">Precio</label>
                                        <input type="text" v-model="form.precio" class="form-control" />
                                   </div>
                                   <div class="col-md-4">
                                        <label for="">Costo Flete</label>
                                        <input type="text" class="form-control" v-model="form.costo_flete" />
                                   </div>
                              </div>
                         </form>
                    </div>
                    <div class="modal-footer">
                         <button type="button" class="btn btn-secondary" @click.prevent="cerrarModal">Cerrar</button>
                         <button type="button" class="btn btn-primary" @click.prevent="Guardar">Guardar</button>
                    </div>
               </div>
          </div>
     </div>
</template>
<script>
export default {
     props: ['show', 'item'],
     data() {
          return {
               form: {
                    cantidad: null,
                    costo_flete: null,
                    descripcion: null,
                    detalle_id: null,
                    fecha_vencimiento: null,
                    importe: null,
                    lote: null,
                    lote_id: null,
                    precio: null,
                    producto_id: null,
                    totalVenta: null
               }
          }
     },
     watch: {
          show: {
               handler(val) {

                    if (val) {
                         $("#modalEdit").modal("show");
                    } else {
                         $("#modalEdit").modal("hide");
                    }
               },
               deep: true
          },
          item: {
               handler(value) {
                    this.form = { ...value };
               },
               deep: true
          },
          form: {
               handler(value) {
                    if (value) {
                         value.cantidad = Number(value.cantidad) === 0 ? 1 : Number(value.cantidad);
                    }
               }
          }
     },
     methods: {
          cerrarModal() {
               this.$emit("update:show", false);
               this.$emit("update:item", null);
          },
          Guardar() {
               try {
                    let cantidad = isNaN(Number(this.form.cantidad)) ? 0 : Number(this.form.cantidad);
                    let precio = isNaN(Number(this.form.precio)) ? 0 : Number(this.form.precio);
                    let costo_flete = Number(this.form.costo_flete);

                    if (cantidad == 0)
                         throw "La cantidad debe ser mayor que 0.";
                    if (precio == 0)
                         throw "El precio debe ser mayor que 0.";
                    this.$emit("editModal", this.form);
                    this.$emit("update:show", false);
                    this.$emit("update:item", null);
               } catch (ex) {
                    toastr.error(ex, 'Editar');
               }

          }
     }
}
</script>