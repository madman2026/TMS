<?php

namespace App\Acceptance\Targets\Control\Data;

use App\Acceptance\Targets\Control\TargetControlProtocol;
use InvalidArgumentException;

final readonly class TargetControlCapabilities
{
    /** @var array<string, list<string>> */
    public array $recipesByComponent;

    /** @var array<string, list<string>> */
    public array $observationsByComponent;

    /**
     * @param  array<string, list<string>>  $recipesByComponent
     * @param  array<string, list<string>>  $observationsByComponent
     */
    public function __construct(
        public string $protocol,
        public string $serverVersion,
        public int $maxLeaseTtlSeconds,
        array $recipesByComponent,
        array $observationsByComponent,
    ) {
        if ($protocol !== TargetControlProtocol::IDENTITY
            || ! TargetControlProtocol::validToken($serverVersion)
            || $maxLeaseTtlSeconds < 1
            || $maxLeaseTtlSeconds > TargetControlProtocol::MAX_LEASE_TTL_SECONDS
            || $recipesByComponent === []
            || count($recipesByComponent) > TargetControlProtocol::MAX_COMPONENTS
            || array_is_list($recipesByComponent)
            || array_keys($recipesByComponent) !== array_keys($observationsByComponent)) {
            throw new InvalidArgumentException('target_control_capabilities_invalid');
        }

        $recipes = [];
        $observations = [];
        foreach ($recipesByComponent as $component => $componentRecipes) {
            $componentObservations = $observationsByComponent[$component] ?? null;
            if (! is_string($component)
                || ! TargetControlProtocol::validToken($component)
                || ! self::validTokenList(
                    $componentRecipes,
                    1,
                    TargetControlProtocol::MAX_RECIPES_PER_COMPONENT,
                )
                || ! is_array($componentObservations)
                || ! self::validTokenList(
                    $componentObservations,
                    0,
                    TargetControlProtocol::MAX_OBSERVATIONS_PER_COMPONENT,
                )) {
                throw new InvalidArgumentException('target_control_capabilities_invalid');
            }

            $recipes[$component] = array_values($componentRecipes);
            $observations[$component] = array_values($componentObservations);
        }

        $this->recipesByComponent = $recipes;
        $this->observationsByComponent = $observations;
    }

    public function supports(string $componentKey, string $recipeKey): bool
    {
        return in_array($recipeKey, $this->recipesByComponent[$componentKey] ?? [], true);
    }

    private static function validTokenList(mixed $values, int $minimum, int $maximum): bool
    {
        if (! is_array($values) || ! array_is_list($values)
            || count($values) < $minimum || count($values) > $maximum) {
            return false;
        }

        $seen = [];
        foreach ($values as $value) {
            if (! is_string($value) || ! TargetControlProtocol::validToken($value) || isset($seen[$value])) {
                return false;
            }
            $seen[$value] = true;
        }

        return true;
    }
}
