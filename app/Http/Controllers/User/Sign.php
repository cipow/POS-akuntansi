<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\User\User;
use Exception;
use JWT;


class Sign extends Controller
{

    public function __construct(){
      parent::__construct();
    }

    public function in(Request $req) {
      $rule = [
        'email'     => 'required|email|max:191',
        'password'  => 'required|string|min:6|max:32',
      ];

      if ($invalid = $this->response->validate($req, $rule)) return $invalid;

      try {
        $token = JWT::attempt($req->all());
        return $this->response->data(['token' => $token]);
      } catch (Exception $e) {
        return $this->response->messageError($e->getMessage(), 400);
      }

    }

    public function up(Request $req) {
      $rule = [
        'nama'      => 'required|string|max:191',
        'email'     => 'required|email|unique:users|max:191',
        'password'  => 'required|string|min:6|max:32',
      ];

      if ($invalid = $this->response->validate($req, $rule)) return $invalid;

      try {
        $user = User::create([
          'nama'      => $req->nama,
          'email'     => $req->email,
          'password'  => Hash::make($req->password)
        ]);

        return $this->response->messageSuccess('berhasil daftar, silahkan login.', 201);
      } catch (Exception $e) {
        return $this->response->serverError();
      }

    }
}
