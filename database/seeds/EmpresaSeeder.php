<?php

use Illuminate\Database\Seeder;

use App\Compras\Proveedor;
use App\Mantenimiento\Empresa\Empresa;
use App\Mantenimiento\Empresa\Facturacion;
use App\Mantenimiento\Empresa\Numeracion;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Agroensancha S.R.L
        $empresa = new Empresa();
        $empresa->ruc = '10802398307';
        $empresa->razon_social = 'SISCOM FAC';
        $empresa->razon_social_abreviada = 'SISCOM FAC';
        $empresa->direccion_fiscal = 'AV ESPAÑA 1319';
        $empresa->direccion_llegada = 'TRUJILLO';
        $empresa->dni_representante = '70004110';
        $empresa->nombre_representante = 'NOMBRE APELLIDOPAT APELLIDOMAT';
        $empresa->num_asiento = 'A00001';
        $empresa->num_partida = '11036086';
        $empresa->estado_ruc = 'ACTIVO';
        $empresa->estado_ruc = 'ACTIVO';
        $empresa->estado_fe= '1';
        $empresa->save();

        $facturacion = new Facturacion();
        $facturacion->empresa_id = $empresa->id; //RELACION CON LA EMPRESA
        $facturacion->fe_id = 1095; //ID EMPRESA API
        $facturacion->sol_user = 'CLIBERPA';
        $facturacion->sol_pass = 'P1lester';
        $facturacion->plan = 'free';
        $facturacion->ambiente = 'beta';
        $facturacion->certificado =  null;
        $facturacion->token_code =  'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MzM0NjcxMzgsImV4cCI6NDc4NzA2NzEzOCwidXNlcm5hbWUiOiJMZXN0ZXIiLCJjb21wYW55IjoiMTA4MDIzOTgzMDcifQ.YjQK8uvUFn8glmKHwDdPXfhqCIBUU51Rl5hF1OKZ9BC0QDcbPFelunk_mXws9k6wqrXvISitKwVltlpdPfrbx9NoU0sygEhIyr4EanYYdthvtRj18X_bki_fk90sRi1AKf0rXHObVGeXZtdAYIwvYQRy_PmUORJlmJf_K6EYpO6tFib529Eqzs0DaiOVR4k21nCI3u7RDUFlABJMv75IpS24jL9WmtwptkswuskpotC4tbr6FUll7Yk1lG3kniFqf60G0nA30HUpctmjQY7oPCjEySLsjGYqnE78l7r5bdHi9TTUaRr3U4gsdvO39Uzw_TmOm9PxArYd2z19iBoQ3eoF-pYBk3V8xjUCy3-zXzE_2aq3jzZvMoUy7L89iXw2zODca3JcszM_BM2gxx97ulTm62lGPYiPLW1hLath3HvwyYNGH6Xihd9I-xNNwK3MGiNnbbmNqKh5FPGK-DIBLfnm4y0QJil0lM89jXjaaTeNOHuN8By45mKrzG6jZSxY8pG-YoncHMRMRwzMXu6SxjQgWuDvXk53BMnw3xOtvA1QwslJmnhblpiG9-_AAWDSQuQXmz4mQaK375aSGLc8QHXjarKuq6ToXVoF29hBh9CWuXt7F_5wa54Xbq6J_EPNtu4vdG3vrul_Q2zSuMMQRZygjDIJd8mT37200Ft3CLc';
        $facturacion->save();

        Numeracion::create([
            'empresa_id' => $empresa->id,
            'serie' => 'F001',
            'tipo_comprobante' => 127,
            'numero_iniciar' => 1,
            'emision_iniciada' => 1,
        ]);

        Numeracion::create([
            'empresa_id' => $empresa->id,
            'serie' => 'B001',
            'tipo_comprobante' => 128,
            'numero_iniciar' => 1,
            'emision_iniciada' => 1,
        ]);

        Numeracion::create([
            'empresa_id' => $empresa->id,
            'serie' => 'N001',
            'tipo_comprobante' => 129,
            'numero_iniciar' => 1,
            'emision_iniciada' => 1,
        ]);



        $proveedor = new Proveedor();
        $proveedor->descripcion = 'LIMPIATODO S.A.C';
        $proveedor->tipo_documento = 'RUC';
        $proveedor->tipo_persona = 'PERSONA JURIDICA';
        $proveedor->direccion = 'Jr. Puerto Inca Nro. 250 Dpto. 402';
        $proveedor->correo = 'CCUBAS@UNITRU.EDU.PE';
        $proveedor->telefono = '043313520';
        $proveedor->zona = 'NOROESTE';
        $proveedor->contacto = 'CARLOS CUBAS';
        $proveedor->telefono_contacto = '950837445';
        $proveedor->correo_contacto = 'CCUBAS@UNITRU.EDU.PE';
        $proveedor->transporte = 'SEDACHIMBOTE S.A.';
        $proveedor->ruc_transporte = '20136341066';
        $proveedor->direccion_transporte = 'JR. LA CALETA NRO. 176 A.H.  MANUEL SEOANE CORRALES - ANCASH - SANTA - CHIMBOTE';

        $proveedor->estado_transporte = 'ACTIVO';
        $proveedor->estado_documento = 'ACTIVO';
        $proveedor->save();

    }
}
