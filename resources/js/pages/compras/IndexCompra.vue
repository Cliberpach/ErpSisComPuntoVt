<template>
     <div class="wrapper wrapper-content animated fadeInRight">
          <div class="row">
               <div class="col-lg-12">
                    <div class="ibox ">
                         <div class="ibox-content">

                              <div class="table-responsive">
                                   <table class="table table-sm table-hover" style="text-transform:uppercase">
                                        <thead>
                                             <tr>
                                                  <th colspan="7" class="text-center">
                                                       DOCUMENTO DE COMPRA
                                                  </th>
                                                  <th colspan="2" class="text-center">
                                                       FORMAS DE PAGO
                                                  </th>
                                                  <th colspan="3" class="text-center"></th>
                                             </tr>
                                             <tr>
                                                  <th class="text-center bg-success">ID</th>
                                                  <th class="text-center bg-success">O.C</th>
                                                  <th class="text-center bg-success">#Doc</th>
                                                  <th class="text-center bg-success">EMISION</th>
                                                  <th class="text-center bg-success">TIPO</th>
                                                  <th class="text-center bg-success">PROVEEDOR</th>
                                                  <th class="text-center bg-success">MODO</th>
                                                  <th class="text-center bg-success">MONTO</th>
                                                  <th class="text-center bg-success">A. CTA</th>
                                                  <th class="text-center bg-success">SALDO</th>
                                                  <th class="text-center bg-success">ESTADO</th>
                                                  <th class="text-center bg-success">ACCIONES</th>
                                             </tr>
                                        </thead>
                                        <tbody>
                                             <template v-if="pagination">
                                                  <tr v-for="(item, index) in dataCompras" :key="index">
                                                       <td class="text-center">{{ item.id }}</td>
                                                       <td class="text-center d-flex justify-content-center">
                                                            <template v-if="item.orden_compra">
                                                                 <div class="custom-control custom-checkbox">
                                                                      <input type="checkbox" checked disabled
                                                                           class="custom-control-input"
                                                                           :id="`input-${item.id}`">
                                                                      <label class="custom-control-label"
                                                                           :for="`input-${item.id}`"></label>
                                                                 </div>
                                                            </template>
                                                            <template v-else>
                                                                 <div class="custom-control custom-checkbox">
                                                                      <input type="checkbox" disabled
                                                                           class="custom-control-input"
                                                                           :id="`input-${item.id}`">
                                                                      <label class="custom-control-label"
                                                                           :for="`input-${item.id}`"></label>
                                                                 </div>
                                                            </template>
                                                       </td>
                                                       <td class="text-center">{{ item.numero_doc }}</td>
                                                       <td class="text-center">{{ item.fecha_emision }}</td>
                                                       <td class="text-center">{{ item.tipo }}</td>
                                                       <td>{{ item.proveedor }}</td>
                                                       <td class="text-center">{{ item.modo }}</td>
                                                       <td class="text-center">{{ item.total_pagar }}</td>
                                                       <td class="text-center">{{ item.acuenta }}</td>
                                                       <td class="text-center">{{ item.saldo }}</td>
                                                       <td v-html="estado(item)" class="text-center"></td>
                                                       <td>
                                                            <div class="btn-group" role="group"
                                                                 aria-label="Basic example">
                                                                 <button @click="editar(item.orden_compra, item.id)"
                                                                      type="button" title="Modificar"
                                                                      class="btn btn-info">
                                                                      <i class="fa fa-edit"></i>
                                                                      Editar
                                                                 </button>
                                                                 <button type="button" title="Ver Detalles"
                                                                      class="btn btn-secondary"
                                                                      @click.prevent="verDetalle(item.id)">
                                                                      <i class="fa fa-eye"></i>
                                                                      Ver
                                                                 </button>
                                                                 <button type="button" title="Notas"
                                                                      class="btn btn-warning"
                                                                      @click.prevent="notas(item.id)">
                                                                      <i class="fa fa-file"></i>
                                                                      Notas
                                                                 </button>
                                                            </div>
                                                       </td>
                                                  </tr>
                                             </template>
                                             <template v-else>
                                                  <tr>
                                                       <td colspan="12" class="text-center">
                                                            <strong>cargando datos....</strong>
                                                       </td>
                                                  </tr>
                                             </template>
                                        </tbody>
                                   </table>
                              </div>
                              <v-paginate @changePage="getDocuments" :pagination="pagination"
                                   v-if="pagination"></v-paginate>
                         </div>
                    </div>
               </div>
          </div>
     </div>
</template>
<script>
export default {
     data() {
          return {
               dataCompras: [],
               pagination: null,
               search: ''
          };
     },
     watch: {
          dataCompras() {

          }
     },
     created() {
          this.getDocuments();
     },
     methods: {
          async getDocuments(page) {
               try {
                    const { data } = await axios.get(route("getDocument"), {
                         params: {
                              page,
                              search: this.search
                         }
                    });
                    this.dataCompras = data.data;
                    this.pagination = data;
                    this.pagination.limit = 5;
               } catch (ex) {
                    console.log(ex);
               }
          },
          estado(data) {
               switch (data.estado) {
                    case "PENDIENTE":
                         return "<span class='badge badge-warning' d-block>" + data.estado +
                              "</span>";
                         break;
                    case "PAGADA":
                         return "<span class='badge badge-primary' d-block>" + data.estado +
                              "</span>";
                         break;
                    case "ADELANTO":
                         return "<span class='badge badge-success' d-block>" + data.estado +
                              "</span>";
                         break;
                    default:
                         return "<span class='badge badge-success' d-block>" + data.estado +
                              "</span>";
               }
          },
          tooltip() {
               $('[data-toggle="tooltip"]').tooltip();
          },
          editar(orden, id) {
               if (orden) {
                    toastr.error('El documento de compra fue generado por una orden (Opción "Editar" en orden de compra).', 'Error');
               } else {
                    Swal.fire({
                         title: 'Opción Modificar',
                         text: "¿Seguro que desea modificar registro?",
                         icon: 'warning',
                         showCancelButton: true,
                         confirmButtonColor: "#1ab394",
                         confirmButtonText: 'Si, Confirmar',
                         cancelButtonText: "No, Cancelar",
                    }).then((result) => {
                         if (result.isConfirmed) {
                              var url_editar = route('compras.documento.edit', { id });
                              $(location).attr('href', url_editar);
                         } else if (
                              result.dismiss === Swal.DismissReason.cancel
                         ) {
                              swalWithBootstrapButtons.fire(
                                   'Cancelado',
                                   'La Solicitud se ha cancelado.',
                                   'error'
                              );
                         }
                    });
               }
          },
          verDetalle(id) {
               location.href = route('compras.documento.show', { id });
          },
          notas(id) {
               location.href = route('compras.notas', { id });
          }
     }
};
</script>
