<?php

namespace App\Http\Middleware;

use Closure;

class EmailVerifyMiddleware
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
        if (auth()->user()->email_verified_at == null && disable_email_verification() != 1) {
            return $request->expectsJson()
            ? abort(403, 'Your email address is not verified.')
            : redirect()->route('verify');
        } else {
            return $next($request);
        }
    }
}
