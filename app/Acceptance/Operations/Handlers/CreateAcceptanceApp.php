<?php

namespace App\Acceptance\Operations\Handlers;

use App\Acceptance\Modules\Contracts\TargetModuleCreator;
use App\Acceptance\Modules\TargetModuleDefinition;
use App\Acceptance\Operations\Contracts\AcceptanceOperationHandler;
use App\Acceptance\Operations\Data\OperationRequest;
use App\Acceptance\Operations\Data\OperationResult;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

final class CreateAcceptanceApp implements AcceptanceOperationHandler
{
    public function __construct(
        private readonly TargetModuleCreator $creator,
        private readonly ConfigRepository $config,
    ) {}

    public function name(): string
    {
        return 'acceptance.app.create';
    }

    public function handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult
    {
        $parameters = $request->parameters;
        $data = $this->creator->create(new TargetModuleDefinition(
            $parameters['module_name'],
            $parameters['app_key'],
            (string) $this->config->get('modules.namespace', 'Modules'),
        ), $parameters['dry_run'] ?? true);

        return new OperationResult($this->name(), 'succeeded', null, $correlationId, $operationId, data: $data);
    }
}
