<?php

namespace Intranet\Http\Controllers\API\Auth;

use JWTAuth;
use Response;
use Illuminate\Http\Request;
use Intranet\Http\Services\Auth\AuthService;
use Intranet\Exceptions\InvalidCredentialsException;
use Illuminate\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function authenticate(Request $request)
    {
        try {
            $user = $this->authService->attemp($request->only('user', 'password'));
        } catch (InvalidCredentialsException $e) {
            return Response::json([
                    'error'   => 'invalid_credentials',
                    'message' => $e->getMessage()
                ], $e->getCode());
        }

        try {
            $token = JWTAuth::fromUser($user);
        } catch (Exception $e) {
            return Response::json([
                'error'   => 'could_not_create_token',
                'message' => 'There was a problem in the server',
            ], 500);
        }

        $user->load('accreditor');

        return Response::json(compact('token', 'user'));
    }

}