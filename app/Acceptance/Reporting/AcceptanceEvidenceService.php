<?php

namespace App\Acceptance\Reporting;

use App\Acceptance\Reporting\Data\AcceptanceEvidenceView;
use App\Acceptance\Reporting\Data\EvidenceMetadataInput;
use App\Models\AcceptanceEvidence;
use App\Models\AcceptanceExecutionAttempt;
use Illuminate\Database\QueryException;
use Throwable;

final class AcceptanceEvidenceService
{
    public function record(int $attemptId, EvidenceMetadataInput $input): AcceptanceEvidenceView
    {
        if ($attemptId < 1 || ! AcceptanceExecutionAttempt::query()->whereKey($attemptId)->exists()) {
            throw AcceptanceReportingException::because('acceptance_report_not_found');
        }

        try {
            $evidence = AcceptanceEvidence::query()->create([
                'attempt_id' => $attemptId,
                'type' => $input->type,
                'reference_key' => $input->referenceKey,
                'checksum_sha256' => $input->checksumSha256,
                'size_bytes' => $input->sizeBytes,
                'media_type' => $input->mediaType,
                'width' => $input->width,
                'height' => $input->height,
                'duration_ms' => $input->durationMs,
                'captured_at' => $input->capturedAt,
                'available_until' => $input->availableUntil,
            ]);
        } catch (QueryException) {
            throw AcceptanceReportingException::because('acceptance_evidence_reference_invalid');
        }

        return $this->view($evidence);
    }

    public function view(AcceptanceEvidence $evidence): AcceptanceEvidenceView
    {
        try {
            return new AcceptanceEvidenceView(
                $evidence->id,
                $evidence->type,
                $evidence->reference_key,
                $evidence->checksum_sha256,
                $evidence->size_bytes,
                $evidence->media_type,
                $evidence->width,
                $evidence->height,
                $evidence->duration_ms,
                $evidence->captured_at,
                $evidence->available_until,
                $evidence->available_until === null || $evidence->available_until->isFuture(),
            );
        } catch (Throwable) {
            throw AcceptanceReportingException::because('acceptance_reporting_failed');
        }
    }
}
