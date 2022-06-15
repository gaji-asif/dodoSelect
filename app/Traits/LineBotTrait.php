<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

trait LineBotTrait
{
    public function triggerPushMessage($message, $to="")
    {
        try {
            if (!empty($message)) {
                if (empty($to)) {
                    $to = env('LINE_BOT_CHANNEL_2_MESSAGE_TO');
                }
                $httpClient = new CurlHTTPClient(env('LINE_BOT_CHANNEL_2_ACCESS_TOKEN'));
                $bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_BOT_CHANNEL_2_SECRET')]);
                $msg = new TextMessageBuilder($message);
                $bot->pushMessage($to, $msg);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }
}