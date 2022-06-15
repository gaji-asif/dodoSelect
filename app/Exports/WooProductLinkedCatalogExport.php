<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\WooShop;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WooProductLinkedCatalogExport implements FromCollection, WithHeadings, WithMapping
{
    /** @var int */
    private $sellerId;

    /** @var WooShop */
    private $wooShops;

    /**
     * Create new instance
     *
     * @param  int  $sellerId
     * @return void
     */
    public function __construct(int $sellerId)
    {
        $this->sellerId = $sellerId;

        $this->wooShops = WooShop::query()
            ->where('seller_id', $sellerId)
            ->with('shops')
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
            ->with('wooProducts')
            ->get();

        return $products->map(function ($product) {
            $wooProductsSku = $product->wooProducts->map(function ($wooProduct) {
                return [
                    'product_code' => $wooProduct->product_code,
                    'website_id' => $wooProduct->website_id
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
                'woo_products_shops' => $wooProductsSku
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
        foreach ($this->wooShops as $wooShop) {
            $headingShopNames->push($wooShop->shops->name);
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
        foreach ($this->wooShops as $shop) {
            $rowData->push($row['woo_products_shops'][$shop->id] ?? '');
        }

        return $rowData->all();
    }
}
