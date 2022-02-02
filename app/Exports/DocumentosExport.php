<?php

namespace App\Exports;

use App\Ventas\Documento\Documento;
use App\Ventas\Guia;
use App\Ventas\Nota;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class DocumentosExport implements FromCollection,WithHeadings,WithEvents
{

    use Exportable;
    public $tipo,$fecha_desde,$fecha_hasta;

    public function headings(): array
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
    }

    function title(): String
    {
        return "CodigoBarraProducto";
    }

    public function __construct($tipo,$fecha_desde,$fecha_hasta)
    {
        $this->tipo = $tipo;
        $this->fecha_desde = $fecha_desde;
        $this->fecha_hasta = $fecha_hasta;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $consulta = Documento::where('estado','!=','ANULADO')->where('tipo_venta', $this->tipo);
            if($this->fecha_desde && $this->fecha_hasta)
            {
                $consulta = $consulta->whereBetween('fecha_documento', [$this->fecha_desde, $this->fecha_hasta]);
            }

        $consulta = $consulta->orderBy('id', 'desc')->get();

        $coleccion = collect();
        foreach($consulta as $doc){
            $transferencia = 0.00;
            $otros = 0.00;
            $efectivo = 0.00;

            if($doc->tipo_pago_id)
            {
                if ($doc->tipo_pago_id == 1) {
                    $efectivo = $doc->importe;
                }
                else if ($doc->tipo_pago_id == 2){
                    $transferencia = $doc->importe ;
                    $efectivo = $doc->efectivo;
                }
                else {
                    $otros = $doc->importe;
                    $efectivo = $doc->efectivo;
                }
            }
            $coleccion->push([
                'RUC-EMISOR' => $doc->ruc_empresa,
                'DOC.' => $doc->nombreDocumento(),
                'CODIGO.DOC' => $doc->tipoDocumento(),
                'TICKET' => $doc->serie.' - '.$doc->correlativo,
                'TIENDA' => $doc->empresa,
                'RUC/DNI' => $doc->documento_cliente,
                'TIPO.CLIENTE' => $doc->tipoDocumentoCliente(),
                'CLIENTE' => $doc->cliente,
                'ESTADO' => $doc->estado,
                'MONEDA' => $doc->simboloMoneda(),
                'MONTO' => $doc->total,
                'OP.GRAVADA' => $doc->sub_total,
                'IVG' => $doc->total_igv,
                'EFECTIVO' => $efectivo,
                'TRANSFERENCIA' => $transferencia,
                'YAPE/PLIN' => $otros,
                'FECHA' => $doc->fecha_documento,
                'ENVIADA' => $doc->sunat == '1' ? 'SI' : 'NO',
                'HASH' => $doc->hash
            ]);
        }

        return $coleccion;
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
