<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Unit\Acceptance\Components\NotificationDelivery\Catalogs;

use InvalidArgumentException;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Coverage\SourceCaseMappings;
use PHPUnit\Framework\TestCase;

final class OperationsHealthVariantCatalogTest extends TestCase
{
    public function test_it_exposes_the_exact_ordered_twenty_one_variant_contract(): void
    {
        $catalog = new OperationsHealthVariantCatalog;
        $variants = $catalog->all();

        self::assertSame([
            'v.005502ef537b4a85', 'v.1586b7aa8872f011', 'v.2197c878348bb598',
            'v.3b6dd77897b98280', 'v.3f982418b8fb90aa', 'v.409086a3f7ea716c',
            'v.5d5ea9c6f7091a2e', 'v.60c2d47b88f49444', 'v.664312d320688977',
            'v.68112113f9e6b4f6', 'v.798c9da3e4155865', 'v.7e0e7faddc435fee',
            'v.987b893eeeab92b5', 'v.acb807843d0fa3b6', 'v.b2ee9a53fab09e29',
            'v.be7939d7e43d403b', 'v.be81b57e2f6394f4', 'v.c51c5e04047f90a5',
            'v.d7e5a9544b75803b', 'v.de2c1cb3da9d87f3', 'v.e1b6f42e950f61e0',
        ], array_column($variants, 'key'));
        self::assertCount(21, array_unique(array_column($variants, 'sourceCaseId')));
        self::assertSame(5, count(array_filter(
            $variants,
            static fn ($variant): bool => $variant->key === match ($variant->caseKey) {
                'no-evidence' => 'v.005502ef537b4a85',
                'stale' => 'v.2197c878348bb598',
                'config-invalid' => 'v.3f982418b8fb90aa',
                'fresh' => 'v.b2ee9a53fab09e29',
                'empty-providers' => 'v.de2c1cb3da9d87f3',
            },
        )));
        self::assertCount(16, array_filter(
            $variants,
            static fn ($variant): bool => $variant->caseKey === 'fresh'
                && $variant->key !== 'v.b2ee9a53fab09e29',
        ));

        foreach ($variants as $variant) {
            self::assertSame('super-user', $variant->persona);
            self::assertSame('fixture.health.evidence', $variant->fixture);
            self::assertSame('none', $variant->providerIo);
            self::assertCount(16, $variant->expectedFacts);
        }
    }

    public function test_every_variant_preserves_its_imported_source_mapping(): void
    {
        $catalog = new OperationsHealthVariantCatalog;
        $mappings = array_values(array_filter(
            iterator_to_array(SourceCaseMappings::all()),
            static fn ($mapping): bool => $mapping->scenarioKey === 'nd-component-operations-health',
        ));

        self::assertSame(
            array_column($catalog->all(), 'key'),
            array_map(static fn ($mapping): string => $mapping->variantKey, $mappings),
        );
        self::assertSame(
            array_column($catalog->all(), 'sourceCaseId'),
            array_map(static fn ($mapping): string => $mapping->sourceCaseId, $mappings),
        );
        foreach ($mappings as $mapping) {
            self::assertNotSame([], $mapping->coveredAssertions);
            self::assertSame([], $mapping->uncoveredAssertions);
        }
    }

    public function test_unknown_variant_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('acceptance_configuration_invalid');

        (new OperationsHealthVariantCatalog)->require('v.ffffffffffffffff');
    }
}
