<?php

namespace Modules\Core\Contracts;

use Modules\Core\Data\RunOptions;
use Playwright\Browser\BrowserContextInterface;

interface BrowserFactory
{
    public function create(RunOptions $options): BrowserContextInterface;
}
