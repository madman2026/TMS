<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuthService;

class MainController extends Controller
{
    public function __construct(
        public AuthService $auth_service,
    ){}
    public function __invoke(Request $request)
    {
        $auth_group_result = $this->auth_service->all($request->attributes->get('profile'));
        return $result->status
            ? $this->success($result->data , $result->message)
            : $this->error($result->message , $result->data);
    }
}
