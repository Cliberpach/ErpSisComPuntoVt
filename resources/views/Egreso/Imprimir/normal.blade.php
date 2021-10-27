<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Nota Egreso</title>
    <style>
        .tipo-letra {
            font-size: 9pt;
            font-family: Arial, Helvetica, sans-serif;
            color: black;
        }

        .cuadroDerecha {
            position: relative;
            left: 70%;
            border: 1px #ccc solid;
            border-radius: 10px;
            padding: 5px;
            width: 200px;
            height: 60px;
        }

        .cuadroDerecha .title {

            margin-top: 5px !important;
            font-size: 15px;
            text-align: center;
        }

        .restaurant {
            position: absolute;
            font-size: 18px;
            text-transform: uppercase;
            left: 15%;

        }

        .nombre {
            position: relative;
            font-size: 14px;
            top: 10px;
            left: 5%;
        }

        .dni {
            position: absolute;
            font-size: 14px;
            top: 8.7%;
            left: 48%;
        }

        .fecha {
            position: absolute;
            width: 200px;
            top: 8.7%;
            left: 66%;
            font-size: 14px;
        }

        .direccion {
            position: relative;
            font-size: 14px;
            top: 10px;
            left: 5%;
        }

        .ruc {
            font-size: 14px;
            text-align: center;
        }

        .cabeceraTabla {
            border-top: 1px solid;
            background-color: rgb(241, 239, 239);
        }

        .cuerpoTabla {
            border-top: 1px solid;
            border-bottom: 1px solid;
        }

    </style>
</head>

<body>
    <div style="width: 100%; margin-left: -9mm; margin-right: -9mm; margin-top: -5mm" class="tipo-letra">
        <br>
        <div class="restaurant">

        </div>
        <div class="cuadroDerecha">
            <div class="ruc">
                R.U.C:{{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->ruc }}</div>

            <div class="title">Recibo Egreso
                <br>
            </div>

        </div>
        <br>
        <div style="font-size:14px;position:absolute;left: 15%;top:17px;">
            Razon Social: {{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->razon_social }}
            <br>
            Direccion: {{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->direccion_fiscal }}
            <br>
            Correo:{{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->correo}}
            <br>
            Telefono:{{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->telefono}}
        </div>
        <div class="fecha">
            Fecha: {{date('Y-m-d h:i')}}</div>
        </div>

    </div>
    <br>
    <br>
    <br>
    <table style="width: 100%">
        <thead class="cabeceraTabla">
            <tr>
                <th style="text-align: center">CAJERO</th>
                <th style="text-align: center">CUENTA</th>
                <th style="text-align: center">DESCRIPCION</th>
                <th style="text-align: center">IMPORTE</th>
            </tr>
        </thead>
        <tbody class="cuerpoTabla">
            <tr>
                <td style="text-align: center">-</td>
                <td style="text-align: center">{{$egreso->cuenta->descripcion}}</td>
                <td style="text-align: center">{{$egreso->descripcion}}</td>
                <td style="text-align: center">{{$egreso->importe}}</td>

            </tr>
        </tbody>
    </table>
    <br>
    {{-- <img width="20" src="{{str_replace('\\','/','http://127.0.0.1:8000/'.str_replace('public','storage',DB::table('empresas')->first()->ruta_logo))}}" alt=""> --}}

    </div>
</body>

</html>
