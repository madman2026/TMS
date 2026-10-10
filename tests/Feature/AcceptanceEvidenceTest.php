<?php

namespace Tests\Feature;

use App\Acceptance\Reporting\AcceptanceEvidenceService;
use App\Acceptance\Reporting\AcceptanceReportingException;
use App\Acceptance\Reporting\Data\EvidenceMetadataInput;
use App\Acceptance\Reporting\Enums\EvidenceType;
use App\Models\AcceptanceBatch;
use App\Models\AcceptanceBatchItem;
use App\Models\AcceptanceExecutionAttempt;
use App\Models\AcceptanceExecutionOperation;
use App\Models\Profile;
use App\Models\Test;
use App\Models\User;
use App\TestStatusEnum;
use DateTimeImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcceptanceEvidenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_evidence_metadata_is_persisted_related_and_expires_without_exposing_content(): void
    {
        [$attempt, $test] = $this->persistAttemptWithTest();
        $service = $this->app->make(AcceptanceEvidenceService::class);
        $input = $this->input('evidence-feature-1', new DateTimeImmutable('2026-10-11T00:00:00+00:00'));

        $this->travelTo(new DateTimeImmutable('2026-10-10T00:00:00+00:00'));
        $view = $service->record($attempt->id, $input);
        $this->assertTrue($view->available);
        $this->assertDatabaseHas('acceptance_evidence', [
            'attempt_id' => $attempt->id,
            'reference_key' => 'evidence-feature-1',
            'media_type' => 'image/png',
        ]);
        $this->assertSame('evidence-feature-1', $test->fresh()->acceptanceEvidence->sole()->reference_key);

        $this->travelTo(new DateTimeImmutable('2026-10-12T00:00:00+00:00'));
        $this->assertFalse($service->view($attempt->fresh()->evidence->sole())->available);
        $test->delete();
        $this->assertNull($attempt->fresh()->test_id);
        $this->assertDatabaseHas('acceptance_evidence', ['reference_key' => 'evidence-feature-1']);

        try {
            $attempt->delete();
            $this->fail('Evidence must prevent deletion of its attempt.');
        } catch (QueryException) {
            $this->assertDatabaseHas('acceptance_evidence', ['reference_key' => 'evidence-feature-1']);
        }
    }

    public function test_duplicate_or_missing_evidence_references_return_stable_errors(): void
    {
        [$attempt] = $this->persistAttemptWithTest();
        $service = $this->app->make(AcceptanceEvidenceService::class);
        $service->record($attempt->id, $this->input('evidence-feature-duplicate'));

        try {
            $service->record($attempt->id, $this->input('evidence-feature-duplicate'));
            $this->fail('Expected duplicate evidence to be rejected.');
        } catch (AcceptanceReportingException $exception) {
            $this->assertSame('acceptance_evidence_reference_invalid', $exception->errorCode);
        }

        try {
            $service->record($attempt->id + 10_000, $this->input('evidence-feature-missing'));
            $this->fail('Expected a missing attempt to be rejected.');
        } catch (AcceptanceReportingException $exception) {
            $this->assertSame('acceptance_report_not_found', $exception->errorCode);
        }
    }

    public function test_the_pack_migration_is_reversible_on_the_approved_in_memory_database(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $migration = require database_path('migrations/2026_10_10_000030_create_acceptance_evidence_table.php');

        $indexes = collect(DB::select("PRAGMA index_list('acceptance_evidence')"))->pluck('name')->all();
        $foreign = collect(DB::select("PRAGMA foreign_key_list('acceptance_evidence')"))
            ->first(fn (object $entry): bool => $entry->from === 'attempt_id');
        $this->assertContains('acceptance_evidence_reference_key_unique', $indexes);
        $this->assertContains('acceptance_evidence_checksum_sha256_index', $indexes);
        $this->assertContains('acceptance_evidence_attempt_captured_index', $indexes);
        $this->assertContains('acceptance_evidence_available_until_index', $indexes);
        $columns = Schema::getColumnListing('acceptance_evidence');
        foreach (['content', 'raw', 'path', 'url', 'payload', 'metadata', 'secret'] as $forbidden) {
            $this->assertNotContains($forbidden, $columns);
        }
        $this->assertSame('acceptance_execution_attempts', $foreign->table);
        $this->assertSame('RESTRICT', strtoupper($foreign->on_delete));
        $this->assertSame('CASCADE', strtoupper($foreign->on_update));

        $migration->down();
        $this->assertFalse(Schema::hasTable('acceptance_evidence'));
        $migration->up();
        $this->assertTrue(Schema::hasColumns('acceptance_evidence', [
            'attempt_id', 'type', 'reference_key', 'checksum_sha256', 'available_until',
        ]));
    }

    /** @return array{AcceptanceExecutionAttempt, Test} */
    private function persistAttemptWithTest(): array
    {
        $profile = Profile::factory()->for(User::factory())->create();
        $batch = AcceptanceBatch::factory()->for($profile)->create();
        $operation = AcceptanceExecutionOperation::factory()->for($batch, 'batch')->create();
        $item = AcceptanceBatchItem::factory()->for($batch, 'batch')->create();
        $test = $profile->tests()->create([
            'name' => 'Pack 0014 evidence',
            'status' => TestStatusEnum::FINISHED,
            'app_key' => $item->app_key,
            'component_key' => $item->component_key,
            'suite_key' => $item->suite_key,
            'scenario_key' => $item->scenario_key,
            'variant_key' => $item->variant_key,
        ]);
        $attempt = AcceptanceExecutionAttempt::factory()->create([
            'item_id' => $item->id,
            'operation_id' => $operation->id,
            'test_id' => $test->id,
        ]);

        return [$attempt, $test];
    }

    private function input(string $reference, ?DateTimeImmutable $availableUntil = null): EvidenceMetadataInput
    {
        return new EvidenceMetadataInput(
            EvidenceType::SCREENSHOT,
            $reference,
            str_repeat('b', 64),
            2_048,
            'image/png',
            640,
            480,
            null,
            new DateTimeImmutable('2026-10-10T00:00:00+00:00'),
            $availableUntil,
        );
    }
}
