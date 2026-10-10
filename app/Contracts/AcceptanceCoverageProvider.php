<?php

namespace App\Contracts;

use App\Acceptance\Coverage\Data\SourceCaseMapping;

interface AcceptanceCoverageProvider extends AcceptanceComponentProvider
{
    /** @return iterable<SourceCaseMapping> */
    public function sourceCaseMappings(): iterable;
}
