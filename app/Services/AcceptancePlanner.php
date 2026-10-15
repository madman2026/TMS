<?php

namespace App\Services;

use App\Data\AcceptancePlan;
use App\Data\AcceptancePlanItem;
use App\Data\AcceptanceSelector;
use App\Exceptions\AcceptanceCatalogException;
use Modules\Core\Data\AcceptanceExecutionIdentity;

/** Bounded streaming selection over one explicit hierarchy contract. */
final class AcceptancePlanner
{
    public function __construct(private readonly AcceptanceCatalog $catalog) {}

    /** Build one exact executable identity without scanning unrelated scenario variants. */
    public function planExact(AcceptanceExecutionIdentity $identity): AcceptancePlan
    {
        if (! in_array($identity->appKey, $this->catalog->appKeys(), true)) {
            throw AcceptanceCatalogException::because('acceptance_app_not_found');
        }

        $selector = new AcceptanceSelector(
            apps: [$identity->appKey],
            components: [$identity->componentKey],
            suites: [$identity->suiteKey],
            scenarios: [$identity->scenarioKey],
            variants: [$identity->variantKey],
            limit: 1,
        );
        $version = $this->catalog->version($identity->appKey);
        $visits = 0;
        $componentFound = false;
        foreach ($this->catalog->components($identity->appKey) as $component) {
            AcceptanceCatalog::visit($visits);
            $componentFound = $componentFound || $component->key === $identity->componentKey;
        }

        $suiteFound = false;
        foreach ($this->catalog->suites($identity->appKey) as $suite) {
            AcceptanceCatalog::visit($visits);
            $suiteFound = $suiteFound || ($suite->key === $identity->suiteKey
                && $suite->componentKey === $identity->componentKey);
        }

        $selectedDescriptor = null;
        foreach ($this->catalog->descriptors($identity->appKey) as $descriptor) {
            AcceptanceCatalog::visit($visits);
            if ($descriptor->key === $identity->scenarioKey) {
                $selectedDescriptor = $descriptor;
            }
        }
        if (! $componentFound || ! $suiteFound || $selectedDescriptor === null
            || $selectedDescriptor->componentKey !== $identity->componentKey
            || $selectedDescriptor->suiteKey !== $identity->suiteKey) {
            throw AcceptanceCatalogException::because('acceptance_selector_not_found');
        }

        $variantFound = false;
        foreach ($this->catalog->variants($identity->appKey, $identity->scenarioKey) as $variant) {
            AcceptanceCatalog::visit($visits);
            $variantFound = $variantFound || $variant->key === $identity->variantKey;
        }
        if (! $variantFound) {
            throw AcceptanceCatalogException::because('acceptance_selector_not_found');
        }
        if ($this->catalog->version($identity->appKey) !== $version
            || $this->catalog->version($identity->appKey) !== $version) {
            throw AcceptanceCatalogException::because('acceptance_catalog_changed');
        }

        return new AcceptancePlan($selector, [new AcceptancePlanItem(
            $identity->appKey,
            $identity->componentKey,
            $identity->suiteKey,
            $identity->scenarioKey,
            $identity->variantKey,
            $selectedDescriptor->metadata,
        )], [$identity->appKey => $version]);
    }

    public function plan(AcceptanceSelector $selector): AcceptancePlan
    {
        $registered = $this->catalog->appKeys();
        if (array_diff($selector->apps, $registered)) {
            throw AcceptanceCatalogException::because('acceptance_app_not_found');
        }
        $apps = $selector->apps !== [] ? $selector->apps : $registered;
        $versions = [];
        $items = [];
        $vocabulary = array_fill_keys([
            'components', 'suites', 'scenarios', 'variants', 'capabilities', 'tags',
        ], []);
        $visits = 0;

        foreach ($apps as $appKey) {
            $versions[$appKey] = $this->catalog->version($appKey);
            foreach ($this->catalog->components($appKey) as $component) {
                AcceptanceCatalog::visit($visits);
                $vocabulary['components'][$component->key] = true;
            }
            foreach ($this->catalog->suites($appKey) as $suite) {
                AcceptanceCatalog::visit($visits);
                $vocabulary['suites'][$suite->key] = true;
            }
            foreach ($this->catalog->descriptors($appKey) as $descriptor) {
                AcceptanceCatalog::visit($visits);
                $vocabulary['scenarios'][$descriptor->key] = true;
                foreach (['capabilities', 'tags'] as $field) {
                    foreach ($descriptor->metadata->$field as $key) {
                        $vocabulary[$field][$key] = true;
                    }
                }
                $scenarioMatches = $selector->scenarios === []
                    || in_array($descriptor->key, $selector->scenarios, true);
                if (! $scenarioMatches && $selector->variants === []) {
                    continue;
                }
                $matches = $scenarioMatches && $selector->matchesScenario($descriptor);
                if (! $matches && $selector->variants === []) {
                    continue;
                }
                foreach ($this->catalog->variants($appKey, $descriptor->key) as $variant) {
                    AcceptanceCatalog::visit($visits);
                    $vocabulary['variants'][$variant->key] = true;
                    if (! $matches || ($selector->variants !== [] && ! in_array($variant->key, $selector->variants, true))) {
                        continue;
                    }
                    if (count($items) >= $selector->limit) {
                        throw AcceptanceCatalogException::because('acceptance_catalog_limit_exceeded');
                    }
                    $items[] = new AcceptancePlanItem(
                        $appKey,
                        $descriptor->componentKey,
                        $descriptor->suiteKey,
                        $descriptor->key,
                        $variant->key,
                        $descriptor->metadata,
                    );
                }
            }
            if ($this->catalog->version($appKey) !== $versions[$appKey]) {
                throw AcceptanceCatalogException::because('acceptance_catalog_changed');
            }
        }
        foreach ($versions as $appKey => $version) {
            if ($this->catalog->version((string) $appKey) !== $version) {
                throw AcceptanceCatalogException::because('acceptance_catalog_changed');
            }
        }
        foreach ($vocabulary as $field => $known) {
            foreach ($selector->$field as $term) {
                if (! isset($known[$term])) {
                    throw AcceptanceCatalogException::because('acceptance_selector_not_found');
                }
            }
        }

        return new AcceptancePlan($selector, $items, $versions);
    }
}
