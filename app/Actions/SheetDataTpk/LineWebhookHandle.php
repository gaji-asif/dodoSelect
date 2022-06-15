<?php

namespace App\Actions\SheetDataTpk;

use App\Actions\Shopee\OrderPurchase\DailyReportMessage as ShopeeDailyReportMessage;
use App\Jobs\SheetTpkSendDailyReport;
use Illuminate\Support\Str;
use Log;
use Lorisleiva\Actions\Concerns\AsAction;

class LineWebhookHandle
{
    use AsAction;

    public function handle(array $webhookData)
    {
        if (isset($webhookData['events'][0]['message']['text'])) {
            $text = $webhookData['events'][0]['message']['text'];
            $source = $webhookData['events'][0]['source'];
            $replyToken = $webhookData['events'][0]['replyToken'];

            if ($text == '/schedule-start') {
                LineRegisterChatId::make()->handle($source, $replyToken);
                return;
            }

            if ($text == '/schedule-stop') {
                LineRemoveChatId::make()->handle($source, $replyToken);
                return;
            }

            if (Str::startsWith($text, '/daily-tpk')) {
                $dateParameter = trim(Str::after($text, '/daily-tpk'));

                $message = DailyReportMessage::make()->handle($dateParameter);
                SheetTpkSendDailyReport::dispatch($replyToken, $message, 'reply');

                return;
            }

            if (Str::startsWith($text, '/daily-shopee')) {
                $dateParameter = trim(Str::after($text, '/daily-shopee'));

                $message = ShopeeDailyReportMessage::make()->handle($dateParameter);
                SheetTpkSendDailyReport::dispatch($replyToken, $message, 'reply');

                return;
            }

            if (Str::startsWith($text, '/daily')) {
                $dateParameter = trim(Str::after($text, '/daily'));

                $message = DailyReportMessage::make()->handle($dateParameter);
                $message .= "\n\n\n___######################___\n\n\n";
                $message .= ShopeeDailyReportMessage::make()->handle($dateParameter);

                SheetTpkSendDailyReport::dispatch($replyToken, $message, 'reply');

                return;
            }

            if (Str::startsWith($text, '/')) {
                LineResponseInvalidCommand::make()->handle($replyToken);
                return;
            }
        }

        return;
    }
}
