<?php

namespace App\Console\Commands;

use App\Jobs\WooWebhookSync;
use Illuminate\Console\Command;

class WebhookSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dodo:woocommerce-webhook-status-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Woocommerce Webhook Status Update';

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
        WooWebhookSync::dispatch();
        $this->info('Webhook Status has been updated');
    }
}
