<?php

namespace App\Jobs;

use App\Actions\SheetDataTpk\LinePushMessage;
use App\Actions\SheetDataTpk\LineReplyMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SheetTpkSendDailyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var string */
    private $chatId;

    /** @var string */
    private $composedMessage;

    /** @var string */
    private $messageType;

    /**
     * Create a new job instance.
     *
     * @param  string  $chatId
     * @param  string  $composedMessage
     * @param  string  $messageType
     * @return void
     */
    public function __construct(string $chatId, string $composedMessage, string $messageType = 'push')
    {
        $this->chatId = $chatId;
        $this->composedMessage = $composedMessage;
        $this->messageType = $messageType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->messageType == 'push') {
            LinePushMessage::make()->handle($this->chatId, $this->composedMessage);
            return;
        }

        if ($this->messageType == 'reply') {
            LineReplyMessage::make()->handle($this->chatId, $this->composedMessage);
            return;
        }
    }
}
