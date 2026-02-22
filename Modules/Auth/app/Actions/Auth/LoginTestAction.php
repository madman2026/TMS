<?php

namespace Modules\Auth\Actions\Auth;

use App\Contracts\BaseAction;
use App\Contracts\StepResult;
use App\Contracts\TestContext;
use Modules\Core\Traits\Assertion;
use Modules\Core\Traits\HasStep;
class LoginTestAction extends BaseAction
{
    use Assertion , HasStep;

    protected $test;

    public function handle(TestContext $context): StepResult
    {
        return $this->step('login successfully', function() use ($context) {
            $page = $context->page;
            $phone = '09922926708';
            $password = '12345678';
            $phoneSelector = '#phone_inp';
            $passwordSelector = '#passinp';
            $submitSelector = '.btn-primary-custom';
            // $page->goto(env('DK__ECOMMERCE_URL' , 'https://www.dieselkhodro.com/').'login' , [
            $page->goto('https://www.dieselkhodro.com/login' , [
                'timeout' => 1000000,
                'waitUntil' => 'domcontentloaded'
            ]);
            $page->locator($phoneSelector)->fill($phone);
            $page->locator($passwordSelector)->fill($password);
            $page->locator($submitSelector)->click();
            $this->assertTextContains($page , 'a[href="/profile"]' , 'سید محمد حسین هاشمی' , 1);
            dd($context->browser->cookies());

        }, 'Login page and assert profile link', true, $context);
    }
}
