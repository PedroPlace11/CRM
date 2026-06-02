<?php

namespace App\Providers;

use App\Models\CalendarEvent;
use App\Models\Deal;
use App\Models\Entity;
use App\Models\Person;
use App\Policies\CalendarEventPolicy;
use App\Policies\DealPolicy;
use App\Policies\EntityPolicy;
use App\Policies\PersonPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Entity::class, EntityPolicy::class);
        Gate::policy(Person::class, PersonPolicy::class);
        Gate::policy(Deal::class, DealPolicy::class);
        Gate::policy(CalendarEvent::class, CalendarEventPolicy::class);
    }
}
