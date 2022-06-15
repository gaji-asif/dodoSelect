<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class StockExport implements FromArray, WithHeadings,WithTitle
{
    
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
        $this->sheetNames = [];
    }

    public function array():array
    {
        return $this->data;
    }
    

    public function headings(): array
    {
        return [
            'Product ID',
            'Name',
            'SKU',
            'Quantity',
            'Alert Stock',
        ];
    }

    public function title(): string
    {
        return 'lowstock.xlsx';
    }
}