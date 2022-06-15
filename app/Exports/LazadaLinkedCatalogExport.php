<?php

namespace App\Exports;

use App\Models\Lazada;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LazadaLinkedCatalogExport implements FromCollection, WithHeadings, WithMapping
{
    /** @var int */
    private $sellerId;

    /** @var Lazada */
    private $lazadaShops;

    /**
     * Create new instance.
     *
     * @param  int  $sellerId
     * @return void
     */
    public function __construct(int $sellerId)
    {
        $this->sellerId = $sellerId;

        $this->lazadaShops = Lazada::query()
            ->where('seller_id', $sellerId)
            ->get();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $products = Product::query()
            ->selectRaw('products.id, products.product_code, products.product_name')
            ->where('seller_id', $this->sellerId)
            ->with('lazadaProducts')
            ->get();

        return $products->map(function ($product) {
            $lazadaProductsSku = $product->lazadaProducts->map(function ($lazadaProduct) {
                return [
                    'product_code' => $lazadaProduct->product_code,
                    'website_id' => $lazadaProduct->website_id
                ];
            })
                ->sortBy('website_id')
                ->groupBy('website_id')
                ->map(function ($shop) {
                    return $shop->implode('product_code', ', ');
                });

            return [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'product_code' => $product->product_code,
                'lazada_products_shops' => $lazadaProductsSku
            ];
        });
    }


    /**
     * Define the headings row
     *
     * @return array
     */
    public function headings(): array
    {
        $headingShopNames = collect([]);
        foreach ($this->lazadaShops as $shop) {
            $headingShopNames->push($shop->shop_name);
        }

        return $headingShopNames->prepend('###')->all();
    }

    /**
     * Mapping the data according to the headings
     *
     * @param  mixed  $row
     * @return array
     */
    public function map($row): array
    {
        $rowData = collect([$row['product_code']]);
        foreach ($this->lazadaShops as $shop) {
            $rowData->push($row['lazada_products_shops'][$shop->id] ?? '');
        }

        return $rowData->all();
    }
}
