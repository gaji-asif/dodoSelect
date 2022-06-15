<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\Shopee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ShopeeLinkedCatalogExport implements FromCollection, WithHeadings, WithMapping
{
    /** @var int */
    private $sellerId;

    /** @var Shopee */
    private $shopeeShops;

    /**
     * Create new instance
     *
     * @param  int  $sellerId
     * @return void
     */
    public function __construct(int $sellerId)
    {
        $this->sellerId = $sellerId;

        $this->shopeeShops = Shopee::query()
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
            ->with('shopeeProducts')
            ->get();

        return $products->map(function ($product) {
            $shopeeProductsSku = $product->shopeeProducts->map(function ($shopeeProduct) {
                return [
                    'product_code' => $shopeeProduct->product_code,
                    'website_id' => $shopeeProduct->website_id
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
                'shopee_products_shops' => $shopeeProductsSku
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
        foreach ($this->shopeeShops as $shop) {
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
        foreach ($this->shopeeShops as $shop) {
            $rowData->push($row['shopee_products_shops'][$shop->shop_id] ?? '');
        }

        return $rowData->all();
    }
}
