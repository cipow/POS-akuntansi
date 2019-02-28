<?php

namespace App\Http\Middleware;

use Closure;
use Res;

class Modal
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
      if ($request->user->modal == 0) return (new Res())->messageError("Nilai Modal Kosong", 403);
      return $next($request);
    }
}
