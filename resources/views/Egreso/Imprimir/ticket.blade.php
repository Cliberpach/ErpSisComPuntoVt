<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Recibo Egreso</title>
    <style>
        .tipo-letra {
            font-size: 9pt;
            font-family: Arial, Helvetica, sans-serif;
            color: black;
        }

    </style>
</head>

<body>
    <div style="width: 100%; margin-left: -9mm; margin-right: -9mm; margin-top: -5mm" class="tipo-letra">
        <div style="text-align: center; font-size: 12pt">
            <span>***** Recibo Egreso *****</span>
        </div>
        <br>
        <div style="text-align: center; font-size: 12pt">
            <span>Ruc: {{ DB::table('empresas')->count() == 0 ? '- ' :  DB::table('empresas')->first()->ruc }}</span>
        </div>
        <br>
        <div style="margin-top: 5pt">
            <span>Razon Social: {{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->razon_social }}</span>
        </div>
        <div style="margin-top: 5pt">
            <span>Correo:{{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->correo}}</span>
        </div>
        <div style="margin-top: 5pt">
            <span>Telefono:{{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->telefono}}</span>
        </div>

        <div style="margin-top: 5pt">
            <span>Fecha Emision: {{ date('Y-m-d h:i') }}</span>
        </div>
        <br>
        <div>
            <span>----------------------------------------------------------------</span>
        </div>
        <br>
        <table style="width: 100%;">
            <thead>
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
        <div>
            <span>----------------------------------------------------------------</span>
        </div>
        <br>
        <br><br>
        <br>
        <div style="margin-top: 5pt; text-align: center">
            <span>!!! RECIBO IMPRESO!!!</span>
        </div>
    </div>


</body>

</html>
