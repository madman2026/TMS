<?php

namespace App\Http\Controllers;

use App\Http\Requests\RunAllTestRequest;
use App\Models\Profile;
use Modules\Auth\Services\AuthService;

class MainController extends Controller
{
    public function __construct(
        public AuthService $auth_service,
    ){}
    public function __invoke(RunAllTestRequest $request)
    {
        $profile = $request->attributes->get('profile');
        if (!is_null($request->group))
            foreach ($request->group as $name) {
                $result[] = match ($name) {
                    'auth' => $this->auth_service->all($profile),
                    'auth' => $this->auth_service->all($profile),
                    'auth' => $this->auth_service->all($profile),
                    default => $this->runAllTests($profile),
                };
            }

        $result['auth group'] = $this->auth_service->all($profile);
        foreach ($result as $service_result)
        {
            if ($service_result->status)
            {
                continue;
            }
            return $this->error('tests failed' , $result);
        }
        return $this->success($result , 'testing successful');
    }

    protected function runAllTests(Profile $profile)
    {
        $result = [
            'auth group' => $this->auth_service->all($profile)
        ];
        return $result;
    }
}
