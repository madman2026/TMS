<?php

namespace App\Services;

use App\Data\AcceptancePlan;
use App\Data\AcceptancePlanItem;
use App\Data\AcceptanceSelector;
use App\Exceptions\AcceptanceCatalogException;

/** Bounded streaming selection with canonical output, not global materialization. */
final class AcceptancePlanner
{
    public function __construct(private readonly AcceptanceCatalog $catalog) {}

    public function plan(AcceptanceSelector $selector): AcceptancePlan
    {
        $registered = $this->catalog->appKeys();
        if (array_diff($selector->apps, $registered)) {
            throw AcceptanceCatalogException::because('acceptance_app_not_found');
        }
        $apps = $selector->apps !== [] ? $selector->apps : $registered;
        $versions = [];
        $items = [];
        $vocabulary = array_fill_keys(['scenarios', 'variants', 'suites', 'capabilities', 'tags'], []);
        $visits = 0;

        foreach ($apps as $appKey) {
            $versions[$appKey] = $this->catalog->version($appKey);
            foreach ($this->catalog->descriptors($appKey) as $descriptor) {
                AcceptanceCatalog::visit($visits);
                $vocabulary['scenarios'][$descriptor->key] = true;
                foreach (['suites', 'capabilities', 'tags'] as $field) {
                    foreach ($descriptor->metadata->$field as $key) {
                        $vocabulary[$field][$key] = true;
                    }
                }
                if ($selector->scenarios !== [] && ! in_array($descriptor->key, $selector->scenarios, true)) {
                    continue;
                }
                $matches = $selector->matchesScenario($descriptor);
                // Explicit variant vocabulary is checked before classification intersection.
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
                    $items[] = new AcceptancePlanItem($appKey, $descriptor->key, $variant->key, $descriptor->metadata);
                }
            }
            if ($this->catalog->version($appKey) !== $versions[$appKey]) {
                throw AcceptanceCatalogException::because('acceptance_catalog_changed');
            }
        }
        // Recheck earlier Apps after later provider iterations, too.
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
