<?php

namespace App\Rules\WCProduct;

use App\Models\WooProduct;
use Illuminate\Contracts\Validation\Rule;

class RequiredIfHasParent implements Rule
{
    /**
     * Define properties
     *
     * @var mixed
     */
    private $wooProduct;

    /**
     * Create a new rule instance.
     *
     * @param  \App\Models\WooProduct  $wooProduct
     * @return void
     */
    public function __construct(WooProduct $wooProduct)
    {
        $this->wooProduct = $wooProduct;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->hasParent() && !empty($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.required');
    }

    /**
     * Check if has parent
     *
     * @return bool
     */
    private function hasParent()
    {
        return !empty($this->wooProduct->parent->id);
    }
}
