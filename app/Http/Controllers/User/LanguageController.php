<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\Language\UpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    /**
     * User user preferred language
     *
     * @param  \App\Http\Requests\Profile\Language\UpdateRequest
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request)
    {
        try {
            $user = User::where('id', Auth::user()->id)->first();

            $user->pref_lang = $request->lang;
            $user->save();

            App::setLocale($request->pref_lang);

            return redirect()->back()->with('lang-success', __('translation.language_updated'));

        } catch (\Throwable $th) {
            report($th);

            return redirect()->back()->with('lang-error', __('translation.something_went_wrong'));
        }
    }
}
