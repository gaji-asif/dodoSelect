<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class OrderAnalysisExport implements FromArray, WithHeadings,WithTitle,WithEvents,WithHeadingRow,WithStartRow
{
    
    protected $data;

    public function __construct($data,$dateFrom,$dateTo)
    {
        $this->data = $data;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->sheetNames = [];
    }

    public function headingRow(): int
    {
    return 2;
    }

    /**
 * @return int
 */
public function startRow(): int
{
    return 3;
}


    public function array():array
    {
        return $this->data;
    }
    

    public function headings(): array
    {
        $headers1 = [
            'Date From'
        ];
        
        $headers2 = [
            'SL#',
            'Name',
            'SKU',
            'Quantity',
            'Incoming',
            'Alert Stock',
            'PO Qty',
            'Stock In',
            'Stock Out',
            'Normarl Price',
            'Lowest Sell Price',
            'Status'
        ];

        if(!empty($this->dateFrom) && !empty($this->dateTo)){
            return [$headers1,$headers2];
        }else{
            return [$headers2];
        }
        
    }

    public function title(): string
    {
        return 'order-analysis.xlsx';
    }

          // ...

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {



               

                
                

                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(60);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(13);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('J')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('K')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('L')->setWidth(15);

                $cellRange = 'A1:Z1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(13);


                if(!empty($this->dateFrom) && !empty($this->dateTo)){
                $arrHeadingDetails = ' ';
                
                //$arrHeadingDetails .= $this->heading_details['title'];
                $arrHeadingDetails .= "Date From  ".$this->dateFrom;
                $arrHeadingDetails .= " To  ".$this->dateTo;

                $event->sheet->setCellValue('A1',$arrHeadingDetails)->mergeCells('A1:Z1')
                    ->getStyle('A1:Z1')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                    
                    $cellRange = 'A2:Z2'; // All headers
                    $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(13);                    
                }



                $event->sheet->getStyle('D:Z')
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);


            },

            
          

            
        ];
    }
}