<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Profile;
use Closure;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Contracts\RequestOptions;

class TestAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $profileName = $request->header('X-Profile', 'primary');

        $profile = Profile::where('name', $profileName)->firstOrFail();
        $options = app(RequestOptions::class);
        $profileRuntime = clone $profile;

        $data = $profileRuntime->data;

        $data['browser']['driver'] = $options->browser->value ?? $data['browser']['driver'];
        $data['global']['speed'] = $options->speed->value ?? $data['global']['internet_speed'];
        $profileRuntime->data = $data;

        $request->attributes->set('profile', $profileRuntime);
        return $next($request);
    }

}
