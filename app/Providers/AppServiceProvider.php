<?php

namespace App\Providers;

use App\Enums\UserPrefLangEnum;
use App\Models\Category;

use App\Observers\CategoryObserver;
use App\Utilities\QueueJobStatusUtil;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

//use Revolution\Line\Facades\Bot;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultstringLength(191);
        Category::observe(CategoryObserver::class);

        /**
         * Track any queue job status by using cache
         */
        Queue::before(function (JobProcessing $event) {
            $jobPayload = $event->job->payload();

            $queueJobStatus = new QueueJobStatusUtil($jobPayload['displayName']);
            $queueJobStatus->setProcessing(true);
        });

        Queue::failing(function (JobFailed $event) {
            $jobPayload = $event->job->payload();

            $queueJobStatus = new QueueJobStatusUtil($jobPayload['displayName']);
            $queueJobStatus->setProcessing(false);
        });

        Queue::after(function (JobProcessed $event) {
            $jobPayload = $event->job->payload();

            $queueJobStatus = new QueueJobStatusUtil($jobPayload['displayName']);
            $queueJobStatus->setProcessing(false);
        });
        /** ------- end of queue job tracking --------- */

        View::share('userPrefLangs', UserPrefLangEnum::toArray());
    }
}
