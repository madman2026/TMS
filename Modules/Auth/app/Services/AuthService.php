<?php

namespace Modules\Auth\Services;

use App\Contracts\BaseService;
use Modules\Core\Contracts\TestContext;
use App\Exceptions\TestFailedException;
use App\Models\Profile;
use App\Models\Test;
use Exception;
use Modules\Auth\Actions\Auth\LoginTestAction;
use Modules\Auth\Actions\Auth\LogoutTestAction;
use Modules\Auth\Actions\Auth\RegisterTestAction;
use Modules\Core\Traits\NodeResolver;
use Modules\Core\Traits\RuntimeEnv;
use Modules\Core\Traits\WebDriverFactory;
use Throwable;

class AuthService extends BaseService
{
    use RuntimeEnv , NodeResolver , WebDriverFactory;

    public function __construct(
        private LoginTestAction $loginAction ,
        private RegisterTestAction $registerAction ,
        private LogoutTestAction $logoutAction
        ){
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        $this->setup();
    }

    public function all(?Profile $profile)
    {
        return $this->execute(function ($response) use($profile) {
            $result['login test'] = $this->login($profile);
            $result['register test'] = $this->register($profile);
            foreach ($result as $name => $item) {
                if (!$item->status) {
                    $response->status = false;
                    $response->message = 'error on '. $name;
                    break;
                }
            }

            return $result;
        });
    }

    public function login(?Profile $profile){
        return $this->execute(function () use ($profile) {

            $test = $profile->tests()->create([
                'name' => 'login test'
            ]);
            $context = new TestContext(
                profile: $profile,
                test: $test
            );
            $result = $this->loginAction->handle($context);

            return [
                'login test' => $result
            ];
        } , 'login test failed!');
    }
    public function register(?Profile $profile) {
        return $this->execute(function () use($profile) {

            $test = $profile->tests()->create([
                'name' => 'login test'
            ]);

            return [
                'register test' => $this->registerAction->handle(new TestContext(profile: $profile,test: $test))
            ];
        } , 'register test failed!');
    }
    public function logout(?Profile $profile) {
        return $this->execute(fn () => $this->logoutAction->handle($context));
    }
}
