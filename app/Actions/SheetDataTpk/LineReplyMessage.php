<?php

namespace App\Actions\SheetDataTpk;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use Lorisleiva\Actions\Concerns\AsAction;

class LineReplyMessage
{
    use AsAction;

    public function handle(string $replyToken, string $message)
    {
        $httpClient = new CurlHTTPClient(env('LINE_BOT_TPK_CHANNEL_TOKEN'));
        $bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_BOT_TPK_CHANNEL_SECRET')]);
        $bot->replyMessage($replyToken, new TextMessageBuilder($message));
    }
}
