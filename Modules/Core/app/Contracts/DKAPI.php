<?php

namespace Modules\Core\Contracts;

use Illuminate\Support\Facades\Http;

class DKAPI
{
    public static function getOTP(string $number)
    {
        $response = Http::withHeader('x-e2e-token', config('dieselkhodro.token'))
            ->post(config('dieselkhodro.url') . 'e2e/auth/register', [
                'mobile' => $number,
            ]);
        if ($response->failed()) {
            throw new \Exception('API request for get OTP failed: ' . json_decode($response->body())->message);
        }

        return $response->json()['result']['otp'];
    }

    public static function getCityIds(int $stateID)
    {
        $response = Http::get(config('dieselkhodro.url') . 'get-province-cities' , ['state_id' => $stateID]);
        if ($response->failed()) {
            throw new \Exception('API request for get cities id failed: ' . $response->body());
        }

        return $response->json()['cities'];
    }
}
