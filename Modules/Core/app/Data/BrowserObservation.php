<?php

declare(strict_types=1);

namespace Modules\Core\Data;

use Modules\Core\Enums\BrowserObservationStatus;
use Modules\Core\Exceptions\BrowserProbeException;

/**
 * Validated in-memory evidence only. Complete schemas and finite bounds keep
 * raw browser content and partially available evidence out of serialization.
 */
final readonly class BrowserObservation
{
    public const ROLES = [
        'button', 'link', 'textbox', 'searchbox', 'checkbox', 'radio', 'combobox',
        'listbox', 'option', 'heading', 'img', 'dialog', 'list', 'listitem',
        'table', 'row', 'cell', 'columnheader', 'rowheader', 'region', 'alert',
        'status', 'navigation', 'main', 'banner', 'contentinfo', 'form', 'search',
        'progressbar', 'slider', 'spinbutton', 'switch', 'tab', 'tablist',
        'tabpanel', 'menu', 'menuitem', 'separator', 'none', 'presentation',
    ];

    private const SCHEMAS = [
        'visibility' => ['visible' => 'bool', 'enabled' => 'bool'],
        'layout' => [
            'x' => 'coordinate', 'y' => 'coordinate', 'width' => 'dimension',
            'height' => 'dimension', 'visibleFraction' => 'fraction',
            'clipped' => 'bool', 'overflowX' => 'bool', 'overflowY' => 'bool',
        ],
        'focus' => ['focused' => 'bool'],
        'keyboard' => ['dispatched' => 'bool'],
        'scroll' => ['dispatched' => 'bool'],
        'semantics' => [
            'role' => 'role', 'checked' => 'checked', 'expanded' => 'nullable-bool',
            'selected' => 'nullable-bool', 'disabled' => 'bool',
            'hasAriaLabel' => 'bool', 'hasLabelledBy' => 'bool', 'hasNativeLabel' => 'bool',
        ],
        'media' => ['colorScheme' => 'scheme', 'reducedMotion' => 'motion', 'forcedColors' => 'bool'],
        'viewport' => ['width' => 'viewport', 'height' => 'viewport', 'deviceScaleFactor' => 'scale'],
        'touch' => ['dispatched' => 'bool'],
        'contrast' => [],
    ];

    /** @param array<string, bool|int|float|string|null> $data */
    private function __construct(
        public string $operation,
        public BrowserObservationStatus $status,
        public array $data,
        public ?string $errorCode,
    ) {}

    /** @param array<string, mixed> $data */
    public static function available(string $operation, array $data): self
    {
        $schema = self::SCHEMAS[$operation] ?? null;
        if ($schema === null || $operation === 'contrast' || count($data) !== count($schema)) {
            throw new BrowserProbeException('browser_probe_invalid_payload');
        }

        $safe = [];
        foreach ($schema as $key => $type) {
            if (! array_key_exists($key, $data) || ! self::validValue($type, $data[$key])) {
                throw new BrowserProbeException('browser_probe_invalid_payload');
            }
            // Copy scalar values; copying the input array would retain caller references.
            $value = $data[$key];
            $safe[$key] = $value;
        }

        return new self($operation, BrowserObservationStatus::AVAILABLE, $safe, null);
    }

    public static function unavailable(string $operation, string $errorCode): self
    {
        return self::failure($operation, BrowserObservationStatus::UNAVAILABLE, $errorCode);
    }

    public static function unsupported(string $operation, string $errorCode): self
    {
        return self::failure($operation, BrowserObservationStatus::UNSUPPORTED, $errorCode);
    }

    /** @return array<string, bool|int|float|string|null> */
    public function requireAvailable(): array
    {
        if ($this->status !== BrowserObservationStatus::AVAILABLE) {
            throw new BrowserProbeException($this->errorCode ?? 'browser_probe_invalid_payload');
        }

        return $this->data;
    }

    /** @return array{operation: string, status: string, data: array, errorCode: ?string} */
    public function toArray(): array
    {
        return [
            'operation' => $this->operation, 'status' => $this->status->value,
            'data' => $this->data, 'errorCode' => $this->errorCode,
        ];
    }

    private static function failure(string $operation, BrowserObservationStatus $status, string $errorCode): self
    {
        $codes = $status === BrowserObservationStatus::UNAVAILABLE
            ? BrowserProbeException::UNAVAILABLE_CODES : BrowserProbeException::UNSUPPORTED_CODES;
        if (! array_key_exists($operation, self::SCHEMAS) || ! in_array($errorCode, $codes, true)
            || ($errorCode === 'browser_probe_geometry_unavailable' && $operation !== 'layout')
            || ($errorCode === 'browser_probe_touch_unsupported' && $operation !== 'touch')
            || ($errorCode === 'browser_probe_contrast_unsupported' && $operation !== 'contrast')
            || ($errorCode === 'browser_probe_capability_unsupported' && ! in_array($operation, ['layout', 'focus', 'media'], true))
            || ($operation === 'contrast' && $errorCode !== 'browser_probe_contrast_unsupported')) {
            throw new BrowserProbeException('browser_probe_invalid_payload');
        }

        return new self($operation, $status, [], $errorCode);
    }

    private static function validValue(string $type, mixed $value): bool
    {
        return match ($type) {
            'bool' => is_bool($value),
            'nullable-bool' => $value === null || is_bool($value),
            'checked' => $value === null || is_bool($value) || $value === 'mixed',
            'role' => $value === null || in_array($value, self::ROLES, true),
            'scheme' => in_array($value, ['dark', 'light', 'no-preference'], true),
            'motion' => in_array($value, ['reduce', 'no-preference'], true),
            'viewport' => is_int($value) && $value >= 1 && $value <= 16384,
            'coordinate' => self::numberInRange($value, -1000000, 1000000),
            'dimension' => self::numberInRange($value, 0, 1000000),
            'fraction' => self::numberInRange($value, 0, 1),
            'scale' => self::numberInRange($value, 0, 16) && $value > 0,
            default => false,
        };
    }

    private static function numberInRange(mixed $value, float $min, float $max): bool
    {
        return (is_int($value) || is_float($value)) && is_finite((float) $value)
            && $value >= $min && $value <= $max;
    }
}
