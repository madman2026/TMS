<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $service
    ){}
    public function all(Request $request)
    {
        $this->service->all($request->string('profile'));
    }

    public function login(Request $request)
    {
        return $this->service->login($request->string('profile'));

    }

    public function register(Request $request)
    {
        $this->service->register($request->string('profile'));
    }

    public function logout(Request $request)
    {
        $this->service->logout($request->string('profile'));
    }

    public function report(Request $request)
    {
        $this->service->report($request->string('profile'));
    }
}
