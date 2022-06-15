<?php

namespace App\Console\Commands;

use App\Actions\Lazada\OrderPurchase\DailyReportMessage;
use App\Jobs\SheetTpkSendDailyReport;
use App\Models\LazadaOrderPurchase;
use App\Models\SheetDataTpkLineChat;
use Illuminate\Console\Command;

class DailyReportLazadaSale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dodo:daily-report-lazada-sale
                            {--date=null : Date of Daily Report (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Line Apps notification of Lazada sales';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Sending message...');
        $this->info('...');

        $date = $this->option('date');

        //$composedMessage = DailyReportMessage::make()->handle($date);

        $composedMessage = "asif";

        $recipients = SheetDataTpkLineChat::all();

        $delay = 0;
        foreach ($recipients as $recipient) {
            SheetTpkSendDailyReport::dispatch($recipient->chat_id, $composedMessage, 'push')
                ->delay(now()->addSeconds($delay));

            $delay += 2;
        }

        return 0;
    }
}
