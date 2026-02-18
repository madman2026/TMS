<?php

namespace Modules\Auth\Services;

use App\Contracts\BaseService;
use Modules\Auth\Actions\Auth\LoginTestAction;
use Modules\Auth\Actions\Auth\LogoutTestAction;
use Modules\Auth\Actions\Auth\RegisterTestAction;
use Modules\Core\Traits\NodeResolver;
use Modules\Core\Traits\RuntimeEnv;
use Modules\Core\Traits\WebDriverFactory;

class AuthService extends BaseService
{
    use RuntimeEnv , NodeResolver , WebDriverFactory;
    public function __construct(private LoginTestAction $loginAction , private RegisterTestAction $registerAction , private LogoutTestAction $logoutAction){
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        $this->setup();
    }
    public function all(?string $profile){
        return $this->execute(fn () => $this->loginAction->handle($this->createBrowser(false)->newPage()));
    }
    public function login(?string $profile){
        return $this->execute(fn () => $this->loginAction->handle($this->createBrowser(false)->newPage()));
    }
    public function register(?string $profile) {
        return $this->execute(fn () => $this->registerAction->handle($this->createBrowser(false)->newPage()));
    }
    public function logout(?string $profile) {
        return $this->execute(fn () => $this->logoutAction->handle($this->createBrowser(false)->newPage()));
    }
}
