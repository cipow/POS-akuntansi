<?php

namespace App\Http\Support;

use Illuminate\Support\Facades\Hash;
use App\Models\User\User;
use App\Models\User\Token;
use JWTFirebase;
use Exception;

class JWT
{

  public static function attempt($req) {
    $jwt = new JWT;
    $user = $jwt->email((object) $req);

    $jti = str_random(10);

    $payload = [
      'iss' => 'POS akuntansi',
      'iat' => time(),
      'exp' => time() + (((60 * 60) * 24) * 365),
      'sub' => $user->id,
      'jti' => $jti
    ];

    $token = JWTFirebase::encode($payload, env('JWT_SECRET'));

    try {
      Token::create([
        'id' => $jti,
        'token' => $token
      ]);
      return $token;
    } catch (Exception $e) {
      return response($e->getMessage(), 500);
    }

  }

  public static function parse($header) {
    list($token) = sscanf($header, 'Bearer %s');

    if (!$token) {
      throw new Exception("Invalid Authorization", 1);
    }

    $decode = JWTFirebase::decode($token, env('JWT_SECRET'), ['HS256']);
    $provide = Token::where('id', $decode->jti)->first();

    if (!$provide) {
      throw new Exception("Token Not Provided", 1);
    }

    return User::findOrFail($decode->sub);
  }

  public static function invalidate($token) {
    $decode = JWTFirebase::decode($token, env('JWT_SECRET'), ['HS256']);
    $provide = Token::where('id', $decode->jti)->first();
    $provide->delete();
    return true;
  }

  private function email($req) {
    if ($user = User::where('email', $req->email)->first()) {
      if (Hash::check($req->password, $user->password)) {
        return $user;
      }
    }

    throw new Exception("Invalid email or password", 1);
  }


}
