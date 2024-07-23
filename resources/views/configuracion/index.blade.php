    @extends('layout') @section('content')

@section('mantenimiento-active', 'active')
@section('configuracion-active', 'active')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-12 col-md-12">
       <h2  style="text-transform:uppercase"><b>Configuración</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('home')}}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Configuración</strong>
            </li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        @foreach ($config as $item)
        @if ($item->slug == 'CEC')
        <div class="col-12 col-md-4">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>{{ $item->descripcion }}</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <form action="{{ route('configuracion.update', $item->id)}}" method="POST">
                        @csrf
                        @method('put')
                        <div class="form-group row">
                            <div class="col-9">
                                <input type="hidden" name="slug" id="" value="{{ $item->slug }}">
                                <select name="propiedad" id="propiedad"  class="select2_form form-control" required>
                                    <option value=""></option>
                                    <option value="SI" {{$item->propiedad == 'SI' ? 'selected' : ''}}>SI</option>
                                    <option value="NO" {{$item->propiedad == 'NO' ? 'selected' : ''}}>NO</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @php
        $greenter_mode  =   null;
    @endphp

    @if ($item->slug === "AG")
    @php
        $greenter_mode  =   $item->propiedad;
    @endphp
    <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>AMBIENTE GREENTER</h5>
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                </div>
            </div>
            <div class="ibox-content" >
                <div class="row">
                    <div class="col-12" style="height: 50px;">
                        
                        <div class='toggle' id='switch'>
                            <div class='toggle-text-off'>BETA</div>
                            <div class='glow-comp'></div>
                            <div class='toggle-button'></div>
                            <div class='toggle-text-on'>PRODUCCIÓN</div>
                        </div>
                         
                          
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
        @endforeach
        <div class="col-12 col-md-4">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>CODIGO DE PRECIOS MENOR</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <form action="{{ route('configuracion.empresa.update')}}" method="POST">
                        @csrf
                        @method('put')
                        <div class="row align-items-end">
                            <div class="col-7">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="codigo_precio_menor" value="{{ $empresa->codigo_precio_menor }}" placeholder="Código">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label> <input type="checkbox" class="i-checks" name="estado_precio_menor" id="estado_precio_menor" value="1" {{ $empresa->estado_precio_menor == '1' ? 'checked' : ''}}> Activo </label>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@stop
@push('styles')
<!-- DataTable -->
<link href="{{asset('Inspinia/css/plugins/dataTables/datatables.min.css')}}" rel="stylesheet">

<link href="{{ asset('Inspinia/css/plugins/dataTables/datatables.min.css') }}" rel="stylesheet">
<link href="{{ asset('Inspinia/css/plugins/select2/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('Inspinia/css/plugins/iCheck/custom.css' )}}" rel="stylesheet">
<style>
    .letrapequeña {
        font-size: 11px;
    }



#switch{
    top: 0;
    left: 20%;
    width: 170px;
    height: 40px;
}

.toggle{
    position: absolute;
    border: 2px solid #444249;
    border-radius: 20px;
    -webkit-transition: border-color .6s  ease-out;
    transition: border-color .6s  ease-out;
    box-sizing: border-box;
}

.toggle.toggle-on{
    border-color: rgba(137, 194, 217, .4);
    -webkit-transition: all .5s .15s ease-out;
    transition: all .5s .15s ease-out;
}

.toggle-button{
    position: absolute;
    top: 4px;
    width: 28px;
    bottom: 4px;
    right: 130px;
    background-color: #444249;
    border-radius: 19px; 
    cursor: pointer;
    -webkit-transition: all .3s .1s, width .1s, top .1s, bottom .1s;
    transition: all .3s .1s, width .1s, top .1s, bottom .1s;
}

.toggle-on .toggle-button{
    top: 3px;
    bottom: 3px;
    right: 3px;
    border-radius: 23px;
    background-color: #89c2da;
    box-shadow: 0 0 16px #4b7a8d;
    -webkit-transition: all .2s .1s, right .1s;
    transition: all .2s .1s, right .1s;
}


