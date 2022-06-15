<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockValueExport implements FromCollection, ShouldAutoSize, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    private $sellerId;

    /**
     * Create new instance
     *
     * @param  int  $sellerId
     * @return void
     */
    public function __construct(int $sellerId)
    {
        $this->sellerId = $sellerId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Product::query()
            ->stockValueReport()
            ->where('seller_id', $this->sellerId)
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Set width of the column
     *
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'B' => 50
        ];
    }

    /**
     * Define the headings row
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Product Name',
            'SKU',
            'Quantity',
            'Price',
            'Cost Price',
            'Stock Value',
            'Stock Cost Value',
            'Profit Margin (%)'
        ];
    }

    /**
     * Mapping the data according to the heading
     *
     * @param  mixed  $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->product_name,
            $row->product_code,
            $row->quantity,
            round($row->price, 3),
            round($row->pc_cost_price, 3),
            round($row->stock_value, 3),
            round($row->stock_cost_value, 3),
            round($row->profit_margin, 2)
        ];
    }

    /**
     * Style the cell
     *
     * @param  Worksheet  $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true
                ]
            ]
        ];
    }
}
