<?php

namespace App\Http\Middleware;

use App\DriverTypeEnum;
use App\Contracts\RequestOptions;
use App\DeviceTypeEnum;
use App\InternetSpeedEnum;
use Closure;
use Illuminate\Http\Request;
use Throwable;

class ResolveRequestOptionsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try{
            $options = new RequestOptions(
                browser: DriverTypeEnum::from($request->query('browser' , 'chrome')),
                device: DeviceTypeEnum::from($request->query('device' , 'desktop')),
                speed: InternetSpeedEnum::from($request->query('speed' , 'normal'))
            );

            $request->attributes->set('profile_customize', $options);

            app()->instance(RequestOptions::class, $options);

        }catch (Throwable $e) {
            abort(422 , 'invalid args!');
        }

        return $next($request);
    }
}
