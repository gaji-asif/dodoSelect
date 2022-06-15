<?php

namespace App\Traits\Models;

trait ThailandAddressQuery
{
    /**
     * Search query from select two
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchSelectTwo($query, $keyword = null)
    {
        if (!empty($keyword)) {
            return $query->where(function($query) use ($keyword) {
                $query->where('name_en', 'like', '%'.$keyword.'%')
                    ->orWhere('name_th', 'like', '%'.$keyword.'%');
            });
        }

        return;
    }
}