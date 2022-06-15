<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TaxRateSetting extends Model
{
    use HasFactory;

    /**
     * Append custom attributes
     *
     * @var array
     */
    protected $appends = [
        'company_logo_url'
    ];

    /**
     * Accessor for `company_logo_url`
     *
     * @return string
     */
    public function getCompanyLogoUrlAttribute()
    {
        $logoAttribute = $this->attributes['company_logo'] ?? null;

        if (!empty($logoAttribute) && Storage::disk('public')->exists($logoAttribute)) {
            return asset(Storage::url($logoAttribute));
        }

        return asset('No-Image-Found.png');
    }
}
