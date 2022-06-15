<?php

namespace App\Traits;

trait CalculateDiscountAmountWoo
{
    /**
     * return the calculated discount amount for woo product
     *
     * @param  int|null $regular_price, $sale_price
     * @return int
     */
    public function getDiscountAmount($regular_price = null, $sale_price = null)
    {
        $discount = null;
        $price = null;
 
        //if there is no sale price price will = regular price
        if((!empty($regular_price) && $regular_price>0) && (empty($sale_price) && $sale_price !=0)){

        	$price = $regular_price;
        	$discount = ($regular_price - $price) / $regular_price;

        }

        //if has sale price then price will = sale price
        if((!empty($sale_price) && $sale_price>0) && (!empty($regular_price) && $regular_price > 0)){
        	$price = $sale_price;
        	$discount = ($regular_price - $price) / $regular_price;
        	
        }

        if($discount > 0){
        	return number_format($discount*100);
        }
        else{
        	return 0;
        }
    }

}