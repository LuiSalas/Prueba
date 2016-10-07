<?php

namespace Intranet\Http\Controllers\API\User;

use JWTAuth;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller as BaseController;

class UserController extends BaseController
{
    use Helpers;

    public function getUserInfo()
    {
        $user =  JWTAuth::parseToken()->authenticate();
        $user->load('professor');

        return $user;
    }
}
