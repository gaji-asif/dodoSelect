<?php

namespace App\Console\Commands;

use App\Jobs\ScanTranslationWordJob;
use Illuminate\Console\Command;

class ScanTranslationWord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dodo:scan-translation-word';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan translation word and store them into translations table.';

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
        $this->info('Scanner works in the background...');
        $this->info('...');

        ScanTranslationWordJob::dispatch();

        return 0;
    }
}
