<?php

namespace App\Contracts;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

abstract class BaseService
{
    protected function execute(
        callable $callback,
        string $errorMessage = "Internal Server Error!",
        string $successMessage = "Operation Completed Successfully!",
        bool $useTransaction = true,
    ): ServiceResponse {
    $response = new ServiceResponse(
        status: true,
        message: $successMessage,
        data: null
    );

    try {
        $runner = fn () => $callback($response);

        $result = $useTransaction
            ? DB::transaction($runner)
            : $runner();

        $response->data = $result;

        return $response;
    } catch (Throwable $e) {
        report($e);

        return ServiceResponse::error(
            $errorMessage,
            config('app.debug') ? $e->getMessage() : null
        );
    }
}
}
