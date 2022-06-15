<?php

namespace App\Rules;

use App\Models\Shop;
use Illuminate\Contracts\Validation\Rule;

class UniqueShopCode implements Rule
{
    /** @var int */
    protected $sellerId;

    /** @var int|null */
    protected $exceptShopId = null;

    /**
     * Create a new rule instance.
     *
     * @param  int  $sellerId
     * @param  int|null  $exceptShopId
     * @return void
     */
    public function __construct(int $sellerId, ?int $exceptShopId = null)
    {
        $this->sellerId = $sellerId;
        $this->exceptShopId = $exceptShopId;
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
        $existingShop = Shop::query()
            ->where('seller_id', $this->sellerId)
            ->where('code', trim($value))
            ->first();

        if (!empty($existingShop)) {
            if (empty($this->exceptShopId)) {
                return false;
            }

            return $existingShop->id == $this->exceptShopId;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.unique');
    }
}
