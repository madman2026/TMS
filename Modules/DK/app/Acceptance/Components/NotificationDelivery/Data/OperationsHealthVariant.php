<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Components\NotificationDelivery\Data;

use InvalidArgumentException;

final readonly class OperationsHealthVariant
{
    /** @var array<string, bool|int|float|string|null> */
    public array $expectedFacts;

    /**
     * @param  array<string, bool|int|float|string|null>  $expectedFacts
     */
    public function __construct(
        public string $key,
        public string $sourceCaseId,
        public string $caseKey,
        public string $recipeKey,
        public string $locale,
        public string $direction,
        public string $theme,
        public int $viewportWidth,
        public int $viewportHeight,
        public bool $focused,
        array $expectedFacts,
        public string $persona = 'super-user',
        public string $fixture = 'fixture.health.evidence',
        public string $providerIo = 'none',
    ) {
        if (preg_match('/^v\.[a-f0-9]{16}$/D', $key) !== 1
            || $sourceCaseId !== 'nd.component.operations.health.'.$key
            || ! in_array($caseKey, ['no-evidence', 'stale', 'fresh', 'config-invalid', 'empty-providers'], true)
            || $recipeKey !== 'operations-health.'.$caseKey.'.v1'
            || ! in_array($locale, ['en', 'fa'], true)
            || $direction !== ($locale === 'fa' ? 'rtl' : 'ltr')
            || ! in_array($theme, ['light', 'dark'], true)
            || ! in_array([$viewportWidth, $viewportHeight], [[1440, 900], [390, 844]], true)
            || $persona !== 'super-user'
            || $fixture !== 'fixture.health.evidence'
            || $providerIo !== 'none'
            || count($expectedFacts) !== 16
            || array_is_list($expectedFacts)) {
            throw new InvalidArgumentException('acceptance_configuration_invalid');
        }

        $this->expectedFacts = $expectedFacts;
    }
}
