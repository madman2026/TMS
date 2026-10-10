<?php

namespace Modules\DK\Providers;

use App\Acceptance\Targets\TargetResourceAdapters;
use App\Acceptance\Targets\TargetResourceRegistry;
use App\Services\AcceptanceAppRegistry;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Modules\DK\Acceptance\DKAcceptanceApp;
use Modules\DK\Acceptance\Shared\Target\DKTargetProfile;
use Modules\DK\Acceptance\Shared\Target\DKTargetResourceAdapter;

final class DKServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'dk');

        $this->app->singleton(
            DKTargetProfile::class,
            static function (Application $app): DKTargetProfile {
                $targetConfig = $app->make(ConfigRepository::class)->get('dk.target', []);

                return DKTargetProfile::fromConfig(is_array($targetConfig) ? $targetConfig : []);
            },
        );
        $this->app->singleton(DKTargetResourceAdapter::class);

        $this->app->afterResolving(
            AcceptanceAppRegistry::class,
            static function (AcceptanceAppRegistry $registry): void {
                if ($registry->app('dk') === null) {
                    $registry->register(new DKAcceptanceApp);
                }
            },
        );

        $this->app->afterResolving(
            TargetResourceRegistry::class,
            static function (TargetResourceRegistry $registry, Container $container): void {
                if ($registry->for('dk') !== null) {
                    return;
                }

                $adapter = $container->make(DKTargetResourceAdapter::class);
                $registry->register('dk', new TargetResourceAdapters(
                    readiness: $adapter,
                    accounts: $adapter,
                    fixtures: $adapter,
                    oracle: $adapter,
                    cleanup: $adapter,
                ));
            },
        );
    }
}
