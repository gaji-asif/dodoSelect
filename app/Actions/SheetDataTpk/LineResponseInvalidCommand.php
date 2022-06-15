<?php

namespace App\Actions\SheetDataTpk;

use Lorisleiva\Actions\Concerns\AsAction;

class LineResponseInvalidCommand
{
    use AsAction;

    public function handle(string $replyToken)
    {
        $message = 'Sorry, I don\'t understand that command. :)';

        LineReplyMessage::make()->handle($replyToken, $message);
    }
}
