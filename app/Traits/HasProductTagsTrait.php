<?php

namespace App\Traits;

use App\Models\Product;
use App\Models\ProductTag;
use Illuminate\Support\Collection;

trait HasProductTagsTrait {

    public function giveTagsTo(... $tags) {

        $tags = $this->getAllTags($tags);
//        dd($tags);
        if($tags === null) {
            return $this;
        }
        $this->tags()->saveMany($tags);
        return $this;
    }

    public function withdrawProductsTo( ... $products )
    {
        $product = $this->getAllProducts($products);
        $this->products()->detach($product);
        return $this;
    }

    public function refreshTags( ... $tags ) {

        $this->tags()->detach();
        return $this->giveTagsTo($tags);
    }

    public function hasTagTo($tag) {

        return $this->hasTag($tag);
    }

    public function hasProduct( ... $products ) {

        foreach ($products as $product) {
            if ($this->products->contains('name', $product)) {
                return true;
            }
        }
        return false;
    }

    public function getProductNames(): Collection
    {
        return $this->products->pluck('name');
    }

    public function getProductIds(): Collection
    {
        return $this->products->pluck('id');
    }

    public function getTagNames()
    {
        return $this->tags->pluck('name');
    }

    public function getTagIds()
    {
        return $this->tags->pluck('id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class,'product_has_tags', 'product_tag_id', 'product_id');
    }

    public function tags()
    {
        return $this->belongsToMany(ProductTag::class, 'product_has_tags', 'product_id', 'product_tag_id');
    }

    protected function hasTag($tag) {

        return (bool) $this->tags->where('id', $tag)->count();
    }

    protected function getAllTags(array $tags) {

        return ProductTag::whereIn('id',$tags)->get();
    }

    protected function getAllProducts(array $product) {

        return Product::whereIn('id',$product)->get();
    }

}
