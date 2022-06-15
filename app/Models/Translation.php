<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'lang_en'
    ];

    /**
     * Hidden attribute by default
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Query to searching from datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchDataTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            return $query->where(function ($translation) use ($keyword) {
                $translation->where('lang_en', 'like', "%$keyword%");
            });
        }

        return;
    }
}
