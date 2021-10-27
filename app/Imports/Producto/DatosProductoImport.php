<?php

namespace App\Imports\Producto;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DatosProductoImport implements ToCollection, WithHeadingRow
{
    public $data = array();
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            array_push($this->data, array(
                'unidadmedida' => $row['unidadmedida'],
                'peso' => $row['peso'],
                'nombre' => $row['nombre'],
                'categorias' => $row['categorias'],
                'marcas' => $row['marcas'],
                'almacenes' => $row['almacenes'],
                'stockminimo' => $row['stockminimo'],
                'precioventaminimo' => $row['precioventaminimo'],
                'precioventamaximo' => $row['precioventamaximo'],
                'codigobarra' => $row['codigobarra'],
                'igv' => $row['igv'],
                'precionormal' => $row['precionormal'],
                'preciodistribuidor' => $row['preciodistribuidor'],
            ));
        }
    }
    public function get_data()
    {
        return $this->data;
    }
}
