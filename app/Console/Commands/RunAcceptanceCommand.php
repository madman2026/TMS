<?php

namespace App\Console\Commands;

use App\Exceptions\AcceptanceRegistryException;
use App\Models\Profile;
use App\Services\AcceptanceAppRegistry;
use App\Services\AcceptanceRunService;
use App\TestStatusEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Core\Data\RunOptions;
use Modules\Core\Exceptions\AcceptanceExecutionException;
use Throwable;

final class RunAcceptanceCommand extends Command
{
    protected $signature = 'acceptance:run
        {app}
        {scenario}
        {profile}
        {--browser=}
        {--headed}
        {--timeout=}
        {--slow-mo=}';

    public function handle(
        AcceptanceAppRegistry $registry,
        AcceptanceRunService $runService,
    ): int {
        $executionStarted = false;

        try {
            $app = $registry->app((string) $this->argument('app'));

            if ($app === null) {
                return $this->reject('acceptance_app_not_found');
            }

            $scenario = $registry->scenario($app->key(), (string) $this->argument('scenario'));

            if ($scenario === null) {
                return $this->reject('acceptance_scenario_not_found');
            }

            $profile = Profile::query()->find($this->argument('profile'));

            if ($profile === null) {
                return $this->reject('acceptance_profile_not_found');
            }

            $options = $this->runOptions();
            $executionStarted = true;
            $test = $runService->run($profile, $app, $scenario, $options);

            $this->writeJson([
                'status' => $test->status->value,
                'test_id' => $test->getKey(),
                'app_key' => $app->key(),
                'scenario_key' => $scenario->key(),
                'error_code' => $test->error_code,
            ]);

            return $test->status === TestStatusEnum::FINISHED
                ? self::SUCCESS
                : self::FAILURE;
        } catch (AcceptanceExecutionException $exception) {
            if (! $executionStarted && $exception->errorCode === 'acceptance_configuration_invalid') {
                return $this->reject($exception->errorCode);
            }

            $this->writeJson([
                'status' => 'failed',
                'error_code' => $exception->errorCode,
            ]);

            return self::FAILURE;
        } catch (AcceptanceRegistryException $exception) {
            return $this->unexpectedFailure($exception->errorCode, $exception);
        } catch (Throwable $exception) {
            return $this->unexpectedFailure('acceptance_command_failed', $exception);
        }
    }

    private function runOptions(): RunOptions
    {
        $browser = $this->option('browser');

        return new RunOptions(
            browser: is_string($browser) && $browser !== ''
                ? $browser
                : (string) config('core.acceptance.browser', 'chromium'),
            headless: $this->option('headed')
                ? false
                : (bool) config('core.acceptance.headless', true),
            timeoutMs: $this->integerOption(
                'timeout',
                (int) config('core.acceptance.timeout_ms', 30_000),
                1,
            ),
            slowMoMs: $this->integerOption(
                'slow-mo',
                (int) config('core.acceptance.slow_mo_ms', 0),
                0,
            ),
        );
    }

    private function integerOption(string $name, int $default, int $minimum): int
    {
        $value = $this->option($name);

        if ($value === null || $value === '') {
            return $default;
        }

        if (! is_string($value) || ! ctype_digit($value)) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        $parsed = (int) $value;

        if ($parsed < $minimum) {
            throw AcceptanceExecutionException::configurationInvalid();
        }

        return $parsed;
    }

    private function reject(string $errorCode): int
    {
        $this->writeJson([
            'status' => 'rejected',
            'error_code' => $errorCode,
        ]);

        return self::INVALID;
    }

    private function unexpectedFailure(string $errorCode, Throwable $exception): int
    {
        Log::error('tms.acceptance.command.failed', [
            'error_code' => $errorCode,
            'exception_class' => $exception::class,
        ]);

        $this->writeJson([
            'status' => 'failed',
            'error_code' => $errorCode,
        ]);

        return self::FAILURE;
    }

    /**
     * @param  array<string, int|string|null>  $payload
     */
    private function writeJson(array $payload): void
    {
        $this->line(json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }
}
