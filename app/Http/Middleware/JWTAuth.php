<?php

namespace App\Http\Middleware;

use Closure;
use Res;
use Exception;
use JWT;

class JWTAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      try {
        $user = JWT::parse($request->header('Authorization'));
        return $next($request->merge(['user' => $user]));

      } catch (Exception $e) {
        return (new Res())->messageError($e->getMessage(), 401);
      }

    }
}
