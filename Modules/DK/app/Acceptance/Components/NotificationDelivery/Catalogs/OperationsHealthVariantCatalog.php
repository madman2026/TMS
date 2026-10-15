<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs;

use InvalidArgumentException;
use Modules\DK\Acceptance\Components\NotificationDelivery\Data\OperationsHealthVariant;

final class OperationsHealthVariantCatalog
{
    public const FIXED_INSTANT = '2026-10-13T08:00:00Z';

    /** @return list<OperationsHealthVariant> */
    public function all(): array
    {
        return [
            $this->variant('v.005502ef537b4a85', 'no-evidence', 'fa', 'light', 'desktop', false),
            $this->variant('v.1586b7aa8872f011', 'fresh', 'en', 'dark', 'desktop', false),
            $this->variant('v.2197c878348bb598', 'stale', 'fa', 'light', 'desktop', false),
            $this->variant('v.3b6dd77897b98280', 'fresh', 'en', 'light', 'desktop', true),
            $this->variant('v.3f982418b8fb90aa', 'config-invalid', 'fa', 'light', 'desktop', false),
            $this->variant('v.409086a3f7ea716c', 'fresh', 'en', 'dark', 'narrow', false),
            $this->variant('v.5d5ea9c6f7091a2e', 'fresh', 'en', 'light', 'narrow', false),
            $this->variant('v.60c2d47b88f49444', 'fresh', 'en', 'dark', 'narrow', true),
            $this->variant('v.664312d320688977', 'fresh', 'fa', 'light', 'narrow', false),
            $this->variant('v.68112113f9e6b4f6', 'fresh', 'fa', 'dark', 'narrow', true),
            $this->variant('v.798c9da3e4155865', 'fresh', 'fa', 'light', 'desktop', false),
            $this->variant('v.7e0e7faddc435fee', 'fresh', 'fa', 'dark', 'narrow', false),
            $this->variant('v.987b893eeeab92b5', 'fresh', 'en', 'light', 'desktop', false),
            $this->variant('v.acb807843d0fa3b6', 'fresh', 'en', 'light', 'narrow', true),
            $this->variant('v.b2ee9a53fab09e29', 'fresh', 'fa', 'light', 'desktop', false),
            $this->variant('v.be7939d7e43d403b', 'fresh', 'fa', 'dark', 'desktop', true),
            $this->variant('v.be81b57e2f6394f4', 'fresh', 'en', 'dark', 'desktop', true),
            $this->variant('v.c51c5e04047f90a5', 'fresh', 'fa', 'dark', 'desktop', false),
            $this->variant('v.d7e5a9544b75803b', 'fresh', 'fa', 'light', 'narrow', true),
            $this->variant('v.de2c1cb3da9d87f3', 'empty-providers', 'fa', 'light', 'desktop', false),
            $this->variant('v.e1b6f42e950f61e0', 'fresh', 'fa', 'light', 'desktop', true),
        ];
    }

    public function find(string $key): ?OperationsHealthVariant
    {
        foreach ($this->all() as $variant) {
            if ($variant->key === $key) {
                return $variant;
            }
        }

        return null;
    }

    public function require(string $key): OperationsHealthVariant
    {
        return $this->find($key)
            ?? throw new InvalidArgumentException('acceptance_configuration_invalid');
    }

    private function variant(
        string $key,
        string $case,
        string $locale,
        string $theme,
        string $viewport,
        bool $focused,
    ): OperationsHealthVariant {
        [$width, $height] = $viewport === 'desktop' ? [1440, 900] : [390, 844];

        return new OperationsHealthVariant(
            key: $key,
            sourceCaseId: 'nd.component.operations.health.'.$key,
            caseKey: $case,
            recipeKey: 'operations-health.'.$case.'.v1',
            locale: $locale,
            direction: $locale === 'fa' ? 'rtl' : 'ltr',
            theme: $theme,
            viewportWidth: $width,
            viewportHeight: $height,
            focused: $focused,
            expectedFacts: $this->expectedFacts($case),
        );
    }

    /** @return array<string, bool|int|float|string|null> */
    private function expectedFacts(string $case): array
    {
        if ($case === 'empty-providers') {
            return [
                'config_valid' => true,
                'provider_count' => 0,
                'administrative_status' => null,
                'computed_status' => null,
                'effective_status' => null,
                'freshness' => null,
                'reason_code' => null,
                'eligible_sample_count' => null,
                'failure_rate' => null,
                'latency_ms' => null,
                'telemetry_only' => null,
                'remote_probe_supported' => null,
                'last_evidence_source' => null,
                'last_evidence_outcome' => null,
                'clock_state' => 'frozen',
                'lease_state' => 'active',
            ];
        }

        $hasEvidence = in_array($case, ['fresh', 'stale'], true);
        $stale = $case === 'stale';

        return [
            'config_valid' => $case !== 'config-invalid',
            'provider_count' => 1,
            'administrative_status' => 'enabled',
            'computed_status' => $hasEvidence ? 'healthy' : 'unknown',
            'effective_status' => $hasEvidence && ! $stale ? 'healthy' : 'unknown',
            'freshness' => $hasEvidence ? ($stale ? 'stale' : 'fresh') : 'no_evidence',
            'reason_code' => $hasEvidence
                ? ($stale ? 'provider_health_evidence_stale' : null)
                : 'provider_health_evidence_insufficient',
            'eligible_sample_count' => $hasEvidence ? 1 : 0,
            'failure_rate' => $hasEvidence ? 0 : null,
            'latency_ms' => $hasEvidence ? 120 : null,
            'telemetry_only' => true,
            'remote_probe_supported' => false,
            'last_evidence_source' => $hasEvidence ? 'delivery_attempt' : null,
            'last_evidence_outcome' => $hasEvidence ? 'success' : null,
            'clock_state' => 'frozen',
            'lease_state' => 'active',
        ];
    }
}
