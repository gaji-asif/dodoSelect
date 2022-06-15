<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerShippingMethod extends Model
{
    /**
     * Define `is_selected` field value
     *
     * @var mixed
     */
    CONST IS_SELECTED_NO = 0;
    CONST IS_SELECTED_YES = 1;

    /**
     * Define `enable_status` field value
     *
     * @var mixed
     */
    CONST ENABLE_STATUS_NO = 0;
    CONST ENABLE_STATUS_YES = 1;

    /**
     * Define `is_new_status` field value
     *
     * @var mixed
     */
    CONST IS_NEW_STATUS_NO = 0;
    CONST IS_NEW_STATUS_YES = 1;

    /**
     * Relationship to `shipping_costs` table
     *
     * @return mixed
     */
    public function shipping_cost()
    {
        return $this->belongsTo(ShippingCost::class)->withDefault();
    }

    /**
     * Get All `enable_status` field value
     *
     * @return array
     */
    public static function getAllEnableStatus()
    {
        return [
            self::ENABLE_STATUS_NO => 'Disable',
            self::ENABLE_STATUS_YES => 'Enable'
        ];
    }

    /**
     * Get All `is_selected` field value
     *
     * @return array
     */
    public static function getAllIsSelected()
    {
        return [
            self::IS_SELECTED_NO => 'No',
            self::IS_SELECTED_YES => 'Yes'
        ];
    }
}
