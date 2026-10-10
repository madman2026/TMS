<?php

namespace Modules\DK\Providers;

use App\Services\AcceptanceAppRegistry;
use Illuminate\Support\ServiceProvider;
use Modules\DK\Acceptance\DKAcceptanceApp;

final class DKServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->afterResolving(
            AcceptanceAppRegistry::class,
            static function (AcceptanceAppRegistry $registry): void {
                if ($registry->app('dk') === null) {
                    $registry->register(new DKAcceptanceApp);
                }
            },
        );
    }
}
