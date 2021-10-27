<?php

namespace App\Exports;

use App\Almacenes\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductosExport implements ShouldAutoSize,WithHeadings,FromArray,WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $productos= DB::table('productos as p')->where('p.estado','ACTIVO')->get();
        $data=array();
        foreach($productos as $producto)
        {
            $categoria=DB::table('categorias')->where('id',$producto->categoria_id)->first();
            $medida=DB::table('tabladetalles')->where('id',$producto->medida)->first();
            $marca=DB::table('marcas')->where('id',$producto->marca_id)->first();
            $almacen=DB::table('almacenes')->where('id',$producto->almacen_id)->first();
            array_push($data,array(
                "codigo"=>$producto->codigo,
                "nombre"=>$producto->nombre,
                "descripcion"=>$producto->descripcion,
                "categoria"=>$categoria->descripcion,
                "medida"=>$medida->descripcion,
                "marca"=>$marca->marca,
                "almacen"=>$almacen->descripcion,
                "codigo_barra"=>$producto->codigo_barra,
                "stock"=>$producto->stock,
                "stock_minimo"=>$producto->stock_minimo,
                "precio_venta_minimo"=>$producto->precio_venta_minimo,
                "precio_venta_maximo"=>$producto->precio_venta_maximo,
                "peso_producto"=>$producto->peso_producto,
                "igv"=>($producto->igv=="1") ? "SI": "NO"

            ));
        }
        /*->join('familias as f','f.id','p.familia_id')
        ->join('subfamilias as sf','sf.id','p.sub_familia_id')
        ->select('p.codigo','p.nombre','p.descripcion'
                  ,'f.familia','sf.descripcion')
        ->where('p.estado','ACTIVO')->get();*/
        return $data;
    }

    public function headings(): array
    {
        return [
            ['codigo',
            'nombre',
            'descripcion',
            'categoria',
            'medida',
            'marca',
            'almacen',
            'codigo_barra',
            'stock',
            'stock_minimo',
            'precio_venta_minimo',
            'precio_venta_maximo',
            'peso_producto',
            'igv',
            'codigo_lote',
            'cantidad',
            'fecha_vencimiento',
            'fecha_entrega'
            ]
        ]
       ;
    }
    public function registerEvents(): array
    {
        return [

            BeforeWriting::class => [self::class, 'beforeWriting'],
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('O1:R1')->applyFromArray([


                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                        'rotation' => 90,
                        'startColor' => [
                            'argb' => '00bbd4',
                        ],
                        'endColor' => [
                            'argb' => '00bbd4',
                        ],
                    ],


                ]

                );
                $event->sheet->getStyle('A1:N1')->applyFromArray([


                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                        'rotation' => 90,
                        'startColor' => [
                            'argb' => '1ab394',
                        ],
                        'endColor' => [
                            'argb' => '1ab394',
                        ],
                    ],


                ]

                );



               // $event->sheet->getColumnDimension('C')->setWidth(20);

            },
        ];
    }
}
