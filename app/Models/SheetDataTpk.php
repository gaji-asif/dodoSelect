<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheetDataTpk extends Model
{
    use HasFactory;

    /**
     * The actual column on google sheet
     *
     * @var array
     */
    public static $actualSheetColumns = [
        'A' => 'Date',
        'B' => 'First Name',
        'C' => 'Surname',
        'D' => 'Order',
        'E' => 'QTY',
        'F' => 'Shipping',
        'G' => 'Amount',
        'H' => 'Type',
        'I' => 'Channel',
        'J' => 'Channel ID',
        'K' => 'Phone',
        'L' => 'Tracking',
        'M' => '',
        'N' => 'Order By',
        'O' => 'Shop',
        'P' => 'Line ID',
        'Q' => 'Charged Shipping Cost',
        'R' => 'Actual Shipping Cost'
    ];

    /**
     * Get shops data using
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shopData()
    {
        return $this->belongsTo(Shop::class, 'shop', 'code');
    }

    /**
     * Join to others table for datatable
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinedDatatable($query)
    {
        $sheetDataTpkTable = $this->getTable();
        $sheetNameTable = (new SheetName())->getTable();

        return $query->selectRaw("{$sheetDataTpkTable}.*, {$sheetNameTable}.sheet_name")
            ->join("{$sheetNameTable}", "{$sheetDataTpkTable}.sheet_name_id", '=', "{$sheetNameTable}.id");
    }

    /**
     * Query to searching data from datatable
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $keyword
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchTable($query, $keyword = null)
    {
        if (! empty($keyword)) {
            $sheetDataTpkTable = $this->getTable();
            $sheetNameTable = (new SheetName())->getTable();

            return $query->where(function ($query) use ($keyword, $sheetDataTpkTable, $sheetNameTable) {
                $query->where("{$sheetDataTpkTable}.date", 'like', '%' . $keyword . '%')
                    ->orWhere("{$sheetDataTpkTable}.amount", 'like', '%' . $keyword . '%')
                    ->orWhere("{$sheetDataTpkTable}.charged_shipping_cost", 'like', '%' . $keyword . '%')
                    ->orWhere("{$sheetNameTable}.sheet_name", 'like', '%' . $keyword . '%');
            });
        }

        return $query;
    }

    /**
     * Select query for order analysis datatable
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $interval
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSelectOrderAnalysis($query, $interval)
    {
        $dateFormatQuery = "DATE_FORMAT(date, '%Y-%m-%d')";

        if ($interval == 'per_week') {
            $dateFormatQuery = "STR_TO_DATE(CONCAT(YEARWEEK(date, 3), '1'), '%x%v%w')";
        }

        if ($interval == 'per_month') {
            $dateFormatQuery = "DATE_FORMAT(date, '%Y-%m')";
        }

        if ($interval == 'per_year') {
            $dateFormatQuery = "DATE_FORMAT(date, '%Y')";
        }

        return $query->selectRaw("
            {$dateFormatQuery} as str_date,
            COUNT(*) AS total_orders,
            SUM(amount) AS total_amount,
            shop
        ");
    }

    /**
     * Query to filter by `shop` field
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $shopCode
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByShop($query, $shopCode = null)
    {
        if (! empty($shopCode)) {
            return $query->where('shop', $shopCode);
        }

        return $query;
    }

    /**
     * Query to filter by `channel` field
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $channel
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByChannel($query, $channel = '')
    {
        if ($channel == 'all') {
            return $query;
        }

        if ($channel == '') {
            return $query->where('channel', '');
        }

        return $query->where('channel', $channel);
    }

    /**
     * Get all actual columns
     *
     * @return array
     */
    public static function getAllActualColumns()
    {
        return self::$actualSheetColumns;
    }

    /**
     * Get actual column of tpk sheet data
     *
     * @return array
     */
    public static function getHeaderColumnNames()
    {
        return array_values(self::getAllActualColumns());
    }

    /**
     * Get index of column by header name
     *
     * @param  string  $columnName
     * @return int
     */
    public static function getColumnIndexByHeaderName(string $columnName)
    {
        return array_search($columnName, self::getHeaderColumnNames());
    }
}
