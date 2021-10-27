<div class="modal inmodal" id="modal_editar_cliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" onclick="limpiar()" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <i class="fa fa-cogs modal-icon"></i>
                <h4 class="modal-title">Cliente</h4>
                <small class="font-bold">Modificar Cliente.</small>
            </div>
            <div class="modal-body">
                <form role="form" id="">

                        <div class="form-group">
                            <label class="required">Cliente</label>
                            <select class="select2_form form-control" style="text-transform: uppercase; width:100%"
                                name="cliente_id" id="cliente_id_editar" disabled>
                                <option></option>
                                @foreach (tipo_clientes() as $cliente)
                                <option value="{{$cliente->descripcion}}">{{$cliente->descripcion}}
                                </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"><b><span id="error-cliente_id_editar"></span></b></div>
                        </div>

                    <div class="form-group row">

                        <input type="hidden" name="" id="indice">

                        <div class="col-md-8">
                            <label class="required">Moneda</label>
                            <select class="select2_form form-control" style="text-transform: uppercase; width:100%"
                                name="moneda_id" id="moneda_id_editar" disabled>
                                <option></option>
                                @foreach (tipos_moneda() as $tipo)
                                <option value="{{$tipo->id}}">{{$tipo->simbolo.' - '.$tipo->descripcion}}
                                </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"><b><span id="error-moneda_id_editar"></span></b></div>
                        </div>

                        <div class="col-md-4">
                            <label class="required" for="amount">Monto</label>
                            <input type="text" id="monto_editar" class="form-control">
                            <div class="invalid-feedback"><b><span id="error-monto_editar"></span></b></div>
                        </div>

                    </div>

            </div>


            <div class="modal-footer">
                <div class="col-md-6 text-left" style="color:#fcbc6c">
                    <i class="fa fa-exclamation-circle"></i> <small>Los campos marcados con asterisco (<label
                            class="required"></label>) son obligatorios.</small>
                </div>
                <div class="col-md-6 text-right">
                    <a class="btn btn-primary btn-sm editarRegistro" style="color:white"><i class="fa fa-save"></i>
                        Guardar</a>
                    <button type="button" onclick="limpiar()" class="btn btn-danger btn-sm" data-dismiss="modal"><i
                            class="fa fa-times"></i>
                        Cancelar</button>
                </div>
            </div>

            </form>
        </div>
    </div>
</div>


@push('scripts')
<script>

    function limpiarErroresRegistro() {
        $('#monto_editar').removeClass("is-invalid")
        $('#error-monto_editar').text('')
        
        $('#moneda_id_editar').removeClass("is-invalid")
        $('#error-moneda_id_editar').text('')
    }

//Validacion al ingresar tablas
$(".editarRegistro").click(function() {
    limpiarErroresRegistro()
    var enviar = false;

    if ($('#monto_editar').val() == '') {

        toastr.error('Ingrese el Monto del tipo de cliente.', 'Error');
        enviar = true;

        $("#monto_editar").addClass("is-invalid");
        $('#error-monto_editar').text('El campo Monto es obligatorio.')
    }

    if ($('#moneda_id_editar').val() == '') {

        toastr.error('Seleccione la moneda del tipo de cliente.', 'Error');
        enviar = true;

        $("#moneda_id_editar").addClass("is-invalid");
        $('#error-moneda_id_editar').text('El campo Moneda es obligatorio.')
    }


    if (enviar != true) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger',
                container: 'my-swal',
            },
            buttonsStyling: false
        })

        Swal.fire({
            customClass: {
                container: 'my-swal'
            },
            title: 'Opción Modificar',
            text: "¿Seguro que desea modificar Cliente?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: "#1ab394",
            confirmButtonText: 'Si, Confirmar',
            cancelButtonText: "No, Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                actualizarTabla($('#indice').val())
                // sumaTotal()
                limpiar()

            } else if (
                /* Read more about handling dismissals below */
                result.dismiss === Swal.DismissReason.cancel
            ) {
                swalWithBootstrapButtons.fire(
                    'Cancelado',
                    'La Solicitud se ha cancelado.',
                    'error'
                )
            }
        })

    }
})

function actualizarTabla(i) {
    var table = $('.dataTables-clientes').DataTable();
    table.row(i).remove().draw();
    var detalle = {
        cliente: $('#cliente_id_editar').val(),
        monto: $('#monto_editar').val(),
        moneda: $('#moneda_id_editar').val(),
        id_moneda: $('#moneda_id_editar').val(),
    }
    agregarTabla(detalle);

}


function limpiar() {

    $('#moneda_id_editar').removeClass("is-invalid")
    $('#error-moneda_id_editar').text('')

    $('#monto_editar').removeClass("is-invalid")
    $('#error-monto_editar').text('')

    $('#modal_editar_cliente').modal('hide');
}

$('#modal_editar_cliente').on('hidden.bs.modal', function(e) {
    limpiar()
});
</script>
@endpush