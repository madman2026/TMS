<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Unit\Acceptance\Shared\Target;

use App\Acceptance\Targets\Control\Exceptions\TargetControlException;
use LogicException;
use Modules\DK\Acceptance\Shared\Target\LocalLoopbackSecretHandleResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LocalLoopbackSecretHandleResolverTest extends TestCase
{
    public function test_it_maps_only_the_approved_dk_handle_to_the_public_marker(): void
    {
        $credential = (new LocalLoopbackSecretHandleResolver)->resolve(
            'dk',
            LocalLoopbackSecretHandleResolver::HANDLE,
        );

        self::assertSame(
            LocalLoopbackSecretHandleResolver::PUBLIC_MARKER,
            $credential->withValue(static fn (string $value): string => $value),
        );
        self::assertSame([], $credential->__debugInfo());

        $this->expectException(LogicException::class);
        serialize($credential);
    }

    #[DataProvider('invalidHandleProvider')]
    public function test_it_fails_closed_for_every_other_app_or_handle(string $app, string $handle): void
    {
        $this->expectException(TargetControlException::class);
        $this->expectExceptionMessage('target_control_unavailable');

        (new LocalLoopbackSecretHandleResolver)->resolve($app, $handle);
    }

    /** @return array<string, array{string, string}> */
    public static function invalidHandleProvider(): array
    {
        return [
            'wrong app' => ['core', LocalLoopbackSecretHandleResolver::HANDLE],
            'wrong handle' => ['dk', 'app-secret://dk/other'],
            'empty handle' => ['dk', ''],
        ];
    }
}
