<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WooShop;
use Illuminate\Support\Facades\Http;


class WooWebhookSubChildSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $webhook_updateUrl;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($webhook_updateUrl, $data)
    {
        $this->webhook_updateUrl = $webhook_updateUrl;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = Http::withOptions([
            'verify' => false,
        ])
        ->put($this->webhook_updateUrl, $this->data);
    }
}