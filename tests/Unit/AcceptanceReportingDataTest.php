<?php

namespace Tests\Unit;

use App\Acceptance\Coverage\Enums\CoverageDisposition;
use App\Acceptance\Execution\Enums\BatchState;
use App\Acceptance\Execution\Enums\ExecutionMode;
use App\Acceptance\Execution\Enums\OperationState;
use App\Acceptance\Reporting\Data\AcceptanceAttemptView;
use App\Acceptance\Reporting\Data\AcceptanceCoverageView;
use App\Acceptance\Reporting\Data\AcceptanceEvidenceView;
use App\Acceptance\Reporting\Data\AcceptanceReport;
use App\Acceptance\Reporting\Data\AcceptanceReportItem;
use App\Acceptance\Reporting\Data\AcceptanceStatusView;
use App\Acceptance\Reporting\Data\CoverageCounts;
use App\Acceptance\Reporting\Data\CoverageSourceCaseView;
use App\Acceptance\Reporting\Data\EvidenceMetadataInput;
use App\Acceptance\Reporting\Enums\EvidenceType;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Tests\TestCase;

class AcceptanceReportingDataTest extends TestCase
{
    private const UUID = 'dcb1cf9d-207c-4a44-963b-000000000014';

    public function test_metadata_input_accepts_only_opaque_type_compatible_values(): void
    {
        $input = $this->input();

        $this->assertSame('evidence-1', $input->referenceKey);
        $this->assertSame(EvidenceType::SCREENSHOT, $input->type);
    }

    #[DataProvider('invalidMetadata')]
    public function test_metadata_input_rejects_unsafe_or_incompatible_values(array $changes): void
    {
        $arguments = [
            'type' => EvidenceType::SCREENSHOT,
            'referenceKey' => 'evidence-1',
            'checksumSha256' => str_repeat('a', 64),
            'sizeBytes' => 10,
            'mediaType' => 'image/png',
            'width' => 100,
            'height' => 50,
            'durationMs' => null,
            'capturedAt' => new DateTimeImmutable('2026-10-10T10:00:00+00:00'),
            'availableUntil' => null,
        ];

        $this->expectException(InvalidArgumentException::class);
        new EvidenceMetadataInput(...array_replace($arguments, $changes));
    }

    public static function invalidMetadata(): array
    {
        return [
            'url-like reference' => [['referenceKey' => 'https://example.test/file']],
            'uppercase checksum' => [['checksumSha256' => str_repeat('A', 64)]],
            'wrong media type' => [['mediaType' => 'text/html']],
            'invalid width' => [['width' => 0]],
            'availability before capture' => [[
                'availableUntil' => new DateTimeImmutable('2026-10-09T10:00:00+00:00'),
            ]],
            'unsupported version' => [['version' => 2]],
        ];
    }

    public function test_coverage_counts_and_pages_copy_their_input_lists(): void
    {
        $counts = new CoverageCounts(1, 2, 3, 4, 5);
        $cases = [$this->coverageCase('case-1')];
        $view = new AcceptanceCoverageView('app-a', 'v1', $counts, $cases, 50, null, null, false);
        $cases[] = $this->coverageCase('case-2');

        $this->assertSame(15, $counts->total);
        $this->assertCount(1, $view->cases);
        $this->assertSame('case-1', $view->cases[0]->sourceCaseId);
    }

    public function test_report_dtos_are_immutable_transport_neutral_values(): void
    {
        $status = $this->statusView();
        $report = new AcceptanceReport($status, [], 50, null, null, false);

        $this->assertSame(1, $report->status->batchId);
        foreach ([
            EvidenceMetadataInput::class,
            AcceptanceEvidenceView::class,
            AcceptanceAttemptView::class,
            AcceptanceReportItem::class,
            AcceptanceStatusView::class,
            AcceptanceReport::class,
            CoverageCounts::class,
            CoverageSourceCaseView::class,
            AcceptanceCoverageView::class,
        ] as $class) {
            $reflection = new ReflectionClass($class);
            $this->assertTrue($reflection->isFinal());
            $this->assertTrue($reflection->isReadOnly());
            $this->assertFalse($reflection->implementsInterface(\JsonSerializable::class));
            $this->assertFalse($reflection->hasMethod('toArray'));
        }
    }

    public function test_page_dtos_reject_inconsistent_cursors(): void
    {
        foreach ([
            fn () => new AcceptanceReport($this->statusView(), [], 50, null, null, true),
            fn () => new AcceptanceCoverageView('app-a', 'v1', new CoverageCounts(0, 0, 0, 0, 0), [], 50, null, null, true),
        ] as $construct) {
            try {
                $construct();
                $this->fail('Expected cursor consistency validation.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    private function input(): EvidenceMetadataInput
    {
        return new EvidenceMetadataInput(
            EvidenceType::SCREENSHOT,
            'evidence-1',
            str_repeat('a', 64),
            10,
            'image/png',
            100,
            50,
            null,
            new DateTimeImmutable('2026-10-10T10:00:00+00:00'),
        );
    }

    private function coverageCase(string $sourceCaseId): CoverageSourceCaseView
    {
        return new CoverageSourceCaseView(
            $sourceCaseId,
            CoverageDisposition::AUTOMATED_FULL,
            'component-a',
            'suite-a',
            'scenario-a',
            'default',
            null,
            null,
            null,
            null,
            null,
            [],
            [],
        );
    }

    private function statusView(): AcceptanceStatusView
    {
        return new AcceptanceStatusView(
            self::UUID,
            1,
            self::UUID,
            OperationState::SUCCEEDED,
            BatchState::COMPLETED,
            ExecutionMode::SYNC,
            0,
            0,
            1,
            1,
            0,
            0,
            0,
            0,
            0,
            1,
            0,
            0,
            0,
            null,
            null,
            null,
            false,
            null,
            null,
        );
    }
}
