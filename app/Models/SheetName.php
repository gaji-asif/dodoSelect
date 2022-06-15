<?php

namespace App\Models;

use App\Enums\SheetNameSyncStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheetName extends Model
{
    use HasFactory;

    /**
     * Attributes should casts
     *
     * @var array
     */
    protected $casts = [
        'last_sync' => 'datetime',
        'sync_status' => SheetNameSyncStatusEnum::class
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleted(function ($sheetName) {
            SheetDataTpk::where('sheet_name_id', $sheetName->id)->delete();
        });
    }

    /**
     * Get sheet doc data
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sheetDoc()
    {
        return $this->belongsTo(SheetDoc::class, 'sheet_doc_id')->withDefault();
    }

    /**
     * Get seller data
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id')->withDefault();
    }

    /**
     * Get sheet data tpk data
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sheetDataTpks()
    {
        return $this->hasMany(SheetDataTpk::class, 'sheet_name_id');
    }

    /**
     * Get sheet doc data
     *
     * @param \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $keyword
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchTable($query, $keyword = null)
    {
        if (! empty($keyword)) {
            return $query->where(function ($query) use ($keyword) {
                $query->where('sheet_name', 'like', '%' . $keyword . '%');
            });
        }

        return $query;
    }

    /**
     * Get sheet names that available to sync
     *
     * @param \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailableToSync($query)
    {
        $currentTimestamp = now()->format('Y-m-d H:i:s');

        return $query->whereRaw("
            allow_to_sync = 1
            AND (
                last_sync IS NULL
                OR
                TIMESTAMPDIFF(MINUTE, '{$currentTimestamp}', IFNULL(last_sync, '{$currentTimestamp}')) <= -10
            )");
    }
}
