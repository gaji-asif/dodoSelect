<?php

namespace App\Rules;

use App\Models\{ProductMainStock, StockLog};
use Illuminate\Contracts\Validation\Rule;

class AvailableStockToRemove implements Rule
{
    /**
     * Define properties
     *
     * @var mixed
     */
    private $stockLogId;
    private $productId;
    private $type;

    /**
     * Create a new rule instance.
     *
     * @param int $stockLogId;
     * @param int $productId
     * @param int $type
     * @return void
     */
    public function __construct(
        int $stockLogId,
        int $productId,
        int $type
        )
    {
        $this->stockLogId = $stockLogId;
        $this->productId = $productId;
        $this->type = $type;
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
        if ($this->type == StockLog::CHECK_IN_OUT_REMOVE) {
            $stockLog = StockLog::where('id', $this->stockLogId)->first();
            $productStock = ProductMainStock::where('product_id', $this->productId)->first();

            $prevStockLogQty = !empty($stockLog->quantity) ? $stockLog->quantity : 0;
            $currentQty = !empty($productStock->quantity) ? $productStock->quantity : 0;

            return $currentQty + $prevStockLogQty >= $value;
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
        return __('translation.validation.custom.product.available_stock_to_remove');
    }
}
