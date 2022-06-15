<?php

namespace App\Providers;

use App\Events\LineEvent;
use App\Events\LineEventACSale;
use App\Listeners\TextListener;
use App\Listeners\TextListenerACSale;
use App\Models\Category;
use App\Models\OrderPurchase;
use App\Models\Shipment;
use App\Models\StockLog;
use App\Observers\CategoryObserver;
use App\Observers\OrderPurchaseObserver;
use App\Observers\ShipmentObserver;
use App\Observers\StockLogObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        LineEvent::class => [
            TextListener::class,
        ],
        LineEventACSale::class => [
            TextListenerACSale::class,
        ]
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        StockLog::observe(StockLogObserver::class);
        OrderPurchase::observe(OrderPurchaseObserver::class);
        Category::observe(CategoryObserver::class);
        Shipment::observe(ShipmentObserver::class);
    }
}
