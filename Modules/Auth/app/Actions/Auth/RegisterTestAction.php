<?php

namespace Modules\Auth\Actions\Auth;

use App\Contracts\DKAPI;
use App\Contracts\TestContext;
use Modules\Core\Traits\Assertion;

class RegisterTestAction
{
    use Assertion;

    public function handle(TestContext $context)
    {
        $page = $context->page;
        $timeout = 10000; // 10 ثانیه تایم‌اوت پیش‌فرض

        $phone = '09123456789';
        $name = 'mohammad hosein';
        $gender = fake()->randomElement(['male','female']);
        $job = fake()->randomElement(['فروشنده لوازم یدکی','مکانیک','استوک فروش','مشتری','سایر']);
        $state = fake()->randomElement([56,67,69,70,71,72,73,74,75,76,77,78,79,80,81,83,84,85,87,88,89,90,91,93,94,95,96,98,99,100,102]);

        $fullNameSelector = '#fullname';
        $phoneSelector = '#phone_inppp';
        $otpSelector = '#random_code';
        $resumeButtonSelector = '#next_step_btn';
        $stateSelector = '#address-state';
        $citySelector = '#address-city';
        $jobSelector = '#job';
        $genderSelector = '#gender';
        $familiaritySelector = '#familiarity_select';
        $privacySelector = '#privacy_inp';
        $buttonSelector = '#register_submit';

        // رفتن به صفحه ثبت نام
        $page->goto(env('DK__ECOMMERCE_URL', 'https://www.dieselkhodro.com/') . 'register', [
            'waitUntil' => 'domcontentloaded',
            'timeout' => $timeout
        ]);
        // Step 1
        $page->locator($fullNameSelector, ['timeout' => $timeout])->fill($name);
        $page->locator($phoneSelector, ['timeout' => $timeout])->fill($phone);

        $otp = DKAPI::getOTP($phone)['otp'] ?? '0000';
        $page->locator($otpSelector, ['timeout' => $timeout])->fill($otp);

        $page->locator($resumeButtonSelector, ['timeout' => $timeout])->click();

        $page->locator($stateSelector, ['timeout' => $timeout])->selectOption($state);

        $page->waitForSelector($citySelector . ' option', ['timeout' => $timeout]);

        $cityOptions = $page->locator($citySelector . ' option', ['timeout' => $timeout])->allTextContents();
        $randomCity = fake()->randomElement($cityOptions);

        $page->locator($citySelector, ['timeout' => $timeout])->selectOption(['label' => $randomCity]);

        $page->locator($jobSelector, ['timeout' => $timeout])->selectOption($job);
        $page->locator($genderSelector, ['timeout' => $timeout])->selectOption($gender);
        $page->locator($familiaritySelector, ['timeout' => $timeout])->selectOption(fake()->randomElement([
            'google',
            'social_media',
            'friends',
            'marketer',
            'ads',
            'other',
        ]));
        $page->locator($privacySelector, ['timeout' => $timeout])->check();
        $page->locator($buttonSelector, ['timeout' => $timeout])->click();
        $this->assertTextContains($page, 'a[href="/profile"]', $name, $timeout);
    }
}
