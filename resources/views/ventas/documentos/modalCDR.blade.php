<div class="modal fade" id="modalCDR" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="title-header"></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row form-group">
                    <div class="col-md-12">
                        <label for=""><strong>MOTIVO:</strong></label>
                        <p id="motivo"></p>
                        <input type="hidden" id="iddocumento" name="iddocumento">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label for=""><strong>Este documento se anulará y se creará otro documento.</strong></label>
                        <div class="form-check d-none">
                            <input class="form-check-input" type="radio" name="optionCDR" id="optionCDR1"
                                value="DUPLICAR" checked>
                            <label class="form-check-label" for="optionCDR1">
                                Duplicar documento
                            </label>
                        </div>
                        <div class="form-check  d-none">
                            <input class="form-check-input" type="radio" name="optionCDR" id="optionCDR2"
                                value="ANULAR">
                            <label class="form-check-label" for="optionCDR2">
                                Anular documento
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="EnviarCDR">Enviar</button>
            </div>
        </div>
    </div>
</div>
