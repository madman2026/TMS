<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Http\Requests\TestLoginRequest;
use Modules\Auth\Services\AuthService;

use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\Authenticated;

#[Group(
    name: 'Auth',
    description: 'Authentication & automated auth tests'
)]
class AuthController extends Controller
{
    public function __construct(private AuthService $service) {}

    #[Endpoint(
        title: 'Run All Auth Tests',
        description: 'Run login, register, logout and forgot-password tests with optional presets'
    )]
    #[Header('Accept', example: 'application/json')]
    #[BodyParam('driver', 'string', 'Webdriver name', example: 'chrome')]
    #[BodyParam('profile', 'string', 'Preset profile for variables/settings', example: 'primary')]
    #[BodyParam('internet_speed', 'string', 'Network profile', example: '5G')]

    #[Response(status: 200, description: 'All tests executed', content: [
        'status' => true,
        'message' => 'Success',
        'data' => [
            'login test' => [
                'name' => 'login successfully',
                'passed' => true,
                'error' => null,
                'duration' => 3.123142,
                'results' => ['token' => 'sdfadsfssdfdsfsdfs'],
                'critical' => true
            ],
            'register test' => [
                'name' => 'register successfully',
                'passed' => true,
                'error' => null,
                'duration' => 2.314,
                'results' => [],
                'critical' => false
            ],
        ]
    ])]
    #[Response(status: 422, description: 'Validation error', content: [
        'status' => false,
        'message' => 'Validation error',
        'errors' => [
            'phone' => ['The phone field is required.']
        ]
    ])]
    public function all(Request $request)
    {
        $result = $this->service->all($request->attributes->get('profile'));

        return $result->status
            ? $this->success($result->data, $result->message)
            : $this->error($result->message, $result->data);
    }

    #[Endpoint(
        title: 'Login User',
        description: 'Login with phone and password'
    )]
    #[Header('Accept', example: 'application/json')]
    #[Response(status: 200, description: 'Logged in', content: [
        'status' => true,
        'message' => 'Logged in',
        'data' => [
            'token' => '1|abcdefg'
        ]
    ])]
    #[Response(status: 401, description: 'Invalid credentials', content: [
        'status' => false,
        'message' => 'Invalid credentials'
    ])]
    public function login(TestLoginRequest $request)
    {
        $result = $this->service->login($request->attributes->get('profile'));

        return $result->status
            ? $this->success($result->data, $result->message)
            : $this->error($result->message, $result->data);
    }

    #[Endpoint('Register User')]
    #[Header('Accept', example: 'application/json')]
    #[BodyParam('name', 'string', 'User name', example: 'Ali')]
    #[BodyParam('phone', 'string', 'User phone', example: '09123456789')]
    #[BodyParam('password', 'string', 'Password (min 6 chars)', example: '12345678')]

    #[Response(status: 201, description: 'Registered', content: [
        'status' => true,
        'message' => 'Registered',
        'data' => [
            'user_id' => 1
        ]
    ])]
    public function register(Request $request)
    {
        $result = $this->service->register($request->attributes->get('profile'));

        return $result->status
            ? $this->success($result->data, $result->message)
            : $this->error($result->message, $result->data);
    }

    #[Endpoint('Logout User')]
    #[Authenticated]
    #[Header('Authorization', example: 'Bearer 1|abcdefg')]
    #[Response(status: 200, description: 'Logged out', content: [
        'status' => true,
        'message' => 'Logged out'
    ])]
    public function logout(Request $request)
    {
        $this->service->logout($request->attributes->get('profile'));

        return $this->success([], 'Logged out');
    }
}
