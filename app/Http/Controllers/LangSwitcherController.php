<?php

namespace App\Http\Controllers;

use App\Enums\UserPrefLangEnum;
use Illuminate\Support\Facades\Session;

class LangSwitcherController extends Controller
{
    public function __invoke(string $lang = 'en')
    {
        $fallbackLang = config('app.fallback_locale');
        Session::put('app_locale', $fallbackLang);

        if (in_array($lang, array_keys(UserPrefLangEnum::toArray()))) {
            Session::put('app_locale', $lang);
        }

        return redirect()->back();
    }
}
