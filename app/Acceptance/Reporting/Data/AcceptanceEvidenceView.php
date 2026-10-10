<?php

namespace App\Acceptance\Reporting\Data;

use App\Acceptance\Reporting\Enums\EvidenceType;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class AcceptanceEvidenceView
{
    public function __construct(
        public int $id,
        public EvidenceType $type,
        public string $referenceKey,
        public string $checksumSha256,
        public int $sizeBytes,
        public string $mediaType,
        public ?int $width,
        public ?int $height,
        public ?int $durationMs,
        public DateTimeImmutable $capturedAt,
        public ?DateTimeImmutable $availableUntil,
        public bool $available,
        public int $version = 1,
    ) {
        try {
            new EvidenceMetadataInput(
                $type,
                $referenceKey,
                $checksumSha256,
                $sizeBytes,
                $mediaType,
                $width,
                $height,
                $durationMs,
                $capturedAt,
                $availableUntil,
                $version,
            );
        } catch (InvalidArgumentException) {
            throw new InvalidArgumentException('acceptance_evidence_view_invalid');
        }
        if ($id < 1) {
            throw new InvalidArgumentException('acceptance_evidence_view_invalid');
        }
    }
}
