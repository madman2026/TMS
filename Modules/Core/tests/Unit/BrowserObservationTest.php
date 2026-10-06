<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit;

use Error;
use Modules\Core\Data\BrowserObservation;
use Modules\Core\Enums\BrowserObservationStatus;
use Modules\Core\Exceptions\BrowserProbeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BrowserObservationTest extends TestCase
{
    #[DataProvider('availableProvider')]
    public function test_each_available_schema_is_validated(string $operation, array $data): void
    {
        $observation = BrowserObservation::available($operation, $data);

        $this->assertSame(BrowserObservationStatus::AVAILABLE, $observation->status);
        $this->assertSame($data, $observation->requireAvailable());
        $this->assertSame([
            'operation' => $operation, 'status' => 'available', 'data' => $data, 'errorCode' => null,
        ], $observation->toArray());
        $this->assertFalse(property_exists($observation, 'passed'));
    }

    public static function availableProvider(): iterable
    {
        yield 'hidden visibility' => ['visibility', ['visible' => false, 'enabled' => true]];
        yield 'layout bounds' => ['layout', [
            'x' => -1000000, 'y' => 1000000, 'width' => 0, 'height' => 1000000,
            'visibleFraction' => 0.5, 'clipped' => true, 'overflowX' => false, 'overflowY' => true,
        ]];
        yield 'unfocused' => ['focus', ['focused' => false]];
        yield 'keyboard' => ['keyboard', ['dispatched' => true]];
        yield 'scroll' => ['scroll', ['dispatched' => true]];
        yield 'semantics' => ['semantics', self::semantics()];
        yield 'null semantics' => ['semantics', self::semantics(null, null)];
        yield 'media' => ['media', ['colorScheme' => 'dark', 'reducedMotion' => 'reduce', 'forcedColors' => false]];
        yield 'viewport bounds' => ['viewport', ['width' => 1, 'height' => 16384, 'deviceScaleFactor' => 16]];
        yield 'touch' => ['touch', ['dispatched' => true]];
        foreach (BrowserObservation::ROLES as $role) {
            yield 'role '.$role => ['semantics', self::semantics($role, true)];
        }
    }

    #[DataProvider('invalidPayloadProvider')]
    public function test_invalid_payloads_are_rejected_safely(string $operation, array $data): void
    {
        try {
            BrowserObservation::available($operation, $data);
            $this->fail('Unsafe observation was accepted.');
        } catch (BrowserProbeException $exception) {
            $this->assertSafeException($exception, 'browser_probe_invalid_payload');
        }
    }

    public static function invalidPayloadProvider(): iterable
    {
        foreach (self::availableProvider() as $name => [$operation, $data]) {
            $key = array_key_first($data);
            yield $name.' raw field' => [$operation, [...$data, $key => 'example-sensitive-value']];
            $missing = $data;
            unset($missing[$key]);
            yield $name.' missing' => [$operation, $missing];
            yield $name.' extra' => [$operation, [...$data, 'raw' => 'example-sensitive-value']];
        }
        yield 'unknown operation' => ['example-sensitive-value', ['dispatched' => true]];
        yield 'contrast cannot be available' => ['contrast', []];
        yield 'nested content' => ['focus', ['focused' => ['raw' => 'example-sensitive-value']]];
        yield 'object content' => ['focus', ['focused' => (object) ['raw' => 'example-sensitive-value']]];
        yield 'boolean coercion' => ['focus', ['focused' => 1]];
        foreach ([NAN, INF, -INF, true, '1', -1, 1000001] as $value) {
            yield 'dimension '.var_export($value, true) => ['layout', [
                'x' => 0, 'y' => 0, 'width' => $value, 'height' => 1,
                'visibleFraction' => 1, 'clipped' => false, 'overflowX' => false, 'overflowY' => false,
            ]];
        }
        foreach ([0, -1, 16385, 10.5, '800'] as $value) {
            yield 'viewport '.var_export($value, true) => ['viewport', ['width' => $value, 'height' => 600, 'deviceScaleFactor' => 1]];
        }
        foreach ([0, -1, 16.01, INF, NAN] as $value) {
            yield 'scale '.var_export($value, true) => ['viewport', ['width' => 800, 'height' => 600, 'deviceScaleFactor' => $value]];
        }
        yield 'motion token' => ['media', ['colorScheme' => 'light', 'reducedMotion' => 'example-sensitive-value', 'forcedColors' => false]];
        yield 'state token' => ['semantics', [...self::semantics(), 'checked' => 'true']];
        yield 'nullable state type' => ['semantics', [...self::semantics(), 'expanded' => 'true']];
        yield 'fraction above bound' => ['layout', [
            'x' => 0, 'y' => 0, 'width' => 1, 'height' => 1, 'visibleFraction' => 1.001,
            'clipped' => false, 'overflowX' => false, 'overflowY' => false,
        ]];
    }

    #[DataProvider('failureProvider')]
    public function test_failure_observations_cannot_be_required_as_available(string $operation, string $code, bool $unsupported): void
    {
        $observation = $unsupported ? BrowserObservation::unsupported($operation, $code)
            : BrowserObservation::unavailable($operation, $code);
        $this->assertSame($unsupported ? BrowserObservationStatus::UNSUPPORTED : BrowserObservationStatus::UNAVAILABLE, $observation->status);
        $this->assertSame([], $observation->data);
        $this->assertSame($code, $observation->errorCode);
        $this->assertSame([
            'operation' => $operation, 'status' => $unsupported ? 'unsupported' : 'unavailable',
            'data' => [], 'errorCode' => $code,
        ], $observation->toArray());
        try {
            $observation->requireAvailable();
            $this->fail('Missing evidence became available.');
        } catch (BrowserProbeException $exception) {
            $this->assertSafeException($exception, $code);
        }
    }

    public static function failureProvider(): iterable
    {
        foreach (BrowserProbeException::UNAVAILABLE_CODES as $code) {
            yield $code => [$code === 'browser_probe_geometry_unavailable' ? 'layout' : 'visibility', $code, false];
        }
        yield 'unsupported layout' => ['layout', 'browser_probe_capability_unsupported', true];
        yield 'unsupported focus' => ['focus', 'browser_probe_capability_unsupported', true];
        yield 'unsupported media' => ['media', 'browser_probe_capability_unsupported', true];
        yield 'unsupported touch' => ['touch', 'browser_probe_touch_unsupported', true];
        yield 'unsupported contrast' => ['contrast', 'browser_probe_contrast_unsupported', true];
    }

    #[DataProvider('invalidFailureProvider')]
    public function test_inconsistent_failure_codes_are_rejected(string $operation, string $code, bool $unsupported): void
    {
        try {
            $unsupported ? BrowserObservation::unsupported($operation, $code) : BrowserObservation::unavailable($operation, $code);
            $this->fail('Inconsistent failure was accepted.');
        } catch (BrowserProbeException $exception) {
            $this->assertSafeException($exception, 'browser_probe_invalid_payload');
        }
    }

    public static function invalidFailureProvider(): iterable
    {
        yield ['visibility', 'example-sensitive-value', false];
        yield ['example-sensitive-value', 'browser_probe_page_closed', false];
        yield ['visibility', 'browser_probe_touch_unsupported', false];
        yield ['visibility', 'browser_probe_touch_unsupported', true];
        yield ['visibility', 'browser_probe_geometry_unavailable', false];
        yield ['visibility', 'browser_probe_contrast_unsupported', true];
        yield ['contrast', 'browser_probe_operation_failed', false];
        yield ['media', 'browser_probe_page_closed', true];
        yield ['viewport', 'browser_probe_capability_unsupported', true];
    }

    public function test_caller_references_cannot_change_observation(): void
    {
        $focused = false;
        $input = ['focused' => &$focused];
        $observation = BrowserObservation::available('focus', $input);
        $focused = 'example-sensitive-value';
        $copy = $observation->requireAvailable();
        $copy['focused'] = true;
        $serialized = $observation->toArray();
        $serialized['data']['focused'] = true;
        $this->assertSame(['focused' => false], $observation->requireAvailable());
        $this->assertSame(['focused' => false], $observation->toArray()['data']);

        $this->expectException(Error::class);
        $observation->data['focused'] = true;
    }

    private static function semantics(?string $role = 'checkbox', bool|string|null $checked = 'mixed'): array
    {
        return ['role' => $role, 'checked' => $checked, 'expanded' => null, 'selected' => false,
            'disabled' => false, 'hasAriaLabel' => true, 'hasLabelledBy' => false, 'hasNativeLabel' => false];
    }

    private function assertSafeException(BrowserProbeException $exception, string $code): void
    {
        $this->assertSame($code, $exception->errorCode);
        $this->assertFalse($exception->retryable);
        $this->assertSame('Browser probe could not provide the requested observation.', $exception->getMessage());
        $this->assertNull($exception->getPrevious());
        $this->assertStringNotContainsString('example-sensitive-value', $exception->getMessage());
    }
}
