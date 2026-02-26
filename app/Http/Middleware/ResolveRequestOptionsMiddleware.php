<?php

namespace App\Http\Middleware;

use App\DriverTypeEnum;
use App\DeviceTypeEnum;
use App\InternetSpeedEnum;
use Closure;
use Illuminate\Http\Request;
use Modules\Core\Contracts\RequestOptions;
use Throwable;

class ResolveRequestOptionsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try{
            $options = new RequestOptions(
                browser: DriverTypeEnum::from($request->string('browser' , 'chrome')),
                device: DeviceTypeEnum::from($request->string('device' , 'desktop')),
                speed: InternetSpeedEnum::from($request->string('speed' , 'normal'))
            );

            $request->attributes->set('profile_customize', $options);

            app()->instance(RequestOptions::class, $options);

        }catch (Throwable $e) {
            report($e);
            abort(422 , 'invalid args!');
        }

        return $next($request);
    }
}
