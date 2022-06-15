<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ImportThailandAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dodo:import-thailand-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Thailand Administrative Data (Province, District, Sub-District, Postcode)';

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
        $this->info('Please wait, this may take a few moments');
        $this->info('Importing data...');
        $this->info('...');

        Artisan::call('db:seed --class=ThailandProvinceSeeder');
        Artisan::call('db:seed --class=ThailandDistrictSeeder');
        Artisan::call('db:seed --class=ThailandSubDistrictSeeder');
        Artisan::call('db:seed --class=ThailandPostCodeSeeder');

        $this->info('Data imported.');
    }
}
