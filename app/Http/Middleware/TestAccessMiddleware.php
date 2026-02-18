<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Profile;
use Closure;
use Illuminate\Support\Facades\Hash;

class TestAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $data = [
            'profile' => $request->header('X-Profile'),
            'secret'  => $request->header('X-Secret'),
        ];

        Validator::validate($data, [
            'profile' => ['required', 'string'],
            'secret'  => ['required', 'string'],
        ]);

        $profile = Profile::where('name', $data['profile'])->first();

        if (!$profile || !Hash::check($data['secret'], $profile->secret)) {
            abort(403, 'Invalid profile or secret');
        }

        $request->attributes->set('auth_profile', $profile);

        return $next($request);
    }
}
