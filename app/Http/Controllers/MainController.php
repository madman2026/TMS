<?php

namespace App\Http\Controllers;

use App\Http\Requests\RunAllTestRequest;
use Modules\Auth\Services\AuthService;

class MainController extends Controller
{
    public function __construct(
        public AuthService $auth_service,
    ){}
    public function __invoke(RunAllTestRequest $request)
    {
        $result['auth group'] = $this->auth_service->all($request->attributes->get('profile'));
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
}
