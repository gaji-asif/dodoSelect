<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SheetDoc extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get shet names data
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sheetNames()
    {
        return $this->hasMany(SheetName::class);
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
            return $query->where(function ($query) use ($keyword) {
                $query->where('file_name', 'like', '%' . $keyword . '%')
                    ->orWhere('spreadsheet_id', 'like', '%' . $keyword . '%');
            });
        }

        return $query;
    }
}
