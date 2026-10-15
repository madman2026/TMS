<?php

declare(strict_types=1);

namespace Modules\DK\Providers;

use App\Acceptance\Prerequisites\PrerequisiteReferenceResolverRegistry;
use App\Acceptance\Targets\Control\Http\HttpTargetControlClient;
use App\Acceptance\Targets\TargetBrowserRuntimeRegistry;
use App\Acceptance\Targets\TargetResourceAdapters;
use App\Acceptance\Targets\TargetResourceRegistry;
use App\Services\AcceptanceAppRegistry;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationAuditOracle;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationCallbackSimulator;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationProvider;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationTimeOracle;
use Modules\DK\Acceptance\Components\NotificationDelivery\Contracts\NotificationWorkerOracle;
use Modules\DK\Acceptance\Components\NotificationDelivery\Fakes\InMemoryNotificationDeliveryFake;
use Modules\DK\Acceptance\Components\NotificationDelivery\Fixtures\OperationsHealthFixture;
use Modules\DK\Acceptance\DKAcceptanceApp;
use Modules\DK\Acceptance\Shared\Prerequisites\DKPrerequisiteReferenceResolver;
use Modules\DK\Acceptance\Shared\Target\Contracts\DKSyntheticTargetControl;
use Modules\DK\Acceptance\Shared\Target\DKBrowserTargetRuntimeResolver;
use Modules\DK\Acceptance\Shared\Target\DKTargetControlAdapter;
use Modules\DK\Acceptance\Shared\Target\DKTargetProfile;
use Modules\DK\Acceptance\Shared\Target\DKTargetResourceAdapter;
use Modules\DK\Acceptance\Shared\Target\LocalLoopbackSecretHandleResolver;

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

        $this->app->singleton(OperationsHealthVariantCatalog::class);
        $this->app->singleton(DKPrerequisiteReferenceResolver::class);
        $this->app->singleton(LocalLoopbackSecretHandleResolver::class);
        $this->app->singleton(DKTargetControlAdapter::class, static function (Application $app): DKTargetControlAdapter {
            [$endpoint] = self::targetControlConfig($app->make(ConfigRepository::class));

            return new DKTargetControlAdapter(
                new HttpTargetControlClient(
                    $app->make(HttpFactory::class),
                    $app->make(LocalLoopbackSecretHandleResolver::class),
                    allowLocalLoopbackHttp: true,
                ),
                $endpoint,
            );
        });
        $this->app->alias(DKTargetControlAdapter::class, DKSyntheticTargetControl::class);
        $this->app->singleton(OperationsHealthFixture::class, static function (Application $app): OperationsHealthFixture {
            [, $secretHandle] = self::targetControlConfig($app->make(ConfigRepository::class));

            return new OperationsHealthFixture(
                $app->make(DKSyntheticTargetControl::class),
                $app->make(OperationsHealthVariantCatalog::class),
                $secretHandle,
            );
        });
        $this->app->singleton(DKTargetResourceAdapter::class);
        $this->app->singleton(DKBrowserTargetRuntimeResolver::class, static function (Application $app): DKBrowserTargetRuntimeResolver {
            [$endpoint, $secretHandle] = self::targetControlConfig($app->make(ConfigRepository::class));

            return new DKBrowserTargetRuntimeResolver(
                $app->make(DKSyntheticTargetControl::class),
                $app->make(OperationsHealthVariantCatalog::class),
                $endpoint,
                $secretHandle,
            );
        });

        $this->app->singleton(InMemoryNotificationDeliveryFake::class);
        $this->app->alias(InMemoryNotificationDeliveryFake::class, NotificationProvider::class);
        $this->app->alias(InMemoryNotificationDeliveryFake::class, NotificationCallbackSimulator::class);
        $this->app->alias(InMemoryNotificationDeliveryFake::class, NotificationWorkerOracle::class);
        $this->app->alias(InMemoryNotificationDeliveryFake::class, NotificationTimeOracle::class);
        $this->app->alias(InMemoryNotificationDeliveryFake::class, NotificationAuditOracle::class);

        $this->app->afterResolving(
            AcceptanceAppRegistry::class,
            static function (AcceptanceAppRegistry $registry): void {
                if ($registry->app('dk') === null) {
                    $registry->register(new DKAcceptanceApp);
                }
            },
        );

        $this->app->afterResolving(
            PrerequisiteReferenceResolverRegistry::class,
            static function (PrerequisiteReferenceResolverRegistry $registry, Container $container): void {
                if ($registry->for('dk') === null) {
                    $registry->register('dk', $container->make(DKPrerequisiteReferenceResolver::class));
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

        $this->app->afterResolving(
            TargetBrowserRuntimeRegistry::class,
            static function (TargetBrowserRuntimeRegistry $registry, Container $container): void {
                if ($registry->for('dk') === null) {
                    $registry->register('dk', $container->make(DKBrowserTargetRuntimeResolver::class));
                }
            },
        );
    }

    /** @return array{string, string} */
    private static function targetControlConfig(ConfigRepository $config): array
    {
        $profile = $config->get('dk.target.control.profile');
        $endpoint = $config->get('dk.target.control.endpoint');
        $secretHandle = $config->get('dk.target.control.secret_handle');
        $origin = is_string($endpoint) ? parse_url($endpoint) : false;
        if ($profile !== 'local-loopback'
            || ! is_array($origin)
            || $origin !== ['scheme' => 'http', 'host' => '127.0.0.1', 'port' => 8000]
            || $secretHandle !== LocalLoopbackSecretHandleResolver::HANDLE) {
            throw new InvalidArgumentException('acceptance_configuration_invalid');
        }

        return [$endpoint, $secretHandle];
    }
}
