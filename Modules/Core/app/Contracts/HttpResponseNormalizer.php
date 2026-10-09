<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\Data\HttpExecutorData;

interface HttpResponseNormalizer
{
    /**
     * Raw transport data remains transient inside the target-owned normalizer.
     *
     * @param  array<string, list<string>>  $headers
     */
    public function normalize(int $statusCode, array $headers, string $body): HttpExecutorData;
}
