<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use App\Observers\ClientObserver;
use App\Observers\ServiceObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The model observers for your application.
     *
     * @var array
     */
    protected $observers = [
        Client::class => [ClientObserver::class],
        Service::class => [ServiceObserver::class],
        User::class => [UserObserver::class],
    ];

    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
