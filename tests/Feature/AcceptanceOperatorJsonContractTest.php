<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AcceptanceOperatorJsonContractTest extends TestCase
{
    public function test_explicit_json_keeps_the_existing_catalog_projection(): void
    {
        $this->assertSame(0, Artisan::call('acceptance:list', ['--json' => true, '--no-interaction' => true]));
        $payload = json_decode(trim(Artisan::output()), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame([
            'schema_version', 'status', 'plan_version', 'catalog_versions', 'counts', 'fingerprint', 'items',
        ], array_keys($payload));
        $this->assertSame(2, $payload['schema_version']);
        $this->assertSame('listed', $payload['status']);
    }

    public function test_new_operation_rejections_use_the_ordered_envelope_and_object_errors(): void
    {
        $this->assertSame(2, Artisan::call('acceptance:app:validate', [
            'module' => 'DefinitelyMissingModule',
            '--json' => true,
            '--no-interaction' => true,
        ]));
        $json = trim(Artisan::output());
        $payload = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame([
            'schema_version', 'operation', 'status', 'error_code', 'correlation_id', 'operation_id',
            'retryable', 'permanent', 'admin_action_required', 'errors', 'data',
        ], array_keys($payload));
        $this->assertSame('acceptance.app.validate', $payload['operation']);
        $this->assertSame('rejected', $payload['status']);
        $this->assertStringContainsString('"errors":{}', $json);
        $this->assertNull($payload['data']);
    }

    public function test_machine_output_is_one_line_and_does_not_echo_invalid_input(): void
    {
        $this->assertSame(2, Artisan::call('acceptance:coverage', [
            'app' => 'example-sensitive-value/invalid',
            '--json' => true,
            '--no-interaction' => true,
        ]));
        $output = trim(Artisan::output());

        $this->assertSame(0, substr_count($output, "\n"));
        $this->assertStringNotContainsString('example-sensitive-value', $output);
        $this->assertSame('operation_request_invalid', json_decode(
            $output,
            true,
            flags: JSON_THROW_ON_ERROR,
        )['error_code']);
    }
}
