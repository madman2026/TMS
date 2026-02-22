<?php

namespace App\Http\Middleware;

use App\BrowserTypeEnum;
use App\Contracts\RequestOptions;
use App\InternetSpeedEnum;
use Closure;
use Illuminate\Http\Request;

class ResolveRequestOptionsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $options = new RequestOptions(
            browser: BrowserTypeEnum::tryFrom($request->query('browser' , 'chrome')),
            speed: InternetSpeedEnum::tryFrom($request->query('speed' , 'normal'))
        );

        $request->attributes->set('profile_customize', $options);

        app()->instance(RequestOptions::class, $options);

        return $next($request);
    }
}
