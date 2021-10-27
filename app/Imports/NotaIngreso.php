<?php

namespace App\Imports;

use App\Almacenes\DetalleNotaIngreso;
use App\Almacenes\LoteProducto;
use App\Almacenes\MovimientoNota;
use App\Almacenes\NotaIngreso as AlmacenesNotaIngreso;
use App\Almacenes\Producto;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;

class NotaIngreso implements ToCollection, WithHeadingRow, WithValidation
{
    use Importable;
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $fecha_hoy = Carbon::now()->toDateString();
        $fecha = Carbon::createFromFormat('Y-m-d', $fecha_hoy);
        $fecha = str_replace("-", "", $fecha);
        $fecha = str_replace(" ", "", $fecha);
        $fecha = str_replace(":", "", $fecha);

        $fecha_actual = Carbon::now();
        $fecha_actual = date("d/m/Y", strtotime($fecha_actual));
        $fecha_5 = date("Y-m-d", strtotime($fecha_hoy . "+ 5 years"));

        $numero = $fecha . (DB::table('nota_ingreso')->count() + 1);

        $nota = AlmacenesNotaIngreso::create([
            'numero' => $numero,
            'fecha' => $fecha_hoy,
            'destino' => 'ALMACEN',
            'origen' => 'IMPORT EXCEL',
            'usuario' => Auth()->user()->usuario
        ]);

        foreach ($collection as $row) {
            if ($row['codigo'] != null) {
                $producto = DB::table('productos')->where('codigo', $row['codigo'])->first();
                DetalleNotaIngreso::create([
                    'nota_ingreso_id' => $nota->id,
                    'lote' => $row['codigo_lote'],
                    'cantidad' => $row['cantidad'],
                    'producto_id' => $producto->id,
                    'fecha_vencimiento' => $fecha_5
                ]);
            }
        }
    }
    public function rules(): array
    {
        return [
            'codigo' => function ($attribute, $value, $onFailure) {

                $valor = DB::table('productos')->where('codigo', $value)->count();

                if ($valor === 0) {

                    $onFailure('No existe este Producto');
                }
            }
        ];
    }
}
