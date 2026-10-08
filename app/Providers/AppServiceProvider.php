<?php

namespace App\Providers;

use App\Acceptance\Operations\AcceptanceOperationRegistry;
use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Handlers\ListAcceptanceApps;
use App\Acceptance\Operations\Handlers\PlanAcceptance;
use App\Acceptance\Operations\Handlers\RunAcceptanceScenario;
use App\Services\AcceptanceAppRegistry;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AcceptanceAppRegistry::class);
        $this->app->singleton(AcceptanceOperationRegistry::class, function ($app) {
            $registry = new AcceptanceOperationRegistry;
            // Factories stay lazy so list/plan never construct the execution graph.
            $registry->register('acceptance.list', fn () => $app->make(ListAcceptanceApps::class));
            $registry->register('acceptance.plan', fn () => $app->make(PlanAcceptance::class));
            $registry->register('acceptance.run', fn () => $app->make(RunAcceptanceScenario::class));

            return $registry;
        });
        $this->app->singleton(AcceptanceOperationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
