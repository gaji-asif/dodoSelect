<?php

namespace App\Actions;

use Ibnuhalimm\LaravelThaiBulkSms\Facades\ThaiBulkSms;
use Lorisleiva\Actions\Concerns\AsAction;
use Propaganistas\LaravelPhone\PhoneNumber;

class SendOtp
{
    use AsAction;

    public function handle(string $mobileNumber, string $otp)
    {
        $appName = config('app.name');
        $formattedPhoneNumber = (string)PhoneNumber::make($mobileNumber)->ofCountry('TH');

        $message = "{$appName} mobile verification. Your OTP is {$otp}";

        return ThaiBulkSms::send($formattedPhoneNumber, $message);
    }
}
