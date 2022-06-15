<?php

namespace App\Actions\SheetDataTpk;

use App\Models\SheetDataTpkLineChat;
use Lorisleiva\Actions\Concerns\AsAction;

class LineRegisterChatId
{
    use AsAction;

    public function handle(array $sourceData, string $replyToken)
    {
        $lineChatType = 'user';
        $lineChatId = '';

        if ($sourceData['type'] == 'user') {
            $lineChatType = 'user';
            $lineChatId = $sourceData['userId'];
            $lineMessage = 'I will send the scheduled report to you.';
        }

        if ($sourceData['type'] == 'group') {
            $lineChatType = 'group';
            $lineChatId = $sourceData['groupId'];
            $lineMessage = 'I will send the scheduled report to this group.';
        }

        if (!empty($lineChatId)) {
            $tpkChatId = SheetDataTpkLineChat::where('chat_id', $lineChatId)->first();

            if (empty($tpkChatId)) {
                $tpkChatId = new SheetDataTpkLineChat();
                $tpkChatId->type = $lineChatType;
                $tpkChatId->chat_id = $lineChatId;
                $tpkChatId->save();
            }

            LineReplyMessage::make()->handle($replyToken, $lineMessage);
        }
    }
}
