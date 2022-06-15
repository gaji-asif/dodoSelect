<?php

namespace App\Utilities;

use App\Models\Translation;
use Illuminate\Support\Facades\Cache;

class TranslationUtil
{
    private $lang = 'en';

    public function __construct($lang)
    {
        $this->lang = $lang;
    }

    public function resources()
    {
        $twentyForHourInSeconds = 86400;

        $translations = collect(Cache::remember('translations', $twentyForHourInSeconds, function() {
            return Translation::all();
        }));

        $pluckWithKey = $translations->pluck('lang_' . $this->lang, 'keyword');
        $pluckWithEnglish = $translations->pluck('lang_' . $this->lang, 'lang_en');

        return $pluckWithKey->merge($pluckWithEnglish)->all();
    }
}