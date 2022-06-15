<?php

namespace App\Console\Commands;

use App\Actions\GoogleSheet\FetchTpkPackingData;
use Illuminate\Console\Command;

class SheetDataTpkSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dodo:sheet-data-tpk-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync TPK Packing Data on Google Sheet';

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
        $this->info('We are syncing data in the background...');
        $this->info('...');

        FetchTpkPackingData::make()->handle();

        return 0;
    }
}
