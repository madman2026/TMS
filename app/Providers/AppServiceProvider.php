<?php

namespace App\Providers;

use App\Acceptance\Modules\Contracts\TargetModuleCreator;
use App\Acceptance\Modules\NwidartTargetModuleCreator;
use App\Acceptance\Operations\AcceptanceOperationRegistry;
use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Handlers\ApproveOperation;
use App\Acceptance\Operations\Handlers\CancelAcceptanceBatch;
use App\Acceptance\Operations\Handlers\CancelOperationRequest;
use App\Acceptance\Operations\Handlers\CreateAcceptanceApp;
use App\Acceptance\Operations\Handlers\CreateAcceptanceComponent;
use App\Acceptance\Operations\Handlers\DiscoverOperationRequirements;
use App\Acceptance\Operations\Handlers\ImportAcceptanceScenarios;
use App\Acceptance\Operations\Handlers\ListAcceptanceApps;
use App\Acceptance\Operations\Handlers\PlanAcceptance;
use App\Acceptance\Operations\Handlers\ResumeAcceptanceBatch;
use App\Acceptance\Operations\Handlers\RetryAcceptanceBatchItem;
use App\Acceptance\Operations\Handlers\RunAcceptanceScenario;
use App\Acceptance\Operations\Handlers\StartAcceptanceBatch;
use App\Acceptance\Operations\Handlers\SubmitOperationInput;
use App\Acceptance\Operations\Handlers\ValidateAcceptanceApp;
use App\Acceptance\Prerequisites\Contracts\OperatorContextProvider;
use App\Acceptance\Prerequisites\LocalCliOperatorContextProvider;
use App\Acceptance\Targets\TargetResourceRegistry;
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
        $this->app->bind(TargetModuleCreator::class, NwidartTargetModuleCreator::class);
        $this->app->bind(OperatorContextProvider::class, LocalCliOperatorContextProvider::class);
        $this->app->singleton(TargetResourceRegistry::class);
        $this->app->singleton(AcceptanceOperationRegistry::class, function ($app) {
            $registry = new AcceptanceOperationRegistry;
            // Factories stay lazy so list/plan never construct the execution graph.
            $registry->register('acceptance.list', fn () => $app->make(ListAcceptanceApps::class));
            $registry->register('acceptance.plan', fn () => $app->make(PlanAcceptance::class));
            $registry->register('acceptance.run', fn () => $app->make(RunAcceptanceScenario::class));
            $registry->register('acceptance.app.create', fn () => $app->make(CreateAcceptanceApp::class));
            $registry->register('acceptance.app.validate', fn () => $app->make(ValidateAcceptanceApp::class));
            $registry->register('acceptance.component.create', fn () => $app->make(CreateAcceptanceComponent::class));
            $registry->register('acceptance.scenarios.import', fn () => $app->make(ImportAcceptanceScenarios::class));
            $registry->register('acceptance.prerequisite.request.prepare', fn () => $app->make(DiscoverOperationRequirements::class));
            $registry->register('acceptance.prerequisite.input.submit', fn () => $app->make(SubmitOperationInput::class));
            $registry->register('acceptance.prerequisite.approval.grant', fn () => $app->make(ApproveOperation::class));
            $registry->register('acceptance.prerequisite.request.cancel', fn () => $app->make(CancelOperationRequest::class));
            $registry->register('acceptance.batch.start', fn () => $app->make(StartAcceptanceBatch::class));
            $registry->register('acceptance.batch.resume', fn () => $app->make(ResumeAcceptanceBatch::class));
            $registry->register('acceptance.batch.item.retry', fn () => $app->make(RetryAcceptanceBatchItem::class));
            $registry->register('acceptance.batch.cancel', fn () => $app->make(CancelAcceptanceBatch::class));

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
