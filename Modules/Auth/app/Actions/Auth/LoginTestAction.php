<?php

namespace Modules\Auth\Actions\Auth;

use Modules\Core\Traits\Assertion;
use Playwright\Page\PageInterface;

class LoginTestAction
{
    use Assertion;
    public function handle(PageInterface $page)
    {

        $phone = '09922926708';
        $password = '12345678';

        $phoneSelector = '#phone_inp';
        $passwordSelector = '#passinp';
        $submitSelector = '.btn-primary-custom';
        $page->goto(env('DK__ECOMMERCE_URL' , 'https://www.dieselkhodro.com/').'login' , [
            'timeout' => 1000000,
            'waitUntil' => 'domcontentloaded'
        ]);
        $page->locator($phoneSelector)->fill($phone);
        $page->locator($passwordSelector)->fill($password);
        $page->locator($submitSelector)->click();

        $this->assertTextContains($page , 'a[href="/profile"]' , 'سید محمد حسین هاشمی');
    }
}
