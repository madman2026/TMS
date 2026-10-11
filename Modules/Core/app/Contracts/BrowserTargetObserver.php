<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\Data\BrowserTargetObservation;
use Playwright\Page\PageInterface;

interface BrowserTargetObserver
{
    public function observe(PageInterface $page, string $observationKey): BrowserTargetObservation;
}
