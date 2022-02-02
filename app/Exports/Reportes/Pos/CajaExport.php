<?php

namespace App\Exports\Reportes\Pos;

use App\Pos\MovimientoCaja;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class CajaExport implements FromCollection,WithEvents
{
    public $movimiento;
    use Exportable;

    /*public function headings(): array
    {
        return [
            ["RUC-EMISOR",
            "DOC.",
            "CODIGO.DOC",
            "TICKET",
            "TIENDA",
            "RUC/DNI",
            "TIPO.CLIENTE",
            "CLIENTE",
            "ESTADO",
            "MONEDA",
            "MONTO",
            "OP.GRAVADA",
            "IVG",
            "EFECTIVO",
            "TRANSFERENCIA",
            "YAPE/PLIN",
            "FECHA",
            "ENVIADA",
            "HASH"]
        ];
    }*/

    function title(): String
    {
        return "MOVIMIENTO CAJA ".$this->movimiento->colaborador->persona->nombres;
    }

    public function __construct($movimiento)
    {
        $this->movimiento = $movimiento;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $movimiento = MovimientoCaja::findOrFail($this->movimiento->id);
        $coleccion = collect();
        $coleccion->push([
        ]);

    }
    public function registerEvents(): array
    {
        return [

            BeforeWriting::class => [self::class, 'beforeWriting'],
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:S1')->applyFromArray([

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
                $event->sheet->getStyle('A1:S1')->applyFromArray([
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



               $event->sheet->getColumnDimension('A')->setWidth(20);
               $event->sheet->getColumnDimension('B')->setWidth(20);
               $event->sheet->getColumnDimension('C')->setWidth(20);
               $event->sheet->getColumnDimension('D')->setWidth(20);
               $event->sheet->getColumnDimension('E')->setWidth(20);
               $event->sheet->getColumnDimension('F')->setWidth(20);
               $event->sheet->getColumnDimension('G')->setWidth(20);
               $event->sheet->getColumnDimension('H')->setWidth(20);
               $event->sheet->getColumnDimension('I')->setWidth(20);
               $event->sheet->getColumnDimension('J')->setWidth(20);
               $event->sheet->getColumnDimension('K')->setWidth(20);
               $event->sheet->getColumnDimension('L')->setWidth(20);
               $event->sheet->getColumnDimension('M')->setWidth(20);
               $event->sheet->getColumnDimension('N')->setWidth(20);
               $event->sheet->getColumnDimension('O')->setWidth(20);
               $event->sheet->getColumnDimension('P')->setWidth(20);
               $event->sheet->getColumnDimension('Q')->setWidth(20);
               $event->sheet->getColumnDimension('R')->setWidth(20);
               $event->sheet->getColumnDimension('S')->setWidth(20);

            },
        ];
    }
}
