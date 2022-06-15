<?php

namespace App\Actions\SheetDataTpk;

use App\Models\SheetDataTpkLineChat;
use Lorisleiva\Actions\Concerns\AsAction;

class LineRemoveChatId
{
    use AsAction;

    public function handle(array $sourceData, string $replyToken)
    {
        $lineChatType = 'user';
        $lineChatId = '';

        if ($sourceData['type'] == 'user') {
            $lineChatType = 'user';
            $lineChatId = $sourceData['userId'];
        }

        if ($sourceData['type'] == 'group') {
            $lineChatType = 'group';
            $lineChatId = $sourceData['groupId'];
        }

        if (!empty($lineChatId)) {
            SheetDataTpkLineChat::query()
                ->where('type', $lineChatType)
                ->where('chat_id', $lineChatId)
                ->delete();

            $lineMessage = 'You\'re free from my scheduled report.';
            LineReplyMessage::make()->handle($replyToken, $lineMessage);
        }
    }
}
