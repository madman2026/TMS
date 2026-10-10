<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Unit;

use App\Acceptance\Targets\Data\TargetEnvironment;
use Modules\DK\Acceptance\Shared\Target\DKTargetProfile;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DKTargetProfileTest extends TestCase
{
    public function test_invalid_or_missing_configuration_falls_back_to_disabled_unknown(): void
    {
        $invalidConfigurations = [
            [],
            ['enabled' => 'false', 'environment' => 'testing'],
            ['enabled' => true],
            ['enabled' => true, 'environment' => []],
            ['enabled' => true, 'environment' => 'TESTING'],
            ['enabled' => true, 'environment' => 'unsupported'],
        ];

        foreach ($invalidConfigurations as $configuration) {
            $profile = DKTargetProfile::fromConfig($configuration);

            $this->assertFalse($profile->enabled);
            $this->assertSame(TargetEnvironment::UNKNOWN, $profile->environment);
            $this->assertSame(1, $profile->version);
        }
    }

    public function test_exact_supported_configuration_is_preserved_without_retaining_extra_input(): void
    {
        $profiles = [
            DKTargetProfile::fromConfig(['enabled' => true, 'environment' => 'testing']),
            DKTargetProfile::fromConfig(['enabled' => false, 'environment' => 'staging']),
            DKTargetProfile::fromConfig(['enabled' => true, 'environment' => 'production']),
            DKTargetProfile::fromConfig([
                'enabled' => false,
                'environment' => 'unknown',
                'unexpected' => 'discarded',
            ]),
        ];

        $this->assertTrue($profiles[0]->enabled);
        $this->assertSame(TargetEnvironment::TESTING, $profiles[0]->environment);
        $this->assertFalse($profiles[1]->enabled);
        $this->assertSame(TargetEnvironment::STAGING, $profiles[1]->environment);
        $this->assertTrue($profiles[2]->enabled);
        $this->assertSame(TargetEnvironment::PRODUCTION, $profiles[2]->environment);
        $this->assertFalse($profiles[3]->enabled);
        $this->assertSame(TargetEnvironment::UNKNOWN, $profiles[3]->environment);

        $propertyNames = array_map(
            static fn ($property): string => $property->getName(),
            (new ReflectionClass(DKTargetProfile::class))->getProperties(),
        );
        sort($propertyNames);

        $this->assertSame(['enabled', 'environment', 'version'], $propertyNames);
    }
}
