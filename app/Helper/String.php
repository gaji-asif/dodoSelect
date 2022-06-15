<?php

if (!function_exists('escape_user_phone')) {
    function escape_user_phone($value) {
        return preg_replace('/[^0-9]+/i', '', $value);
    }
}

if(!function_exists('product_id_by_code')){
    function product_id_by_code($product_code)
    {
        $product = \App\Models\Product::where("product_code", trim($product_code))->first();
        return $product->id;
    }
}

if (!function_exists('currency_symbol')) {
    function currency_symbol($currencyCode = 'USD') {
		$currencies = [
            'USD'=>'$', // US Dollar
            'EUR'=> '€', // Euro
            'CRC'=> '₡', // Costa Rican Colón
            'GBP'=> '£', // British Pound Sterling
            'ILS'=> '₪', // Israeli New Sheqel
            'INR'=> '₹', // Indian Rupee
            'JPY'=> '¥', // Japanese Yen
            'KRW'=> '₩', // South Korean Won
            'NGN'=> '₦', // Nigerian Naira
            'PHP'=> '₱', // Philippine Peso
            'PLN'=> 'zł', // Polish Zloty
            'PYG'=> '₲', // Paraguayan Guarani
            'THB'=> '฿', // Thai Baht
            'UAH'=> '₴', // Ukrainian Hryvnia
            'VND'=> '₫', // Vietnamese Dong)
        ];

		if(array_key_exists($currencyCode, $currencies)){
			return $currencies[$currencyCode];
		}

        return $currencyCode;
    }
}


if (!function_exists('product_image_url')) {
    function product_image_url($imagePath = null)
    {
        if (!empty($imagePath) && file_exists(public_path($imagePath))) {
            return asset($imagePath);
        }

        return asset('No-Image-Found.png');
    }
}


if (!function_exists('currency_number')) {
    function currency_number($number = 0, $decimals = 0) {
        $currencyFormat = number_format($number, $decimals);
        $explodedCurrencyFormat = explode('.', $currencyFormat);
        $decimalsValue = intval(end($explodedCurrencyFormat));

        if ($decimalsValue > 0) {
            return $currencyFormat;
        }

        return number_format($number);
    }
}