.toggle-text-on{
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0px;
    right: 50px;
    line-height: 36px;
    text-align: center;
    font-family: 'Quicksand', sans-serif;
    font-size: 11px;
    font-weight: normal;
    cursor: pointer;
    -webkit-user-select: none; /* Chrome/Safari */    
    -moz-user-select: none; /* Firefox */
    -ms-user-select: none; /* IE10+ */


    color: rgba(0,0,0,0);
}

.toggle-on .toggle-text-on{
    color: #3b6a7d;
    -webkit-transition: color .3s .15s ;
    transition: color .3s .15s ;
}

.toggle-text-off{
    position: absolute;
    top: 0;
    bottom: 0;
    right: 6px;
    line-height: 36px;
    text-align: center;
    font-family: 'Quicksand', sans-serif;
    font-size: 14px;
    font-weight: bold;
    -webkit-user-select: none; /* Chrome/Safari */        
    -moz-user-select: none; /* Firefox */
    -ms-user-select: none; /* IE10+ */

    cursor: pointer;

    color: #444249;
}

.toggle-on .toggle-text-off{
    color: rgba(0,0,0,0);
}

/* used for streak effect */
.glow-comp{
    position: absolute;
    opacity: 0;
    top: 10px;
    bottom: 10px;
    left: 10px;
    right: 10px;
    border-radius: 6px;
    background-color: rgba(75, 122, 141, .1);
    box-shadow: 0 0 12px rgba(75, 122, 141, .2);
    -webkit-transition: opacity 4.5s 1s;
    transition: opacity 4.5s 1s;
}

.toggle-on .glow-comp{
    opacity: 1;
    -webkit-transition: opacity 1s;
    transition: opacity 1s;
}


</style>
@endpush

@push('scripts')
<!-- DataTable -->
<script src="{{asset('Inspinia/js/plugins/dataTables/datatables.min.js')}}"></script>
<script src="{{asset('Inspinia/js/plugins/dataTables/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{ asset('Inspinia/js/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('Inspinia/js/plugins/iCheck/icheck.min.js') }}"></script>
<script>
  
    document.addEventListener('DOMContentLoaded',()=>{
        loadSelect2();
        loadICheck();
        loadGreenterMode();
        events();
    })

    function events(){
        $('.toggle').click(function(e){
            e.preventDefault(); 

            const switchToggle  =       document.querySelector('#switch');
            const modo_cambiar  =   switchToggle.classList.contains('toggle-on')? "BETA" : "PRODUCCIÓN" ;

            const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
            });
            swalWithBootstrapButtons.fire({
            title: `Desea cambiar el ambiente de greenter a ${modo_cambiar}?`,
            text: "Está acción afectará la facturación de la empresa!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí!",
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
            }).then((result) => {
            if (result.isConfirmed) {

                $(this).toggleClass('toggle-on');
                const modo          =       switchToggle.classList.contains('toggle-on')? "PRODUCCION" : "BETA" ;
                setGreenterModo(modo);

            } else if (
                /* Read more about handling dismissals below */
                result.dismiss === Swal.DismissReason.cancel
            ) {
                swalWithBootstrapButtons.fire({
                title: "Operación cancelada",
                text: "No se aplicaron cambios",
                icon: "error"
                });
            }
            });

        });
    }

    function loadSelect2(){
        $(".select2_form").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            height: '200px',
            width: '100%',
        });
    }

    function loadICheck(){
        $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
        });
    }


    function loadGreenterMode(){
        const greenter_mode =   @json($greenter_mode);
        
        if(greenter_mode    === "PRODUCCION"){
            $('.toggle').toggleClass('toggle-on');
        }
    }

    async function setGreenterModo(modo) {
        try {
            const res   =   await axios.post(route('configuracion.greenter.modo'),{modo});
            
            if(res.data.success){
                toastr.success(res.data.message,'CONFIGURACIÓN APLICADA');
            }else{
                toastr.error(res.data.exception,res.data.message);
            }
        } catch (error) {
            toastr.error(error,'ERROR EN EL SERVIDOR');
        }
    }
</script>
@endpush
