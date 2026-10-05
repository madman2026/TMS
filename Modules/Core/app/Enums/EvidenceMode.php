<?php

namespace Modules\Core\Enums;

enum EvidenceMode: string
{
    case METADATA_ONLY = 'metadata-only';
    case NON_SENSITIVE_VISUAL = 'non-sensitive-visual';
    case SENSITIVE_NO_CAPTURE = 'sensitive-no-capture';
}
