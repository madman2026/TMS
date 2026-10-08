<?php

namespace App\Acceptance\Operations\Data;

use InvalidArgumentException;

/** Safe semantic result; no transport schema, human message or serializer. */
final readonly class OperationResult
{
    public const ERROR_CODES = [
        'operation_not_found', 'operation_request_invalid', 'operation_registry_invalid', 'operation_registry_duplicate',
        'acceptance_app_not_found', 'acceptance_profile_not_found',
        'acceptance_selector_invalid', 'acceptance_selector_not_found', 'acceptance_catalog_limit_exceeded',
        'acceptance_variant_not_executable', 'acceptance_configuration_invalid',
        'acceptance_registry_invalid', 'acceptance_registry_duplicate', 'acceptance_catalog_invalid',
        'acceptance_hierarchy_invalid', 'acceptance_hierarchy_duplicate',
        'acceptance_catalog_changed', 'acceptance_catalog_failed',
        'acceptance_browser_start_failed', 'acceptance_result_persistence_failed',
        'acceptance_scenario_failed', 'acceptance_step_failed', 'acceptance_command_failed',
    ];

    public const REQUEST_FIELDS = [
        'version', 'correlationId', 'parameters', 'app', 'component', 'suite', 'scenario', 'variant',
        'capability', 'tag', 'disposition', 'evidence_mode', 'limit', 'app_key',
        'component_key', 'suite_key', 'scenario_key', 'variant_key',
        'profile_id', 'browser', 'headed', 'timeout_ms', 'slow_mo_ms',
    ];

    public array $errors;

    public function __construct(
        public ?string $operation,
        public string $status,
        public ?string $errorCode,
        public string $correlationId,
        public ?string $operationId = null,
        public ?bool $retryable = null,
        public ?bool $permanent = null,
        public bool $adminActionRequired = false,
        array $errors = [],
        public CatalogOperationData|RunOperationData|null $data = null,
        public int $version = 2,
    ) {
        if ($version !== 2 || ! in_array($status, ['succeeded', 'rejected', 'failed'], true)
            || ($operation !== null && ! preg_match('/^[a-z0-9][a-z0-9._-]{0,63}$/D', $operation))
            || ($errorCode !== null && ! in_array($errorCode, self::ERROR_CODES, true))
            || ! self::isUuid($correlationId) || ($operationId !== null && ! self::isUuid($operationId))) {
            throw new InvalidArgumentException('operation_result_invalid');
        }
        $copy = [];
        foreach ($errors as $field => $codes) {
            if (! in_array($field, self::REQUEST_FIELDS, true) || ! is_array($codes) || ! array_is_list($codes)) {
                throw new InvalidArgumentException('operation_result_invalid');
            }
            $values = [];
            foreach ($codes as $code) {
                if ($code !== 'operation_request_invalid') {
                    throw new InvalidArgumentException('operation_result_invalid');
                }
                $values[] = $code;
            }
            $copy[$field] = $values;
        }
        $this->errors = $copy;
    }

    public static function isUuid(string $value): bool
    {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D', $value) === 1;
    }
}
