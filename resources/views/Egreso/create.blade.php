<div class="modal inmodal" id="modal_crear_egreso" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <i class="fa fa-cogs modal-icon"></i>
                <h4 class="modal-title">Egreso</h4>
                <small class="font-bold">Nuevo Egreso</small>
            </div>
            <div class="modal-body">
                <form role="form" action="{{ route('Egreso.store') }}" method="POST" id="frm_egreso_create">
                    {{ csrf_field() }} {{ method_field('POST') }}
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="" class="required">Cuentas</label>
                            <select name="cuenta" id="cuenta" class="select2_form form-control" required>
                                    <option value=""></option>
                                @foreach (cuentas() as $cuenta)
                                    <option value="{{$cuenta->id}}">{{$cuenta->descripcion}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="required">Importe:</label>
                            <input type="text" class="form-control" id="importe" name="importe" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label class="required">Tipo Documento:</label>
                            <input type="text" name="tipo_documento" id="tipo_documento" class="form-control" value="RECIBO" disabled>
                            {{-- <select name="tipo_documento" id="tipo_documento" class="form-control select2_form" required>
                                <option value=""></option>
                                @foreach (tipo_compra() as $documento)
                                        <option value="{{$documento->id}}">{{$documento->descripcion}}</option>
                                @endforeach
                            </select> --}}
                        </div>
                        <div class="col-md-6">
                            <label class="required">Documento:</label>
                            <input type="text" name="documento" id="documento" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label class="required">Descripcion:</label>
                            <textarea name="descripcion" id="descripcion" cols="30" rows="2"
                                class="form-control" required></textarea>
                        </div>

                    </div>
            </div>
            <div class="modal-footer">
                <div class="col-md-6 text-left" style="color:#fcbc6c">
                    <i class="fa fa-exclamation-circle"></i> <small>Los campos marcados con asterisco (<label
                            class="required"></label>) son obligatorios.</small>
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-primary btn-sm btn-submit-egreso"><i class="fa fa-save"></i> Guardar</button>
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i
                            class="fa fa-times"></i> Cancelar</button>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>
@push('styles')
    <link href="{{ asset('Inspinia/css/plugins/select2/select2.min.css') }}" rel="stylesheet">
@endpush
@push('scripts')
    <!-- Select2 -->
    <script src="{{ asset('Inspinia/js/plugins/select2/select2.full.min.js') }}"></script>
    <script>
        //Select2
        $(".select2_form").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            height: '200px',
            width: '100%',
        });
        $("#tipo_documento").on('change', function (e) {
            var tipoDocumento=$("#tipo_documento option:selected").text()
           if(tipoDocumento=="RECIBO")
           {
            $("#documento").attr('disabled',true)
           }
           else
           {
            $("#documento").attr('disabled',false)
           }
        });
        $(".btn-submit-egreso").on('click', function(e) {
            axios.get("{{route('Caja.movimiento.verificarestado')}}").then((value) => {
                if(value.data=="")
                {
                    toastr.error("No hay ninguna apertura de caja");
                }
                else{
                    $("#frm_egreso_create").submit();
                }
            })
        })
    </script>
@endpush
