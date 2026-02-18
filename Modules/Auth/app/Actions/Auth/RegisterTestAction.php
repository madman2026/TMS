<?php

namespace Modules\Auth\Actions\Auth;

use Modules\Core\Traits\Assertion;
use Playwright\Page\PageInterface;

class RegisterTestAction
{
    use Assertion;
    public function handle(PageInterface $page)
    {
        $phone = '09903001905';
        $name = 'mohammad hosein';
        $otp = '1234';
        $gender = fake()->randomElement([
            'male',
            'female'
        ]);
        $job = fake()->randomElement(['فروشنده لوازم یدکی','مکانیک','استوک فروش','مشتری','سایر']);
        $state = fake()->randomElement([56,67,69,70,71,72,73,74,75,76,77,78,79,80,81,83,84,85,87,88,89,90,91,93,94,95,96,98,99,100,102]);
        $city = fake()->randomElement([range(1 , 3)]);
        $fullNameSelector= '#fullname';
        $phoneSelector= '#phone_inppp';
        $stateSelector= '#address-state';
        $jobSelector= '#job';
        $genderSelector= '#gender';
        $otpSelector= '#random_code';
        $citySelector= '#address-city';
        $familiaritySelector= 'familiarity_select';
        $privacySelector = '#privacy_inp';
        $buttonSelector = '#register_submit';

        $page->goto(env('DK__ECOMMERCE_URL' , 'https://www.dieselkhodro.com/').'register');

        $page->locator($fullNameSelector)->fill($name);
        $page->locator($phoneSelector)->fill($phone);
        $page->locator($stateSelector)->selectOption($state);
        $page->locator($jobSelector)->fill($job);
        $page->locator($genderSelector)->fill($gender);
        $page->locator($otpSelector)->fill($otp);
        $page->locator($citySelector)->selectOption($city);
        $page->locator($familiaritySelector)->fill($familiaritySelector);
        $page->locator($privacySelector)->fill($privacySelector);
        $page->locator($buttonSelector)->fill($buttonSelector);

        $page->click($privacySelector);
        $page->click($buttonSelector);

        $this->assertTextContains($page , 'a[href="/profile"]' , 'mohammad hosein');

    }
}
