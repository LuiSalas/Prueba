<?php

namespace Intranet\Http\Services\Auth;

use Hash;
use Intranet\Models\User;
use Intranet\Models\Teacher;
use Intranet\Models\Accreditor;
use Intranet\Exceptions\InvalidCredentialsException;

class AuthService
{

    public function attemp($credentials)
    {
        $user = User::where('Usuario', $credentials['user'])->first();

        if ( is_null($user) || ! Hash::check($credentials['password'], $user->Contrasena) ){
            throw new InvalidCredentialsException;
        }

        return $user;
    }

    public function findTeacher($id) {
		$teacher = Teacher::where('IdUsuario',$id)->first();
		return $teacher;
	}

	public function findAccreditor($id) {
		$acrreditor = Accreditor::where('IdUsuario',$id)->first();
		return $acrreditor;
	}

}