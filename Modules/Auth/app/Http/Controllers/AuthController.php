<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Http\Requests\TestLoginRequest;
use Modules\Auth\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $service
    ){}
    public function all(Request $request)
    {
        $result = $this->service->all($request->attributes->get('profile'));

        return $result->status
            ? $this->success($result->data , $result->message)
            : $this->error($result->message , $result->data);
    }

    public function login(TestLoginRequest $request)
    {
        $result = $this->service->login($request->attributes->get('profile'));

        return $result->status
            ? $this->success($result->data , $result->message)
            : $this->error($result->message , $result->data);
    }

    public function register(Request $request)
    {
        $this->service->register($request->attributes->get('profile'));
    }

    public function logout(Request $request)
    {
        $this->service->logout($request->attributes->get('profile'));
    }
}
