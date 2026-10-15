<?php

declare(strict_types=1);

namespace Modules\DK\Tests\Feature;

use App\Acceptance\Operations\AcceptanceOperationService;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Prerequisites\Data\PrerequisiteOperationData;
use App\Acceptance\Prerequisites\Data\PrerequisiteReferenceContext;
use App\Acceptance\Prerequisites\Enums\InputResolution;
use App\Acceptance\Prerequisites\Enums\InputSensitivity;
use App\Acceptance\Prerequisites\Enums\InputSource;
use App\Acceptance\Prerequisites\Enums\InputType;
use App\Acceptance\Prerequisites\Enums\PrerequisiteState;
use App\Acceptance\Prerequisites\PrerequisiteReferenceResolverRegistry;
use App\Acceptance\Prerequisites\PrerequisiteService;
use App\Acceptance\Targets\TargetBrowserRuntimeRegistry;
use App\Models\AcceptanceOperationInput;
use App\Models\Profile;
use App\Models\User;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Modules\Core\Enums\AutomationDisposition;
use Modules\Core\Enums\EvidenceMode;
use Modules\DK\Acceptance\Components\NotificationDelivery\Catalogs\OperationsHealthVariantCatalog;
use Modules\DK\Acceptance\Components\NotificationDelivery\Scenarios\NdComponentOperationsHealth;
use Modules\DK\Acceptance\Shared\Prerequisites\DKPrerequisiteReferenceResolver;
use Modules\DK\Acceptance\Shared\Target\DKBrowserTargetRuntimeResolver;
use Modules\DK\Acceptance\Shared\Target\LocalLoopbackSecretHandleResolver;
use Tests\TestCase;

final class NotificationDeliveryOperationsHealthAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_exact_bounded_group_is_executable_without_expanding_side_effect_scope(): void
    {
        Http::preventStrayRequests();
        Notification::fake();
        Queue::fake();
        Event::fake();
        $catalog = $this->app->make(AcceptanceCatalog::class);
        $app = $this->app->make(AcceptanceAppRegistry::class)->app('dk');
        $variantCatalog = $this->app->make(OperationsHealthVariantCatalog::class);
        $descriptors = iterator_to_array($catalog->descriptors('dk'));
        $selected = array_values(array_filter(
            $descriptors,
            static fn ($descriptor): bool => $descriptor->key === 'nd-component-operations-health',
        ));

        self::assertSame('v4', $catalog->version('dk'));
        self::assertCount(139, $descriptors);
        self::assertCount(1, $selected);
        self::assertSame(AutomationDisposition::AUTOMATED, $selected[0]->metadata->disposition);
        self::assertSame(EvidenceMode::NON_SENSITIVE_VISUAL, $selected[0]->metadata->evidenceMode);
        self::assertCount(138, array_filter(
            $descriptors,
            static fn ($descriptor): bool => $descriptor->metadata->disposition === AutomationDisposition::NOT_IMPLEMENTED,
        ));

        $variants = iterator_to_array($catalog->variants('dk', 'nd-component-operations-health'));
        self::assertSame(array_column($variantCatalog->all(), 'key'), array_column($variants, 'key'));
        foreach ($variants as $variant) {
            $schema = $variant->prerequisiteSchema;
            self::assertSame('operations-health-v2', $schema->version);
            self::assertCount(1, $schema->inputs);
            self::assertSame('credential', $schema->inputs[0]->key);
            self::assertSame(InputType::SECRET_REFERENCE, $schema->inputs[0]->type);
            self::assertSame(InputSensitivity::SECRET_REFERENCE, $schema->inputs[0]->sensitivity);
            self::assertTrue($schema->inputs[0]->required);
            self::assertFalse($schema->inputs[0]->approvalRelevant);
            self::assertSame([InputSource::SECRET_REFERENCE], $schema->inputs[0]->allowedSources);
            self::assertSame(InputResolution::APP_REFERENCE, $schema->inputs[0]->resolution);
            self::assertSame([], $schema->approvals);
            self::assertInstanceOf(NdComponentOperationsHealth::class, $app->resolveScenario(
                'notification-delivery',
                'admin.delivery',
                'nd-component-operations-health',
                $variant->key,
            ));
        }

        $mappings = iterator_to_array($app->sourceCaseMappings());
        self::assertCount(24_668, $mappings);
        self::assertCount(21, array_filter(
            $mappings,
            static fn ($mapping): bool => $mapping->scenarioKey === 'nd-component-operations-health',
        ));
        $unrelated = iterator_to_array($catalog->variants('dk', 'nd-component-attempts-show'));
        self::assertNotEmpty($unrelated);
        self::assertSame('none-v1', $unrelated[0]->prerequisiteSchema->version);
        self::assertSame([], $unrelated[0]->prerequisiteSchema->inputs);
        self::assertSame([], $unrelated[0]->prerequisiteSchema->approvals);
        self::assertInstanceOf(
            DKBrowserTargetRuntimeResolver::class,
            $this->app->make(TargetBrowserRuntimeRegistry::class)->for('dk'),
        );
        self::assertInstanceOf(
            DKPrerequisiteReferenceResolver::class,
            $this->app->make(PrerequisiteReferenceResolverRegistry::class)->for('dk'),
        );
        self::assertSame([
            'profile' => 'local-loopback',
            'endpoint' => 'http://127.0.0.1:8000',
            'secret_handle' => LocalLoopbackSecretHandleResolver::HANDLE,
        ], config('dk.target.control'));

        Http::assertNothingSent();
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
        Event::assertNothingDispatched();
    }

    public function test_the_official_prerequisite_workflow_delivers_the_opaque_credential_reference(): void
    {
        Http::preventStrayRequests();
        $profile = Profile::factory()->for(User::factory())->create();
        $identity = [
            'app_key' => 'dk',
            'component_key' => 'notification-delivery',
            'suite_key' => 'admin.delivery',
            'scenario_key' => 'nd-component-operations-health',
            'variant_key' => 'v.005502ef537b4a85',
        ];
        $operations = $this->app->make(AcceptanceOperationService::class);

        $prepared = $operations->execute(new OperationRequest(
            'acceptance.prerequisite.request.prepare',
            [...$identity, 'profile_id' => $profile->getKey()],
        ));

        self::assertSame('succeeded', $prepared->status);
        self::assertInstanceOf(PrerequisiteOperationData::class, $prepared->data);
        self::assertSame(PrerequisiteState::READY, $prepared->data->state);
        self::assertSame('operations-health-v2', $prepared->data->schemaVersion);
        self::assertSame([], $prepared->data->missingInputKeys);
        self::assertSame([], $prepared->data->approvalRequirements);
        self::assertDatabaseHas('acceptance_operation_inputs', [
            'acceptance_operation_request_id' => $prepared->data->requestId,
            'key' => 'credential',
            'secret_reference' => LocalLoopbackSecretHandleResolver::HANDLE,
            'submitted_by_type' => 'app_resolver',
            'submitted_by_reference' => 'dk-resolver',
        ]);

        $literal = $operations->execute(new OperationRequest(
            'acceptance.prerequisite.input.submit',
            [
                'request_id' => $prepared->data->requestId,
                'expected_lock_version' => $prepared->data->lockVersion,
                'inputs' => [
                    'credential' => [
                        'source' => 'literal',
                        'value' => 'inert-secret-sentinel',
                    ],
                ],
            ],
        ));

        self::assertSame('rejected', $literal->status);
        self::assertSame('secret_literal_forbidden', $literal->errorCode);
        self::assertStringNotContainsString('inert-secret-sentinel', print_r($literal, true));
        self::assertSame(1, AcceptanceOperationInput::query()->count());

        $submitted = $operations->execute(new OperationRequest(
            'acceptance.prerequisite.input.submit',
            [
                'request_id' => $prepared->data->requestId,
                'expected_lock_version' => $prepared->data->lockVersion,
                'inputs' => [
                    'credential' => [
                        'source' => 'secret_reference',
                        'value' => LocalLoopbackSecretHandleResolver::HANDLE,
                    ],
                ],
            ],
        ));
        $executionData = $this->app->make(PrerequisiteService::class)->executionData(
            $identity,
            (int) $profile->getKey(),
            $prepared->data->requestId,
            'correlation-operations-health-prerequisite',
            'acceptance.run',
        );
        $automaticData = $this->app->make(PrerequisiteService::class)->executionData(
            $identity,
            (int) $profile->getKey(),
            null,
            'correlation-operations-health-automatic',
            'acceptance.run',
        );

        self::assertSame(PrerequisiteState::READY, $submitted->data->state);
        self::assertSame([], $executionData->nonSensitiveInputs);
        self::assertSame(
            ['credential' => LocalLoopbackSecretHandleResolver::HANDLE],
            $executionData->secretReferences,
        );
        self::assertSame($prepared->data->requestId, $executionData->requestId);
        self::assertNotSame($prepared->data->requestId, $automaticData->requestId);
        self::assertSame(
            ['credential' => LocalLoopbackSecretHandleResolver::HANDLE],
            $automaticData->secretReferences,
        );
        self::assertSame(2, AcceptanceOperationInput::query()->count());
        Http::assertNothingSent();
    }

    public function test_the_app_resolver_is_exact_and_declines_unrelated_contexts(): void
    {
        $resolver = $this->app->make(DKPrerequisiteReferenceResolver::class);
        $matching = new PrerequisiteReferenceContext(
            'dk',
            'notification-delivery',
            'admin.delivery',
            'nd-component-operations-health',
            'v.005502ef537b4a85',
            1,
            'operations-health-v2',
            'credential',
        );
        $wrongScenario = new PrerequisiteReferenceContext(
            'dk',
            'notification-delivery',
            'admin.delivery',
            'nd-component-providers-index',
            'v.005502ef537b4a85',
            1,
            'operations-health-v2',
            'credential',
        );
        $wrongVariant = new PrerequisiteReferenceContext(
            'dk',
            'notification-delivery',
            'admin.delivery',
            'nd-component-operations-health',
            'v.ffffffffffffffff',
            1,
            'operations-health-v2',
            'credential',
        );

        self::assertSame(LocalLoopbackSecretHandleResolver::HANDLE, $resolver->resolve($matching));
        self::assertNull($resolver->resolve($wrongScenario));
        self::assertNull($resolver->resolve($wrongVariant));
    }
}
