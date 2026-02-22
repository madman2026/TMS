<?php

namespace Modules\Auth\Actions\Auth;

use App\Contracts\BaseAction;
use App\Contracts\DKAPI;
use App\Contracts\TestContext;
use Modules\Core\Traits\Assertion;
use Modules\Core\Traits\HasStep;

class RegisterTestAction extends BaseAction
{
    use Assertion, HasStep;

    public function handle(TestContext $context)
    {
        return $this->step('register successfully', function () use ($context) {

            $page = $context->page;
            $timeout = 10000;

            $phone = '09123456789';
            $name = 'محمد حسین';
            $gender = fake()->randomElement(['male','female']);
            $job = fake()->randomElement(['مکانیک','فروشنده لوازم یدکی','استوک فروش','مشتری','سایر']);
            $state = (string) fake()->randomElement([
                56,67,69,70,71,72,73,74,75,76,77,78,79,80,81,83,84,85,87,88,89,90,91,93,94,95,96,98,99,100,102
            ]);

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

            $page->goto(env('DK__ECOMMERCE_URL', 'http://localhost:8000/') . 'register', [
                'waitUntil' => 'domcontentloaded',
                'timeout' => $timeout
            ]);

            $page->locator($fullNameSelector, ['timeout' => $timeout])->fill($name);
            $page->locator($phoneSelector, ['timeout' => $timeout])->fill($phone);

            $otp = DKAPI::getOTP($phone);
            $page->locator($otpSelector, ['timeout' => $timeout])->fill($otp);
            $page->locator($resumeButtonSelector, ['timeout' => $timeout])->click();

            $page->locator($genderSelector, ['timeout' => $timeout])->selectOption(['value' => $gender]);
            $page->locator($jobSelector, ['timeout' => $timeout])->selectOption(['label' => $job]);

            $page->locator($stateSelector)->selectOption(['value' => (string)$state]);
            $citiesId = DKAPI::getCityIds($state);
            $citiesId=array_keys(collect($citiesId)->keyBy('id')->toArray());
            $randomCity = fake()->randomElement($citiesId);

            $page->locator($citySelector)->selectOption($randomCity);
            $page->locator($familiaritySelector, ['timeout' => $timeout])
                ->selectOption(fake()->randomElement(['google','social_media','friends','marketer','ads','other']));

            $page->locator($privacySelector, ['timeout' => $timeout])->check();

            $page->locator($buttonSelector, ['timeout' => $timeout])->click();

            $this->assertTextContains($page, 'a[href="/profile"]', $name, $timeout);

        }, 'register operation test', true, $context);
    }
}
