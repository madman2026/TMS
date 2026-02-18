<?php

namespace Modules\Auth\Services;

use App\Contracts\BaseService;
use Modules\Auth\Actions\Auth\LoginTestAction;
use Modules\Core\Traits\NodeResolver;
use Modules\Core\Traits\RuntimeEnv;
use Modules\Core\Traits\WebDriverFactory;

class AuthService extends BaseService
{
    use RuntimeEnv , NodeResolver , WebDriverFactory;
    public function __construct(private LoginTestAction $loginAction){
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        $this->setup();
        // $this->resolveNode();

    }
    public function all(?string $profile){}
    public function login(?string $profile)
    {
        return $this->loginAction->handle($this->createBrowser(false)->newPage());
    }
    public function register(?string $profile) {}
    public function logout(?string $profile) {}
    public function report(?string $profile) {}
}
