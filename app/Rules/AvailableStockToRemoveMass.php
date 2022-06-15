<?php

namespace App\Rules;

use App\Models\ProductMainStock;
use App\Models\StockLog;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class AvailableStockToRemoveMass implements Rule
{
    /**
     * Define properties
     *
     * @var mixed
     */
    private $type;
    private $productIds;

    /**
     * Create a new rule instance.
     *
     * @param int $type
     * @param array $productIds
     * @return void
     */
    public function __construct(int $type, array $productIds)
    {
        $this->type = $type;
        $this->productIds = $productIds;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $values
     * @return bool
     */
    public function passes($attribute, $values)
    {
        $passedStatus = true;

        if ($this->type == StockLog::CHECK_IN_OUT_REMOVE) {
            foreach ($this->productIds as $idx => $productId) {
                $productMainStock = ProductMainStock::where('product_id', $productId)->first();
                $currentStock = !empty($productMainStock->quantity) ? $productMainStock->quantity : 0;
                $stockAdjustValue = isset($values[$idx]) ? $values[$idx] : 0;

                if ($currentStock < $stockAdjustValue) {
                    $passedStatus = false;
                }
            }
        }

        return $passedStatus;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('translation.translate.validation.custom.product.available_stock_to_remove_mass');
    }
}
