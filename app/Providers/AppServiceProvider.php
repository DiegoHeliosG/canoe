<?php

namespace App\Providers;

use App\Events\DuplicateFundWarningEvent;
use App\Listeners\PersistDuplicateFundWarning;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        EventServiceProvider::disableEventDiscovery();
    }

    public function boot(): void
    {
        Event::listen(DuplicateFundWarningEvent::class, PersistDuplicateFundWarning::class);
    }
}
