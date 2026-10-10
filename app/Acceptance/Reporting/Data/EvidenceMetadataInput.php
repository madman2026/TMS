<?php

namespace App\Acceptance\Reporting\Data;

use App\Acceptance\Reporting\Enums\EvidenceType;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class EvidenceMetadataInput
{
    public function __construct(
        public EvidenceType $type,
        public string $referenceKey,
        public string $checksumSha256,
        public int $sizeBytes,
        public string $mediaType,
        public ?int $width,
        public ?int $height,
        public ?int $durationMs,
        public DateTimeImmutable $capturedAt,
        public ?DateTimeImmutable $availableUntil = null,
        public int $version = 1,
    ) {
        if ($version !== 1
            || preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]{0,190}$/D', $referenceKey) !== 1
            || preg_match('/^[a-f0-9]{64}$/D', $checksumSha256) !== 1
            || $sizeBytes < 0 || $sizeBytes > 4_294_967_295
            || ! in_array($mediaType, $type->mediaTypes(), true)
            || ! $this->within($width, 1, 65_535)
            || ! $this->within($height, 1, 65_535)
            || ! $this->within($durationMs, 0, 86_400_000)
            || ($availableUntil !== null && $availableUntil < $capturedAt)) {
            throw new InvalidArgumentException('evidence_metadata_input_invalid');
        }
    }

    private function within(?int $value, int $minimum, int $maximum): bool
    {
        return $value === null || ($value >= $minimum && $value <= $maximum);
    }
}
