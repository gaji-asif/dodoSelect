<?php

namespace App\Rules\WCProduct;

use App\Models\WooProduct;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class RequiredIfVariableType implements Rule
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

        Log::info($this->wooProduct);
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
        return $this->typeIsVariable() && !empty($value);
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
     * Check if doesnt have parent and type is variable
     *
     * @return bool
     */
    private function typeIsVariable()
    {
        return !empty($this->wooProduct->parent->id) OR $this->wooProduct->type != 'variable';
    }
}
